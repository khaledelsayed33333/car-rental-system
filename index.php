<?php
session_start();
include 'db_connection.php';

// Ensure user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Get user's balance
    $stmt = $conn->prepare("SELECT wallet_balance FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $wallet_balance = $user ? $user['wallet_balance'] : 0;
} else {
    header("Location: login.php");
    exit();
}

// Most Reserved Cars Query
$most_reserved_cars_query = "
    SELECT c.car_id, c.model, c.plate_id, c.year, c.price_per_day, c.image, COUNT(r.car_id) AS reservation_count
    FROM cars c
    LEFT JOIN reservations r ON c.car_id = r.car_id
    GROUP BY c.car_id
    ORDER BY reservation_count DESC
    LIMIT 5
";
$most_reserved_cars_result = $conn->query($most_reserved_cars_query);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ($_SESSION['role'] == 'admin') ? 'Admin Dashboard' : 'Home' ?> - Car Rental System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            color: #333;
        }

        /* Sidebar Styling */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
            width: 250px;
            background-color: #333;
            color: #fff;
            padding: 20px;
            overflow-y: auto;
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1000;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        /* Sidebar links */
        .sidebar a {
            color: #fff;
            text-decoration: none;
            display: block;
            margin: 10px 0;
            padding: 15px 20px;
            border-radius: 8px;
            font-size: 16px;
            transition: background-color 0.3s ease, padding-left 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #444;
            padding-left: 30px;
        }

        .sidebar a.active {
            background-color: #007bff;
            color: #fff;
        }

        /* Close button */
        .close-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 24px;
            color: #fff;
            background: none;
            border: none;
            cursor: pointer;
        }

        .close-btn:hover {
            color: #ff6b6b;
        }

        /* Wallet Balance */
        .wallet-balance {
            margin-top: 20px;
            font-size: 16px;
            font-weight: bold;
            text-align: center;
            background-color: #28a745;
            padding: 12px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .sidebar .menu-header {
            text-align: center;
            font-size: 22px;
            font-weight: 600;
            color: #fff;
            margin-bottom: 30px;
        }

        /* Toggle button for sidebar */
        .toggle-btn {
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1100;
            cursor: pointer;
        }

        .toggle-btn img {
            width: 40px;
            height: 40px;
        }

        .toggle-btn.hidden {
            display: none;
        }

/* News Section Styling */
.news-section {
    margin-top: 40px;
    padding: 20px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.news-section h3 {
    text-align: center;
    font-size: 1.5rem;
    font-weight: 600;
    color: #007bff;
    margin-bottom: 30px;
}

.news-card {
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.news-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.news-card .news-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-bottom: 3px solid #ddd;
    transition: transform 0.3s ease;
}

.news-card:hover .news-image img {
    transform: scale(1.05);
}

.news-card .news-text {
    padding: 15px;
    font-family: 'Arial', sans-serif;
    color: #333;
}

.news-card .news-text h5 {
    font-size: 1.25rem;
    font-weight: bold;
    color: #007bff;
    margin-bottom: 10px;
}

.news-card .news-text p {
    font-size: 0.95rem;
    color: #555;
    margin-bottom: 15px;
    line-height: 1.6;
}

.news-card .news-text .text-muted {
    font-size: 0.85rem;
    color: #888;
}

/* Admin Action Buttons */
.news-card .mt-3 a {
    text-decoration: none;
    padding: 8px 12px;
    border-radius: 5px;
    font-size: 0.875rem;
    transition: background-color 0.3s ease, transform 0.3s ease;
}

.news-card .mt-3 a.btn-warning {
    background-color: #ffc107;
    color: #000;
}

.news-card .mt-3 a.btn-danger {
    background-color: #dc3545;
    color: #fff;
}

.news-card .mt-3 a:hover {
    transform: translateY(-2px);
}

.news-card .mt-3 a.btn-warning:hover {
    background-color: #e0a800;
}

.news-card .mt-3 a.btn-danger:hover {
    background-color: #c82333;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .news-section h3 {
        font-size: 1.25rem;
    }

    .news-card .news-text h5 {
        font-size: 1.1rem;
    }

    .news-card .news-text p {
        font-size: 0.9rem;
    }
}


        /* Main content styling */
        .content {
            margin-left: 0;
            transition: margin-left 0.3s ease;
            padding: 20px;
        }

        .content.shift {
            margin-left: 250px;
        }

        /* Add space around each card */
        .recommended-cars .card {
            margin-bottom: 20px;
        }

        /* Horizontal Scroll for Cars Section */
        .scrollable-cars {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 10px;
            scroll-snap-type: x mandatory;
        }

        .scrollable-cars::-webkit-scrollbar {
            height: 8px;
        }

        .scrollable-cars::-webkit-scrollbar-thumb {
            background-color: #007bff;
            border-radius: 4px;
        }

        .scrollable-cars::-webkit-scrollbar-track {
            background-color: #f4f4f4;
        }

        .scrollable-cars .card {
            flex: 0 0 auto;
            width: 250px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            scroll-snap-align: start;
        }

        .scrollable-cars .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        /* New Section Styling for Most Reserved Cars */
        .most-reserved-cars .card {
            margin-bottom: 20px;
        }

        /* Horizontal Scroll for Most Reserved Cars */
        .most-reserved-cars .scrollable-cars {
            display: flex;
            overflow-x: auto;
            gap: 20px;
            padding: 10px;
            scroll-snap-type: x mandatory;
        }

        .most-reserved-cars .scrollable-cars .card {
            flex: 0 0 auto;
            width: 250px;
            border: none;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            scroll-snap-align: start;
        }

        .most-reserved-cars .scrollable-cars .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <!-- Sidebar structure -->
    <div class="sidebar" id="sidebar">
        <button class="close-btn" onclick="closeSidebar()">×</button>
        <div class="menu-header">
            <h4>Car Rental</h4>
            <small><?= ($_SESSION['role'] == 'admin') ? 'Admin' : 'User' ?> menu</small>
        </div>

        <a href="index.php" class="<?= ($page == 'home') ? 'active' : '' ?>">Home</a>

        <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="admin_dashboard.php" class="<?= ($page == 'admin_dashboard') ? 'active' : '' ?>">Dashboard</a>
            <a href="manage_cars.php" class="<?= ($page == 'manage_cars') ? 'active' : '' ?>">Manage Cars</a>
            <a href="manage_users.php" class="<?= ($page == 'manage_users') ? 'active' : '' ?>">Manage Users</a>
            <a href="manage_news.php" class="<?= ($page == 'manage_news') ? 'active' : '' ?>">Manage News</a>
            <a href="fund_wallet.php" class="<?= ($page == 'fund_wallet') ? 'active' : '' ?>">Fund User Wallet</a>
        <?php else: ?>
            <a href="search_car.php" class="<?= ($page == 'search_car') ? 'active' : '' ?>">Browse Cars</a>
            <a href="my_reservation.php" class="<?= ($page == 'my_reservation') ? 'active' : '' ?>">My Reservations</a>
            <a href="user_profile.php" class="<?= ($page == 'user_profile') ? 'active' : '' ?>">Profile</a>
            <div class="wallet-balance">
                <strong>Balance: $<?= htmlspecialchars($wallet_balance) ?></strong>
            </div>
        <?php endif; ?>

        <a href="logout.php" class="btn btn-danger btn-block">Logout</a>
    </div>

    <!-- Toggle Button for Sidebar -->
    <div class="toggle-btn" id="toggleBtn" onclick="openSidebar()">
        <img src="img/icons8-bullet-list-100.png" alt="Menu">
    </div>

    <!-- Main Content -->
    <div class="content" id="content">
        <h2 class="text-center"><?= ($_SESSION['role'] == 'admin') ? 'Admin Dashboard' : 'Car Rental System' ?></h2>
        <p class="text-center">Hello, <?= htmlspecialchars($_SESSION['first_name']) ?></p>

    <!-- News Section -->
    <div class="news-section">
        <h3>Latest News & Updates</h3>
        <div class="row">
            <?php
            // Fetch latest active news from the database
            $news_result = $conn->query("SELECT * FROM news WHERE (expiration_time IS NULL OR expiration_time > NOW()) ORDER BY created_at DESC LIMIT 5");

            if ($news_result && $news_result->num_rows > 0):
                while ($news = $news_result->fetch_assoc()):
                    $image_path = !empty($news['image_path']) ? $news['image_path'] : 'img/default_news.jpg';
            ?>

            <div class="col-md-4 mb-4">
                <div class="news-card">
                    <!-- News Image -->
                    <div class="news-image mb-3">
                        <img src="<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="img-fluid rounded" style="width: 100%; height: auto;">
                    </div>
                    <!-- News Text -->
                    <div class="news-text">
                        <h5><?= htmlspecialchars($news['title']) ?></h5>
                        <p><?= htmlspecialchars($news['description']) ?></p>
                        <small class="text-muted">Published on: <?= htmlspecialchars($news['created_at']) ?></small>
                    </div>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <div class="mt-3">
                            <!-- Admin Action Buttons -->
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php endwhile; ?>
            <?php else: ?>
                <p>No news available at the moment. Stay tuned!</p>
            <?php endif; ?>
        </div>
    </div>
</div>

        <!-- Most Reserved Cars Section -->
        <?php if ($_SESSION['role'] != 'admin'): ?>
            <div class="most-reserved-cars mt-5">
                <h3 class="text-center mb-4" style="color: #007bff; font-weight: bold;">Most Wanted</h3>
                <div class="scrollable-cars">
                    <?php
                    if ($most_reserved_cars_result->num_rows > 0):
                        while ($car = $most_reserved_cars_result->fetch_assoc()): ?>
                            <div class="card">
                                <?php if (!empty($car['image'])): ?>
                                    <img src="<?= htmlspecialchars($car['image']) ?>" class="card-img-top" alt="Car Image">
                                <?php else: ?>
                                    <img src="default_car.jpg" class="card-img-top" alt="Default Car Image">
                                <?php endif; ?>
                                <div class="card-body text-center">
                                    <h5 class="card-title"><?= htmlspecialchars($car['model']) ?></h5>
                                    <p class="card-text">
                                        <strong>Plate ID:</strong> <?= htmlspecialchars($car['plate_id']) ?><br>
                                        <strong>Year:</strong> <?= htmlspecialchars($car['year']) ?><br>
                                        <strong>Price/Day:</strong> $<?= htmlspecialchars($car['price_per_day']) ?><br>
                                        <strong>Reservations:</strong> <?= htmlspecialchars($car['reservation_count']) ?>
                                    </p>
                                    <a href="reserve_car.php?car_id=<?= $car['car_id'] ?>&model=<?= urlencode($car['model']) ?>&price_per_day=<?= $car['price_per_day'] ?>" class="btn btn-primary">Reserve Now</a>
                                </div>
                            </div>
                        <?php endwhile; 
                    else: ?>
                        <div class="text-center w-100">
                            <p class="text-muted">No reserved cars yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Open/Close Scripts -->
    <script>
        const sidebar = document.getElementById('sidebar');
        const content = document.getElementById('content');
        const toggleBtn = document.getElementById('toggleBtn');

        function openSidebar() {
            sidebar.classList.add('active');
            content.classList.add('shift');
            toggleBtn.classList.add('hidden');
        }

        function closeSidebar() {
            sidebar.classList.remove('active');
            content.classList.remove('shift');
            toggleBtn.classList.remove('hidden');
        }
    </script>
</body>
</html>
