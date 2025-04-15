<?php
session_start();
include 'db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, phone, address, wallet_balance, profile_image FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
unset($_SESSION['success_message'], $_SESSION['error_message']);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    
    $profile_image = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_image"]["name"]);
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            $profile_image = $target_file;
        }
    }

    // Check if 'Delete Photo' is requested
    if (isset($_POST['delete_photo']) && $_POST['delete_photo'] == '1') {
        // Delete photo by resetting to default
        $profile_image = 'img/Default Avatar.jpg';
    }

    // Update the user details and profile image
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ?, profile_image = ? WHERE user_id = ?");
    $stmt->bind_param("ssssssi", $first_name, $last_name, $email, $phone, $address, $profile_image, $user_id);
    if ($stmt->execute()) {
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error updating profile. Please try again.";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Car Rental System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
        }
        .profile-container {
            max-width: 700px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            text-align: center;
            position: relative;
        }
        .form-label {
            font-weight: 600;
            color: #333;
        }
        .btn-primary {
            background-color: #2575fc;
            border: none;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #6a11cb;
        }
        .profile-image-container {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto 25px;
            position: relative;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        .profile-image-container:hover {
            transform: scale(1.1);
        }
        .profile-image-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .profile-image-preview {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }
        .options-dropdown {
            display: none;
            position: absolute;
            bottom: 0;
            left: 0;
            background-color: #fff;
            width: 100%;
            padding: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            z-index: 1000;
        }
        .options-dropdown a {
            text-decoration: none;
            color: #2575fc;
            display: block;
            margin: 10px 0;
        }
        .alert {
            text-align: center;
            font-size: 1.1em;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <h2 class="text-center text-dark">My Profile</h2>

        <?php if ($success_message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php endif; ?>

        <form action="user_profile.php" method="post" enctype="multipart/form-data" class="mt-4">
            <div class="profile-image-container" id="profile-image-container">
                <?php
                $profile_image_path = !empty($user['profile_image']) ? $user['profile_image'] : 'img/Default Avatar.jpg';
                ?>
                <img src="<?= htmlspecialchars($profile_image_path) ?>" alt="Profile Image" class="profile-image-preview">
                <!-- Options Dropdown -->
                <div class="options-dropdown" id="options-dropdown">
                    <a href="#" id="change-photo">Change Photo</a>
                    <a href="#" id="delete-photo">Delete Photo</a>
                </div>
            </div>

            <div class="mb-3">
                <label for="profile_image" class="form-label">Profile Image (Optional):</label>
                <input type="file" name="profile_image" id="profile_image" class="form-control">
            </div>

            <div class="mb-3">
                <label for="first_name" class="form-label">First Name:</label>
                <input type="text" name="first_name" id="first_name" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="last_name" class="form-label">Last Name:</label>
                <input type="text" name="last_name" id="last_name" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone:</label>
                <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <div class="mb-3">
                <label for="address" class="form-label">Address:</label>
                <textarea name="address" id="address" class="form-control" rows="3"><?= htmlspecialchars($user['address']) ?></textarea>
            </div>
            <div class="mb-3">
                <label for="wallet_balance" class="form-label">Wallet Balance:</label>
                <input type="text" id="wallet_balance" class="form-control" value="$<?= htmlspecialchars($user['wallet_balance']) ?>" disabled>
            </div>

            <button type="submit" class="btn btn-primary w-100">Save Changes</button>
            <a href="change_password.php" class="btn btn-warning w-100 mt-3">Change Password</a>
            <a href="index.php" class="btn btn-secondary w-100 mt-3">Cancel</a>

            <!-- Add Hidden Input for Delete Photo -->
            <input type="hidden" name="delete_photo" id="delete_photo" value="0">
        </form>
    </div>

    <script>
        const profileImageContainer = document.getElementById('profile-image-container');
        const optionsDropdown = document.getElementById('options-dropdown');
        const changePhoto = document.getElementById('change-photo');
        const deletePhoto = document.getElementById('delete-photo');
        const deletePhotoInput = document.getElementById('delete_photo');

        profileImageContainer.addEventListener('click', function() {
            optionsDropdown.style.display = optionsDropdown.style.display === 'block' ? 'none' : 'block';
        });

        changePhoto.addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('profile_image').click();
        });

        deletePhoto.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm("Are you sure you want to delete your photo?")) {
                deletePhotoInput.value = "1";
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>
