<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get car details
if (isset($_GET['car_id'], $_GET['model'], $_GET['price_per_day'])) {
    $car_id = $_GET['car_id'];
    $model = $_GET['model'];
    $price_per_day = $_GET['price_per_day'];
} else {
    header("Location: search_car.php");
    exit();
}

// Fetch wallet balance
$query = "SELECT wallet_balance FROM users WHERE user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$wallet_balance = $user['wallet_balance'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $total_cost = $_POST['total_cost'];

    // Validate user balance
    if ($total_cost > $wallet_balance) {
        $error_message = "You do not have enough balance to reserve this car. Your balance is $wallet_balance EGP.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reservations (user_id, car_id, start_date, end_date, total_cost, status) VALUES (?, ?, ?, ?, ?, ?)");
        $status = 'pending';
        $stmt->bind_param("iissds", $user_id, $car_id, $start_date, $end_date, $total_cost, $status);

        if ($stmt->execute()) {
            $success_message = "Reservation submitted successfully! Waiting for admin approval.";
        } else {
            $error_message = "Error processing reservation. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserve a Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Arial', sans-serif;
        }
        
        .form-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        h2 {
            font-size: 26px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            border-radius: 8px;
            padding: 14px;
            font-size: 16px;
            transition: background-color 0.3s ease-in-out;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert {
            font-size: 16px;
            margin-bottom: 20px;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 8px;
            padding: 12px;
            font-size: 16px;
            width: 100%;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .text-center a {
            text-decoration: none;
            color: #fff;
        }

        .card-details {
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .card-details p {
            font-size: 18px;
            color: #333;
        }

        .card-details .price {
            font-size: 20px;
            font-weight: bold;
            color: #28a745;
        }

        .card-details .status {
            font-size: 16px;
            font-weight: bold;
            color: #ff6600;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Reserve a Car</h2>

    <?php if (isset($error_message)) : ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
    <?php endif; ?>

    <?php if (isset($success_message)) : ?>
        <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
    <?php endif; ?>

    <div class="card-details">
        <p><strong>Car Model:</strong> <?= htmlspecialchars($model) ?></p>
        <p><strong>Price per Day:</strong> <?= htmlspecialchars($price_per_day) ?> EGP</p>
        <p><strong>Wallet Balance:</strong> <?= htmlspecialchars($wallet_balance) ?> EGP</p>
    </div>

    <form method="POST">
        <div class="mb-3">
            <label for="start_date" class="form-label">Start Date</label>
            <input type="date" name="start_date" id="start_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="end_date" class="form-label">End Date</label>
            <input type="date" name="end_date" id="end_date" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="total_cost" class="form-label">Total Cost</label>
            <input type="number" name="total_cost" id="total_cost" class="form-control" required>
        </div>

        <div class="mt-4 text-center">
            <button type="submit" class="btn btn-primary w-100">Reserve</button>
        </div>
    </form>

    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-secondary w-100">Back to Dashboard</a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
