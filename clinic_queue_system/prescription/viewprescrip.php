<?php
include '../config/database.php';

$query = "
SELECT
    pr.prescript_id,
    p.name AS patient_name,
    d.name AS doctor_name,
    pr.presc_date,
    m.medicine_name,
    pm.quantity,
    m.unit_price,
    (pm.quantity * m.unit_price) AS subtotal
FROM prescription pr
JOIN appointment a ON pr.appoint_id = a.appoint_id
JOIN patient p ON a.patient_id = p.patient_id
JOIN doctor d ON pr.doctor_id = d.doctor_id
JOIN prescription_medicine pm ON pr.prescript_id = pm.prescription_id
JOIN medicine m ON pm.medicine_id = m.medicine_id
ORDER BY pr.prescript_id DESC
";

$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Prescriptions</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Prescription List</h1>

<table>
    <tr>
        <th>ID</th>
        <th>Patient</th>
        <th>Doctor</th>
        <th>Medicine</th>
        <th>Quantity</th>
        <th>Unit Price</th>
        <th>Subtotal</th>
        <th>Date</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

    <tr>
        <td><?php echo $row['prescript_id']; ?></td>
        <td><?php echo $row['patient_name']; ?></td>
        <td><?php echo $row['doctor_name']; ?></td>
        <td><?php echo $row['medicine_name']; ?></td>
        <td><?php echo $row['quantity']; ?></td>
        <td><?php echo $row['unit_price']; ?></td>
        <td><?php echo $row['subtotal']; ?></td>
        <td><?php echo $row['presc_date']; ?></td>
    </tr>

    <?php } ?>

</table>

<a href="../index.php" style="display:inline-block;padding:10px;background:#333;color:#fff;text-decoration:none;border-radius:5px;">
Back to Menu
</a>

</body>
</html>