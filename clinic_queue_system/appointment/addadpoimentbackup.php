<?php
include '../config/database.php';

$patients = mysqli_query($conn, "SELECT * FROM patient");
$doctors = mysqli_query($conn, "SELECT * FROM doctor");

if (isset($_POST['submit'])) {

    $patient_id = $_POST['patient_id'];
    $doctor_id = $_POST['doctor_id'];
    $queue_number = $_POST['queue_number'];
    $appointment_datetime = $_POST['appointment_datetime'];
    $symptoms = $_POST['symptoms'];

    mysqli_query($conn, "
        INSERT INTO appointment
        (patient_id, doctor_id, queue_number, appointment_datetime, status, symptoms)
        VALUES
        ('$patient_id', '$doctor_id', '$queue_number', '$appointment_datetime', 'Waiting', '$symptoms')
    ");

    header("Location: view_queue.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Appointment</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 500px;
            margin: 50px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
        }

        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin: 6px 0 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 10px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #1f2d3a;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 15px;
            text-decoration: none;
            color: #2c3e50;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Add Appointment</h2>

    <form method="POST">

        <label>Patient</label>
        <select name="patient_id" required>
            <?php while ($p = mysqli_fetch_assoc($patients)) { ?>
                <option value="<?php echo $p['patient_id']; ?>">
                    <?php echo $p['name']; ?>
                </option>
            <?php } ?>
        </select>

        <label>Doctor</label>
        <select name="doctor_id" required>
            <?php while ($d = mysqli_fetch_assoc($doctors)) { ?>
                <option value="<?php echo $d['doctor_id']; ?>">
                    <?php echo $d['name']; ?>
                </option>
            <?php } ?>
        </select>

        <label>Queue Number</label>
        <input type="number" name="queue_number" required>

        <label>Date & Time</label>
        <input type="datetime-local" name="appointment_datetime" required>

        <label>Symptoms</label>
        <textarea name="symptoms"></textarea>

        <button type="submit" name="submit">Save Appointment</button>

    </form>

    <a class="back" href="view_queue.php">Back to Queue</a>

</div>

</body>
</html>