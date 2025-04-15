<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$searchTerm = "";
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $stmt = $conn->prepare("SELECT * FROM cars WHERE model LIKE ? OR plate_id LIKE ? OR office_location LIKE ?");
    $searchQuery = "%" . $searchTerm . "%";
    $stmt->bind_param("sss", $searchQuery, $searchQuery, $searchQuery);
    $stmt->execute();
    $cars = $stmt->get_result();
} else {
    $cars = $conn->query("SELECT * FROM cars");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Cars</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f9f9f9;
            background-size: cover;
            min-height: 100vh;
            padding: 20px;
        }

        .search-bar {
            margin-bottom: 30px;
        }

        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card img {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
            height: 200px;
            object-fit: cover;
        }

        .card-body {
            text-align: center;
            background-color: #f9f9f9;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }

        .reserve-btn {
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 10px 20px;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .reserve-btn:hover {
            background-color: #218838;
        }

        .no-results {
            color: #888;
            text-align: center;
            font-size: 18px;
            margin-top: 20px;
        }
        .text-center{
            color: black;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="text-center">Search Available Cars</h2>

        <form method="GET" class="input-group search-bar">
            <input type="text" name="search" class="form-control" placeholder="Search by model, plate ID, or office location" value="<?= htmlspecialchars($searchTerm) ?>">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i>
            </button>
        </form>

        <div class="row">
            <?php if ($cars->num_rows > 0): ?>
                <?php while ($row = $cars->fetch_assoc()) : ?>
                    <div class="col-md-4 mb-4">
                        <div class="card">
                            <?php if (!empty($row['image'])) : ?>
                                <img src="<?= htmlspecialchars($row['image']) ?>" class="card-img-top" alt="Car Image">
                            <?php else : ?>
                                <img src="img/default-car.jpg" class="card-img-top" alt="Default Car Image">
                            <?php endif; ?>

                            <div class="card-body">
                                <h5 class="card-title">Model: <?= htmlspecialchars($row['model']) ?></h5>
                                <p class="card-text">Plate ID: <?= htmlspecialchars($row['plate_id']) ?></p>
                                <p class="card-text">Year: <?= htmlspecialchars($row['year']) ?></p>
                                <p class="card-text">Price/Day: $<?= htmlspecialchars($row['price_per_day']) ?></p>
                                <p class="card-text">Location: <?= htmlspecialchars($row['office_location']) ?></p>

                                <?php if ($row['status'] == 'active') : ?>
                                    <a href="reserve_car.php?car_id=<?= $row['car_id'] ?>&model=<?= urlencode($row['model']) ?>&price_per_day=<?= $row['price_per_day'] ?>" class="btn reserve-btn">Reserve</a>
                                <?php else : ?>
                                    <span class="text-muted">Not Available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="no-results">No cars found.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
