<?php
require_once 'db.php';
session_start();

// Check if the user is logged in and has the 'admin' role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Access Denied');
}

$successMessage = '';

try {

    // Get the user's information from the users table
    $userStmt = $pdo->prepare("SELECT * FROM users WHERE id = :user_id");
    $userStmt->execute(['user_id' => $_SESSION['user_id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Handle username update
if (isset($_POST['update_username'])) {
    $new_username = $_POST['new_username'];

    try {
        // Check if the username is already taken
        $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
        $checkStmt->execute(['username' => $new_username]);
        if ($checkStmt->fetchColumn() > 0) {
            $successMessage = "<span style='color: red;'>Username is already taken!</span>";
        } else {
            // Update the username
            $updateStmt = $pdo->prepare("UPDATE users SET username = :new_username WHERE id = :user_id");
            $updateStmt->execute(['new_username' => $new_username, 'user_id' => $_SESSION['user_id']]);
            
            // Update the username locally for display
            $user['username'] = $new_username;
            $successMessage = "<span style='color: green;'>Username updated successfully!</span>";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

try {
    // Fetch logs performed by the current admin
    $logStmt = $pdo->prepare("
    SELECT timestamp, user_type, action, table_name, column_name
    FROM activity_logs 
    WHERE user_type = 'admin'
    ORDER BY timestamp DESC
");
$logStmt->execute();
$logs = $logStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Schedule</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f4f9;
            color: #333;
            padding: 20px;
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 80%;
            margin: 20px auto;
        }

        .table-container h3 {
            text-align: center;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-container table, th, td {
            border: 1px solid #ccc;
        }

        .table-container th, td {
            padding: 12px;
            text-align: left;
        }

        .btn-container {
            position: fixed;
            top: 20px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 20px;
        }

        .btn-container a {
            background-color: #4CAF50;
            color: white;
            padding: 10px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            margin-right: 70px;
        }

        .btn-container a:hover {
            background-color: #388E3C;
        }

        /* Update form styles */
        .update-username-form input[type="text"] {
            padding: 10px;
            width: 200px;
        }

        .update-username-form button {
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
        }

        .update-username-form button:hover {
            background-color: #388E3C;
        }

        .success-message {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <!-- Navigation buttons -->
    <div class="btn-container">
        <a href="admin_dashboard.php">Back</a> 
        <a href="?logout">Logout</a>
    </div>

    <!-- Success Message -->
    <?php if ($successMessage): ?>
        <div class="success-message">
            <?php echo $successMessage; ?>
        </div>
    <?php endif; ?>

    <!-- User Table -->
    <div class="table-container">
        <h3>Your User Information</h3>
        <table>
            <tr>
                <th>ID</th>
                <th>Username</th>
                <th>Password</th>
                <th>Role</th>
                <th>Created At</th>
                <th>Update Username</th>
            </tr>
            <tr>
                <td><?php echo htmlspecialchars($user['id']); ?></td>
                <td><?php echo htmlspecialchars($user['username']); ?></td>
                <td>*****</td> <!-- Hide the password for security -->
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td><?php echo htmlspecialchars($user['created_at']); ?></td>
                <td>
                    <form method="POST" class="update-username-form">
                        <input type="text" name="new_username" placeholder="New Username" required>
                        <button type="submit" name="update_username">Update</button>
                    </form>
                </td>
            </tr>
        </table>
    </div>
    <div class="table-container">
    <h3>Activity Logs (Performed by Admin)</h3>
    <table>
        <tr>
            <th>Date & Time</th>
            <th>User Type</th>
            <th>Action</th>
            <th>Table Name</th>
            <th>Column Name</th>
        </tr>
        <?php if (!empty($logs)): ?>
            <?php foreach ($logs as $log): ?>
    <tr>
        <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
        <td><?php echo htmlspecialchars($log['user_type']); ?></td>
        <td><?php echo htmlspecialchars($log['action']); ?></td>
        <td><?php echo htmlspecialchars($log['table_name']); ?></td>
        <td><?php echo htmlspecialchars($log['column_name']); ?></td>
    </tr>
<?php endforeach; ?>

        <?php else: ?>
            <tr>
                <td colspan="5" style="text-align: center;">No logs available</td>
            </tr>
        <?php endif; ?>
    </table>
</div>



</body>
</html>
