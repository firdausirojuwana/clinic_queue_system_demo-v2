<?php
include '../config/database.php';

$id = $_GET['id'];

$data = mysqli_fetch_assoc(mysqli_query($conn, "
SELECT * FROM appointment WHERE appoint_id=$id
"));

$patients = mysqli_query($conn, "SELECT * FROM patient");
$doctors = mysqli_query($conn, "SELECT * FROM doctor");

if (isset($_POST['update'])) {

    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $queue_number = $_POST['queue_number'];
    $appointment_datetime = $_POST['appointment_datetime'];
    $status = $_POST['status'];
    $symptoms = $_POST['symptoms'];

    mysqli_query($conn, "
        UPDATE appointment SET
        patient_id='$patient_id',
        doctor_id='$doctor_id',
        queue_number='$queue_number',
        appointment_datetime='$appointment_datetime',
        status='$status',
        symptoms='$symptoms'
        WHERE appoint_id=$id
    ");

    header("Location: view_queue.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Appointment</title>
</head>
<body>

<h2>Edit Appointment</h2>

<form method="POST">

    Patient<br>
    <select name="patient_id">
        <?php while ($p = mysqli_fetch_assoc($patients)) { ?>
            <option value="<?php echo $p['patient_id']; ?>"
                <?php if ($p['patient_id'] == $data['patient_id']) echo "selected"; ?>>
                <?php echo $p['name']; ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    Doctor<br>
    <select name="doctor_id">
        <?php while ($d = mysqli_fetch_assoc($doctors)) { ?>
            <option value="<?php echo $d['doctor_id']; ?>"
                <?php if ($d['doctor_id'] == $data['doctor_id']) echo "selected"; ?>>
                <?php echo $d['name']; ?>
            </option>
        <?php } ?>
    </select>

    <br><br>

    Queue Number<br>
    <input type="number" name="queue_number"
        value="<?php echo $data['queue_number']; ?>">

    <br><br>

    Date & Time<br>
    <input type="datetime-local" name="appointment_datetime"
        value="<?php echo date('Y-m-d\TH:i', strtotime($data['appointment_datetime'])); ?>">

    <br><br>

    Status<br>
    <select name="status">
        <option>Waiting</option>
        <option>In Consultation</option>
        <option>Completed</option>
        <option>Cancelled</option>
    </select>

    <br><br>

    Symptoms<br>
    <textarea name="symptoms"><?php echo $data['symptoms']; ?></textarea>

    <br><br>

    <button type="submit" name="update">Update</button>

</form>

</body>
</html>