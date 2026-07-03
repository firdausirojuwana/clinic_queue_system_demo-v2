<?php
include '../config/database.php';

$id = intval($_GET['id']);

mysqli_query($conn, "DELETE FROM prescription WHERE appoint_id=$id");
mysqli_query($conn, "DELETE FROM appointment WHERE appoint_id=$id");

header("Location: view_queue.php");
exit();
?>