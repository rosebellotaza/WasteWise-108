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

// Check if user is logged in and has the admin role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die('Access Denied');
}


// Update an existing schedule
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
            $stmt->execute([
                ':phone' => $phone,
                ':address' => $address,
                ':waste_type' => $waste_type,
                ':scheduled_date' => $scheduled_date,
                ':scheduled_time' => $scheduled_time,
                ':comments' => $comments,
                ':id' => $schedule_id
            ]);
            echo "Schedule updated successfully.";
            header('Location: admin_dashboard.php');
            exit;
        } catch (Exception $e) {
            echo "Error updating schedule: " . $e->getMessage();
        }
    } 
}




// Insert a new schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'insert') {
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $waste_type = $_POST['waste_type'];
    $scheduled_date = $_POST['scheduled_date'];
    $scheduled_time = $_POST['scheduled_time'];
    $comments = $_POST['comments'];

    $query = "INSERT INTO schedules (user_id, phone, address, waste_type, scheduled_date, scheduled_time, comments) 
              VALUES ((SELECT id FROM users WHERE username = :username), :phone, :address, :waste_type, :scheduled_date, :scheduled_time, :comments)";
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute([
            ':username' => $username,
            ':phone' => $phone,
            ':address' => $address,
            ':waste_type' => $waste_type,
            ':scheduled_date' => $scheduled_date,
            ':scheduled_time' => $scheduled_time,
            ':comments' => $comments
        ]);
        echo "Schedule added successfully.";
        header('Location: admin_dashboard.php');
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Delete a schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $schedule_id = $_POST['schedule_id'];

    $query = "DELETE FROM schedules WHERE id = :id";
    $stmt = $pdo->prepare($query);

    try {
        $stmt->execute([':id' => $schedule_id]);
        header('Location: admin_dashboard.php');
        exit;
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
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

// Fetch activity logs
$filter_sql = "SELECT id, timestamp, user_id, user_type, action, table_name, column_name 
               FROM activity_logs 
               ORDER BY timestamp DESC";
$stmt = $pdo->prepare($filter_sql);
$stmt->execute();
$logs = $stmt->fetchAll();



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Existing Head Content -->
</head>
<body>
    <div class="header">
        <h1>Admin Dashboard</h1>
        <a href="?logout=true" class="logout">Logout</a>
    </div>

    <!-- Add Schedule Section -->
    <div class="container">
        <h2>Add New Schedule</h2>
        <form method="POST" action="">
            <input type="hidden" name="action" value="insert">
            <input type="text" name="username" placeholder="User" required />
            <input type="text" name="phone" placeholder="Phone" required />
            <input type="text" name="address" placeholder="Address" required />
            <input type="text" name="waste_type" placeholder="Waste Type" required />
            <input type="date" name="scheduled_date" required />
            <input type="time" name="scheduled_time" required />
            <textarea name="comments" placeholder="Comments"></textarea>
            <button type="submit" class="btn-insert">Add Schedule</button>
        </form>
    </div>

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

    <!-- Activity Logs Section -->
    <div class="container mt-5">
        <div class="row">

            <!-- Main Content -->
            <div class="col-md-9">
                <h2 class="mb-4">Activity Logs</h2>

                <!-- Display Error if any -->
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Date & Time</th>
            <th>User ID</th>
            <th>User Type</th>
            <th>Action</th>
            <th>Table Name</th>
            <th>Column Name</th>
        </tr>
    </thead>
    <tbody>
        <?php
        try {
            // Fetch activity logs from the database
            $stmt = $pdo->query('SELECT id, user_id, user_type, action, table_name, column_name, TO_CHAR(timestamp, \'YYYY-MM-DD HH24:MI\') AS formatted_timestamp FROM activity_logs ORDER BY timestamp DESC');
            while ($row = $stmt->fetch()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['formatted_timestamp']) . "</td>";
                echo "<td>" . htmlspecialchars($row['user_id']) . "</td>";
                echo "<td>" . htmlspecialchars($row['user_type']) . "</td>";
                echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                echo "<td>" . htmlspecialchars($row['table_name']) . "</td>";
                echo "<td>" . htmlspecialchars($row['column_name']) . "</td>";
                echo "</tr>";
            }
        } catch (PDOException $e) {
            echo "<tr><td colspan='8' class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
        }
        ?>
    </tbody>
</table>

            </div>
        </div>
    </div>
</body>
</html>
