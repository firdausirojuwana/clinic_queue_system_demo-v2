<?php
include '../config/database.php';

// ===== AUTOMATIC: Get statistics =====
$total_doctors = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM doctor"))['total'];
$total_appointments = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM appointment"))['total'];

$doctors = mysqli_query($conn, "
    SELECT d.*, 
           (SELECT COUNT(*) FROM appointment a WHERE a.doctor_id = d.doctor_id) as appointment_count
    FROM doctor d 
    ORDER BY d.name
");

if (isset($_GET['delete'])) {
    $doctor_id = intval($_GET['delete']);
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM appointment WHERE doctor_id = '$doctor_id'");
    $has_appointments = mysqli_fetch_assoc($check)['total'];
    
    if ($has_appointments > 0) {
        header("Location: view_doctors.php?error=cannot_delete");
        exit();
    }
    
    mysqli_query($conn, "DELETE FROM doctor WHERE doctor_id = '$doctor_id'");
    header("Location: view_doctors.php?deleted=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Doctors</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add { padding: 10px 20px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; }
        .btn-add:hover { background: #1f2d3a; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; }
        td { padding: 12px; border-bottom: 1px solid #ddd; }
        tr:hover { background: #f5f5f5; }
        .btn-edit { padding: 5px 12px; background: #2c3e50; color: white; text-decoration: none; border-radius: 3px; margin-right: 5px; }
        .btn-edit:hover { background: #1f2d3a; }
        .btn-delete { padding: 5px 12px; background: #e74c3c; color: white; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; }
        .btn-delete:hover { background: #c0392b; }
        .back-container { text-align: center; margin-top: 20px; }
        .back { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
        .no-data { text-align: center; padding: 30px; color: #7f8c8d; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .total-doctors { color: #2c3e50; font-weight: bold; }
        .status-badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .status-active { background: #d4edda; color: #155724; }
        .status-busy { background: #fff3cd; color: #856404; }
        .status-inactive { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
<div class="container">
    <h2>👨‍⚕️ Doctor List</h2>
    
    <?php if (isset($_GET['success'])) { ?>
        <div class="success-msg">✅ Doctor added successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['updated'])) { ?>
        <div class="success-msg">✏️ Doctor updated successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['deleted'])) { ?>
        <div class="success-msg">🗑️ Doctor deleted successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete') { ?>
        <div class="error-msg">❌ Cannot delete this doctor because they have existing appointments!</div>
    <?php } ?>
    
    <div class="header-actions">
        <span class="total-doctors"><strong>Total Doctors:</strong> <?php echo $total_doctors; ?></span>
        <a href="add_doctor.php" class="btn-add">➕ Add New Doctor</a>
    </div>
    
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>Specialty</th><th>Phone</th><th>Room</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (mysqli_num_rows($doctors) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($doctors)) { 
                    $today = date('Y-m-d');
                    $today_appts = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM appointment WHERE doctor_id = '{$row['doctor_id']}' AND DATE(appointment_datetime) = '$today'"))['total'];
                ?>
                    <tr>
                        <td><?php echo $row['doctor_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['specialty']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['room_number']); ?></td>
                        <td>
                            <?php if ($today_appts > 5) { ?>
                                <span class="status-badge status-busy">🟡 Busy (<?php echo $today_appts; ?> today)</span>
                            <?php } elseif ($today_appts > 0) { ?>
                                <span class="status-badge status-active">🟢 Active (<?php echo $today_appts; ?> today)</span>
                            <?php } else { ?>
                                <span class="status-badge status-inactive">⚪ No appointments today</span>
                            <?php } ?>
                        </td>
                        <td>
                            <a href="edit_doctor.php?id=<?php echo $row['doctor_id']; ?>" class="btn-edit">✏️ Edit</a>
                            <a href="view_doctors.php?delete=<?php echo $row['doctor_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this doctor?')">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="7" class="no-data">No doctors found. <a href="add_doctor.php">Add your first doctor</a></td></tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="back-container">
        <a href="add_doctor.php" class="back back1">Add Doctor</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>
</body>
</html>