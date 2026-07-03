<?php
include '../config/database.php';

$doctor_id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM doctor WHERE doctor_id = '$doctor_id'");
$doctor = mysqli_fetch_assoc($result);

if (!$doctor) {
    header("Location: view_doctors.php");
    exit();
}

if (isset($_POST['update'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialty = mysqli_real_escape_string($conn, $_POST['specialty']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $room = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    $check = mysqli_query($conn, "SELECT * FROM doctor WHERE name='$name' AND specialty='$specialty' AND doctor_id != '$doctor_id'");
    if (mysqli_num_rows($check) > 0) {
        echo "<script>alert('⚠️ Another doctor already exists with this name and specialty!'); window.history.back();</script>";
        exit();
    }
    
    $stmt = mysqli_prepare($conn, "UPDATE doctor SET name=?, specialty=?, phone=?, room_number=? WHERE doctor_id=?");
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $specialty, $phone, $room, $doctor_id);
    mysqli_stmt_execute($stmt);
    
    header("Location: view_doctors.php?updated=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Doctor</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; }
        .container { width: 600px; margin: 40px auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; font-weight: bold; color: #2c3e50; }
        input { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; margin-top: 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #1f2d3a; }
        .back-container { text-align: center; margin-top: 15px; }
        .back { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
    </style>
</head>
<body>
<div class="container">
    <h2>✏️ Edit Doctor</h2>
    <form method="POST">
        <input type="hidden" name="doctor_id" value="<?php echo $doctor['doctor_id']; ?>">
        <label>Doctor Name</label>
        <input type="text" name="name" value="<?php echo htmlspecialchars($doctor['name']); ?>" required>
        <label>Specialty</label>
        <input type="text" name="specialty" value="<?php echo htmlspecialchars($doctor['specialty']); ?>" required>
        <label>Phone Number</label>
        <input type="tel" name="phone" value="<?php echo htmlspecialchars($doctor['phone']); ?>" required>
        <label>Room Number</label>
        <input type="text" name="room_number" value="<?php echo htmlspecialchars($doctor['room_number']); ?>" required>
        <button type="submit" name="update">Update Doctor</button>
    </form>
    <div class="back-container">
        <a href="view_doctors.php" class="back back1">View All Doctors</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>
</body>
</html>