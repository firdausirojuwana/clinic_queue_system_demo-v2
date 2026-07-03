<?php
include '../config/database.php';

$doctors = mysqli_query($conn, "SELECT * FROM doctor");

$appointments = mysqli_query($conn, "
    SELECT a.appoint_id, p.name AS patient_name
    FROM appointment a
    JOIN patient p ON a.patient_id = p.patient_id
    WHERE a.status = 'Completed'
");

$medicines = mysqli_query($conn, "SELECT * FROM medicine");

if (isset($_POST['submit'])) {

    $doctor_id = mysqli_real_escape_string($conn, $_POST['doctor_id']);
    $appoint_id = mysqli_real_escape_string($conn, $_POST['appoint_id']);
    $medicine_ids = $_POST['medicine_id'];
    $quantities = $_POST['quantity'];

    // Check if this appointment already has a prescription
    $check_sql = "SELECT * FROM prescription WHERE appoint_id = '$appoint_id'";
    $check_result = mysqli_query($conn, $check_sql);
    
    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('This appointment already has a prescription!'); window.location.href='add_prescriptions.php';</script>";
        exit();
    }

    // Insert prescription
    $sql1 = "INSERT INTO prescription (doctor_id, appoint_id, total_cost)
             VALUES ('$doctor_id', '$appoint_id', 0)";
    
    if (!mysqli_query($conn, $sql1)) {
        die("Error inserting prescription: " . mysqli_error($conn));
    }
    
    $prescription_id = mysqli_insert_id($conn);
    $total_cost = 0;
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

        // Get medicine price
        $sql2 = "SELECT * FROM medicine WHERE medicine_id = '$mid'";
        $med_result = mysqli_query($conn, $sql2);
        
        if (!$med_result) {
            die("Error getting medicine: " . mysqli_error($conn));
        }
        
        $med = mysqli_fetch_assoc($med_result);
        
        if (!$med) {
            die("Medicine not found with ID: " . $mid);
        }

        $subtotal = $qty * $med['unit_price'];
        $total_cost += $subtotal;

        // Insert prescription_medicine
        $sql3 = "INSERT INTO prescription_medicine (prescription_id, medicine_id, quantity)
                 VALUES ('$prescription_id', '$mid', '$qty')";
        
        if (!mysqli_query($conn, $sql3)) {
            die("Error inserting prescription_medicine: " . mysqli_error($conn));
        }
    }
    
    // If no medicines were added, delete the prescription
    if ($medicines_added == 0) {
        mysqli_query($conn, "DELETE FROM prescription WHERE prescript_id = '$prescription_id'");
        echo "<script>alert('Please select at least one medicine!'); window.location.href='add_prescriptions.php';</script>";
        exit();
    }

    // Update total cost
    $sql4 = "UPDATE prescription SET total_cost = '$total_cost' WHERE prescript_id = '$prescription_id'";
    
    if (!mysqli_query($conn, $sql4)) {
        die("Error updating total cost: " . mysqli_error($conn));
    }

    header("Location: view_prescriptions.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Prescription</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
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
            margin-top: 15px;
            font-weight: bold;
            color: #2c3e50;
        }
        input, select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            margin-top: 20px;
            background: #2c3e50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        button[type="submit"]:hover {
            background: #1f2d3a;
        }
        .row {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            align-items: center;
            flex-wrap: wrap;
        }
        .row select, .row input {
            flex: 1;
            min-width: 100px;
        }
        .row .remove-btn {
            background: #e74c3c;
            color: white;
            border: none;
            padding: 10px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .row .remove-btn:hover {
            background: #c0392b;
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
        .add-btn {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            font-size: 14px;
            width: auto;
        }
        .add-btn:hover {
            background: #2980b9;
        }
        .medicine-row {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>💊 Add Prescription</h2>

    <form method="POST">

        <label>👨‍⚕️ Doctor</label>
        <select name="doctor_id" required>
            <?php 
            mysqli_data_seek($doctors, 0);
            while ($d = mysqli_fetch_assoc($doctors)) { ?>
                <option value="<?php echo $d['doctor_id']; ?>">
                    <?php echo $d['name']; ?>
                </option>
            <?php } ?>
        </select>

        <label>📋 Appointment (Patient)</label>
        <select name="appoint_id" required>
            <?php 
            mysqli_data_seek($appointments, 0);
            while ($a = mysqli_fetch_assoc($appointments)) { ?>
                <option value="<?php echo $a['appoint_id']; ?>">
                    <?php echo $a['patient_name']; ?>
                </option>
            <?php } ?>
        </select>

        <label>💊 Medicine(s)</label>

        <div id="medicines-container">
            <!-- Medicine Row 1 -->
            <div class="row medicine-row">
                <select name="medicine_id[]" required>
                    <option value="">-- Select Medicine --</option>
                    <?php
                    mysqli_data_seek($medicines, 0);
                    while ($m = mysqli_fetch_assoc($medicines)) { ?>
                        <option value="<?php echo $m['medicine_id']; ?>">
                            <?php echo $m['medicine_name']; ?> - RM<?php echo $m['unit_price']; ?>
                        </option>
                    <?php } ?>
                </select>
                <input type="number" name="quantity[]" placeholder="Quantity" required min="1">
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
    
    // Get the medicine options from the first select box
    var firstSelect = document.querySelector('.medicine-row select');
    var medicineOptions = firstSelect.innerHTML;
    
    // Create new row
    var newRow = document.createElement('div');
    newRow.className = 'row medicine-row';
    newRow.style.marginTop = '10px';
    
    newRow.innerHTML = `
        <select name="medicine_id[]">
            ${medicineOptions}
        </select>
        <input type="number" name="quantity[]" placeholder="Quantity" min="1">
        <button type="button" class="remove-btn" onclick="this.parentElement.remove()">✖ Remove</button>
    `;
    
    container.appendChild(newRow);
};
</script>

</body>
</html>