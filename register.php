<?php
require_once 'db.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->execute(['username' => $username, 'password' => $password]);
        echo "<p>Registration successful! You can now <a href='login.php'>login</a>.</p>";
    } catch (PDOException $e) {
        echo "<p>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Register - User Account</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Page Background and General Styling */
        body {
            font-family: Arial, Helvetica, sans-serif;
            background: linear-gradient(135deg, #f8f9fa, #d9d9d9);
            color: #333;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            padding: 10px;
        }

        /* Logo Styling */
        .logo-container {
            margin-bottom: 20px;
        }

        .logo-container img {
            height: 100px;
            width: auto;
        }

        /* Form Container */
        .form-container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Form Title */
        h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #555;
        }

        /* Input Fields */
        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #cccccc;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #555;
            outline: none;
        }

        /* Submit Button */
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

    <!-- Main Registration Form -->
    <div class="form-container">
        <h1>Register</h1>
        <form method="POST">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" placeholder="Enter your username" required>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" placeholder="Enter your password" required>
            <button type="submit">Register</button>
        </form>
        <p style="margin-top: 10px; font-size: 14px;">
            Already have an account? <a href="login.php">Login here</a>.
        </p>
    </div>
</body>
</html>
