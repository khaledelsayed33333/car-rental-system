<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model = $_POST['model'];
    $plate_id = $_POST['plate_id'];
    $year = $_POST['year'];
    $price_per_day = $_POST['price_per_day'];
    $status = $_POST['status'];
    $office_location = $_POST['office_location']; // Get office location

    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/"; // organize images location
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    // Insert a new car including office location
    $stmt = $conn->prepare("INSERT INTO cars (model, plate_id, year, price_per_day, status, image, office_location) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssdsss", $model, $plate_id, $year, $price_per_day, $status, $image, $office_location);
    if ($stmt->execute()) {
        $success_message = "Car has been added successfully!";
    } else {
        $error_message = "Error adding car. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        h2 {
            color: #4a90e2;
            font-weight: bold;
        }
        .form-label {
            font-weight: bold;
            color: #333333;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-primary {
            background-color: #4a90e2;
            border: none;
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 1rem;
        }
        .btn-primary:hover {
            background-color: #357abd;
        }
        .btn-secondary {
            border-radius: 8px;
        }
        .alert {
            border-radius: 8px;
        }
        @media (max-width: 576px) {
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Add a New Car</h2>

        <!-- Error and Success Messages -->
        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger mt-3"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>
        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success mt-3"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <!-- Add Car Form -->
        <form method="POST" enctype="multipart/form-data" class="mt-4">
            <div class="mb-3">
                <label for="model" class="form-label">Model</label>
                <input type="text" name="model" id="model" class="form-control" placeholder="Enter car model" required>
            </div>

            <div class="mb-3">
                <label for="plate_id" class="form-label">Plate ID</label>
                <input type="text" name="plate_id" id="plate_id" class="form-control" placeholder="Enter plate ID" required>
            </div>

            <div class="mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" name="year" id="year" class="form-control" placeholder="Enter year of manufacture" required>
            </div>

            <div class="mb-3">
                <label for="price_per_day" class="form-label">Price per Day</label>
                <input type="number" name="price_per_day" id="price_per_day" class="form-control" placeholder="Enter price per day in USD" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="Available">Active</option>
                    <option value="Booked">Rented</option>
                    <option value="In Maintenance">Out of Service</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="office_location" class="form-label">Office Location</label>
                <input type="text" name="office_location" id="office_location" class="form-control" placeholder="Enter office location" required>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Car Image</label>
                <input type="file" name="image" id="image" class="form-control">
            </div>

            <button type="submit" class="btn btn-primary w-100">Add Car</button>
        </form>

        <!-- Back Button -->
        <div class="mt-4 text-center">
            <a href="manage_cars.php" class="btn btn-secondary">Back to Manage Cars</a>
        </div>
    </div>
</body>
</html>
