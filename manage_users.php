<?php
session_start();
include 'db_connection.php';


if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get all users
$users = $conn->query("SELECT * FROM users");

// User Deletion
if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
    
    if ($_SESSION['role'] == 'admin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: manage_users.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f9f9f9;
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
            color: #4a90e2;
            font-weight: bold;
        }
        table {
            background: #4a90e2;
            border-radius: 12px;
            overflow: hidden;
        }
        table th {
            background-color: #4a90e2; /* Ensures visibility */
            color: #ffffff; /* White text on blue background */
            text-align: center;
        }
        table td, table th {
            vertical-align: middle;
            text-align: center;
        }
        table tbody tr:hover {
            background-color: #ffffff;
        }
        .btn-warning {
            background-color: #f0ad4e;
            border: none;
            border-radius: 8px;
        }
        .btn-warning:hover {
            background-color: #ec971f;
        }
        .btn-danger {
            border-radius: 8px;
        }
        .btn-danger:hover {
            background-color: #c9302c;
        }
        .btn[disabled] {
            cursor: not-allowed;
            opacity: 0.6;
        }
        @media (max-width: 768px) {
            .table {
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Manage Users</h2>

        <h4 class="mt-5">Users List</h4>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>First Name</th>
                        <th>Last Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Wallet Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()) : ?>
                        <tr>
                            <td><?= htmlspecialchars($row['user_id']) ?></td>
                            <td><?= htmlspecialchars($row['first_name']) ?></td>
                            <td><?= htmlspecialchars($row['last_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['role']) ?></td>
                            <td>$<?= isset($row['wallet_balance']) ? number_format($row['wallet_balance'], 2) : '0.00' ?></td>
                            <td>
                                
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="edit_user.php?user_id=<?= $row['user_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                <?php else: ?>
                                    <button class="btn btn-warning btn-sm" disabled>Edit</button>
                                <?php endif; ?>

                                
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <a href="manage_users.php?user_id=<?= $row['user_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                                <?php else: ?>
                                    <button class="btn btn-danger btn-sm" disabled>Delete</button>
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

