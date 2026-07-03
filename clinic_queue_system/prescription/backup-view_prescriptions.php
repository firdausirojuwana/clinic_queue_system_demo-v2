<?php
include '../config/database.php';

$result = mysqli_query($conn, "
SELECT pr.*, p.name AS patient_name, d.name AS doctor_name
FROM prescription pr
JOIN appointment a ON pr.appoint_id = a.appoint_id
JOIN patient p ON a.patient_id = p.patient_id
JOIN doctor d ON pr.doctor_id = d.doctor_id
ORDER BY pr.prescript_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prescriptions</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
        }

        a.button {
            display: inline-block;
            padding: 10px 15px;
            background: #2c3e50;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
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

        .top {
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

    <h2>💊 Prescriptions</h2>

    <div class="top">
        <a class="button" href="add_prescriptions.php">+ Add Prescription</a>
    </div>

    <table>
        <tr>
            <th>Patient</th>
            <th>Doctor</th>
            <th>Total Cost</th>
            <th>Date</th>
        </tr>

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

        <tr>
            <td><?php echo $row['patient_name']; ?></td>
            <td><?php echo $row['doctor_name']; ?></td>
            <td>RM <?php echo $row['total_cost']; ?></td>
            <td><?php echo $row['presc_date']; ?></td>
        </tr>

        <?php } ?>

    </table>

    <a class="back" href="../index.php">Back to Menu</a>

</div>

</body>
</html>