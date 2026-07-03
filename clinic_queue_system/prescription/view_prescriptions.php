<?php
include '../config/database.php';

// ============================================================
// ===== DEFINE VARIABLES =====
// ============================================================
$current_time = date('Y-m-d H:i:s');
$today = date('Y-m-d');

// ============================================================
// ===== SYNC PRESCRIPTIONS WITH APPOINTMENTS =====
// ============================================================

// Sync status from appointments to prescriptions
mysqli_query($conn, "
    UPDATE prescription p
    JOIN appointment a ON p.appoint_id = a.appoint_id
    SET p.status = CASE
        WHEN a.status = 'Completed' THEN 'Ready'
        WHEN a.status = 'In Consultation' THEN 'Ready'
        WHEN a.status = 'Waiting' THEN 'Pending'
        WHEN a.status = 'Cancelled' THEN 'Pending'
        ELSE p.status
    END
");

// Auto-update appointment status based on time
mysqli_query($conn, "
    UPDATE appointment 
    SET status = 'Completed' 
    WHERE appointment_datetime < '$current_time' 
    AND status NOT IN ('Completed', 'Cancelled')
");

// ============================================================
// ===== FUNCTION TO DISPLAY STATUS =====
// ============================================================

function display_status($status) {
    if ($status == 'Ready') {
        return 'Completed';
    }
    return $status;
}

// ============================================================
// ===== GET STATISTICS =====
// ============================================================

$total_prescriptions = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM prescription"))['total'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total_cost) as total FROM prescription"))['total'];
$pending_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM prescription WHERE status = 'Pending'"))['total'];
$ready_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM prescription WHERE status = 'Ready'"))['total'];

// ===== GET TOP MEDICINES =====
$top_medicines = mysqli_query($conn, "
    SELECT 
        m.medicine_id,
        m.medicine_name,
        m.dosage,
        m.unit_price,
        m.stock_quantity,
        COUNT(DISTINCT pm.prescription_id) as prescription_count,
        SUM(pm.quantity) as total_quantity,
        SUM(pm.quantity * m.unit_price) as total_value
    FROM medicine m
    LEFT JOIN prescription_medicine pm ON m.medicine_id = pm.medicine_id
    LEFT JOIN prescription p ON pm.prescription_id = p.prescript_id
    GROUP BY m.medicine_id
    ORDER BY total_quantity DESC
    LIMIT 5
");

// ===== GET DAILY STATS =====
$daily_stats = mysqli_query($conn, "
    SELECT 
        DATE(presc_date) as date,
        COUNT(*) as total,
        SUM(total_cost) as revenue,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'Ready' THEN 1 ELSE 0 END) as ready
    FROM prescription
    WHERE DATE(presc_date) >= DATE_SUB('$today', INTERVAL 7 DAY)
    GROUP BY DATE(presc_date)
    ORDER BY date DESC
");

// ===== GET LOW STOCK ALERT =====
$low_stock_items = mysqli_query($conn, "
    SELECT 
        medicine_name,
        dosage,
        unit_price,
        stock_quantity
    FROM medicine
    WHERE stock_quantity <= 20
    ORDER BY stock_quantity ASC
    LIMIT 10
");
$low_stock_count = mysqli_num_rows($low_stock_items);

// ===== GET OLD PENDING ALERT =====
$pending_old = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT COUNT(*) as count
    FROM prescription 
    WHERE status = 'Pending' 
    AND presc_date < DATE_SUB('$current_time', INTERVAL 3 DAY)
"))['count'];

// ============================================================
// ===== GET PRESCRIPTION DATA =====
// ============================================================

$result = mysqli_query($conn, "
    SELECT 
        pr.prescript_id,
        pr.presc_date,
        pr.total_cost,
        pr.status,
        p.name AS patient_name,
        p.ic_number,
        p.phone AS patient_phone,
        d.name AS doctor_name,
        d.specialty AS doctor_specialty,
        d.room_number,
        a.queue_number,
        a.appointment_datetime,
        a.status as appointment_status,
        (SELECT COUNT(*) FROM prescription_medicine pm WHERE pm.prescription_id = pr.prescript_id) as medicine_count,
        (SELECT GROUP_CONCAT(CONCAT(m.medicine_name, ' (', pm.quantity, 'x)') SEPARATOR ', ')
         FROM prescription_medicine pm 
         JOIN medicine m ON pm.medicine_id = m.medicine_id 
         WHERE pm.prescription_id = pr.prescript_id) as medicine_list
    FROM prescription pr
    JOIN appointment a ON pr.appoint_id = a.appoint_id
    JOIN patient p ON a.patient_id = p.patient_id
    JOIN doctor d ON pr.doctor_id = d.doctor_id
    ORDER BY pr.presc_date DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="refresh" content="60">
    <title>Prescriptions</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; padding: 20px; }
        
        .container { max-width: 1400px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .btn { display: inline-block; padding: 10px 20px; color: white; text-decoration: none; border-radius: 5px; border: none; cursor: pointer; font-size: 14px; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #229954; }
        .btn-primary { background: #2c3e50; }
        .btn-primary:hover { background: #1f2d3a; }
        .btn-warning { background: #f39c12; }
        .btn-warning:hover { background: #d68910; }
        
        .auto-badge { 
            background: #27ae60; 
            color: white; 
            padding: 3px 12px; 
            border-radius: 20px; 
            font-size: 11px; 
            display: inline-block; 
            margin-left: 10px;
        }
        .auto-badge.pending-badge { background: #f39c12; }
        .auto-badge.sync-badge { background: #3498db; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: #f8f9fa; padding: 15px; border-radius: 10px; text-align: center; border-left: 4px solid #2c3e50; }
        .stat-card .number { font-size: 26px; font-weight: bold; color: #2c3e50; }
        .stat-card .label { font-size: 10px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 5px; }
        .stat-card.pending { border-left-color: #f39c12; }
        .stat-card.completed { border-left-color: #27ae60; }
        .stat-card.revenue { border-left-color: #f1c40f; background: #fffbf0; }
        
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th { background: #2c3e50; color: white; padding: 12px 10px; text-align: left; white-space: nowrap; }
        td { padding: 12px 10px; border-bottom: 1px solid #eee; vertical-align: middle; }
        tr:hover td { background: #f5f5f5; }
        
        .badge-status { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: bold; }
        .badge-pending { background: #fff3cd; color: #856404; }
        .badge-completed { background: #d4edda; color: #155724; }
        
        .medicines-list { font-size: 12px; color: #555; max-width: 250px; }
        .medicines-list .med-item { display: inline-block; background: #e8f4f8; padding: 2px 8px; border-radius: 3px; margin: 2px; }
        
        .sidebar-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 25px; }
        @media (max-width: 768px) { .sidebar-grid { grid-template-columns: 1fr; } }
        
        .sidebar-card { background: #f8f9fa; padding: 20px; border-radius: 10px; }
        .sidebar-card h4 { color: #2c3e50; margin-bottom: 15px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .sidebar-card .item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 13px; }
        .sidebar-card .item:last-child { border-bottom: none; }
        
        .daily-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; font-size: 13px; }
        .daily-item .date { font-weight: bold; color: #2c3e50; }
        .daily-item .revenue { color: #27ae60; font-weight: bold; }
        
        .low-stock-item { display: flex; justify-content: space-between; padding: 10px; border-radius: 5px; margin-bottom: 5px; }
        .low-stock-item.critical { background: #fde8e8; border-left: 3px solid #e74c3c; }
        .low-stock-item.low { background: #fff3cd; border-left: 3px solid #f39c12; }
        .low-stock-item.medium { background: #e8f4f8; border-left: 3px solid #3498db; }
        .low-stock-item .med-name { font-weight: bold; }
        .low-stock-item .stock { font-weight: bold; }
        
        .back { display: inline-block; margin-top: 20px; text-decoration: none; color: white; background: #2c3e50; padding: 10px 15px; border-radius: 5px; }
        .back:hover { background: #1f2d3a; }
        .no-data { text-align: center; padding: 30px; color: #7f8c8d; }
        
        .alert-box { background: #fde8e8; border: 1px solid #f5c6cb; padding: 12px 20px; border-radius: 5px; margin-bottom: 20px; color: #721c24; }
        .alert-box strong { color: #e74c3c; }
        
        .info-text { font-size: 12px; color: #7f8c8d; text-align: center; margin-top: 10px; }
        .info-text .auto { color: #27ae60; font-weight: bold; }
        
        @media print { .btn { display: none; } .auto-badge { display: none; } }
    </style>
</head>
<body>

<div class="container">
    <h2>
        💊 Prescriptions
        <span class="auto-badge" id="autoBadge">
            🔄 <span id="countdown">60</span>s
        </span>
        <span class="auto-badge sync-badge">🔗 Synced with Appointments</span>
        <?php if ($pending_old > 0) { ?>
            <span class="auto-badge pending-badge">⚠️ <?php echo $pending_old; ?> old</span>
        <?php } ?>
    </h2>

    <!-- ===== ALERTS ===== -->
    <?php if ($pending_old > 0) { ?>
        <div class="alert-box">
            <strong>⚠️ Alert:</strong> <?php echo $pending_old; ?> prescription(s) pending for more than 3 days.
        </div>
    <?php } ?>

    <?php if ($low_stock_count > 0) { ?>
        <div class="alert-box" style="background: #fff3cd; border-color: #ffeeba; color: #856404;">
            <strong>⚠️ Stock Alert:</strong> <?php echo $low_stock_count; ?> medicine(s) have low stock (≤ 20 units).
        </div>
    <?php } ?>

    <!-- ===== STATS ===== -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="number"><?php echo $total_prescriptions; ?></div>
            <div class="label">📋 Total</div>
        </div>
        <div class="stat-card pending">
            <div class="number"><?php echo $pending_count; ?></div>
            <div class="label">⏳ Pending</div>
        </div>
        <div class="stat-card completed">
            <div class="number"><?php echo $ready_count; ?></div>
            <div class="label">✅ Completed</div>
        </div>
        <div class="stat-card revenue">
            <div class="number">RM <?php echo number_format($total_revenue, 2); ?></div>
            <div class="label">💰 Revenue</div>
        </div>
    </div>

    <!-- ===== PRESCRIPTION LIST ===== -->
    <div class="header-actions">
        <span style="color: #7f8c8d; font-size: 13px;">
            🔗 Status synced with appointments · Auto-refresh: 60s
        </span>
        <div>
            <a href="add_prescriptions.php" class="btn btn-success">➕ Add</a>
            <a href="#" class="btn btn-primary" onclick="window.print()">🖨️ Print</a>
            <a href="view_prescriptions.php" class="btn btn-warning">🔄 Refresh</a>
        </div>
    </div>

    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Patient</th>
                    <th>Doctor</th>
                    <th>Medicines</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) { ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) { 
                        $display_status = display_status($row['status']);
                        $status_class = ($row['status'] == 'Ready') ? 'completed' : strtolower($row['status']);
                        $appointment_status = $row['appointment_status'];
                    ?>
                        <tr>
                            <td><strong>#<?php echo $row['prescript_id']; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['patient_name']); ?></strong>
                                <br><small style="color: #7f8c8d; font-size: 11px;">Queue #<?php echo $row['queue_number']; ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($row['doctor_name']); ?>
                                <br><small style="color: #7f8c8d; font-size: 11px;"><?php echo $row['doctor_specialty']; ?></small>
                            </td>
                            <td>
                                <div class="medicines-list">
                                    <?php 
                                    $meds = explode(', ', $row['medicine_list'] ?? '');
                                    $display = array_slice($meds, 0, 2);
                                    foreach ($display as $med) { 
                                        echo '<span class="med-item">' . htmlspecialchars($med) . '</span> ';
                                    }
                                    if (count($meds) > 2) {
                                        echo '<small style="color: #7f8c8d;">+' . (count($meds) - 2) . ' more</small>';
                                    }
                                    if (empty($meds[0])) {
                                        echo '<small style="color: #7f8c8d;">No medicines</small>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td class="text-center"><?php echo $row['medicine_count']; ?></td>
                            <td style="font-weight: bold; color: #2c3e50;">RM <?php echo number_format($row['total_cost'], 2); ?></td>
                            <td>
                                <span class="badge-status badge-<?php echo $status_class; ?>">
                                    <?php echo $display_status; ?>
                                </span>
                                <br>
                                <small style="color: #7f8c8d; font-size: 10px;">
                                    📋 <?php echo $appointment_status; ?>
                                </small>
                            </td>
                            <td style="font-size: 12px; color: #7f8c8d;">
                                <?php echo date('d/m/Y', strtotime($row['presc_date'])); ?>
                                <br><small><?php echo date('H:i', strtotime($row['presc_date'])); ?></small>
                            </td>
                        </tr>
                    <?php } ?>
                <?php } else { ?>
                    <tr><td colspan="8" class="no-data">No prescriptions found. <a href="add_prescriptions.php">Add your first prescription</a></td></tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- ===== SIDEBAR ===== -->
    <div class="sidebar-grid">
        
        <!-- Top Medicines -->
        <div class="sidebar-card">
            <h4>🏆 Top Medicines</h4>
            <?php if (mysqli_num_rows($top_medicines) > 0) { ?>
                <?php while ($med = mysqli_fetch_assoc($top_medicines)) { ?>
                    <div class="item">
                        <span>
                            <span style="font-weight: bold;"><?php echo htmlspecialchars($med['medicine_name']); ?></span>
                            <br><small style="color: #7f8c8d; font-size: 12px;">
                                <?php echo $med['total_quantity']; ?> units · <?php echo $med['prescription_count']; ?> Rx
                            </small>
                        </span>
                        <span style="color: #27ae60; font-weight: bold;">RM <?php echo number_format($med['total_value'], 2); ?></span>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-data" style="padding: 15px;">No data yet</div>
            <?php } ?>
        </div>

        <!-- Daily Stats -->
        <div class="sidebar-card">
            <h4>📅 Last 7 Days</h4>
            <?php if (mysqli_num_rows($daily_stats) > 0) { ?>
                <?php while ($day = mysqli_fetch_assoc($daily_stats)) { ?>
                    <div class="daily-item">
                        <span class="date"><?php echo date('d M', strtotime($day['date'])); ?></span>
                        <span style="color: #7f8c8d; font-size: 12px;">
                            <?php echo $day['total']; ?> Rx
                            <?php if ($day['pending'] > 0) { ?>
                                | <span style="color: #f39c12;">⏳<?php echo $day['pending']; ?></span>
                            <?php } ?>
                            <?php if ($day['ready'] > 0) { ?>
                                | <span style="color: #27ae60;">✅<?php echo $day['ready']; ?></span>
                            <?php } ?>
                        </span>
                        <span class="revenue">RM <?php echo number_format($day['revenue'], 2); ?></span>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="no-data" style="padding: 15px;">No data</div>
            <?php } ?>
        </div>

        <!-- Low Stock Alert - Full Width -->
        <div class="sidebar-card" style="grid-column: 1 / -1;">
            <h4>⚠️ Stock Alert</h4>
            <?php if ($low_stock_count > 0) { ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 5px;">
                    <?php while ($stock = mysqli_fetch_assoc($low_stock_items)) { 
                        $stock_class = $stock['stock_quantity'] <= 5 ? 'critical' : ($stock['stock_quantity'] <= 10 ? 'low' : 'medium');
                    ?>
                        <div class="low-stock-item <?php echo $stock_class; ?>">
                            <span>
                                <span class="med-name"><?php echo htmlspecialchars($stock['medicine_name']); ?></span>
                                <br><small style="color: #7f8c8d; font-size: 11px;"><?php echo $stock['dosage']; ?></small>
                            </span>
                            <span class="stock"><?php echo $stock['stock_quantity']; ?> left</span>
                        </div>
                    <?php } ?>
                </div>
            <?php } else { ?>
                <div style="color: #27ae60; padding: 15px; text-align: center; background: #d4edda; border-radius: 5px;">
                    ✅ All medicines have adequate stock
                </div>
            <?php } ?>
        </div>

    </div>

    <div style="text-align: center; margin-top: 25px;">
        <a class="back" href="../index.php">🏠 Back to Menu</a>
    </div>
    
    <div class="info-text">
        <span class="auto">🔄 Auto-refreshes every 60 seconds</span> · 
        <span class="auto">🔗 Prescription status syncs with appointment status</span>
    </div>
</div>

<!-- ===== COUNTDOWN TIMER ===== -->
<script>
    let countdown = 60;
    const countdownElement = document.getElementById('countdown');
    
    setInterval(function() {
        countdown--;
        countdownElement.textContent = countdown;
        if (countdown <= 0) {
            location.reload();
        }
    }, 1000);
</script>

</body>
</html>