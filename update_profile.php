<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Update user details
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Validate inputs
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['error_message'] = "First Name, Last Name, and Email are required.";
        header("Location: user_profile.php");
        exit();
    }

    
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE user_id = ?");
    $stmt->bind_param("sssssi", $first_name, $last_name, $email, $phone, $address, $user_id);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Profile updated successfully.";
    } else {
        $_SESSION['error_message'] = "Failed to update profile. Please try again.";
    }

    header("Location: user_profile.php");
    exit();
}
?>
