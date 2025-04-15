<?php
include 'db_connection.php';

// Fetch pending reservations
$user_id = 1;
$pendingReservations = [];

$sql = "SELECT r.*, c.model, c.plate_id, c.price_per_day
        FROM reservations r
        JOIN cars c ON r.car_id = c.car_id
        WHERE r.user_id = $user_id AND r.payment_status = 'pending'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pendingReservations[] = $row;
    }
}

// payment submission
if (isset($_POST['pay'])) {
    $reservation_id = $_POST['reservation_id'];
    $amount = $_POST['amount'];

    // Insert payment
    $sqlPayment = "INSERT INTO payments (reservation_id, amount) VALUES ('$reservation_id', '$amount')";
    if ($conn->query($sqlPayment) === TRUE) {
        // Update reservation status
        $sqlUpdate = "UPDATE reservations SET payment_status = 'paid' WHERE reservation_id = $reservation_id";
        if ($conn->query($sqlUpdate) === TRUE) {
            echo "<div class='alert alert-success'>Payment successful!</div>";
        } else {
            echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Error: " . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Make Payment</title>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Pending Payments</h2>

        <?php if (!empty($pendingReservations)) : ?>
            <table class="table table-bordered mt-4">
                <thead>
                    <tr>
                        <th>Reservation ID</th>
                        <th>Car</th>
                        <th>Plate ID</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Total Cost</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingReservations as $reservation) : ?>
                        <tr>
                            <td><?= htmlspecialchars($reservation['reservation_id']) ?></td>
                            <td><?= htmlspecialchars($reservation['model']) ?></td>
                            <td><?= htmlspecialchars($reservation['plate_id']) ?></td>
                            <td><?= htmlspecialchars($reservation['start_date']) ?></td>
                            <td><?= htmlspecialchars($reservation['end_date']) ?></td>
                            <td>$<?= htmlspecialchars($reservation['total_cost']) ?></td>
                            <td>
                                <form method="POST" action="payment.php">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['reservation_id'] ?>">
                                    <input type="hidden" name="amount" value="<?= $reservation['total_cost'] ?>">
                                    <button type="submit" name="pay" class="btn btn-success btn-sm">Pay</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="alert alert-info">No pending payments at the moment.</div>
        <?php endif; ?>
    </div>
</body>
</html>
