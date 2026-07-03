<?php
include '../config/database.php';

$total_patients = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM patient"))['total'];

$result = mysqli_query($conn, "
    SELECT p.*, 
           (SELECT COUNT(*) FROM appointment a WHERE a.patient_id = p.patient_id) as appointment_count,
           (SELECT COUNT(*) FROM appointment a WHERE a.patient_id = p.patient_id AND DATE(a.appointment_datetime) = CURDATE()) as today_appointments
    FROM patient p 
    ORDER BY p.patient_id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Patients</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { width: 95%; max-width: 1200px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        a.button { display: inline-block; padding: 10px 20px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; }
        a.button:hover { background: #1f2d3a; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #ddd; font-size: 14px; }
        tr:hover { background: #f5f5f5; }
        .edit { color: #2980b9; font-weight: bold; text-decoration: none; padding: 5px 10px; border-radius: 3px; }
        .edit:hover { background: #e8f4f8; }
        .delete { color: #e74c3c; font-weight: bold; text-decoration: none; padding: 5px 10px; border-radius: 3px; }
        .delete:hover { background: #fde8e8; }
        .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; }
        .badge-Male { background: #d4edda; color: #155724; }
        .badge-Female { background: #f8d7da; color: #721c24; }
        .badge-Other { background: #e2e3e5; color: #383d41; }
        .badge-appointments { background: #cce5ff; color: #004085; border-radius: 12px; padding: 3px 10px; font-size: 12px; }
        .back { display: inline-block; margin-top: 20px; text-decoration: none; color: white; background: #2c3e50; padding: 10px 15px; border-radius: 5px; }
        .back:hover { background: #1f2d3a; }
        .no-data { text-align: center; padding: 30px; color: #7f8c8d; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .total-items { color: #2c3e50; font-weight: bold; }
        @media (max-width: 768px) { .header-actions { flex-direction: column; align-items: stretch; } table { font-size: 12px; } th, td { padding: 8px; } }
    </style>
</head>
<body>
<div class="container">
    <h2>👥 Patient Management</h2>
    
    <?php if (isset($_GET['success'])) { ?>
        <div class="success-msg">✅ Patient added successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['updated'])) { ?>
        <div class="success-msg">✏️ Patient updated successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['deleted'])) { ?>
        <div class="success-msg">🗑️ Patient deleted successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete') { ?>
        <div class="error-msg">❌ Cannot delete this patient because they have existing appointments!</div>
    <?php } ?>
    
    <div class="header-actions">
        <span class="total-items"><strong>Total Patients:</strong> <?php echo $total_patients; ?></span>
        <a href="add_patient.php" class="button">➕ Add New Patient</a>
    </div>
    
    <table>
        <thead><tr><th>ID</th><th>Name</th><th>IC Number</th><th>Phone</th><th>Email</th><th>Gender</th><th>Appointments</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($result)) { 
                    $phone = $row['phone'];
                    if (strlen($phone) == 10) {
                        $phone = substr($phone, 0, 3) . '-' . substr($phone, 3, 3) . '-' . substr($phone, 6);
                    } elseif (strlen($phone) == 11) {
                        $phone = substr($phone, 0, 4) . '-' . substr($phone, 4, 3) . '-' . substr($phone, 7);
                    }
                    
                    $ic = $row['ic_number'];
                    if (strlen($ic) == 12) {
                        $ic = substr($ic, 0, 6) . '-' . substr($ic, 6, 2) . '-' . substr($ic, 8, 4);
                    }
                ?>
                    <tr>
                        <td><?php echo $row['patient_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['name']); ?></strong></td>
                        <td><?php echo $ic; ?></td>
                        <td><?php echo $phone; ?></td>
                        <td><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                        <td><span class="badge badge-<?php echo $row['gender']; ?>"><?php echo $row['gender']; ?></span></td>
                        <td><span class="badge-appointments">📋 <?php echo $row['appointment_count']; ?> total</span></td>
                        <td>
                            <a class="edit" href="edit_patient.php?id=<?php echo $row['patient_id']; ?>">✏️ Edit</a>
                            <a class="delete" onclick="return confirm('Are you sure you want to delete this patient?');" href="delete_patient.php?id=<?php echo $row['patient_id']; ?>">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="8" class="no-data">No patients found. <a href="add_patient.php">Add your first patient</a></td></tr>
            <?php } ?>
        </tbody>
    </table>
    <div style="text-align: center;"><a class="back" href="../index.php">🏠 Back to Menu</a></div>
</div>
</body>
</html>