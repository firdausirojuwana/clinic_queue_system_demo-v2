<?php
include '../config/database.php';

$query = "
SELECT
    a.appoint_id,
    a.queue_number,
    p.name AS patient_name,
    d.name AS doctor_name,
    d.room_number,
    a.status,
    a.appointment_datetime
FROM appointment a
JOIN patient p ON a.patient_id = p.patient_id
JOIN doctor d ON a.doctor_id = d.doctor_id
ORDER BY a.queue_number ASC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Queue</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Today's Queue</h1>

<a href="add_appointment.php">+ Add Appointment</a>

<br><br>

<table>
    <tr>
        <th>Queue</th>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Room</th>
        <th>Status</th>
        <th>Date/Time</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

    <tr>
        <td><?php echo $row['queue_number']; ?></td>
        <td><?php echo $row['patient_name']; ?></td>
        <td><?php echo $row['doctor_name']; ?></td>
        <td><?php echo $row['room_number']; ?></td>
        <td><?php echo $row['status']; ?></td>
        <td><?php echo $row['appointment_datetime']; ?></td>
    </tr>

    <?php } ?>

</table>

<a href="../index.php" style="display:inline-block;padding:10px;background:#333;color:#fff;text-decoration:none;border-radius:5px;">
Back to Menu
</a>

</body>
</html>