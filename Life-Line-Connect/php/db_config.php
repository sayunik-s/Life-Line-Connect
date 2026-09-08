<?php
// Oracle Database Connection
$ora_user = 'system'; 
$ora_pass = 'admin'; // ඔබගේ නිවැරදි Oracle Password එක මෙහි දෙන්න
$ora_db = 'localhost/XEPDB1'; 

$ora_conn = oci_connect($ora_user, $ora_pass, $ora_db);
if (!$ora_conn) {
    $e = oci_error();
    die(json_encode(['status' => 'error', 'message' => 'Oracle Connection Failed: ' . $e['message']]));
}
?>