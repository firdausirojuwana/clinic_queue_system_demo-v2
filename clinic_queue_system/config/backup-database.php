<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "clinic_queue"
);

if (!$conn) {
    die("Database connection failed");
}

?>