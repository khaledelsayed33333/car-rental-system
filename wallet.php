<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get wallet balance
$query = "SELECT wallet_balance FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$wallet_balance = $user['wallet_balance'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Wallet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Your Wallet</h2>

        
        <div class="mb-4">
            <p>Your current wallet balance is: <strong>$<?= htmlspecialchars($wallet_balance) ?></strong></p>
        </div>

        
        <form method="POST" action="add_funds.php">
            <div class="mb-3">
                <label for="amount" class="form-label">Amount to Add</label>
                <input type="number" name="amount" id="amount" class="form-control" required min="1" step="any">
            </div>
            <button type="submit" class="btn btn-primary">Add Funds</button>
        </form>

        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
