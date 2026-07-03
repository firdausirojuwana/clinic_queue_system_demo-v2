<!DOCTYPE html>
<html>
<head>
    <title>Clinic Queue System</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }

        .container {
            padding: 30px;
            max-width: 1000px;
            margin: auto;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: 0.2s;
        }

        .card:hover {
            transform: scale(1.03);
        }

        .card a {
            text-decoration: none;
            color: #2c3e50;
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }

        .icon {
            font-size: 40px;
        }
    </style>

</head>
<body>

<div class="header">
    <h1>Clinic Queue Management System</h1>
    <p> </p>
</div>

<div class="container">

    <div class="grid"> 

        <div class="card">
            <div class="icon"> </div>
            <a href="patient/view_patients.php">👥 Patients</a>
        </div>

        <div class="card">
            <div class="icon"> </div>
            <a href="patient/add_patient.php">➕ Add Patient</a>
        </div>

        <div class="card">
            <div class="icon"> </div>
            <a href="appointment/view_queue.php">📋 Queue</a>
        </div>

        <div class="card">
            <div class="icon"> </div>
            <a href="appointment/add_appointment.php">🩺 Add Appointment</a>
        </div>

        <div class="card">
            <div class="icon"> </div>
            <a href="prescription/view_prescriptions.php">💊 Prescriptions</a>
        </div>

    </div>

</div>

</body>
</html>