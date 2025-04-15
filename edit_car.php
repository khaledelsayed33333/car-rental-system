<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get car's details
if (isset($_GET['car_id'])) {
    $car_id = $_GET['car_id'];

    $stmt = $conn->prepare("SELECT * FROM cars WHERE car_id = ?");
    $stmt->bind_param("i", $car_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $car = $result->fetch_assoc();
    $stmt->close();

    if (!$car) {
        header("Location: manage_cars.php");
        exit();
    }
} else {
    header("Location: manage_cars.php");
    exit();
}

// Update car
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $model = $_POST['model'];
    $plate_id = $_POST['plate_id'];
    $year = $_POST['year'];
    $price_per_day = $_POST['price_per_day'];
    $status = $_POST['status'];
    $office_location = $_POST['office_location']; // New office location field

    $image = $car['image']; 
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["image"]["name"]);
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $image = $target_file;
        }
    }

    $stmt = $conn->prepare("UPDATE cars SET model = ?, plate_id = ?, year = ?, price_per_day = ?, status = ?, image = ?, office_location = ? WHERE car_id = ?");
    $stmt->bind_param("sssdsssi", $model, $plate_id, $year, $price_per_day, $status, $image, $office_location, $car_id);
    if ($stmt->execute()) {
        $success_message = "Car has been updated successfully!";
    } else {
        $error_message = "Error updating car. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Car</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            font-family: 'Arial', sans-serif;
        }
        .container {
            max-width: 600px;
            margin: auto;
            margin-top: 60px;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-weight: bold;
            color: #4a90e2;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #4a90e2;
            border: none;
        }
        .btn-primary:hover {
            background-color: #357ab7;
        }
        .btn-secondary {
            background-color: #868e96;
            border: none;
        }
        .btn-secondary:hover {
            background-color: #6c757d;
        }
        .form-control:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
        }
        .form-label {
            font-weight: bold;
            color: #333;
        }
        img {
            border-radius: 5px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Edit Car</h2>

        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="car_id" value="<?= $car['car_id'] ?>">

            <div class="mb-3">
                <label for="model" class="form-label">Model</label>
                <input type="text" name="model" id="model" class="form-control" value="<?= htmlspecialchars($car['model']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="plate_id" class="form-label">Plate ID</label>
                <input type="text" name="plate_id" id="plate_id" class="form-control" value="<?= htmlspecialchars($car['plate_id']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="year" class="form-label">Year</label>
                <input type="number" name="year" id="year" class="form-control" value="<?= htmlspecialchars($car['year']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="price_per_day" class="form-label">Price per Day</label>
                <input type="number" name="price_per_day" id="price_per_day" class="form-control" value="<?= htmlspecialchars($car['price_per_day']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-control" required>
                    <option value="active" <?= $car['status'] == 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="out_of_service" <?= $car['status'] == 'out_of_service' ? 'selected' : '' ?>>Out of Service</option>
                    <option value="rented" <?= $car['status'] == 'rented' ? 'selected' : '' ?>>Rented</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="office_location" class="form-label">Office Location</label>
                <input type="text" name="office_location" id="office_location" class="form-control" value="<?= htmlspecialchars($car['office_location']) ?>" required>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label">Car Image</label>
                <input type="file" name="image" id="image" class="form-control">
                <?php if (!empty($car['image'])): ?>
                    <p><strong>Current Image:</strong></p>
                    <img src="<?= htmlspecialchars($car['image']) ?>" alt="Car Image" style="width: 200px;">
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
        </form>

        <div class="mt-4 text-center">
            <a href="manage_cars.php" class="btn btn-secondary">Back to Manage Cars</a>
        </div>
    </div>
</body>
</html>
