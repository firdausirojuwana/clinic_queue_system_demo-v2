<?php
include '../config/database.php';

if (isset($_POST['submit'])) {
    $medicine_name = mysqli_real_escape_string($conn, $_POST['medicine_name']);
    $dosage = mysqli_real_escape_string($conn, $_POST['dosage']);
    $unit_price = mysqli_real_escape_string($conn, $_POST['unit_price']);
    $stock_quantity = mysqli_real_escape_string($conn, $_POST['stock_quantity']);
    
    $sql = "INSERT INTO medicine (medicine_name, dosage, unit_price, stock_quantity) 
            VALUES ('$medicine_name', '$dosage', '$unit_price', '$stock_quantity')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: view_medicine.php?success=1");
        exit();
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Medicine</title>

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

        button {
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

        .error-msg {
            background: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            .container {
                width: 95%;
            }
        }
    </style>
</head>

<body>

<div class="container">

    <h2>💊 Add New Medicine</h2>

    <?php if (isset($error)) { ?>
        <div class="error-msg">❌ <?php echo $error; ?></div>
    <?php } ?>

    <form method="POST">

        <label>Medicine Name *</label>
        <input type="text" name="medicine_name" placeholder="Enter medicine name" required>

        <label>Dosage</label>
        <input type="text" name="dosage" placeholder="e.g., 500mg, 10ml">

        <div class="form-row">
            <div>
                <label>Unit Price (RM) *</label>
                <input type="number" step="0.01" name="unit_price" placeholder="0.00" min="0" required>
            </div>
            <div>
                <label>Stock Quantity *</label>
                <input type="number" name="stock_quantity" placeholder="Enter quantity" min="0" required>
            </div>
        </div>

        <button type="submit" name="submit">💾 Save Medicine</button>

    </form>

    <div class="back-container">
        <a href="view_medicine.php" class="back back1">View All Medicines</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>

</div>

</body>
</html>