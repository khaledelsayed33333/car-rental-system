<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connection.php';

// Fetch the user's reservations along with office location
$query = "SELECT r.reservation_id, c.model, r.start_date, r.end_date, r.total_cost, r.status, c.office_location 
          FROM reservations r
          JOIN cars c ON r.car_id = c.car_id
          WHERE r.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

if ($result === false) {
    echo "<div class='alert alert-danger'>Error fetching reservations: " . $conn->error . "</div>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f7f7f7;
            font-family: 'Arial', sans-serif;
        }

        .container {
            max-width: 1200px;
            margin-top: 50px;
        }

        h2 {
            color: #333;
            font-size: 2rem;
            font-weight: 600;
        }

        p {
            font-size: 1.1rem;
            color: #555;
        }

        table {
            margin-top: 30px;
            background-color: #bbb;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        th, td {
            padding: 15px;
            text-align: center;
            font-size: 16px;
        }

        th {
            background-color: #4e73df;
            color: white;
            text-transform: uppercase;
        }

        td {
            background-color: #f9f9f9;
        }

        tr:nth-child(even) td {
            background-color: #f1f1f1;
        }

        tr:hover {
            background-color: #4e73df;
        }

        .status-badge {
            font-weight: bold;
            padding: 8px 15px;
            border-radius: 12px;
            color: white;
            text-transform: capitalize;
        }

        .status-accepted {
            background-color: #28a745;
        }

        .status-pending {
            background-color: #ffc107;
        }

        .status-declined {
            background-color: #dc3545;
        }

        .alert {
            border-radius: 8px;
        }

        .btn-primary {
            background-color: #4e73df;
            border-color: #4e73df;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #2e59d9;
            border-color: #2e59d9;
        }

        .table-responsive {
            margin-top: 40px;
        }

        .table-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="table-container">
        <h2 class="text-center">My Reservations</h2>
        <p class="text-center">Below are your recent car reservations:</p>

        <?php if ($result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Car Model</th>
                            <th>Pick up</th>
                            <th>Return</th>
                            <th>Payment (EGP)</th>
                            <th>Office Location</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['reservation_id']) ?></td>
                                <td><?= htmlspecialchars($row['model']) ?></td>
                                <td><?= htmlspecialchars($row['start_date']) ?></td>
                                <td><?= htmlspecialchars($row['end_date']) ?></td>
                                <td><?= htmlspecialchars($row['total_cost']) ?> EGP</td>
                                <td><?= htmlspecialchars($row['office_location']) ?></td>
                                <td>
                                    <?php if ($row['status'] === 'accepted'): ?>
                                        <span class="status-badge status-accepted">Accepted</span>
                                    <?php elseif ($row['status'] === 'declined'): ?>
                                        <span class="status-badge status-declined">Declined</span>
                                    <?php else: ?>
                                        <span class="status-badge status-pending">Pending</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">You have no reservations at the moment.</div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Back to Dashboard</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
