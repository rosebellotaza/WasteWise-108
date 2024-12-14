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

// Handle Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    try {
        $stmt = $pdo->prepare('DELETE FROM schedules WHERE id = ?');
        $stmt->execute([$_POST['schedule_id']]);
        header('Location: collector_dashboard.php');
        exit;
    } catch (Exception $e) {
        die('Error deleting schedule: ' . $e->getMessage());
    }
}


// Handle schedule update if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_collector'])) {
    $schedule_id = $_POST['schedule_id'];
    $phone = htmlspecialchars($_POST['phone']);
    $address = htmlspecialchars($_POST['address']);
    $waste_type = htmlspecialchars($_POST['waste_type']);
    $scheduled_date = htmlspecialchars($_POST['scheduled_date']);
    $scheduled_time = htmlspecialchars($_POST['scheduled_time']);
    $comments = htmlspecialchars($_POST['comments']);

    // Update the schedule in the database
    try {
        $stmt = $pdo->prepare('UPDATE schedules SET phone = ?, address = ?, waste_type = ?, scheduled_date = ?, scheduled_time = ?, comments = ? WHERE id = ?');
        $stmt->execute([$phone, $address, $waste_type, $scheduled_date, $scheduled_time, $comments, $schedule_id]);
        $message = 'Schedule updated successfully!';
    } catch (Exception $e) {
        $message = 'Error updating schedule: ' . $e->getMessage();
    }
}

// Fetch schedules from the database
try {
    $stmt = $pdo->prepare('SELECT schedules.*, users.username FROM schedules JOIN users ON schedules.user_id = users.id ORDER BY schedules.created_at DESC');
    $stmt->execute();
    $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('Error fetching schedules: ' . $e->getMessage());
}
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
        .logout {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background-color: #d32f2f;
            border-radius: 5px;
            float: right;
            margin-top: -50px;
        }
        .logout:hover {
            background-color: #b71c1c;
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
        /* Modal Styles */
        #updateScheduleModal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        #updateScheduleModal > div {
            background-color: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            width: 500px;
        }
    </style>
</head>
<body>
    <!-- Header and logout button -->
    <div class="header">
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
                <?php if (!empty($schedules)): ?>
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
                                <button type="button" class="btn-update" onclick="openUpdateForm(<?php echo htmlspecialchars($schedule['id']); ?>, '<?php echo htmlspecialchars($schedule['phone']); ?>', '<?php echo htmlspecialchars($schedule['address']); ?>', '<?php echo htmlspecialchars($schedule['waste_type']); ?>', '<?php echo htmlspecialchars($schedule['scheduled_date']); ?>', '<?php echo htmlspecialchars($schedule['scheduled_time']); ?>', '<?php echo htmlspecialchars($schedule['comments']); ?>')">Update</button>
                                <form method="POST" action="" style="display:inline;">
                                    <input type="hidden" name="schedule_id" value="<?php echo htmlspecialchars($schedule['id']); ?>">
                                    <button type="submit" name="delete" class="btn-delete">Delete</button>
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

    <!-- Update Schedule Form Modal -->
    <div id="updateScheduleModal">
        <div>
            <h3>Update Schedule</h3>
            <form method="POST" action="">
                <input type="hidden" name="schedule_id" id="schedule_id">
                <label for="phone">Phone:</label><br>
                <input type="text" name="phone" id="phone"><br><br>
                <label for="address">Address:</label><br>
                <input type="text" name="address" id="address"><br><br>
                <label for="waste_type">Waste Type:</label><br>
                <input type="text" name="waste_type" id="waste_type"><br><br>
                <label for="scheduled_date">Date:</label><br>
                <input type="date" name="scheduled_date" id="scheduled_date"><br><br>
                <label for="scheduled_time">Time:</label><br>
                <input type="time" name="scheduled_time" id="scheduled_time"><br><br>
                <label for="comments">Comments:</label><br>
                <textarea name="comments" id="comments"></textarea><br><br>
                <button type="submit" name="update_collector" class="btn-update">Update</button>
                <button type="button" onclick="closeUpdateForm()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openUpdateForm(id, phone, address, waste_type, scheduled_date, scheduled_time, comments) {
            document.getElementById('schedule_id').value = id;
            document.getElementById('phone').value = phone;
            document.getElementById('address').value = address;
            document.getElementById('waste_type').value = waste_type;
            document.getElementById('scheduled_date').value = scheduled_date;
            document.getElementById('scheduled_time').value = scheduled_time;
            document.getElementById('comments').value = comments;
            document.getElementById('updateScheduleModal').style.display = 'flex';
        }

        function closeUpdateForm() {
            document.getElementById('updateScheduleModal').style.display = 'none';
        }
    </script>
</body>
</html>