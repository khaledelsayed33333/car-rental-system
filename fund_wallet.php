<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// wallet funding
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_id'], $_POST['amount'])) {
    $user_id = $_POST['user_id'];
    $amount = $_POST['amount'];

    // Ensure the amount is valid
    if ($amount > 0) {
        // Get the current balance
        $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            $new_balance = $user['wallet_balance'] + $amount;

            // Update the user's balance
            $update_stmt = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE user_id = ?");
            $update_stmt->bind_param("di", $new_balance, $user_id);
            $update_stmt->execute();

            $success_message = "User's wallet has been successfully funded!";
        } else {
            $error_message = "User not found!";
        }
    } else {
        $error_message = "Please enter a valid amount greater than 0.";
    }
}

// Fetch all users
$users = $conn->query("SELECT user_id, first_name, last_name, wallet_balance FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fund User Wallet - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background: linear-gradient(45deg, #f7f9fc, #e4e9f2);
            font-family: 'Arial', sans-serif;
            color: #343a40;
        }
        .container {
            background: #ffffff;
            padding: 40px;
            margin-top: 60px;
            border-radius: 15px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
        }
        .container:hover {
            transform: scale(1.03);
        }
        h2 {
            font-size: 2rem;
            color: #343a40;
            font-weight: 700;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #ddd;
            transition: border 0.3s ease;
        }
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 8px rgba(0, 123, 255, 0.5);
        }
        .btn-primary {
            background-color: #007bff;
            border-radius: 10px;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-radius: 10px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
        }
        .alert {
            border-radius: 8px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }
        .mt-4 {
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .container {
                margin-top: 40px;
                padding: 20px;
            }
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Fund User Wallet</h2>

        <!-- Success and Error Messages -->
        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <!-- Fund Wallet Form -->
        <form method="POST">
            <div class="mb-3">
                <label for="user_id" class="form-label">Select User</label>
                <select name="user_id" id="user_id" class="form-control" required>
                    <option value="">-- Select User --</option>
                    <?php while ($user = $users->fetch_assoc()) : ?>
                        <option value="<?= $user['user_id'] ?>">
                            <?= htmlspecialchars($user['first_name'] . " " . $user['last_name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="amount" class="form-label">Amount to Add</label>
                <input type="number" name="amount" id="amount" class="form-control" min="1" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Fund Wallet</button>
        </form>

        <!-- Back to Dashboard Button -->
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary w-100">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
