<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $description = $_POST['description'];

    // expiration time
    $expiration_time = !empty($_POST['expiration_time']) ? $_POST['expiration_time'] : NULL;

    
    $image_path = null;
    if (!empty($_FILES['image']['name'])) {
        $target_dir = "img/"; // Set your folder to store images
        $target_file = $target_dir . basename($_FILES['image']['name']);
        $upload_ok = 1;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check === false) {
            $upload_ok = 0;
            echo "<div class='alert alert-danger'>File is not an image.</div>";
        }

        
        if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            $upload_ok = 0;
            echo "<div class='alert alert-danger'>Only JPG, JPEG, PNG & GIF files are allowed.</div>";
        }

        
        if ($upload_ok && move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
            $image_path = $target_file; 
        } else {
            echo "<div class='alert alert-danger'>Error uploading the file.</div>";
        }
    }

    // Insert the news
    if ($image_path) {
        $stmt = $conn->prepare("INSERT INTO news (title, description, image_path, expiration_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $title, $description, $image_path, $expiration_time);
    } else {
        
        $stmt = $conn->prepare("INSERT INTO news (title, description, expiration_time) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $title, $description, $expiration_time);
    }

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>News added successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding news. Please try again.</div>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #343a40;
            font-weight: bold;
        }
        label {
            font-weight: 600;
        }
        .form-control {
            border-radius: 8px;
        }
        .btn-success {
            background-color: #4a90e2;
            border: none;
            border-radius: 8px;
        }
        .btn-success:hover {
            background-color: #0056b3;
        }
        .form-text {
            font-size: 0.875rem;
            color: #6c757d;
        }
        @media (max-width: 768px) {
            .container {
                margin-top: 30px;
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
        <h2 class="text-center">Add News</h2>

        <form method="POST" action="add_news.php" enctype="multipart/form-data" class="mt-4">
            <!-- News Title -->
            <div class="mb-3">
                <label for="title" class="form-label">News Title</label>
                <input type="text" name="title" id="title" class="form-control" placeholder="Enter news title" required>
            </div>

            <!-- Description -->
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Enter news description" required></textarea>
            </div>

            <!-- Image Upload -->
            <div class="mb-3">
                <label for="image" class="form-label">Upload Image</label>
                <input type="file" name="image" id="image" class="form-control">
            </div>

            <!-- Expiration Time -->
            <div class="mb-3">
                <label for="expiration_time" class="form-label">Expiration Time (optional)</label>
                <input type="datetime-local" name="expiration_time" id="expiration_time" class="form-control">
                <small class="form-text">Leave this blank if you don't want the news to expire.</small>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-success w-100">Add News</button>
        </form>
    </div>
</body>
</html>
