<?php
include '../config/database.php';

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $ic_number = $_POST['ic_number'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    
    // ===== AUTOMATIC CHECKS =====
    $name = ucwords(strtolower(trim($name)));
    $ic_number = preg_replace('/[^0-9]/', '', $ic_number);
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $email = strtolower(trim($email));
    
    $check = mysqli_query($conn, "SELECT * FROM patient WHERE ic_number = '$ic_number'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('⚠️ A patient with this IC number already exists!'); window.history.back();</script>";
        exit();
    }
    // =================================
    
    $stmt = mysqli_prepare($conn, "INSERT INTO patient (name, ic_number, phone, email, gender, address) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $name, $ic_number, $phone, $email, $gender, $address);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: view_patients.php?success=1");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Patient</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; }
        .container { width: 600px; margin: 40px auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; font-weight: bold; color: #2c3e50; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; box-sizing: border-box; }
        textarea { resize: none; height: 80px; }
        button { width: 100%; padding: 12px; margin-top: 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 15px; }
        button:hover { background: #1f2d3a; }
        .back-container { margin-top: 15px; text-align: center; }
        .back { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; font-size: 14px; }
        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
        .back:hover { opacity: 0.9; }
        .hint { font-size: 12px; color: #7f8c8d; margin-top: 3px; }
    </style>
</head>
<body>
<div class="container">
    <h2>👥 Add Patient</h2>
    <form method="POST">
        <label>Full Name *</label>
        <input type="text" name="name" placeholder="Enter patient's full name" required>
        <label>IC Number *</label>
        <input type="text" name="ic_number" placeholder="e.g., 010101-10-1234" required>
        <div class="hint">Format: YYMMDD-XX-XXXX (e.g., 010101-10-1234)</div>
        <label>Phone Number</label>
        <input type="text" name="phone" placeholder="e.g., 012-3456789">
        <label>Email Address</label>
        <input type="email" name="email" placeholder="patient@email.com">
        <label>Gender</label>
        <select name="gender">
            <option value="Male">Male</option>
            <option value="Female">Female</option>
            <option value="Other">Other</option>
        </select>
        <label>Address</label>
        <textarea name="address" placeholder="Enter patient's address"></textarea>
        <button type="submit" name="submit">💾 Save Patient</button>
    </form>
    <div class="back-container">
        <a href="view_patients.php" class="back back1">View All Patients</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>
</body>
</html>