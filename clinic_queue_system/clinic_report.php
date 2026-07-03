<?php
include 'config/database.php';

// Get filter parameters
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01');
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// ===== AUTOMATIC: Auto-update appointment status =====
$current_time = date('Y-m-d H:i:s');
mysqli_query($conn, "
    UPDATE appointment 
    SET status = 'Completed' 
    WHERE appointment_datetime < '$current_time' 
    AND status NOT IN ('Completed', 'Cancelled')
");

// ===== FIXED: Use correct column names (appoint_id, patient_id, doctor_id) =====
$appointments_query = "SELECT 
                        a.appoint_id as appointment_id,
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
                        d.specialty,
                        d.room_number
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
$consultation_count = 0;
$completed_count = 0;
$cancelled_count = 0;

if ($appointments_result) {
    while ($row = mysqli_fetch_assoc($appointments_result)) {
        $appointments_data[] = $row;
        $total_appointments++;
        
        if ($row['status'] == 'Waiting') {
            $waiting_count++;
        } elseif ($row['status'] == 'In Consultation') {
            $consultation_count++;
        } elseif ($row['status'] == 'Completed') {
            $completed_count++;
        } elseif ($row['status'] == 'Cancelled') {
            $cancelled_count++;
        }
    }
}

// ===== FIXED: Use correct column names (doctor_id, appoint_id) =====
$doctor_performance_query = "SELECT 
                              d.doctor_id,
                              d.name as doctor_name,
                              d.specialty,
                              d.room_number,
                              COUNT(a.appoint_id) as total_appointments,
                              SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed_appointments,
                              SUM(CASE WHEN a.status = 'Waiting' THEN 1 ELSE 0 END) as waiting_appointments,
                              ROUND(AVG(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) * 100, 2) as completion_rate
                             FROM doctor d
                             LEFT JOIN appointment a ON d.doctor_id = a.doctor_id
                                 AND DATE(a.appointment_datetime) BETWEEN '$start_date' AND '$end_date'
                             GROUP BY d.doctor_id
                             ORDER BY total_appointments DESC";

$doctor_performance = mysqli_query($conn, $doctor_performance_query);

// ===== FIXED: Use correct column names =====
$daily_stats_query = "SELECT 
                        DATE(a.appointment_datetime) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN a.status = 'Waiting' THEN 1 ELSE 0 END) as waiting,
                        SUM(CASE WHEN a.status = 'In Consultation' THEN 1 ELSE 0 END) as consultation,
                        SUM(CASE WHEN a.status = 'Completed' THEN 1 ELSE 0 END) as completed,
                        SUM(CASE WHEN a.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
                       FROM appointment a
                       WHERE DATE(a.appointment_datetime) BETWEEN '$start_date' AND '$end_date'
                       GROUP BY DATE(a.appointment_datetime)
                       ORDER BY date DESC
                       LIMIT 10";

$daily_stats = mysqli_query($conn, $daily_stats_query);

// ===== FIXED: Use correct column name (presc_date, not created_at) =====
$revenue_query = "SELECT 
                    SUM(total_cost) as total_revenue,
                    COUNT(*) as total_prescriptions,
                    AVG(total_cost) as avg_cost
                   FROM prescription
                   WHERE DATE(presc_date) BETWEEN '$start_date' AND '$end_date'";

$revenue_result = mysqli_query($conn, $revenue_query);
$revenue_data = mysqli_fetch_assoc($revenue_result);

// ===== AUTOMATIC: Unique patients =====
$unique_patients = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(DISTINCT patient_id) as total 
    FROM appointment 
    WHERE DATE(appointment_datetime) BETWEEN '$start_date' AND '$end_date'
"))['total'];

// ===== AUTOMATIC: Low stock count =====
$low_stock_count = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM medicine 
    WHERE stock_quantity <= 10
"))['total'];

// Calculate date difference
$diff = date_diff(date_create($start_date), date_create($end_date));
$days = $diff->days + 1;
$avg_revenue_per_day = $days > 0 ? ($revenue_data['total_revenue'] ?? 0) / $days : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="60">
    <title>Clinic Report</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1300px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .filter-section { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .filter-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; }
        .filter-group { display: flex; flex-direction: column; }
        .filter-group label { font-weight: bold; color: #2c3e50; margin-bottom: 5px; font-size: 14px; }
        .filter-group input { padding: 8px 12px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
        .btn-filter { padding: 8px 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .btn-filter:hover { background: #1f2d3a; }
        .btn-reset { padding: 8px 20px; background: #7f8c8d; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn-reset:hover { background: #6c7a7d; }
        .btn-export { padding: 8px 20px; background: #27ae60; color: white; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; font-size: 14px; }
        .btn-export:hover { background: #229954; }
        .summary-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .summary-card { background: #f8f9fa; padding: 15px; border-radius: 5px; text-align: center; border-left: 4px solid #2c3e50; }
        .summary-card .label { font-size: 12px; color: #7f8c8d; margin-bottom: 5px; text-transform: uppercase; letter-spacing: 0.5px; }
        .summary-card .value { font-size: 24px; font-weight: bold; color: #2c3e50; }
        .summary-card .value.green { color: #27ae60; }
        .summary-card .value.blue { color: #3498db; }
        .summary-card .value.orange { color: #f39c12; }
        .summary-card .value.red { color: #e74c3c; }
        .summary-card .value.purple { color: #9b59b6; }
        .summary-card .value.teal { color: #1abc9c; }
        .summary-card .value.gold { color: #f1c40f; }
        .report-section { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 25px; }
        .section-title { font-size: 18px; font-weight: bold; color: #2c3e50; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #eee; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 10px; text-align: left; font-size: 13px; }
        td { padding: 10px; border-bottom: 1px solid #ddd; font-size: 13px; }
        tr:hover td { background: #f5f5f5; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .badge-waiting { background: #fff3cd; color: #856404; }
        .badge-consultation { background: #cce5ff; color: #004085; }
        .badge-completed { background: #d4edda; color: #155724; }
        .badge-cancelled { background: #f8d7da; color: #721c24; }
        .doctor-item { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; align-items: center; }
        .doctor-name { font-weight: bold; color: #2c3e50; }
        .doctor-specialty { color: #7f8c8d; font-size: 13px; }
        .doctor-stats { color: #7f8c8d; font-size: 13px; }
        .doctor-completed { color: #27ae60; font-weight: bold; }
        .doctor-completion { font-size: 13px; color: #7f8c8d; }
        .back-container { text-align: center; margin-top: 20px; }
        .back { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
        .no-data { text-align: center; padding: 30px; color: #7f8c8d; }
        .daily-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 13px; }
        .daily-item .date { font-weight: bold; color: #2c3e50; }
        .daily-item .counts { color: #7f8c8d; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 15px; }
        .info-box .info-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 14px; }
        .info-box .info-label { color: #7f8c8d; }
        .info-box .info-value { font-weight: bold; color: #2c3e50; }
        .conversion-rate { background: #e8f4f8; border-radius: 10px; padding: 20px; text-align: center; margin-bottom: 25px; border: 2px dashed #3498db; }
        .conversion-rate .rate-value { font-size: 36px; font-weight: bold; color: #2c3e50; }
        .conversion-rate .rate-label { font-size: 14px; color: #7f8c8d; }
        .conversion-rate .rate-detail { font-size: 13px; color: #7f8c8d; margin-top: 5px; }
        @media (max-width: 768px) { .report-section { grid-template-columns: 1fr; } .filter-form { flex-direction: column; align-items: stretch; } .filter-group { width: 100%; } }
        @media print { .btn-filter, .btn-reset, .btn-export, .back-container { display: none; } .filter-section { background: none; border: 1px solid #ddd; } }
    </style>
</head>
<body>

<div class="container">
    <h2>🏥 Clinic Report</h2>
    
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
                <a href="clinic_report.php" class="btn-reset">🔄 Reset</a>
            </div>
            <div class="filter-group">
                <a href="#" class="btn-export" onclick="window.print()">🖨️ Print</a>
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
            <div class="label">⏳ Waiting</div>
            <div class="value orange"><?php echo $waiting_count; ?></div>
        </div>
        <div class="summary-card">
            <div class="label">🩺 In Consultation</div>
            <div class="value purple"><?php echo $consultation_count; ?></div>
        </div>
        <div class="summary-card">
            <div class="label">✅ Completed</div>
            <div class="value green"><?php echo $completed_count; ?></div>
        </div>
        <div class="summary-card">
            <div class="label">❌ Cancelled</div>
            <div class="value red"><?php echo $cancelled_count; ?></div>
        </div>
        <div class="summary-card" style="border-left-color: #f1c40f; background: #fffbf0;">
            <div class="label">💰 Revenue</div>
            <div class="value gold">RM <?php echo number_format($revenue_data['total_revenue'] ?? 0, 2); ?></div>
        </div>
        <div class="summary-card">
            <div class="label">💊 Prescriptions</div>
            <div class="value teal"><?php echo $revenue_data['total_prescriptions'] ?? 0; ?></div>
        </div>
        <div class="summary-card">
            <div class="label">👤 Unique Patients</div>
            <div class="value blue"><?php echo $unique_patients; ?></div>
        </div>
    </div>

    <!-- Conversion Rate -->
    <?php if ($total_appointments > 0) { 
        $conversion_rate = round(($completed_count / $total_appointments) * 100, 1);
    ?>
        <div class="conversion-rate">
            <div class="rate-label">📈 Appointment Completion Rate</div>
            <div class="rate-value"><?php echo $conversion_rate; ?>%</div>
            <div class="rate-detail">
                <?php echo $completed_count; ?> completed out of <?php echo $total_appointments; ?> total appointments
                <?php if ($waiting_count > 0) { ?>
                    | <span style="color: #f39c12;"><?php echo $waiting_count; ?> still waiting</span>
                <?php } ?>
            </div>
        </div>
    <?php } ?>

    <!-- Main Report -->
    <div class="report-section">
        <!-- Appointments Table -->
        <div>
            <div class="section-title">
                📋 Appointments
                <span style="float: right; font-size: 14px; color: #7f8c8d;">
                    <?php echo $total_appointments; ?> appointments found
                </span>
            </div>
            
            <?php if (count($appointments_data) > 0) { ?>
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Date & Time</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Room</th>
                                <th>Queue #</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments_data as $appointment) { ?>
                                <tr>
                                    <td>#<?php echo $appointment['appointment_id']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($appointment['appointment_datetime'])); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['patient_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['doctor_name'] ?? 'N/A'); ?></td>
                                    <td><?php echo $appointment['room_number'] ?? '-'; ?></td>
                                    <td class="text-center"><?php echo $appointment['queue_number']; ?></td>
                                    <td>
                                        <?php
                                        $status = $appointment['status'];
                                        $badge_class = '';
                                        switch ($status) {
                                            case 'Waiting': $badge_class = 'badge-waiting'; break;
                                            case 'In Consultation': $badge_class = 'badge-consultation'; break;
                                            case 'Completed': $badge_class = 'badge-completed'; break;
                                            case 'Cancelled': $badge_class = 'badge-cancelled'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>">
                                            <?php echo $status; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="no-data">No appointments found for the selected period.</div>
            <?php } ?>
        </div>

        <!-- Sidebar -->
        <div>
            <!-- Doctor Performance -->
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
                                <?php if ($doctor['room_number']) { ?>
                                    (Room <?php echo $doctor['room_number']; ?>)
                                <?php } ?>
                            </div>
                            <div class="doctor-stats">
                                <?php echo $doctor['total_appointments']; ?> appointments
                                <?php if ($doctor['waiting_appointments'] > 0) { ?>
                                    | <span style="color: #f39c12;"><?php echo $doctor['waiting_appointments']; ?> waiting</span>
                                <?php } ?>
                            </div>
                        </div>
                        <div style="text-align: right;">
                            <div class="doctor-completed">
                                <?php echo $doctor['completed_appointments']; ?> completed
                            </div>
                            <div class="doctor-completion">
                                <?php echo $doctor['completion_rate']; ?>% rate
                            </div>
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
            <div class="section-title" style="margin-top: 20px;">📅 Daily Statistics</div>
            
            <?php if ($daily_stats && mysqli_num_rows($daily_stats) > 0) { ?>
                <div class="daily-stats">
                    <?php while ($day = mysqli_fetch_assoc($daily_stats)) { ?>
                        <div class="daily-item">
                            <span class="date"><?php echo date('d M Y', strtotime($day['date'])); ?></span>
                            <span class="counts">
                                Total: <?php echo $day['total']; ?>
                                <?php if ($day['waiting'] > 0) { ?>
                                    | <span style="color: #f39c12;">⏳ <?php echo $day['waiting']; ?></span>
                                <?php } ?>
                                <?php if ($day['completed'] > 0) { ?>
                                    | <span style="color: #27ae60;">✅ <?php echo $day['completed']; ?></span>
                                <?php } ?>
                            </span>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div class="no-data">No daily data found</div>
            <?php } ?>

            <!-- Report Info -->
            <div class="info-box">
                <div class="info-row">
                    <span class="info-label">📅 Period</span>
                    <span class="info-value">
                        <?php echo date('d M Y', strtotime($start_date)); ?> - 
                        <?php echo date('d M Y', strtotime($end_date)); ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">📊 Days</span>
                    <span class="info-value"><?php echo $days; ?> days</span>
                </div>
                <div class="info-row">
                    <span class="info-label">💰 Avg Revenue/Day</span>
                    <span class="info-value">RM <?php echo number_format($avg_revenue_per_day, 2); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">⚠️ Low Stock Items</span>
                    <span class="info-value" style="color: <?php echo $low_stock_count > 0 ? '#e74c3c' : '#27ae60'; ?>;">
                        <?php echo $low_stock_count > 0 ? $low_stock_count . ' items' : '✅ All stocked'; ?>
                    </span>
                </div>
                <div class="info-row" style="border-top: 1px solid #ddd; padding-top: 10px; margin-top: 5px;">
                    <span class="info-label">🕐 Generated</span>
                    <span class="info-value"><?php echo date('d/m/Y H:i:s'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="back-container">
        <a href="appointment/add_appointment.php" class="back back1">Add Appointment</a>
        <a href="index.php" class="back back2">Menu</a>
    </div>
</div>

<!-- ===== AUTO-REFRESH ===== -->
<script>
    // Auto-refresh every 60 seconds for real-time data
    setTimeout(function() {
        location.reload();
    }, 60000);
</script>

</body>
</html>