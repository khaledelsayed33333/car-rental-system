<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}


$reservations = $conn->query("SELECT r.*, u.first_name, u.last_name, c.model, c.plate_id FROM reservations r
    JOIN users u ON r.user_id = u.user_id
    JOIN cars c ON r.car_id = c.car_id");


// Total Users
$sql_users = "SELECT COUNT(*) AS total_users FROM users";
$result_users = mysqli_query($conn, $sql_users);
$row_users = mysqli_fetch_assoc($result_users);
$total_users = $row_users['total_users'];

// Total Reservations
$sql_reservations = "SELECT COUNT(*) AS total_reservations FROM reservations";
$result_reservations = mysqli_query($conn, $sql_reservations);
$row_reservations = mysqli_fetch_assoc($result_reservations);
$total_reservations = $row_reservations['total_reservations'];

// Total Income
$sql_income = "SELECT SUM(amount) AS total_income FROM payments";
$result_income = mysqli_query($conn, $sql_income);
$row_income = mysqli_fetch_assoc($result_income);
$total_income = $row_income['total_income'];

// Daily Payments
$sql_daily_payments = "SELECT SUM(amount) AS total_daily_payments FROM payments WHERE DATE(payment_date) = CURDATE()";
$result_daily_payments = mysqli_query($conn, $sql_daily_payments);
$row_daily_payments = mysqli_fetch_assoc($result_daily_payments);
$daily_payments = $row_daily_payments['total_daily_payments'];

// Accept or Decline Reservation
if (isset($_GET['action']) && isset($_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    $action = $_GET['action'];

    // Update the reservation status
    $stmt = $conn->prepare("UPDATE reservations SET status = ? WHERE reservation_id = ?");
    $stmt->bind_param("si", $action, $reservation_id);
    if ($stmt->execute()) {
        if ($action == 'accepted') {

            // Insert payment when reservation is accepted
            $stmt = $conn->prepare("SELECT total_cost, user_id FROM reservations WHERE reservation_id = ?");
            $stmt->bind_param("i", $reservation_id);
            $stmt->execute();
            $stmt->bind_result($total_cost, $user_id);
            $stmt->fetch();
            $stmt->close();

            // Insert payment record
            $payment_date = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO payments (reservation_id, payment_date, amount) VALUES (?, ?, ?)");
            $stmt->bind_param("isd", $reservation_id, $payment_date, $total_cost);
            if ($stmt->execute()) {
                // deduct the reserved amount
                $stmt = $conn->prepare("UPDATE users SET wallet_balance = wallet_balance - ? WHERE user_id = ?");
                $stmt->bind_param("di", $total_cost, $user_id);
                $stmt->execute();
            }
        }
        $success_message = "Reservation status updated successfully!";
    } else {
        $error_message = "Error updating reservation status. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            font-family: 'Arial', sans-serif;
        }

        .container {
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }

        h2 {
            color: #343a40;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .card-header {
            background-color: #343a40;
            color: #fff;
            font-weight: bold;
        }

        .dashboard-summary .card {
            margin-bottom: 20px;
        }

        .status-badge {
            font-weight: bold;
            border-radius: 5px;
            padding: 5px 10px;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #ffc107;
            color: #fff;
        }

        .status-accepted {
            background-color: #28a745;
            color: #fff;
        }

        .status-declined {
            background-color: #dc3545;
            color: #fff;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .action-btns {
            display: flex;
            justify-content: space-evenly;
            gap: 10px;
            align-items: center;
        }

        .action-btns a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 20px;
            font-size: 18px;
            border-radius: 5px;
            transition: all 0.3s;
            min-width: 120px;
        }

        .action-btns a i {
            margin-right: 8px;
        }

        .action-btns a:hover {
            opacity: 0.8;
        }

        .table-wrapper {
            max-height: 400px;
            overflow-x: auto;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            h2 {
                font-size: 1.5rem;
            }

            .action-btns a {
                padding: 12px 16px;
                font-size: 16px;
                min-width: 100px;
            }
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <h2 class="text-center">Admin Dashboard - Reservations</h2>

        <!-- Success and Error Messages -->
        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <!-- Dashboard Summary -->
        <div class="row dashboard-summary">
            <div class="col-md-3">
                <div class="card text-white bg-primary">
                    <div class="card-header">Total Users</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $total_users ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-success">
                    <div class="card-header">Total Reservations</div>
                    <div class="card-body">
                        <h5 class="card-title"><?= $total_reservations ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-warning">
                    <div class="card-header">Total Income</div>
                    <div class="card-body">
                        <h5 class="card-title">$<?= number_format($total_income, 2) ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-white bg-info">
                    <div class="card-header">Daily Payments</div>
                    <div class="card-body">
                        <h5 class="card-title">$<?= number_format($daily_payments, 2) ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reservations Table -->
        <div class="table-wrapper mt-4">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Customer</th>
                        <th>Car</th>
                        <th>Plate ID</th>
                        <th>Pick up</th>
                        <th>Return</th>
                        <th>Payment</th>
                        <th>Payment Status</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $reservations->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['reservation_id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name'] . " " . $row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['model']) ?></td>
                            <td><?= htmlspecialchars($row['plate_id']) ?></td>
                            <td><?= htmlspecialchars($row['start_date']) ?></td>
                            <td><?= htmlspecialchars($row['end_date']) ?></td>
                            <td>$<?= htmlspecialchars($row['total_cost']) ?></td>
                            <td><?= htmlspecialchars($row['payment_status']) ?></td>
                            <td>
                                <span class="status-badge 
                                    <?= ($row['status'] == 'pending') ? 'status-pending' : 
                                        ($row['status'] == 'accepted' ? 'status-accepted' : 'status-declined') ?>">
                                    <?= ucfirst($row['status']) ?>
                                </span>
                            </td>
                            <td class="action-btns">
                                <?php if ($row['status'] == 'pending') : ?>
                                    <a href="?action=accepted&reservation_id=<?= $row['reservation_id'] ?>" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-circle"></i> Accept
                                    </a>
                                    <a href="?action=declined&reservation_id=<?= $row['reservation_id'] ?>" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times-circle"></i> Decline
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>


