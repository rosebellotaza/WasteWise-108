<?php
require_once 'db.php'; // Include database connection
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

// Check if user is logged in and has the collector role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'collector') {
    die('Access Denied');
}



// Handle Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['insert'])) {
    try {
        $stmt = $pdo->prepare('INSERT INTO schedules (user_id, phone, address, waste_type, scheduled_date, scheduled_time, comments) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$_SESSION['user_id'], $_POST['phone'], $_POST['address'], $_POST['waste_type'], $_POST['scheduled_date'], $_POST['scheduled_time'], $_POST['comments']]);
        header('Location: collector_dashboard.php');
        exit;
    } catch (Exception $e) {
        die('Error inserting schedule: ' . $e->getMessage());
    }
}

//Delete a Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $schedule_id = $_POST['schedule_id'];

    $query = "DELETE FROM schedules WHERE id = :id";
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute([':id' => $schedule_id]);
        header('Location: collector_dashboard.php');
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}


// Handle schedule update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $schedule_id = $_POST['schedule_id'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $address = $_POST['address'] ?? null;
    $waste_type = $_POST['waste_type'] ?? null;
    $scheduled_date = $_POST['scheduled_date'] ?? null;
    $scheduled_time = $_POST['scheduled_time'] ?? null;
    $comments = $_POST['comments'] ?? null;

    if ($schedule_id && $phone && $address && $waste_type && $scheduled_date && $scheduled_time) {
        $query = "UPDATE schedules SET 
                    phone = :phone, 
                    address = :address, 
                    waste_type = :waste_type, 
                    scheduled_date = :scheduled_date, 
                    scheduled_time = :scheduled_time, 
                    comments = :comments
                  WHERE id = :id";
        $stmt = $pdo->prepare($query);

        try {
            // Update the schedule
            $stmt->execute([
                ':phone' => $phone,
                ':address' => $address,
                ':waste_type' => $waste_type,
                ':scheduled_date' => $scheduled_date,
                ':scheduled_time' => $scheduled_time,
                ':comments' => $comments,
                ':id' => $schedule_id
            ]);

            // Log the update action in activity_logs
            $logQuery = "INSERT INTO activity_logs (user_id, user_type, action) VALUES (:user_id, :user_type, :action)";
            $logStmt = $pdo->prepare($logQuery);
            
            $logDescription = "Updated schedule ID $schedule_id with phone: $phone, address: $address, waste type: $waste_type, date: $scheduled_date, time: $scheduled_time.";
            $logStmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':user_type' => 'collector',  // User type set as 'collector'
                ':action' => $logDescription,
            ]);
            


            // Redirect back to dashboard
            header('Location: collector_dashboard.php');
            exit;
        } catch (Exception $e) {
            echo "Error updating schedule: " . $e->getMessage();
        }
    }
}


// Fetch schedules
$query = "SELECT s.*, u.username 
          FROM schedules s 
          JOIN users u ON s.user_id = u.id 
          ORDER BY s.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute();
$schedules = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collector Dashboard</title>
    <style>
        /* Add styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .header {
            background-color: #4CAF50;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        .header h1 {
            margin: 0;
        }
        .container {
            width: 80%;
            margin: 30px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
        }
        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        td {
            background-color: #f9f9f9;
        }
        .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        .btn-update, .btn-delete {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-update {
            background-color: #4CAF50;
            color: white;
        }
        .btn-update:hover {
            background-color: #45a049;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-delete:hover {
            background-color: #d32f2f;
        }
        /* Form styles */
        form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }
        form input, form textarea, form button {
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        form button {
            background-color: #4CAF50;
            color: white;
            cursor: pointer;
        }
        form button:hover {
            background-color: #45a049;
        }

/* Header styling */
.header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background-color: #4CAF50; /* Green theme */
            color: white;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        /* Profile icon on the left */
        .profile-icon {
            width: 50px; /* Icon size */
            height: 50px;
            object-fit: cover;
            border-radius: 50%; /* Makes the icon circular */
        }

        /* Centered Dashboard title */
        .header h1 {
            margin: 0;
            flex-grow: 1; /* Pushes the logout button to the right */
            text-align: center;
            font-size: 1.5em;
        }

        /* Logout link on the right */
        .logout {
            color: white;
            text-decoration: none;
            background-color: #D9534F; /* Red button for logout */
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 0.9em;
        }

        .logout:hover {
            background-color: #C9302C;
        }

    </style>
</head>
<body>
    <!-- Header and logout button -->
    <div class="header">
        <a href="collector_profile.php">
            <img src="uploads/user-icom.png" alt="Logo" class="profile-icon">
        </a>
        <h1>Collector Dashboard</h1>
        <a href="?logout=true" class="logout">Logout</a>
    </div>

    <!-- Insert Form -->
    <div class="container">
        <h2>Insert Schedule</h2>
        <form method="POST" action="">
            <input type="text" name="phone" placeholder="Phone" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="text" name="waste_type" placeholder="Waste Type" required>
            <input type="date" name="scheduled_date" required>
            <input type="time" name="scheduled_time" required>
            <textarea name="comments" placeholder="Comments"></textarea>
            <button type="submit" name="insert">Insert</button>
        </form>
    </div>

    <!-- Schedules Table -->
    
    <!-- Schedules Section -->
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
                    <th>Actions</th>
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
                            <td class="actions">
                                <!-- Only show 'Update' button and form for the schedules list -->
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="update">
                                    <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                                    <button type="submit" class="btn-update">Update</button>
                                </form>

                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                                    <button type="submit" class="btn-delete">Delete</button>
                                </form>
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

    <!-- Update Form -->
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update'): ?>
        <?php
        $schedule_id = $_POST['schedule_id'];
        $query = "SELECT * FROM schedules WHERE id = :id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([':id' => $schedule_id]);
        $schedule = $stmt->fetch();
        ?>
        <div class="container">
            <h2>Update Schedule</h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                <input type="text" name="phone" placeholder="Phone" value="<?php echo htmlspecialchars($schedule['phone']); ?>" required />
                <input type="text" name="address" placeholder="Address" value="<?php echo htmlspecialchars($schedule['address']); ?>" required />
                <input type="text" name="waste_type" placeholder="Waste Type" value="<?php echo htmlspecialchars($schedule['waste_type']); ?>" required />
                <input type="date" name="scheduled_date" value="<?php echo htmlspecialchars($schedule['scheduled_date']); ?>" required />
                <input type="time" name="scheduled_time" value="<?php echo htmlspecialchars($schedule['scheduled_time']); ?>" required />
                <textarea name="comments" placeholder="Comments"><?php echo htmlspecialchars($schedule['comments']); ?></textarea>
                <button type="submit" class="btn-update">Update Schedule</button>
            </form>
        </div>
    <?php endif; ?>
</body>
</html>