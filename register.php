<?php
require_once 'db.php';
session_start();

// Generate CSRF Token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = filter_var(trim($_POST['username']), FILTER_SANITIZE_STRING);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role'];
    $csrf_token = $_POST['csrf_token'];

    // Validate CSRF Token
    if (!hash_equals($_SESSION['csrf_token'], $csrf_token)) {
        die("CSRF token validation failed.");
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        die("<p>Passwords do not match.</p>");
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    try {
        // Insert user into database with role
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, :role)");
        $stmt->execute([
            'username' => $username,
            'password' => $hashed_password,
            'role' => $role
        ]);

        // Redirect to login
        header("Location: login.php?registered=success");
        exit;
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) {
            echo "<p>Error: Username already exists. Please choose another username.</p>";
        } else {
            echo "<p>Error: " . $e->getMessage() . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - User Account</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(to right, #4CAF50, #2E7D32);
            color: #fff;
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo-container img {
            width: 300px;
            height: auto;
        }

        .form-container {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            color: #333;
        }

        .form-container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #4CAF50;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 5px;
            font-weight: bold;
        }

        input, select {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #4CAF50;
        }

        button {
            padding: 10px;
            background: #4CAF50;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #388E3C;
        }

        p {
            text-align: center;
            font-size: 14px;
        }

        a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
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

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>

            <label for="role">Role:</label>
            <select id="role" name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
                <option value="collector">Collector</option>
            </select>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <button type="submit">Register</button>
        </form>
        <p style="margin-top: 10px; font-size: 14px;">
            Already have an account? <a href="login.php">Login here</a>.
        </p>
    </div>
</body>
</html>
