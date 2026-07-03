<?php
include '../config/database.php';

// ============================================================
// ===== GET DATA FOR DROPDOWNS =====
// ============================================================

$doctors = mysqli_query($conn, "SELECT * FROM doctor ORDER BY name");

// Get ALL completed appointments (with or without prescriptions)
$appointments = mysqli_query($conn, "
    SELECT a.appoint_id, p.name AS patient_name, a.appointment_datetime
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    WHERE a.status = 'Completed'
    ORDER BY a.appointment_datetime DESC
");

$medicines = mysqli_query($conn, "
    SELECT *, 
           CASE 
               WHEN stock_quantity <= 10 THEN 'Low Stock'
               WHEN stock_quantity <= 50 THEN 'Medium Stock'
               ELSE 'In Stock'
           END as stock_status
    FROM medicine 
    WHERE stock_quantity > 0
    ORDER BY medicine_name
");

// ============================================================
// ===== HANDLE FORM SUBMISSION =====
// ============================================================

if (isset($_POST['submit'])) {

    $doctor_id = mysqli_real_escape_string($conn, $_POST['doctor_id']);
    $appoint_id = mysqli_real_escape_string($conn, $_POST['appoint_id']);
    $medicine_ids = $_POST['medicine_id'];
    $quantities = $_POST['quantity'];

    // Insert prescription (total_cost will be updated by trigger)
    $stmt1 = mysqli_prepare($conn, "INSERT INTO prescription (doctor_id, appoint_id, total_cost) VALUES (?, ?, 0)");
    mysqli_stmt_bind_param($stmt1, "ii", $doctor_id, $appoint_id);
    
    if (!mysqli_stmt_execute($stmt1)) {
        die("Error inserting prescription: " . mysqli_error($conn));
    }
    
    $prescription_id = mysqli_insert_id($conn);
    $medicines_added = 0;

    // Loop through medicines
    for ($i = 0; $i < count($medicine_ids); $i++) {

        $mid = mysqli_real_escape_string($conn, $medicine_ids[$i]);
        $qty = mysqli_real_escape_string($conn, $quantities[$i]);
        
        // Skip if medicine ID or quantity is empty
        if (empty($mid) || empty($qty) || $qty <= 0) {
            continue;
        }
        
        $medicines_added++;

        // Get medicine to check stock
        $stmt2 = mysqli_prepare($conn, "SELECT * FROM medicine WHERE medicine_id = ?");
        mysqli_stmt_bind_param($stmt2, "i", $mid);
        mysqli_stmt_execute($stmt2);
        $med_result = mysqli_stmt_get_result($stmt2);
        $med = mysqli_fetch_assoc($med_result);
        
        if (!$med) {
            die("Medicine not found with ID: " . $mid);
        }
        
        // Check stock
        if ($med['stock_quantity'] < $qty) {
            echo "<script>alert('⚠️ Insufficient stock for " . $med['medicine_name'] . "! Available: " . $med['stock_quantity'] . ", Requested: " . $qty . "'); window.history.back();</script>";
            // Clean up - delete the prescription we just inserted
            mysqli_query($conn, "DELETE FROM prescription WHERE prescript_id = '$prescription_id'");
            exit();
        }

        // Insert prescription_medicine (triggers will handle stock reduction and total cost)
        $stmt3 = mysqli_prepare($conn, "INSERT INTO prescription_medicine (prescription_id, medicine_id, quantity) VALUES (?, ?, ?)");
        mysqli_stmt_bind_param($stmt3, "iii", $prescription_id, $mid, $qty);
        
        if (!mysqli_stmt_execute($stmt3)) {
            die("Error inserting prescription_medicine: " . mysqli_error($conn));
        }
    }
    
    // If no medicines were added, delete the prescription
    if ($medicines_added == 0) {
        mysqli_query($conn, "DELETE FROM prescription WHERE prescript_id = '$prescription_id'");
        echo "<script>alert('⚠️ Please select at least one medicine!'); window.location.href='add_prescriptions.php';</script>";
        exit();
    }

    // Success! Redirect to view prescriptions
    header("Location: view_prescriptions.php?success=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Prescription</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f4f6f9; margin: 0; }
        .container { width: 600px; margin: 40px auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        label { display: block; margin-top: 15px; font-weight: bold; color: #2c3e50; }
        input, select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button[type="submit"] { width: 100%; padding: 12px; margin-top: 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        button[type="submit"]:hover { background: #1f2d3a; }
        .row { display: flex; gap: 10px; margin-top: 10px; align-items: center; flex-wrap: wrap; }
        .row select, .row input { flex: 1; min-width: 100px; }
        .row .remove-btn { background: #e74c3c; color: white; border: none; padding: 10px 12px; border-radius: 5px; cursor: pointer; font-size: 14px; }
        .row .remove-btn:hover { background: #c0392b; }
        .back-container { text-align: center; margin-top: 15px; }
        .back { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
        .add-btn { background: #3498db; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; margin-top: 10px; font-size: 14px; width: auto; }
        .add-btn:hover { background: #2980b9; }
        .medicine-row { background: #f8f9fa; padding: 10px; border-radius: 5px; }
        .stock-low { color: #e74c3c; font-weight: bold; }
        .stock-medium { color: #f39c12; font-weight: bold; }
        .stock-high { color: #27ae60; font-weight: bold; }
        .no-appointments { color: #7f8c8d; text-align: center; padding: 20px; }
        .info-box { background: #e8f4f8; padding: 10px; border-radius: 5px; margin: 10px 0; font-size: 13px; color: #2c3e50; }
        
        @media (max-width: 600px) {
            .container { width: 95%; padding: 15px; }
            .row { flex-direction: column; }
            .row select, .row input { width: 100%; }
            .row .remove-btn { width: 100%; }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>💊 Add Prescription</h2>
    
    <div class="info-box">
        ℹ️ Shows ALL <strong>Completed</strong> appointments.
        <br>Available appointments: <strong><?php echo mysqli_num_rows($appointments); ?></strong>
    </div>

    <form method="POST">
        <label>👨‍⚕️ Doctor</label>
        <select name="doctor_id" required>
            <?php mysqli_data_seek($doctors, 0); while ($d = mysqli_fetch_assoc($doctors)) { ?>
                <option value="<?php echo $d['doctor_id']; ?>"><?php echo $d['name']; ?></option>
            <?php } ?>
        </select>
        
        <label>📋 Appointment (Patient)</label>
        <select name="appoint_id" required>
            <?php if (mysqli_num_rows($appointments) > 0) { ?>
                <?php while ($a = mysqli_fetch_assoc($appointments)) { ?>
                    <option value="<?php echo $a['appoint_id']; ?>">
                        <?php echo $a['patient_name']; ?> (<?php echo date('d/m/Y H:i', strtotime($a['appointment_datetime'])); ?>)
                    </option>
                <?php } ?>
            <?php } else { ?>
                <option value="">-- No appointments available --</option>
            <?php } ?>
        </select>
        
        <label>💊 Medicine(s)</label>
        <div id="medicines-container">
            <div class="row medicine-row">
                <select name="medicine_id[]" required>
                    <option value="">-- Select Medicine --</option>
                    <?php mysqli_data_seek($medicines, 0); while ($m = mysqli_fetch_assoc($medicines)) { 
                        $stock_class = '';
                        if ($m['stock_quantity'] <= 10) $stock_class = 'stock-low';
                        elseif ($m['stock_quantity'] <= 50) $stock_class = 'stock-medium';
                        else $stock_class = 'stock-high';
                    ?>
                        <option value="<?php echo $m['medicine_id']; ?>">
                            <?php echo $m['medicine_name']; ?> - RM<?php echo $m['unit_price']; ?> 
                            (Stock: <span class="<?php echo $stock_class; ?>"><?php echo $m['stock_quantity']; ?></span>)
                        </option>
                    <?php } ?>
                </select>
                <input type="number" name="quantity[]" placeholder="Qty" required min="1" value="1">
            </div>
        </div>
        <button type="button" class="add-btn" id="addMoreBtn">+ Add Another Medicine</button>
        <button type="submit" name="submit">💾 Save Prescription</button>
    </form>
    
    <div class="back-container">
        <a href="view_prescriptions.php" class="back back1">Back to List</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>

<script>
document.getElementById('addMoreBtn').onclick = function() {
    var container = document.getElementById('medicines-container');
    var firstSelect = document.querySelector('.medicine-row select');
    var medicineOptions = firstSelect.innerHTML;
    var newRow = document.createElement('div');
    newRow.className = 'row medicine-row';
    newRow.style.marginTop = '10px';
    newRow.innerHTML = `
        <select name="medicine_id[]">${medicineOptions}</select>
        <input type="number" name="quantity[]" placeholder="Qty" min="1" value="1">
        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖ Remove</button>
    `;
    container.appendChild(newRow);
};
</script>

</body>
</html>