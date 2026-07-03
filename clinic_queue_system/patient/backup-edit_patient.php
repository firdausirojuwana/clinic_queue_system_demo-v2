<?php
include '../config/database.php';

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM patient WHERE patient_id = $id");
$patient = mysqli_fetch_assoc($result);

if (isset($_POST['update'])) {

    $name = $_POST['name'];
    $ic_number = $_POST['ic_number'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];

    $query = "UPDATE patient SET
        name='$name',
        ic_number='$ic_number',
        phone='$phone',
        email='$email',
        gender='$gender',
        address='$address'
        WHERE patient_id=$id";

    mysqli_query($conn, $query);

    header("Location: view_patients.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Patient</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>

<h1>Edit Patient</h1>

<form method="POST">

    <label>Name</label><br>
    <input type="text" name="name"
        value="<?php echo $patient['name']; ?>" required><br><br>

    <label>IC Number</label><br>
    <input type="text" name="ic_number"
        value="<?php echo $patient['ic_number']; ?>" required><br><br>

    <label>Phone</label><br>
    <input type="text" name="phone"
        value="<?php echo $patient['phone']; ?>"><br><br>

    <label>Email</label><br>
    <input type="email" name="email"
        value="<?php echo $patient['email']; ?>"><br><br>

    <label>Gender</label><br>
    <select name="gender">
        <option <?php if($patient['gender']=="Male") echo "selected"; ?>>Male</option>
        <option <?php if($patient['gender']=="Female") echo "selected"; ?>>Female</option>
        <option <?php if($patient['gender']=="Other") echo "selected"; ?>>Other</option>
    </select>
    <br><br>

    <label>Address</label><br>
    <textarea name="address"><?php echo $patient['address']; ?></textarea>
    <br><br>

    <button type="submit" name="update">Update Patient</button>

</form>

<br>
<a href="view_patients.php">← Back</a>

</body>
</html>