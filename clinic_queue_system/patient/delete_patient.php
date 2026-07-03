<?php
include '../config/database.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // ===== AUTOMATIC: Check if patient has appointments =====
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointment WHERE patient_id = '$id'");
    $has_appointments = mysqli_fetch_assoc($check)['total'];
    
    if ($has_appointments > 0) {
        header("Location: view_patients.php?error=cannot_delete");
        exit();
    }
    // =====================================================
    
    $stmt = mysqli_prepare($conn, "DELETE FROM patient WHERE patient_id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    
    header("Location: view_patients.php?deleted=1");
    exit();
}

header("Location: view_patients.php");
exit();
?>