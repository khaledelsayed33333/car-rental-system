<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// news deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $stmt = $conn->prepare("DELETE FROM news WHERE news_id = ?");
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $success_message = "News item deleted successfully!";
    } else {
        $error_message = "Error deleting the news item.";
    }
    $stmt->close();
}

// Fetch current news items
$current_time = date('Y-m-d H:i:s');
$news_result = $conn->prepare("SELECT * FROM news WHERE expiration_time IS NULL OR expiration_time > ? ORDER BY created_at DESC");
$news_result->bind_param("s", $current_time);
$news_result->execute();
$news_items = $news_result->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage News</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Arial', sans-serif;
        }
        .container {
            margin-top: 50px;
            background: #ffffff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            color: #343a40;
            font-weight: bold;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .btn-danger {
            border-radius: 8px;
        }
        .alert {
            border-radius: 8px;
        }
        table {
            background: #4a90e2;
            border-radius: 12px;
            overflow: hidden;
        }
        th {
            background-color: #007bff;
            color: #ffffff;
            text-align: center;
        }
        td {
            vertical-align: middle;
        }
        @media (max-width: 768px) {
            .container {
                margin-top: 30px;
                padding: 15px;
            }
            h2 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Manage News</h2>

        <!-- Success and Error Messages -->
        <?php if (isset($success_message)) : ?>
            <div class="alert alert-success"> <?= htmlspecialchars($success_message) ?> </div>
        <?php endif; ?>
        <?php if (isset($error_message)) : ?>
            <div class="alert alert-danger"> <?= htmlspecialchars($error_message) ?> </div>
        <?php endif; ?>

        <!-- Add News Button -->
        <div class="d-flex justify-content-end mb-3">
            <a href="add_news.php" class="btn btn-primary">Add News</a>
        </div>

        <!-- News Table -->
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Description</th>
                    <th>Expiration Time</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($news = $news_items->fetch_assoc()) : ?>
                    <tr>
                        <td class="text-center"><?= htmlspecialchars($news['news_id']) ?></td>
                        <td><?= htmlspecialchars($news['title']) ?></td>
                        <td><?= htmlspecialchars(substr($news['description'], 0, 50)) . (strlen($news['description']) > 50 ? '...' : '') ?></td>
                        <td><?= htmlspecialchars($news['expiration_time'] ?? 'No expiration') ?></td>
                        <td class="text-center">
                            <a href="?delete_id=<?= $news['news_id'] ?>" class="btn btn-danger btn-sm" 
                               onclick="return confirm('Are you sure you want to delete this news item?')">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>

