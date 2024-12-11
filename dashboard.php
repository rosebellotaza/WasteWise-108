<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = :id");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all fields are set in POST
    if (isset($_POST['name'], $_POST['phone'], $_POST['address'], $_POST['waste_type'], $_POST['comments'], $_POST['scheduled_date'], $_POST['scheduled_time'])) {
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $waste_type = $_POST['waste_type'];
        $comments = $_POST['comments'];
        $scheduled_date = $_POST['scheduled_date'];
        $scheduled_time = $_POST['scheduled_time'];

        try {
            $stmt = $pdo->prepare("INSERT INTO schedules (user_id, name, phone, address, waste_type, comments, scheduled_date, scheduled_time) VALUES (:user_id, :name, :phone, :address, :waste_type, :comments, :scheduled_date, :scheduled_time)");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
                'name' => $name,
                'phone' => $phone,
                'address' => $address,
                'waste_type' => $waste_type,
                'comments' => $comments,
                'scheduled_date' => $scheduled_date,
                'scheduled_time' => $scheduled_time
            ]);
            echo "<p style='color: green;'>Schedule added successfully!</p>";
        } catch (PDOException $e) {
            echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
        }
    } else {
        echo "<p style='color: red;'>Please fill out all required fields.</p>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Schedule Waste Pick-Up</title>
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body & Background */
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #e0eafc, #cfdef3);
            color: #333;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 10px;
        }

        /* Header */
        header {
            background: #4CAF50;
            color: white;
            padding: 10px 20px;
            width: 100%;
            text-align: center;
            font-size: 1.5em;
            border-radius: 8px 8px 0 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            margin-bottom: 20px;
        }

        /* Main Content Area */
        .content-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            margin-bottom: 30px;
            transition: transform 0.2s ease;
        }

        /* Form styling */
        form label {
            display: block;
            margin: 10px 0 5px;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        form button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header>
        Waste Wise Management System
    </header>

    <!-- Welcome Message -->
    <div class="welcome-message">
        Welcome, <strong><?php echo htmlspecialchars($user['username']); ?>!</strong>
    </div>

    <!-- Main Dashboard Content -->
    <div class="content-container">
        <h2>Schedule Waste Pick-Up</h2>
        <form method="POST">
    <label for="name">Full Name</label>
    <input type="text" id="name" name="name" placeholder="Enter your full name" required>

    <label for="phone">Phone Number</label>
    <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>

    <label for="address">Address</label>
    <input type="text" id="address" name="address" placeholder="Enter your address" required>

    <label for="waste_type">Type of Waste</label>
    <select id="waste_type" name="waste_type" required>
        <option value="organic">Organic</option>
        <option value="recyclable">Recyclable</option>
        <option value="hazardous">Hazardous</option>
        <option value="general">General</option>
    </select>

    <label for="scheduled_date">Scheduled Date</label>
    <input type="date" id="scheduled_date" name="scheduled_date" required>

    <label for="scheduled_time">Scheduled Time</label>
    <input type="time" id="scheduled_time" name="scheduled_time" required>

    <label for="comments">Additional Comments</label>
    <textarea id="comments" name="comments" placeholder="Enter any additional details here..."></textarea>

    <button type="submit">Submit Schedule</button>
</form>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
