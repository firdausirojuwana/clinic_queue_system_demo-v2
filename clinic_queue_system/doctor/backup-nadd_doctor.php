<?php
include '../config/database.php';

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialty = mysqli_real_escape_string($conn, $_POST['specialty']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $room = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    $sql = "INSERT INTO doctor (name, specialty, phone, room_number) 
            VALUES ('$name', '$specialty', '$phone', '$room')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: view_doctors.php");
        exit();
    }
}
?>