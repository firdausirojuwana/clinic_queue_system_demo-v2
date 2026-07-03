<?php
include '../config/database.php';

// ===== REMOVED: The stock_status update query that was causing the error =====
// The column 'stock_status' doesn't exist in your table, so we removed this query

// ============================================================
// ===== GET STATISTICS =====
// ============================================================

$total_medicines = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM medicine"))['total'];
$low_stock_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM medicine WHERE stock_quantity <= 10"))['total'];

// ============================================================
// ===== GET MEDICINES =====
// ============================================================

$medicines = mysqli_query($conn, "
    SELECT *, 
           (unit_price * stock_quantity) as total_value
    FROM medicine 
    ORDER BY 
        CASE 
            WHEN stock_quantity <= 10 THEN 0 
            WHEN stock_quantity <= 50 THEN 1 
            ELSE 2 
        END,
        medicine_name
");

// ============================================================
// ===== HANDLE FILTER =====
// ============================================================

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
if ($filter == 'low') {
    $medicines = mysqli_query($conn, "
        SELECT *, (unit_price * stock_quantity) as total_value
        FROM medicine 
        WHERE stock_quantity <= 10 
        ORDER BY stock_quantity ASC, medicine_name
    ");
}

// ============================================================
// ===== HANDLE DELETE =====
// ============================================================

if (isset($_GET['delete'])) {
    $medicine_id = intval($_GET['delete']);
    $check = mysqli_query($conn, "SELECT COUNT(*) as total FROM prescription_medicine WHERE medicine_id = '$medicine_id'");
    $in_use = mysqli_fetch_assoc($check)['total'];
    
    if ($in_use > 0) {
        header("Location: view_medicine.php?error=cannot_delete");
        exit();
    }
    
    mysqli_query($conn, "DELETE FROM medicine WHERE medicine_id = '$medicine_id'");
    header("Location: view_medicine.php?deleted=1");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>View Medicines</title>
    <style>
        body { font-family: Arial; background: #f4f6f9; margin: 0; padding: 20px; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 25px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { text-align: center; color: #2c3e50; margin-bottom: 20px; }
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .btn-add { padding: 10px 20px; background: #2c3e50; color: white; text-decoration: none; border-radius: 5px; }
        .btn-add:hover { background: #1f2d3a; }
        .filter-buttons { display: flex; gap: 10px; flex-wrap: wrap; }
        .btn-filter { padding: 8px 15px; background: #7f8c8d; color: white; text-decoration: none; border-radius: 5px; font-size: 14px; }
        .btn-filter:hover { background: #6c7a7d; }
        .btn-filter.active { background: #2c3e50; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 12px; text-align: left; font-size: 14px; }
        td { padding: 12px; border-bottom: 1px solid #ddd; font-size: 14px; }
        tr:hover { background: #f5f5f5; }
        .btn-edit { padding: 5px 12px; background: #2c3e50; color: white; text-decoration: none; border-radius: 3px; margin-right: 5px; font-size: 13px; }
        .btn-edit:hover { background: #1f2d3a; }
        .btn-delete { padding: 5px 12px; background: #e74c3c; color: white; text-decoration: none; border-radius: 3px; font-size: 13px; }
        .btn-delete:hover { background: #c0392b; }
        .back-container { text-align: center; margin-top: 20px; }
        .back { display: inline-block; padding: 10px 15px; margin: 5px; text-decoration: none; border-radius: 5px; color: white; }
        .back1 { background: #2c3e50; }
        .back2 { background: #7f8c8d; }
        .no-data { text-align: center; padding: 30px; color: #7f8c8d; }
        .success-msg { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .error-msg { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; text-align: center; }
        .total-items { color: #2c3e50; font-weight: bold; }
        .badge-stock { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .badge-stock.low { background: #f8d7da; color: #721c24; }
        .badge-stock.medium { background: #fff3cd; color: #856404; }
        .badge-stock.high { background: #d4edda; color: #155724; }
        .badge-stock.out { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        @media (max-width: 768px) { .header-actions { flex-direction: column; align-items: stretch; } .filter-buttons { justify-content: center; } table { font-size: 12px; } th, td { padding: 8px; } }
    </style>
</head>
<body>
<div class="container">
    <h2>💊 Medicine Inventory</h2>
    
    <?php if (isset($_GET['success'])) { ?>
        <div class="success-msg">✅ Medicine added successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['deleted'])) { ?>
        <div class="success-msg">🗑️ Medicine deleted successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['updated'])) { ?>
        <div class="success-msg">✏️ Medicine updated successfully!</div>
    <?php } ?>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'cannot_delete') { ?>
        <div class="error-msg">❌ Cannot delete this medicine because it's being used in prescriptions!</div>
    <?php } ?>
    
    <div class="header-actions">
        <span class="total-items"><strong>Total Medicines:</strong> <?php echo $total_medicines; ?></span>
        <div class="filter-buttons">
            <a href="view_medicine.php" class="btn-filter <?php echo $filter == 'all' ? 'active' : ''; ?>">📋 All</a>
            <a href="view_medicine.php?filter=low" class="btn-filter <?php echo $filter == 'low' ? 'active' : ''; ?>">⚠️ Low Stock (<?php echo $low_stock_count; ?>)</a>
        </div>
        <a href="add_medicine.php" class="btn-add">➕ Add New Medicine</a>
    </div>
    
    <table>
        <thead><tr><th>ID</th><th>Medicine Name</th><th>Dosage</th><th>Price (RM)</th><th>Stock</th><th>Total Value</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
            <?php if (mysqli_num_rows($medicines) > 0) { ?>
                <?php while ($row = mysqli_fetch_assoc($medicines)) { 
                    $qty = (int)$row['stock_quantity'];
                    $total_value = $row['unit_price'] * $qty;
                    
                    if ($qty <= 0) {
                        $stock_badge = 'badge-stock out';
                        $stock_status = 'Out of Stock';
                    } elseif ($qty <= 10) {
                        $stock_badge = 'badge-stock low';
                        $stock_status = 'Low Stock';
                    } elseif ($qty <= 50) {
                        $stock_badge = 'badge-stock medium';
                        $stock_status = 'Medium';
                    } else {
                        $stock_badge = 'badge-stock high';
                        $stock_status = 'In Stock';
                    }
                ?>
                    <tr>
                        <td><?php echo $row['medicine_id']; ?></td>
                        <td><strong><?php echo htmlspecialchars($row['medicine_name']); ?></strong></td>
                        <td><?php echo htmlspecialchars($row['dosage'] ?? '-'); ?></td>
                        <td>RM <?php echo number_format((float)$row['unit_price'], 2); ?></td>
                        <td><?php echo $qty; ?> <?php echo $qty <= 0 ? '🚫' : '📦'; ?></td>
                        <td>RM <?php echo number_format($total_value, 2); ?></td>
                        <td><span class="<?php echo $stock_badge; ?>"><?php echo $stock_status; ?></span></td>
                        <td>
                            <a href="edit_medicine.php?id=<?php echo $row['medicine_id']; ?>" class="btn-edit">✏️ Edit</a>
                            <a href="view_medicine.php?delete=<?php echo $row['medicine_id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this medicine?')">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            <?php } else { ?>
                <tr><td colspan="8" class="no-data"><?php echo $filter == 'low' ? 'No medicines with low stock found.' : 'No medicines found. <a href="add_medicine.php">Add your first medicine</a>'; ?></td></tr>
            <?php } ?>
        </tbody>
    </table>
    <div class="back-container">
        <a href="add_medicine.php" class="back back1">Add Medicine</a>
        <a href="../index.php" class="back back2">Menu</a>
    </div>
</div>
</body>
</html>