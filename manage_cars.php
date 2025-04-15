<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all cars
$cars = $conn->query("SELECT * FROM cars");

// Car Deletion
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];
    $stmt = $conn->prepare("DELETE FROM cars WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_cars.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Cars - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            background-color: #f4f7fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 30px;
        }
        .car-card {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            overflow: hidden;
            transition: transform 0.3s ease-in-out;
        }
        .car-card:hover {
            transform: scale(1.05);
        }
        .car-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            transition: opacity 0.3s ease;
        }
        .car-card img:hover {
            opacity: 0.8;
        }
        .card-body {
            padding: 20px;
        }
        .car-card h5 {
            font-size: 1.3rem;
            color: #007bff;
            font-weight: bold;
        }
        .badge {
            padding: 8px 14px;
            font-size: 0.9rem;
            border-radius: 30px;
        }
        .btn-action {
            margin-right: 10px;
            font-size: 0.9rem;
        }
        .btn-action:hover {
            opacity: 0.8;
        }
        .btn-success {
            background-color: #28a745;
            border: none;
        }
        .btn-success:hover {
            background-color: #218838;
        }
        .btn-danger {
            background-color: #dc3545;
            border: none;
        }
        .btn-danger:hover {
            background-color: #c82333;
        }
        .btn-warning {
            background-color: #ffc107;
            border: none;
        }
        .btn-warning:hover {
            background-color: #e0a800;
        }
        @media (max-width: 768px) {
            .car-card img {
                height: 160px;
            }
        }
        .text-center a {
            font-size: 1.2rem;
            padding: 12px 30px;
            border-radius: 30px;
            text-transform: uppercase;
            font-weight: bold;
        }
        .text-center a:hover {
            background-color: #0056b3;
            color: #fff;
        }
        .heading {
            font-size: 2rem;
            text-align: center;
            margin-bottom: 20px;
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="heading">Manage Cars</div>

        <div class="text-center mb-4">
            <a href="add_car.php" class="btn btn-success btn-lg">Add New Car</a>
        </div>

        <div class="row">
            <?php while ($row = $cars->fetch_assoc()) : ?>
                <div class="col-md-4 col-sm-6">
                    <div class="car-card">
                        <?php if (!empty($row['image'])) : ?>
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['model']) ?>">
                        <?php else : ?>
                            <img src="placeholder.jpg" alt="No Image Available">
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['model']) ?> (<?= htmlspecialchars($row['year']) ?>)</h5>
                            <p class="card-text"><strong>Plate ID:</strong> <?= htmlspecialchars($row['plate_id']) ?></p>
                            <p class="card-text"><strong>Price/Day:</strong> $<?= htmlspecialchars($row['price_per_day']) ?></p>
                            <p class="card-text"><strong>Office Location:</strong> <?= htmlspecialchars($row['office_location']) ?></p>
                            <p class="card-text">
                                <span class="badge
                                    <?php
                                        if ($row['status'] == 'active') echo 'bg-success';
                                        elseif ($row['status'] == 'out_of_service') echo 'bg-danger';
                                        elseif ($row['status'] == 'rented') echo 'bg-warning text-dark';
                                        else echo 'bg-secondary';
                                    ?>">
                                    <?= htmlspecialchars(ucwords($row['status'])) ?>
                                </span>
                            </p>
                            <div class="d-flex justify-content-between">
                                <a href="edit_car.php?car_id=<?= $row['car_id'] ?>" class="btn btn-warning btn-action">Edit</a>
                                <a href="manage_cars.php?car_id=<?= $row['car_id'] ?>" class="btn btn-danger btn-action" onclick="return confirm('Are you sure you want to delete this car?')">Delete</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>
