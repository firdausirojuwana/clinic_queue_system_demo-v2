<?php
include '../config/database.php';

$result = mysqli_query($conn, "
SELECT a.*, p.name AS patient_name, d.name AS doctor_name, d.room_number
FROM appointment a
JOIN patient p ON a.patient_id = p.patient_id
JOIN doctor d ON a.doctor_id = d.doctor_id
ORDER BY a.queue_number ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Appointment Queue</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 95%;
            max-width: 1000px;
            margin: 40px auto;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        a.button {
            display: inline-block;
            padding: 8px 12px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        a.button:hover {
            background: #1f2d3a;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 10px;
        }

        td {
            padding: 10px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f1f1f1;
        }

        .edit {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }

        .delete {
            color: #e74c3c;
            text-decoration: none;
            font-weight: bold;
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .back {
            display: inline-block;
            margin-top: 15px;
            text-decoration: none;
            color: #2c3e50;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Appointment Queue</h2>

    <div class="top-bar">
        <a class="button" href="add_appointment.php">+ Add Appointment</a>
    </div>

    <table>
        <tr>
            <th>Queue</th>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Room</th>
            <th>Status</th>
            <th>Date/Time</th>
            <th>Action</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>
            <td><?php echo $row['queue_number']; ?></td>
            <td><?php echo $row['patient_name']; ?></td>
            <td><?php echo $row['doctor_name']; ?></td>
            <td><?php echo $row['room_number']; ?></td>
            <td><?php echo $row['status']; ?></td>
            <td><?php echo $row['appointment_datetime']; ?></td>

            <td>
                <a class="edit" href="edit_appointment.php?id=<?php echo $row['appoint_id']; ?>">Edit</a>
                |
                <a class="delete"
                   onclick="return confirm('Delete this appointment?');"
                   href="delete_appointment.php?id=<?php echo $row['appoint_id']; ?>">
                   Delete
                </a>
            </td>
        </tr>

        <?php } ?>

    </table>

    <a class="back" href="../index.php">Back to Menu</a>

</div>

</body>
</html>