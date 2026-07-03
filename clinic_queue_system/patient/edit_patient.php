<?php
include '../config/database.php';

$patient_id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM patient WHERE patient_id = '$patient_id'");
$patient = mysqli_fetch_assoc($result);

if (!$patient) {
    header("Location: view_patients.php");
    exit();
}

if (isset($_POST['update'])) {
    $name = $_POST['name'];
    $ic_number = $_POST['ic_number'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    
    $name = ucwords(strtolower(trim($name)));
    $ic_number = preg_replace('/[^0-9]/', '', $ic_number);
    $phone = preg_replace('/[^0-9]/', '', $phone);
    $email = strtolower(trim($email));
    
    $check = mysqli_query($conn, "SELECT * FROM patient WHERE ic_number='$ic_number' AND patient_id != '$patient_id'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('⚠️ Another patient already exists with this IC number!'); window.history.back();</script>";
        exit();
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE patient SET name=?, ic_number=?, phone=?, email=?, gender=?, address=? WHERE patient_id=?");
    mysqli_stmt_bind_param($stmt, "ssssssi", $name, $ic_number, $phone, $email, $gender, $address, $patient_id);
    mysqli_stmt_execute($stmt);
    
    header("Location: view_patients.php?updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Patient</title>
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
        .hint { font-size: 12px; color: #7f8c8d; margin-top: 3px; }
    </style>
</head>
<body>
<div class="container">
    <h2>✏️ Edit Patient</h2>
    <form method="POST">
        <input type="hidden" name="patient_id" value="<?php echo $patient['patient_id']; ?>">
        <label>Full Name *</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($patient['name']); ?>" required>
        <label>IC Number *</label>
        <input type="text" name="ic_number" value="<?php echo htmlspecialchars($patient['ic_number']); ?>" required>
        <div class="hint">Format: YYMMDD-XX-XXXX (e.g., 010101-10-1234)</div>
        <label>Phone Number</label>
        <input type="text" name="phone" value="<?php echo htmlspecialchars($patient['phone'] ?? ''); ?>">
        <label>Email Address</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($patient['email'] ?? ''); ?>">
        <label>Gender</label>
        <select name="gender">
            <option value="Male" <?php if($patient['gender'] == 'Male') echo "selected"; ?>>Male</option>
            <option value="Female" <?php if($patient['gender'] == 'Female') echo "selected"; ?>>Female</option>
            <option value="Other" <?php if($patient['gender'] == 'Other') echo "selected"; ?>>Other</option>
        </select>
        <label>Address</label>
        <textarea name="address"><?php echo htmlspecialchars($patient['address'] ?? ''); ?></textarea>
        <button type="submit" name="update">💾 Update Patient</button>
    </form>
    <div class="back-container">
        <a href="view_patients.php" class="back back1">View All Patients</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>
</body>
</html>