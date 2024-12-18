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

    // Fetch user id from the username
    $query = "SELECT id FROM users WHERE username = :username";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    if ($user) {
        $user_id = $user['id'];
        
        // Insert schedule into the database
        $query = "INSERT INTO schedules (user_id, phone, address, waste_type, scheduled_date, scheduled_time, comments) 
                  VALUES (:user_id, :phone, :address, :waste_type, :scheduled_date, :scheduled_time, :comments)";
        $stmt = $pdo->prepare($query);

        try {
            $stmt->execute([
                ':user_id' => $user_id,
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
    } else {
        echo "User not found.";
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
    <style>
                .actions {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        /* General Styles */
body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f7f9fc;
}

.header {
    background-color: #4B8B3B;
    color: white;
    text-align: center;
    padding: 15px;
    position: relative;
}

.header a.logout {
    position: absolute;
    right: 20px;
    top: 15px;
    background-color: #dc3545;
    color: white;
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 5px;
    font-weight: bold;
}

.header a.logout:hover {
    background-color: #c82333;
}

.container {
    max-width: 1100px;
    margin: 30px auto;
    padding: 20px;
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
        .btn-search {
            background-color: #4CAF50;
            color: white;
            height: 50px;
            width: 100px;
            margin-top: 20px;
            margin-left: 10px;
        }

.container h2 {
    color: #333;
    font-size: 1.5rem;
    margin-bottom: 20px;
}

/* Form Styling */
form {
    display: grid;
    gap: 20px;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

form .form-group {
    display: flex;
    flex-direction: column;
}

form .form-group label {
    font-weight: bold;
    margin-bottom: 5px;
    font-size: 1rem;
}

form input, form textarea, form select {
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 5px;
    font-size: 1rem;
}

form textarea {
    resize: vertical;
}

form button.btn-submit {
    background-color: #4B8B3B;
    color: white;
    padding: 5px 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1rem;
    height: 80px;
}

form button.btn-submit:hover {
    background-color: #3a6f2e;
}

/* Table Styling */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

table thead {
    background-color: #4B8B3B;
    color: white;
}

table th, table td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: left;
}

table tbody tr:nth-child(even) {
    background-color: #f9f9f9;
}

table tbody tr:hover {
    background-color: #f1f1f1;
}

/* Responsive Design */
@media (max-width: 768px) {
    .container {
        padding: 15px;
    }

    form {
        grid-template-columns: 1fr;
    }
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

        /* General button styling */
.btn-summary {
    background-color: #28a745; /* Success green color */
    color: #fff; /* White text color */
    font-size: 1rem; /* Base font size */
    font-weight: 600; /* Semi-bold font */
    padding: 10px 20px; /* Top/Bottom, Left/Right padding */
    border-radius: 8px; /* Rounded corners */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
    text-decoration: none; /* Remove default underline */
    transition: all 0.3s ease-in-out; /* Smooth transitions */
    margin-left: 900px;
    margin-bottom: 50px;
}

/* Hover effect */
.btn-summary:hover {
    background-color: #218838; /* Darker green on hover */
    color: #f8f9fa; /* Slightly lighter white for contrast */
    transform: translateY(-2px); /* Slight upward movement */
    box-shadow: 0 6px 10px rgba(0, 0, 0, 0.15); /* Enhance shadow on hover */
}

/* Active effect (button press) */
.btn-summary:active {
    background-color: #1e7e34; /* Even darker green */
    transform: translateY(1px); /* Slight downward movement */
    box-shadow: none; /* Remove shadow to simulate press */
}


    </style>
</head>
<body>
    <div class="header">
    <a href="admin_profile.php">
            <img src="uploads/user-icom.png" alt="Logo" class="profile-icon">
        </a>
        <h1>Admin Dashboard</h1>
        <a href="?logout=true" class="logout">Logout</a>
    </div>

    <!-- Add Schedule Section -->
<div class="container">
<h2>Search and Manage Schedules</h2>

<!-- Search Form -->
<form method="GET" action="admin_dashboard.php">
    <div class="form-group">
        <label for="search">Search Schedules</label>
        <input 
            type="text" 
            id="search" 
            name="search" 
            placeholder="Search here"
            value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>"
            style="width: 100%; padding: 10px; margin-bottom: 10px;"
        />
    </div>
    <button type="submit" class="btn btn-search">Search</button>
</form>

<!-- Schedule Table -->
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
        <?php
        // Search query logic
        $search = $_GET['search'] ?? '';
        $query = "
        SELECT s.*, u.username 
        FROM schedules s 
        JOIN users u ON s.user_id = u.id
        WHERE (
            s.phone ILIKE :search OR 
            s.address ILIKE :search OR 
            s.waste_type ILIKE :search OR 
            CAST(s.scheduled_date AS TEXT) ILIKE :search OR 
            u.username ILIKE :search OR 
            CAST(s.scheduled_time AS TEXT) ILIKE :search OR 
            s.comments ILIKE :search OR
            CAST(s.created_at AS TEXT) ILIKE :search
        )
        ORDER BY s.created_at DESC
    ";       
        $stmt = $pdo->prepare($query);
        $stmt->execute([':search' => '%' . $search . '%']);
        $filtered_schedules = $stmt->fetchAll();

        if (count($filtered_schedules) > 0):
            foreach ($filtered_schedules as $schedule):
        ?>
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
        <?php
            endforeach;
        else:
        ?>
            <tr>
                <td colspan="10" style="text-align:center;">No schedules found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
<div class="container">
<h2>Add New Schedule</h2>
    <form method="POST" action="">
        <input type="hidden" name="action" value="insert">
        <div class="form-group">
            <label for="username">User</label>
            <input type="text" id="username" name="username" placeholder="Enter username" required />
        </div>
        <div class="form-group">
            <label for="phone">Phone</label>
            <input type="text" id="phone" name="phone" placeholder="Enter phone number" required />
        </div>
        <div class="form-group">
            <label for="address">Address</label>
            <input type="text" id="address" name="address" placeholder="Enter address" required />
        </div>
        <div class="form-group">
            <label for="waste_type">Waste Type</label>
            <select id="waste_type" name="waste_type" required>
                <option value="">Select Waste Type</option>
                <option value="bio">Bio</option>
                <option value="non-bio">Non-Bio</option>
                <option value="recyclable">Recyclable</option>
                <option value="electronic">Electronic</option>
                <option value="special">Special</option>
            </select>
        </div>
        <div class="form-group">
            <label for="scheduled_date">Scheduled Date</label>
            <input type="date" id="scheduled_date" name="scheduled_date" required />
        </div>
        <div class="form-group">
            <label for="scheduled_time">Scheduled Time</label>
            <input type="time" id="scheduled_time" name="scheduled_time" required />
        </div>
        <div class="form-group">
            <label for="comments">Comments</label>
            <textarea id="comments" name="comments" placeholder="Enter comments (optional)"></textarea>
        </div>
        <button type="submit" class="btn btn-submit">Add Schedule</button>
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

</div>
    
    <!-- Activity Logs Section -->
<div class="container mt-5">
    <div class="row">

        <!-- Main Content -->
        <div class="col-md-9">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Activity Logs</h2>
        <a href="summary.php" class="btn-summary">View All Summary</a>
    </div>
</div>


            <!-- Display Error if any -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filter Form -->
            <form method="GET" action="" id="roleFilterForm">
                <div class="role-group">
                    <select name="role" id="role" class="form-control" onchange="this.form.submit()">
                        <option value="">All</option>
                        <option value="user" <?php echo (isset($_GET['role']) && $_GET['role'] === 'user') ? 'selected' : ''; ?>>User</option>
                        <option value="admin" <?php echo (isset($_GET['role']) && $_GET['role'] === 'admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="collector" <?php echo (isset($_GET['role']) && $_GET['role'] === 'collector') ? 'selected' : ''; ?>>Collector</option>
                    </select>
                </div>
            </form>

            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User Type</th>
                        <th>Action</th>
                        <th>Table Name</th>
                        <th>Column Name</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Get the selected role from the URL query string
                    $roleFilter = isset($_GET['role']) ? $_GET['role'] : '';

                    // Start building the SQL query
                    $query = 'SELECT user_type, action, table_name, column_name, TO_CHAR(timestamp, \'YYYY-MM-DD HH24:MI\') AS formatted_timestamp FROM activity_logs';

                    // Apply the role filter if one is selected
                    if ($roleFilter) {
                        $query .= ' WHERE user_type = :role';
                    }

                    $query .= ' ORDER BY timestamp DESC';

                    try {
                        // Prepare and execute the query
                        $stmt = $pdo->prepare($query);

                        // Bind the role filter parameter if applicable
                        if ($roleFilter) {
                            $stmt->bindParam(':role', $roleFilter, PDO::PARAM_STR);
                        }

                        $stmt->execute();

                        // Display the logs
                        while ($row = $stmt->fetch()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['formatted_timestamp']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['user_type']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['action']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['table_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['column_name']) . "</td>";
                            echo "</tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='5' class='text-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>

        </div>
    </div>
</div>
</body>
</html>
