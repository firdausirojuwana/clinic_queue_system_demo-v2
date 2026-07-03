<?php
include '../config/database.php';

$id = intval($_GET['id']);

$data = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM appointment WHERE appoint_id=$id
"));

$patients = mysqli_query($conn, "SELECT * FROM patient");
$doctors = mysqli_query($conn, "SELECT * FROM doctor");

if (isset($_POST['update'])) {

    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $appointment_datetime = $_POST['appointment_datetime'];
    $status = $_POST['status'];
    $symptoms = $_POST['symptoms'];

    // ===== AUTOMATIC QUEUE NUMBER =====
    $old_doctor = $data['doctor_id'];
    
    if ($old_doctor != $doctor_id) {
        $today = date('Y-m-d');
        $count_query = mysqli_query($conn, "
            SELECT COUNT(*) as total 
            FROM appointment 
            WHERE doctor_id = '$doctor_id' 
            AND DATE(appointment_datetime) = '$today'
            AND appoint_id != $id
        ");
        $count_result = mysqli_fetch_assoc($count_query);
        $queue_number = $count_result['total'] + 1;
    } else {
        $queue_number = $data['queue_number'];
    }
    // ==================================

    $stmt = mysqli_prepare($conn, "
        UPDATE appointment SET
        patient_id=?,
        doctor_id=?,
        queue_number=?,
        appointment_datetime=?,
        status=?,
        symptoms=?
        WHERE appoint_id=?
    ");
    mysqli_stmt_bind_param($stmt, "iiisssi", $patient_id, $doctor_id, $queue_number, $appointment_datetime, $status, $symptoms, $id);
    mysqli_stmt_execute($stmt);

    header("Location: view_queue.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Appointment</title>
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
    <h2>✏️ Edit Appointment</h2>
    <form method="POST">
        <input type="hidden" name="queue_number" value="<?php echo $data['queue_number']; ?>">
        <label>Patient</label>
        <select name="patient_id">
            <?php while ($p = mysqli_fetch_assoc($patients)) { ?>
                <option value="<?php echo $p['patient_id']; ?>" <?php if ($p['patient_id'] == $data['patient_id']) echo "selected"; ?>>
                    <?php echo $p['name']; ?>
                </option>
            <?php } ?>
        </select>
        <label>Doctor</label>
        <select name="doctor_id">
            <?php while ($d = mysqli_fetch_assoc($doctors)) { ?>
                <option value="<?php echo $d['doctor_id']; ?>" <?php if ($d['doctor_id'] == $data['doctor_id']) echo "selected"; ?>>
                    <?php echo $d['name']; ?>
                </option>
            <?php } ?>
        </select>
        <label>Queue Number</label>
        <input type="text" value="Auto-assigned: <?php echo $data['queue_number']; ?>" disabled class="disabled-input">
        <label>Date & Time</label>
        <input type="datetime-local" name="appointment_datetime" value="<?php echo date('Y-m-d\TH:i', strtotime($data['appointment_datetime'])); ?>">
        <label>Status</label>
        <select name="status">
            <option <?php if($data['status']=="Waiting") echo "selected"; ?>>Waiting</option>
            <option <?php if($data['status']=="In Consultation") echo "selected"; ?>>In Consultation</option>
            <option <?php if($data['status']=="Completed") echo "selected"; ?>>Completed</option>
            <option <?php if($data['status']=="Cancelled") echo "selected"; ?>>Cancelled</option>
        </select>
        <label>Symptoms</label>
        <textarea name="symptoms"><?php echo $data['symptoms']; ?></textarea>
        <button type="submit" name="update">Update</button>
    </form>
    <div class="back-container">
        <a href="view_queue.php" class="back back1">Back</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>
</body>
</html>