<!DOCTYPE html>
<html>
<head>
    <title>Clinic Queue System</title>
    <style>
        /* RESET */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f4f6f9;
            display: flex;
            min-height: 100vh;
        }

        /* ===== LEFT SIDEBAR ===== */
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar .logo {
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid #34495e;
        }

        .sidebar .logo h2 {
            font-size: 18px;
            color: #fff;
        }

        .sidebar .logo small {
            font-size: 12px;
            opacity: 0.7;
        }

        .sidebar .menu-section {
            padding: 15px 20px 5px 20px;
            font-size: 12px;
            text-transform: uppercase;
            color: #7f8c8d;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: block;
            padding: 12px 25px;
            color: #ecf0f1;
            text-decoration: none;
            border-left: 3px solid transparent;
            transition: 0.3s;
        }

        .sidebar a:hover {
            background: #34495e;
            border-left-color: #3498db;
        }

        .sidebar a.active {
            background: #34495e;
            border-left-color: #3498db;
        }

        .sidebar a i {
            margin-right: 10px;
        }

        /* ===== MAIN CONTENT ===== */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: 100%;
        }

        .header {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .header h1 {
            font-size: 24px;
            color: #2c3e50;
        }

        .header p {
            color: #7f8c8d;
            margin-top: 5px;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            text-align: center;
        }

        .card h3 {
            font-size: 14px;
            color: #7f8c8d;
        }

        .card .number {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }

        .card a {
            color: #3498db;
            text-decoration: none;
        }

        .card a:hover {
            text-decoration: underline;
        }

        .footer {
            text-align: center;
            color: #95a5a6;
            margin-top: 40px;
            font-size: 14px;
        }
    </style>
</head>
<body>

<!-- ============================================ -->
<!-- LEFT SIDEBAR                                  -->
<!-- ============================================ -->
<div class="sidebar">
    <div class="logo">
        <h2>🏥 Clinic Queue</h2>
        <small>Management System</small>
    </div>

    <!-- VIEW DATA -->
    <div class="menu-section">📊 View Data</div>
    <a href="appointment/view_queue.php">📋 Queues</a>
    <a href="patient/view_patients.php">👤 Patients</a>
    <a href="prescription/view_prescriptions.php">💊 Prescriptions</a>

    <!-- ADD DATA -->
    <div class="menu-section">➕ Add Data</div>
    <a href="patient/add_patient.php">👤 Add Patient</a>
    <a href="appointment/add_appointment.php">📅 Add Appointment</a>
    <a href="prescription/add_prescriptions.php">💊 Add Prescription</a>

    <!-- NEW: MANAGE DATA -->
    <div class="menu-section">⚙️ Manage</div>
    <a href="doctor/add_doctor.php">👨‍⚕️ Add Doctor</a>
    <a href="medicine/add_medicine.php">💊 Add Medicine</a>
   

    <!-- REPORTS -->
    <div class="menu-section">📈 Reports</div>
    <a href="sales_report.php">💰 Sales Report</a>
</div>

<!-- ============================================ -->
<!-- MAIN CONTENT                                  -->
<!-- ============================================ -->
<div class="main-content">

    <div class="header">
        <h1>🏥 Clinic Queue Management System</h1>
        <p>Welcome to the dashboard. Manage patients, appointments, prescriptions, and more.</p>
    </div>

    <?php
    include 'config/database.php';

    // Count totals
    $patients = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM patient"));
    $appointments = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM appointment"));
    $prescriptions = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prescription"));
    $doctors = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM doctor"));
    $medicines = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM medicine"));

    // Total revenue
    $revenue_result = mysqli_query($conn, "SELECT SUM(total_cost) AS total FROM prescription");
    $total_revenue = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;
    ?>

    <!-- STATISTICS CARDS -->
    <div class="cards">
        <div class="card">
            <h3>👤 Patients</h3>
            <div class="number"><?php echo $patients; ?></div>
            <a href="patient/view_patients.php">View All</a>
        </div>

        <div class="card">
            <h3>📅 Appointments</h3>
            <div class="number"><?php echo $appointments; ?></div>
            <a href="appointment/view_queue.php">View All</a>
        </div>

        <div class="card">
            <h3>💊 Prescriptions</h3>
            <div class="number"><?php echo $prescriptions; ?></div>
            <a href="prescription/view_prescriptions.php">View All</a>
        </div>

        <div class="card">
            <h3>👨‍⚕️ Doctors</h3>
            <div class="number"><?php echo $doctors; ?></div>
            <a href="doctor/view_doctors.php">View All</a>
        </div>

        <div class="card">
            <h3>💊 Medicines</h3>
            <div class="number"><?php echo $medicines; ?></div>
            <a href="medicine/view_medicine.php">View All</a>
        </div>

        <div class="card">
            <h3>💰 Total Revenue</h3>
            <div class="number">RM <?php echo number_format($total_revenue, 2); ?></div>
            <a href="sales_report.php">View Report</a>
        </div>
    </div>

    <div class="footer">
        &copy; <?php echo date('Y'); ?> Clinic Queue Management System | IMD261
    </div>

</div>

</body>
</html>