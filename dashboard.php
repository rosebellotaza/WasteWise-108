<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
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
    if (isset($_POST['phone'], $_POST['address'], $_POST['waste_type'], $_POST['comments'], $_POST['scheduled_date'], $_POST['scheduled_time'])) {
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $waste_type = $_POST['waste_type'];
        $comments = $_POST['comments'];
        $scheduled_date = $_POST['scheduled_date'];
        $scheduled_time = $_POST['scheduled_time'];

        try {
            $stmt = $pdo->prepare("
                INSERT INTO schedules (user_id, phone, address, waste_type, comments, scheduled_date, scheduled_time)
                VALUES (:user_id, :phone, :address, :waste_type, :comments, :scheduled_date, :scheduled_time)
            ");
            $stmt->execute([
                'user_id' => $_SESSION['user_id'],
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Schedule Waste Pick-Up</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #4CAF50, #81C784);
            color: #333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px;
        }

        header {
            background: #388E3C;
            color: white;
            width: 100%;
            padding: 15px;
            text-align: center;
            font-size: 1.8em;
            border-radius: 8px;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .content-container {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            width: 100%;
            animation: fadeIn 0.5s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            text-align: center;
            color: #388E3C;
            margin-bottom: 20px;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }

        form input,
        form select,
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        form input:focus,
        form select:focus,
        form textarea:focus {
            border-color: #4CAF50;
            outline: none;
        }


        form button {
            width: auto; /* Adjust width to auto */
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        form button:hover {
            background-color: #388E3C;
        }

        .logout-btn {
            display: block;
            text-align: center;
            margin-top: 15px;
            color:red;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }

        .logout-btn:hover {
            color: #388E3C;
        }

        .welcome-message {
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.2em;
            color: #333;
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
        <br>

        <a href="logout.php" class="logout-btn">Logout</a>
    </div>
</body>
</html>
