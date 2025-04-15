<?php
include 'db_connection.php';

// Fetch available cars
$availableCars = [];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    $sql = "SELECT * FROM cars
            WHERE car_id NOT IN (
                SELECT car_id
                FROM reservations
                WHERE ('$start_date' BETWEEN start_date AND end_date)
                OR ('$end_date' BETWEEN start_date AND end_date)
            ) AND status = 'active'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $availableCars[] = $row;
        }
    }
}

// reservation submission
if (isset($_POST['reserve'])) {
    $user_id = $_POST['user_id'];
    $car_id = $_POST['car_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];

    // Calculate the total cost
    $sqlPrice = "SELECT price_per_day FROM cars WHERE car_id = $car_id";
    $priceResult = $conn->query($sqlPrice);
    $price = $priceResult->fetch_assoc()['price_per_day'];
    $total_days = (strtotime($end_date) - strtotime($start_date)) / (60 * 60 * 24) + 1;
    $total_cost = $price * $total_days;

    // Insert reservation
    $sql = "INSERT INTO reservations (user_id, car_id, start_date, end_date, total_cost, payment_status)
            VALUES ('$user_id', '$car_id', '$start_date', '$end_date', '$total_cost', 'pending')";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='alert alert-success'>Reservation successful! Total cost: $total_cost</div>";
    } else {
        echo "<div class='alert alert-danger'>Error: " . $sql . "<br>" . $conn->error . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Car Reservation</title>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Car Reservation</h2>
        <form method="POST" action="reservation.php" class="mt-4">
            <div class="mb-3">
                <label for="start_date" class="form-label">Start Date</label>
                <input type="date" class="form-control" name="start_date" id="start_date" required>
            </div>
            <div class="mb-3">
                <label for="end_date" class="form-label">End Date</label>
                <input type="date" class="form-control" name="end_date" id="end_date" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Search Available Cars</button>
        </form>

        <?php if (!empty($availableCars)) : ?>
            <h3 class="mt-5">Available Cars</h3>
            <form method="POST" action="reservation.php">
                <input type="hidden" name="user_id" value="1"> <!-- Replace with dynamic logged-in user ID -->
                <input type="hidden" name="start_date" value="<?= htmlspecialchars($_POST['start_date']) ?>">
                <input type="hidden" name="end_date" value="<?= htmlspecialchars($_POST['end_date']) ?>">
                <div class="mb-3">
                    <select class="form-select" name="car_id" required>
                        <option value="" disabled selected>Select a Car</option>
                        <?php foreach ($availableCars as $car) : ?>
                            <option value="<?= $car['car_id'] ?>">
                                <?= $car['model'] ?> (<?= $car['plate_id'] ?>) - $<?= $car['price_per_day'] ?>/day
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" name="reserve" class="btn btn-success w-100">Reserve</button>
            </form>
        <?php elseif ($_SERVER["REQUEST_METHOD"] == "POST") : ?>
            <div class="alert alert-warning mt-4">No cars available for the selected dates.</div>
        <?php endif; ?>
    </div>
</body>
</html>
