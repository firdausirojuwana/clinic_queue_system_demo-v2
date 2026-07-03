<?php
include '../config/database.php';

$id = intval($_GET['id']);

// Check if appointment has prescriptions
$check = mysqli_query($conn, "SELECT COUNT(*) as total FROM prescription WHERE appoint_id = '$id'");
$has_prescription = mysqli_fetch_assoc($check)['total'];

if ($has_prescription > 0) {
    // First delete prescription_medicine then prescription
    mysqli_query($conn, "DELETE pm FROM prescription_medicine pm 
                         JOIN prescription p ON pm.prescription_id = p.prescript_id 
                         WHERE p.appoint_id = $id");
    mysqli_query($conn, "DELETE FROM prescription WHERE appoint_id = $id");
}

mysqli_query($conn, "DELETE FROM appointment WHERE appoint_id=$id");

header("Location: view_queue.php");
exit();
?>