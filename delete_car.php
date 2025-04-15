<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


if ($_SESSION['role'] != 'admin') {
    echo "<div class='alert alert-danger'>You are not authorized to access this page.</div>";
    exit();
}

// car deletion
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    $stmt = $conn->prepare("DELETE FROM cars WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_cars.php");
    exit();
} else {
    echo "Car ID not provided!";
    exit();
}
?>
