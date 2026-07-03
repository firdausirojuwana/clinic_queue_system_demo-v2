<?php
include '../config/database.php';

if (isset($_POST['submit'])) {

    $name = $_POST['name'];
    $ic_number = $_POST['ic_number'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    $query = "INSERT INTO patient
    (name, ic_number, phone, email, gender, address)
    VALUES
    ('$name', '$ic_number', '$phone', '$email', '$gender', '$address')";

    mysqli_query($conn, $query);

    header("Location: view_patients.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Patient</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Add Patient</h1>

<form method="POST">

    <label>Name</label><br>
    <input type="text" name="name" required><br><br>

    <label>IC Number</label><br>
    <input type="text" name="ic_number" required><br><br>

    <label>Phone</label><br>
    <input type="text" name="phone"><br><br>

    <label>Email</label><br>
    <input type="email" name="email"><br><br>

    <label>Gender</label><br>
    <select name="gender">
        <option>Male</option>
        <option>Female</option>
        <option>Other</option>
    </select>
    <br><br>

    <label>Address</label><br>
    <textarea name="address"></textarea><br><br>

    <button type="submit" name="submit">Save Patient</button>

</form>

<br>
<a href="view_patients.php">Check Queues</a>

<a href="../index.php" style="display:inline-block;padding:10px;background:#333;color:#fff;text-decoration:none;border-radius:5px;">
Back to Menu
</a>

</body>
</html>