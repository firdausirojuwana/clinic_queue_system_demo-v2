<?php
include '../config/database.php';

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $query = "DELETE FROM patient WHERE patient_id = $id";

    mysqli_query($conn, $query);
}

header("Location: view_patients.php");
exit();
?>