<?php
require_once 'db.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, password FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error_message = "Invalid credentials.";
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login - User Account</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Page Background */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #e3eafc, #d9d9d9);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
        }

        /* Logo Section */
        .logo-container {
            margin-bottom: 20px;
        }

        .logo-container img {
            height: 100px;
            width: auto;
        }

        /* Login Form Styling */
        .form-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #555;
        }

        /* Input Fields Styling */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #555;
            outline: none;
        }

        /* Submit Button Styling */
        button[type="submit"] {
            background-color: #555;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        button[type="submit"]:hover {
            background-color: #333;
        }

        /* Error Message */
        .error-message {
            color: red;
            margin: 10px 0;
            font-size: 14px;
        }

        /* Links */
        a {
            color: #555;
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <!-- Logo Section -->
    <div class="logo-container">
        <img src="uploads/logo2.png" alt="Logo">
    </div>

    <!-- Login Form Section -->
    <div class="form-container">
        <h1>Login</h1>
        <?php if (isset($error_message)) { ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php } ?>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Login</button>
        </form>
        <p style="margin-top: 10px; font-size: 14px;">
            Don't have an account? <a href="register.php">Register here</a>.
        </p>
    </div>
</body>
</html>
