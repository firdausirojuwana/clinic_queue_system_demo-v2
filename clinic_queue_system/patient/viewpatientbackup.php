<?php
include '../config/database.php';

$result = mysqli_query($conn, "SELECT * FROM patient ORDER BY patient_id DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Patients</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Patient List</h1>

<a href="add_patient.php">+ Add Patient</a>

<br><br>

<table>
    <tr>
        <th>ID</th>
        <th>Name</th>
        <th>IC Number</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Gender</th>
        <th>Action</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)) { ?>

    <tr>
        <td><?php echo $row['patient_id']; ?></td>
        <td><?php echo $row['name']; ?></td>
        <td><?php echo $row['ic_number']; ?></td>
        <td><?php echo $row['phone']; ?></td>
        <td><?php echo $row['email']; ?></td>
        <td><?php echo $row['gender']; ?></td>

        <td> 
            <a href="edit_patient.php?id=<?php echo $row['patient_id']; ?>">Edit</a> |
            <a href="delete_patient.php?id=<?php echo $row['patient_id']; ?>" 
               onclick="return confirm('Delete this patient?');"
               style="color:red;">
               Delete
            </a>
        </td>
    </tr>

    <?php } ?>

</table>

<a href="../index.php" style="display:inline-block;padding:10px;background:#333;color:#fff;text-decoration:none;border-radius:5px;">
Back to Menu
</a>

</body>
</html>