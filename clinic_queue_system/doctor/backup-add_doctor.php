<?php
include '../config/database.php';

// Fetch existing doctors (optional - for display purposes)
$doctors = mysqli_query($conn, "SELECT * FROM doctor");

if (isset($_POST['submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $specialty = mysqli_real_escape_string($conn, $_POST['specialty']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $room = mysqli_real_escape_string($conn, $_POST['room_number']);
    
    $sql = "INSERT INTO doctor (name, specialty, phone, room_number) 
            VALUES ('$name', '$specialty', '$phone', '$room')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: view_doctors.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Doctor</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            margin: 0;
        }

        .container {
            width: 600px;
            margin: 40px auto;
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #2c3e50;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
            resize: none;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        button:hover {
            background: #1f2d3a;
        }

        .back-container {
            text-align: center;
            margin-top: 15px;
        }

        .back {
            display: inline-block;
            padding: 10px 15px;
            margin: 5px;
            text-decoration: none;
            border-radius: 5px;
            color: white;
        }

        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
    </style>
</head>

<body>

<div class="container">

    <h2>👨‍⚕️ Add New Doctor</h2>

    <form method="POST">

        <label>Doctor Name</label>
        <input type="text" name="name" placeholder="Enter doctor's full name" required>

        <label>Specialty</label>
        <input type="text" name="specialty" placeholder="e.g., Cardiology, Pediatrics" required>

        <label>Phone Number</label>
        <input type="tel" name="phone" placeholder="e.g., 012-3456789" required>

        <label>Room Number</label>
        <input type="text" name="room_number" placeholder="e.g., A-101" required>

        <button type="submit" name="submit">Save Doctor</button>

    </form>

    <div class="back-container">
        <a href="view_doctors.php" class="back back1">View All Doctors</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>

</div>

</body>
</html>