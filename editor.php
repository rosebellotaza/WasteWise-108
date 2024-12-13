<?php
require_once 'db.php';
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

try {
    // Fetch all schedules with user information
    $stmt = $pdo->prepare("\n        SELECT \n            schedules.id, \n            schedules.phone, \n            schedules.address, \n            schedules.waste_type, \n            schedules.scheduled_date, \n            schedules.scheduled_time, \n            schedules.comments, \n            schedules.created_at,
            users.username \n        FROM schedules \n        INNER JOIN users ON schedules.user_id = users.id\n        ORDER BY schedules.created_at DESC\n    ");
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .header {
            background-color: #555;
            color: #fff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .back-btn {
            background-color: #4CAF50;
            color: white;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .back-btn:hover {
            background-color: #45a049;
        }

        .logout {
            background-color: #f44336;
            color: #fff;
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .logout:hover {
            background-color: #d32f2f;
        }

        .container {
            width: 90%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }

        table th {
            background-color: #555;
            color: #fff;
        }

        table tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table tr:hover {
            background-color: #f1f1f1;
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="admin_dashboard.php" class="back-btn">&larr; Back</a>
        <h1>Admin Dashboard</h1>
        <a href="?logout=true" class="logout">Logout</a>
    </div>

    <div class="container">
        <h2>Schedules</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>User</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Waste Type</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Comments</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($schedules) > 0): ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($schedule['id']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['username']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['phone']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['address']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['waste_type']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['scheduled_date']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['scheduled_time']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['comments']); ?></td>
                            <td><?php echo htmlspecialchars($schedule['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" style="text-align: center;">No schedules found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>