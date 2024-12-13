<?php
require_once 'db.php';
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Fetch schedules
$query = "SELECT s.*, u.username FROM schedules s JOIN users u ON s.user_id = u.id ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$schedules = $stmt->fetchAll();

// Fetch activity logs
// Fetch activity logs
$filter_sql = "SELECT id, timestamp, user_type, action, table_name, column_name 
               FROM activity_logs 
               ORDER BY timestamp DESC";
$stmt = $pdo->prepare($filter_sql);
$stmt->execute();
$logs = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

        .actions a {
            margin-right: 10px;
            color: #007BFF;
            text-decoration: none;
        }

        .actions a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="header">
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

                            </td>
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

    <div class="container">
    <h2>Activity Logs</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Timestamp</th>
                <th>User Type</th>
                <th>Action</th>
                <th>Table Name</th>
                <th>Column Name</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($logs) > 0): ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['id']); ?></td>
                        <td><?php echo htmlspecialchars($log['timestamp']); ?></td>
                        <td><?php echo htmlspecialchars($log['user_type']); ?></td>
                        <td><?php echo htmlspecialchars($log['action']); ?></td>
                        <td><?php echo htmlspecialchars($log['table_name']); ?></td>
                        <td><?php echo htmlspecialchars($log['column_name']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No activity logs found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
