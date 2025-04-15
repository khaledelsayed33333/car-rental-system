<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// reservation update
if (isset($_GET['action'], $_GET['reservation_id'])) {
    $reservation_id = $_GET['reservation_id'];
    $action = $_GET['action'];

    // Decline reservation
    if ($action == 'decline') {
        // Get the user ID and total cost of the declined reservation
        $stmt = $conn->prepare("SELECT user_id, total_cost FROM reservations WHERE reservation_id = ?");
        $stmt->bind_param("i", $reservation_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $reservation = $result->fetch_assoc();
            $user_id = $reservation['user_id'];
            $total_cost = $reservation['total_cost'];

            // Fetch the user's balance
            $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                $wallet_balance = $user['wallet_balance'];

                // Refund the money back to the user's wallet
                $new_balance = $wallet_balance + $total_cost;
                $update_wallet = $conn->prepare("UPDATE users SET wallet_balance = ? WHERE user_id = ?");
                $update_wallet->bind_param("di", $new_balance, $user_id);
                
                if ($update_wallet->execute()) {
                    // Successfully updated the wallet balance
                    $update_reservation = $conn->prepare("UPDATE reservations SET status = 'declined' WHERE reservation_id = ?");
                    $update_reservation->bind_param("i", $reservation_id);
                    $update_reservation->execute();
                    
                    $success_message = "Reservation declined successfully and refund processed!";
                } else {
                    $error_message = "Error updating the user's wallet balance. Please try again.";
                }
            } else {
                $error_message = "User not found for this reservation.";
            }
        } else {
            $error_message = "Reservation not found.";
        }
    }
}

// Fetch all reservations
$reservations = $conn->query("SELECT r.*, u.first_name, u.last_name, c.model, c.plate_id 
                              FROM reservations r
                              JOIN users u ON r.user_id = u.user_id
                              JOIN cars c ON r.car_id = c.car_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Manage Reservations</h2>

        
        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Reservation ID</th>
                    <th>Customer</th>
                    <th>Car</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Total Cost</th>
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
                        <td><?= htmlspecialchars($row['start_date']) ?></td>
                        <td><?= htmlspecialchars($row['end_date']) ?></td>
                        <td>$<?= htmlspecialchars($row['total_cost']) ?></td>
                        <td>
                            <?php
                                if ($row['status'] == 'pending') {
                                    echo '<span class="badge bg-warning">Pending</span>';
                                } elseif ($row['status'] == 'accepted') {
                                    echo '<span class="badge bg-success">Accepted</span>';
                                } elseif ($row['status'] == 'declined') {
                                    echo '<span class="badge bg-danger">Declined</span>';
                                }
                            ?>
                        </td>
                        <td>
                            
                            <?php if ($row['status'] == 'pending') : ?>
                                <a href="?action=accept&reservation_id=<?= $row['reservation_id'] ?>" class="btn btn-success">Accept</a>
                                <a href="?action=decline&reservation_id=<?= $row['reservation_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to decline this reservation?')">Decline</a>
                            <?php else: ?>
                                <span class="text-muted">N/A</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
