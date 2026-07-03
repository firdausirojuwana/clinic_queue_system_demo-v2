<?php
include 'config/database.php';

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Get total revenue from prescriptions (same as your index page)
$revenue_result = mysqli_query($conn, "SELECT SUM(total_cost) AS total FROM prescription");
$total_revenue = mysqli_fetch_assoc($revenue_result)['total'] ?? 0;

// Get filtered revenue for the date range
$filtered_revenue_result = mysqli_query($conn, "SELECT SUM(total_cost) AS total FROM prescription WHERE DATE(created_at) BETWEEN '$start_date' AND '$end_date'");
$filtered_revenue = mysqli_fetch_assoc($filtered_revenue_result)['total'] ?? 0;

// Get prescription count
$prescription_count = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM prescription"));

// Get appointment statistics
$appointments_query = "SELECT 
                        a.appoint_id,
                        a.patient_id,
                        a.doctor_id,
                        a.queue_number,
                        a.appointment_datetime,
                        a.status,
                        a.symptoms,
                        a.diagnosis,
                        a.blood_pressure,
                        a.temperature,
                        p.name as patient_name,
                        d.name as doctor_name,
                        d.specialty
                       FROM appointment a
                       LEFT JOIN patient p ON a.patient_id = p.patient_id
                       LEFT JOIN doctor d ON a.doctor_id = d.doctor_id
                       WHERE DATE(a.appointment_datetime) BETWEEN '$start_date' AND '$end_date'
                       ORDER BY a.appointment_datetime DESC";

$appointments_result = mysqli_query($conn, $appointments_query);

// Store appointment data
$appointments_data = [];
$total_appointments = 0;
$waiting_count = 0;
$completed_count = 0;
$cancelled_count = 0;

if ($appointments_result) {
    while ($row = mysqli_fetch_assoc($appointments_result)) {
        $appointments_data[] = $row;
        $total_appointments++;
        
        if ($row['status'] == 'Waiting') {
            $waiting_count++;
        } elseif ($row['status'] == 'Completed') {
            $completed_count++;
        } elseif ($row['status'] == 'Cancelled') {
            $cancelled_count++;
        }
    }
}

// Get doctor performance
$doctor_performance_query = "SELECT 
                              d.doctor_id,
                              d.name as doctor_name,
                              d.specialty,
                              COUNT(a.appoint_id) as total_appointments,
                              SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed_appointments
                             FROM doctor d
                             LEFT JOIN appointment a ON d.doctor_id = a.doctor_id
                             WHERE DATE(a.appointment_datetime) BETWEEN '$start_date' AND '$end_date'
                             GROUP BY d.doctor_id
                             ORDER BY total_appointments DESC";

$doctor_performance = mysqli_query($conn, $doctor_performance_query);

// Get daily statistics
$daily_stats_query = "SELECT 
                        DATE(a.appointment_datetime) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN a.status = 'Waiting' THEN 1 ELSE 0 END) as waiting,
                        SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed
                       FROM appointment a
                       WHERE DATE(a.appointment_datetime) BETWEEN '$start_date' AND '$end_date'
                       GROUP BY DATE(a.appointment_datetime)
                       ORDER BY date DESC
                       LIMIT 10";

$daily_stats = mysqli_query($conn, $daily_stats_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sales Report</title>

    <style>
        body {
            font-family: Arial;
            background: #f4f6f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
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

        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .filter-form {
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
            font-size: 14px;
        }

        .filter-group input {
            padding: 8px 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .btn-filter {
            padding: 8px 20px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-filter:hover {
            background: #1f2d3a;
        }

        .btn-reset {
            padding: 8px 20px;
            background: #7f8c8d;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-reset:hover {
            background: #6c7a7d;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .summary-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            border-left: 4px solid #2c3e50;
        }

        .summary-card .label {
            font-size: 14px;
            color: #7f8c8d;
            margin-bottom: 5px;
        }

        .summary-card .value {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .summary-card .value.green { color: #27ae60; }
        .summary-card .value.blue { color: #3498db; }
        .summary-card .value.orange { color: #f39c12; }
        .summary-card .value.red { color: #e74c3c; }
        .summary-card .value.purple { color: #9b59b6; }
        .summary-card .value.gold { color: #f1c40f; }

        .report-section {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }

        tr:hover {
            background: #f5f5f5;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-waiting { background: #fff3cd; color: #856404; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }

        .doctor-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }

        .doctor-item:last-child {
            border-bottom: none;
        }

        .doctor-name {
            font-weight: bold;
            color: #2c3e50;
        }

        .doctor-specialty {
            color: #7f8c8d;
            font-size: 14px;
        }

        .doctor-stats {
            color: #7f8c8d;
            font-size: 14px;
        }

        .doctor-completed {
            color: #27ae60;
            font-weight: bold;
        }

        .back-container {
            text-align: center;
            margin-top: 20px;
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

        .no-data {
            text-align: center;
            padding: 30px;
            color: #7f8c8d;
        }

        .export-btn {
            padding: 8px 15px;
            background: #27ae60;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-size: 14px;
        }

        .export-btn:hover {
            background: #229954;
        }

        .daily-stats {
            margin-top: 15px;
        }

        .daily-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }

        .daily-item .date {
            font-weight: bold;
            color: #2c3e50;
        }

        .daily-item .counts {
            color: #7f8c8d;
        }

        .conversion-rate {
            margin-top: 15px;
            padding: 15px;
            background: #e8f4f8;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 25px;
        }

        .conversion-rate .rate-value {
            font-size: 32px;
            font-weight: bold;
            color: #2c3e50;
        }

        .conversion-rate .rate-label {
            font-size: 14px;
            color: #7f8c8d;
        }

        .card {
            background: linear-gradient(135deg, #2c3e50, #1a252f);
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin-top: 20px;
            color: white;
        }

        .card h3 {
            font-size: 14px;
            text-transform: uppercase;
            opacity: 0.8;
            margin-bottom: 10px;
            color: #fff;
        }

        .card .number {
            font-size: 36px;
            font-weight: bold;
            color: #f1c40f;
        }

        @media (max-width: 768px) {
            .report-section {
                grid-template-columns: 1fr;
            }
            
            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h2>📊 Sales Report</h2>

    <!-- Filter Section -->
    <div class="filter-section">
        <form method="GET" class="filter-form">
            <div class="filter-group">
                <label>Start Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>">
            </div>
            
            <div class="filter-group">
                <label>End Date</label>
                <input type="date" name="end_date" value="<?php echo $end_date; ?>">
            </div>
            
            <div class="filter-group">
                <button type="submit" class="btn-filter">🔍 Filter</button>
            </div>
            
            <div class="filter-group">
                <a href="sales_report.php" class="btn-reset">🔄 Reset</a>
            </div>
            
            <div class="filter-group" style="margin-left: auto;">
                <a href="#" class="export-btn" onclick="window.print()">🖨️ Print Report</a>
            </div>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="label">Total Appointments</div>
            <div class="value blue"><?php echo $total_appointments; ?></div>
        </div>
        <div class="summary-card">
            <div class="label">Waiting</div>
            <div class="value orange"><?php echo $waiting_count; ?></div>
        </div>
        <div class="summary-card">
            <div class="label">Completed</div>
            <div class="value green"><?php echo $completed_count; ?></div>
        </div>
        <div class="summary-card">
            <div class="label">Total Prescriptions</div>
            <div class="value purple"><?php echo $prescription_count; ?></div>
        </div>
    </div>

    <!-- Conversion Rate -->
    <?php if ($total_appointments > 0) { 
        $conversion_rate = round(($completed_count / $total_appointments) * 100, 1);
    ?>
        <div class="conversion-rate">
            <div class="rate-label">Appointment Completion Rate</div>
            <div class="rate-value"><?php echo $conversion_rate; ?>%</div>
            <div style="font-size: 14px; color: #7f8c8d; margin-top: 5px;">
                <?php echo $completed_count; ?> completed out of <?php echo $total_appointments; ?> total appointments
            </div>
        </div>
    <?php } ?>

    <!-- Main Report -->
    <div class="report-section">
        <!-- Appointments Table -->
        <div>
            <div class="section-title">
                Appointment Details
                <span style="float: right; font-size: 14px; color: #7f8c8d;">
                    <?php echo $total_appointments; ?> appointments found
                </span>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Date & Time</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Queue #</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($appointments_data) > 0) { ?>
                        <?php foreach ($appointments_data as $appointment) { ?>
                            <tr>
                                <td>#<?php echo $appointment['appoint_id']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($appointment['appointment_datetime'])); ?></td>
                                <td><?php echo htmlspecialchars($appointment['patient_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($appointment['doctor_name'] ?? 'N/A'); ?></td>
                                <td class="text-center"><?php echo $appointment['queue_number']; ?></td>
                                <td>
                                    <?php if ($appointment['status'] == 'Waiting') { ?>
                                        <span class="badge badge-waiting">⏳ Waiting</span>
                                    <?php } elseif ($appointment['status'] == 'Completed') { ?>
                                        <span class="badge badge-completed">✅ Completed</span>
                                    <?php } elseif ($appointment['status'] == 'Cancelled') { ?>
                                        <span class="badge badge-cancelled">❌ Cancelled</span>
                                    <?php } else { ?>
                                        <span class="badge badge-waiting"><?php echo $appointment['status']; ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                    <?php } else { ?>
                        <tr>
                            <td colspan="6" class="no-data">No appointments found for the selected period.</td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>

        <!-- Sidebar: Doctor Performance & Daily Stats -->
        <div>
            <div class="section-title">👨‍⚕️ Doctor Performance</div>
            
            <?php if ($doctor_performance && mysqli_num_rows($doctor_performance) > 0) { ?>
                <?php 
                $rank = 1;
                while ($doctor = mysqli_fetch_assoc($doctor_performance)) { 
                ?>
                    <div class="doctor-item">
                        <div>
                            <div class="doctor-name">
                                #<?php echo $rank; ?> <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                            </div>
                            <div class="doctor-specialty">
                                <?php echo htmlspecialchars($doctor['specialty']); ?>
                            </div>
                            <div class="doctor-stats">
                                <?php echo $doctor['total_appointments']; ?> appointments
                            </div>
                        </div>
                        <div class="doctor-completed">
                            <?php echo $doctor['completed_appointments']; ?> completed
                        </div>
                    </div>
                <?php 
                    $rank++;
                } 
                ?>
            <?php } else { ?>
                <div class="no-data">No doctor data found</div>
            <?php } ?>

            <!-- Daily Statistics -->
            <div class="section-title" style="margin-top: 25px;">📅 Daily Statistics</div>
            
            <?php if ($daily_stats && mysqli_num_rows($daily_stats) > 0) { ?>
                <div class="daily-stats">
                    <?php while ($day = mysqli_fetch_assoc($daily_stats)) { ?>
                        <div class="daily-item">
                            <span class="date"><?php echo date('d M Y', strtotime($day['date'])); ?></span>
                            <span class="counts">
                                Total: <?php echo $day['total']; ?> | 
                                <span style="color: #f39c12;">Waiting: <?php echo $day['waiting']; ?></span> | 
                                <span style="color: #27ae60;">Completed: <?php echo $day['completed']; ?></span>
                            </span>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="no-data">No daily data found</div>
            <?php } ?>

            <!-- Report Info -->
            <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 5px;">
                <div style="font-weight: bold; color: #2c3e50;">Report Period</div>
                <div style="font-size: 14px; color: #7f8c8d; margin-top: 5px;">
                    <?php echo date('d M Y', strtotime($start_date)); ?> - 
                    <?php echo date('d M Y', strtotime($end_date)); ?>
                </div>
                <div style="font-size: 14px; color: #7f8c8d; margin-top: 5px;">
                    Generated: <?php echo date('d/m/Y H:i:s'); ?>
                </div>
                <div style="font-size: 14px; color: #7f8c8d; margin-top: 5px;">
                    Filtered Revenue: RM <?php echo number_format($filtered_revenue, 2); ?>
                </div>
            </div>

            <!-- Total Revenue Card -->
            <div class="card">
                <h3>💰 Total Revenue</h3>
                <div class="number">RM <?php echo number_format($total_revenue, 2); ?></div>
            </div>
        </div>
    </div>

    <div class="back-container">
        <a href="appointment/add_appointment.php" class="back back1">Add Appointment</a>
        <a href="index.php" class="back back2">Menu</a>
    </div>

</div>

</body>
</html>