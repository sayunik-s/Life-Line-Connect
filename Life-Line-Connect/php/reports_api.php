<?php
// JSON widiyata thama api data pass karanne frontend ekata
header('Content-Type: application/json');

// Error reporting (Debug karanna ona unoth witharak error_reporting on karanna, nattan JSON eka kadenawa)
error_reporting(0);
ini_set('display_errors', 0);

// 1. Database Connection (Oyage Oracle DB details)
$db_user = "system"; 
$db_pass = "admin";   
$db_host = "localhost/XEPDB1"; 

$conn = oci_connect($db_user, $db_pass, $db_host);

// Connection eka fail unoth error eka JSON widiyata yawanna
if (!$conn) {
    $e = oci_error();
    echo json_encode(["error" => "Database connection failed: " . $e['message']]);
    exit;
}

// Frontend eken evana 'action' eka ha 'filter_val' eka allaganna
$action = isset($_GET['action']) ? $_GET['action'] : '';
$filter_val = isset($_GET['filter_val']) ? $_GET['filter_val'] : null;

$data = [];
$curs = oci_new_cursor($conn); // Cursor eka hadaganna

try {
    switch ($action) {
        
        case 'units_by_camp':
            $sql = "BEGIN Get_Camp_Collections(:cursor); END;";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ":cursor", $curs, -1, OCI_B_CURSOR);
            break;

        case 'expiring_units':
            // UI eke '7 Days' kiyala thiyena nisa dawas 7 kiyala hardcode karanawa
            $days = 7; 
            $sql = "BEGIN Get_Expiring_Inventory(:days, :cursor); END;";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ":days", $days);
            oci_bind_by_name($stid, ":cursor", $curs, -1, OCI_B_CURSOR);
            break;

        case 'low_stock':
            // Minimum limit eka 15 units kiyala gannawa
            $threshold = 15; 
            $sql = "BEGIN Get_Low_Stock_Alerts(:threshold, :cursor); END;";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ":threshold", $threshold);
            oci_bind_by_name($stid, ":cursor", $curs, -1, OCI_B_CURSOR);
            break;

        case 'donor_eligibility':
            if (empty($filter_val)) {
                echo json_encode(["error" => "Donor ID is required"]);
                exit;
            }
            $donor_id = (int)$filter_val;
            $sql = "BEGIN Get_Donor_Eligibility(:donor_id, :cursor); END;";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ":donor_id", $donor_id);
            oci_bind_by_name($stid, ":cursor", $curs, -1, OCI_B_CURSOR);
            break;

        case 'monthly_performance':
            if (empty($filter_val)) {
                echo json_encode(["error" => "Month is required (1-12)"]);
                exit;
            }
            $month = (int)$filter_val;
            $year = (int)date("Y"); // Current year eka auto gannawa PHP walin
            
            $sql = "BEGIN Get_Monthly_Performance(:year, :month, :cursor); END;";
            $stid = oci_parse($conn, $sql);
            oci_bind_by_name($stid, ":year", $year);
            oci_bind_by_name($stid, ":month", $month);
            oci_bind_by_name($stid, ":cursor", $curs, -1, OCI_B_CURSOR);
            break;

        default:
            echo json_encode(["error" => "Invalid report type requested"]);
            exit;
    }

    // Procedure eka execute karanawa
    if (!oci_execute($stid)) {
        $e = oci_error($stid);
        echo json_encode(["error" => "Failed to execute procedure: " . $e['message']]);
        exit;
    }

    // Cursor eka execute karanawa
    if (!oci_execute($curs)) {
        $e = oci_error($curs);
        echo json_encode(["error" => "Failed to execute cursor: " . $e['message']]);
        exit;
    }

    // Data tika array ekakata loop karala dagannawa
    while (($row = oci_fetch_array($curs, OCI_ASSOC + OCI_RETURN_NULLS)) != false) {
        $data[] = $row;
    }

    // Array eka JSON widiyata print karanawa
    echo json_encode($data);

} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

// Connections close karanawa memory eka free karanna
if (isset($stid)) oci_free_statement($stid);
if (isset($curs)) oci_free_statement($curs);
oci_close($conn);
?>