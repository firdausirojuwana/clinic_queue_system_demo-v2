<?php
include '../config/database.php';

$patients = mysqli_query($conn, "SELECT * FROM patient");
$doctors = mysqli_query($conn, "SELECT * FROM doctor");

if (isset($_POST['submit'])) {

    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_datetime = $_POST['appointment_datetime'];
    $symptoms = $_POST['symptoms'];

    // ===== AUTOMATIC QUEUE NUMBER =====
    $today = date('Y-m-d');
    
    $count_query = mysqli_query($conn, "
        SELECT COUNT(*) as total 
        FROM appointment 
        WHERE doctor_id = '$doctor_id' 
        AND DATE(appointment_datetime) = '$today'
    ");
    
    $count_result = mysqli_fetch_assoc($count_query);
    $queue_number = $count_result['total'] + 1;
    // ==================================

    // Use prepared statement for security
    $stmt = mysqli_prepare($conn, "
        INSERT INTO appointment
        (patient_id, doctor_id, queue_number, appointment_datetime, status, symptoms)
        VALUES (?, ?, ?, ?, 'Waiting', ?)
    ");
    mysqli_stmt_bind_param($stmt, "iiiss", $patient_id, $doctor_id, $queue_number, $appointment_datetime, $symptoms);
    mysqli_stmt_execute($stmt);

    header("Location: view_queue.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Appointment</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; }
        .container { width: 600px; margin: 40px auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        label { display: block; margin-top: 10px; font-weight: bold; color: #2c3e50; }
        input, select, textarea { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        textarea { height: 80px; resize: none; }
        button { width: 100%; padding: 12px; margin-top: 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; }
        button:hover { background: #1f2d3a; }
        .back-container { text-align: center; margin-top: 15px; }
        .back { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
        .disabled-input { background: #f0f0f0; color: #555; }
    </style>
</head>
<body>
<div class="container">
    <h2>🩺 Add Appointment</h2>
    <form method="POST">
        <label>Patient</label>
        <select name="patient_id" required>
            <?php while ($p = mysqli_fetch_assoc($patients)) { ?>
                <option value="<?php echo $p['patient_id']; ?>"><?php echo $p['name']; ?></option>
            <?php } ?>
        </select>
        <label>Doctor</label>
        <select name="doctor_id" required>
            <?php while ($d = mysqli_fetch_assoc($doctors)) { ?>
                <option value="<?php echo $d['doctor_id']; ?>"><?php echo $d['name']; ?></option>
            <?php } ?>
        </select>
        <label>Queue Number</label>
        <input type="text" value="Auto-assigned by system" disabled class="disabled-input">
        <label>Date & Time</label>
        <input type="datetime-local" name="appointment_datetime" required>
        <label>Symptoms</label>
        <textarea name="symptoms"></textarea>
        <button type="submit" name="submit">Save Appointment</button>
    </form>
    <div class="back-container">
        <a href="view_queue.php" class="back back1">Back</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>
</body>
</html>