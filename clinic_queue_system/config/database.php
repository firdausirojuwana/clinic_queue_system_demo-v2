<?php
$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "clinic_queue"
);

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8
mysqli_set_charset($conn, "utf8");
?>