<?php
session_start();
include 'db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // fetch the user by email
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['first_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'];

            header("Location: index.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Invalid password!</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>No account found with this email address.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <title>Login</title>
    <style>
        body {
            background: url('img/inner.jpg') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .container {
            background: rgba(0, 0, 0, 0.6);
            color: white;
            padding: 30px;
            border-radius: 12px;
            width: 400px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.4);
            animation: fadeIn 1.5s;
        }
        .logo {
            display: block;
            margin: 0 auto 20px;
            width: 120px;
        }
        .btn-primary {
            background-color: #ff5722;
            border: none;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #e64a19;
        }
        .form-control {
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .position-relative .btn-link {
            color: white;
        }
        .alert {
            background: rgba(255, 0, 0, 0.8);
            color: white;
            border: none;
            border-radius: 8px;
            text-align: center;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="img/default-image.jpg" class="logo" alt="Logo">
        <h2 class="text-center">Login</h2>
        <form method="POST" action="login.php" class="mt-4">
            <div class="mb-3">
                <input type="email" name="email" class="form-control" placeholder="Email" required>
            </div>
            <div class="mb-3 position-relative">
                <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                <button type="button" id="togglePassword" class="btn btn-link position-absolute p-0" 
                    style="top: 50%; right: 10px; transform: translateY(-50%);">
                    <i class="fa-solid fa-eye" id="eyeIcon"></i>
                </button>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <div class="mt-3 text-center">
            <p>Don't have an account? <a href="register.php" style="color: #ff9800;">Create one</a></p>
        </div>
    </div>

    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const passwordField = document.querySelector('#password');
        const eyeIcon = document.querySelector('#eyeIcon');

        togglePassword.addEventListener('click', function () {
            const type = passwordField.type === 'password' ? 'text' : 'password';
            passwordField.type = type;
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
