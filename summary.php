<?php
include 'db.php';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #e8f5e9; /* Light green background */
            color: #2e7d32; /* Dark green text */
            margin: 0;
            padding: 0;
        }
        h2 {
            text-align: center;
            color: #1b5e20;
        }
        table {
            border-collapse: collapse;
            margin: 20px auto;
            width: 90%;
            background-color: #ffffff;
        }
        th, td {
            border: 1px solid #c8e6c9; /* Light green border */
            padding: 10px;
            text-align: center;
        }
        th {
            background-color:rgb(42, 110, 47); /* Medium green for header */
            color: #ffffff;
        }
        tr:nth-child(even) {
            background-color: #f1f8e9; /* Slightly lighter green for alternate rows */
        }
        tr:hover {
            background-color: #c8e6c9; /* Highlight on hover */
        }
        footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #1b5e20; /* Dark green footer */
            color: #ffffff;
        }
        form {
            text-align: center;
            margin: 20px;
        }
        input[type='date'], input[type='text'] {
            padding: 8px;
            margin: 5px;
            font-size: 16px;
        }
        input[type='submit'] {
            padding: 8px 16px;
            background-color: #1b5e20;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        input[type='submit']:hover {
            background-color: #2e7d32;
        }
        p, h3 {
        text-align: center;
        }  
        .back-btn {
    display: inline-block;
    margin: 20px;
    padding: 10px 20px;
    font-size: 16px;
    color: #fff;
    background-color: #2e7d32; /* Dark green */
    text-decoration: none;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    transition: background-color 0.3s, transform 0.2s;
}

.back-btn:hover {
    background-color: #1b5e20; /* Darker green on hover */
    transform: translateY(-2px);
}

    </style>
</head>
<body>";

echo "<a href='admin_dashboard.php' class='back-btn'>&larr; Back to Dashboard</a>";
echo "<h2>Select Date Range for Schedules</h2>";
echo "<form method='GET'>
        <label for='start_date'>Start Date:</label>
        <input type='date' id='start_date' name='start_date' required>
        <label for='end_date'>End Date:</label>
        <input type='date' id='end_date' name='end_date' required>
        <input type='submit' value='View Schedules'>
      </form>";

if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];

    try {
        $stmt = $pdo->prepare("SELECT * FROM get_schedules_in_date_range(:start_date, :end_date)");
        $stmt->execute(['start_date' => $start_date, 'end_date' => $end_date]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo "<br><br><h2>Schedules from $start_date to $end_date</h2>";
        echo "<table>
                <tr>
                    <th>User ID</th>
                    <th>Username</th>
                    <th>Scheduled Date</th>
                    <th>Waste Type</th>
                    <th>Comments</th>
                </tr>";
        foreach ($results as $row) {
            echo "<tr>
                    <td>{$row['user_id']}</td>
                    <td>{$row['username']}</td>
                    <td>{$row['scheduled_date']}</td>
                    <td>{$row['waste_type']}</td>
                    <td>{$row['comments']}</td>
                  </tr>";
        }
        echo "</table>";
    } catch (PDOException $e) {
        echo "<p>Error fetching data: " . $e->getMessage() . "</p>";
    }
}

try {
    $query = $pdo->query("SELECT * FROM user_schedule_summary");
    $results = $query->fetchAll(PDO::FETCH_ASSOC);

    echo "<br><h2>User Schedule Summary</h2>";
    echo "<table>
            <tr>
                <th>User ID</th>
                <th>Username</th>
                <th>Total Schedules</th>
                <th>Next Schedule Date</th>
                <th>Last Schedule Date</th>
            </tr>";
    foreach ($results as $row) {
        echo "<tr>
                <td>{$row['user_id']}</td>
                <td>{$row['username']}</td>
                <td>{$row['total_schedules']}</td>
                <td>{$row['next_schedule_date']}</td>
                <td>{$row['last_schedule_date']}</td>
              </tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p>Error fetching data: " . $e->getMessage() . "</p>";
}

try {
    $query = $pdo->query("SELECT * FROM role_based_activity_summary");
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    echo "<br><br><h2>Role Activity Summary</h2>";
    echo "<table>
            <tr>
                <th>Role</th>
                <th>Total Actions</th>
                <th>Last Action Time</th>
            </tr>";
    foreach ($results as $row) {
        echo "<tr>
                <td>{$row['user_role']}</td>
                <td>{$row['total_actions']}</td>
                <td>{$row['last_action_time']}</td>
            </tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p>Error fetching data: " . $e->getMessage() . "</p>";
}

try {
    $query = $pdo->query("SELECT * FROM monthly_waste_summary");
    $results = $query->fetchAll(PDO::FETCH_ASSOC);
    echo "<br><br><h2>Monthly Waste Summary</h2>";
    echo "<table>
            <tr>
                <th>Year-Month</th>
                <th>Waste Type</th>
                <th>Total Collections</th>
            </tr>";
    foreach ($results as $row) {
        echo "<tr>
                <td>{$row['month']}</td>
                <td>{$row['waste_type']}</td>
                <td>{$row['total_collections']}</td>
            </tr>";
    }
    echo "</table>";
} catch (PDOException $e) {
    echo "<p>Error fetching data: " . $e->getMessage() . "</p>";
}

echo "<br><h2>Total Waste Collections for a Specific Type</h2>";
echo "<form method='GET'>
        <label for='waste_type'>Enter Waste Type:</label>
        <input type='text' id='waste_type' name='waste_type' required>
        <input type='submit' value='Get Total Collections'>
      </form>";

if (isset($_GET['waste_type'])) {
    $waste_type = $_GET['waste_type'];

    try {
        $stmt = $pdo->prepare("SELECT get_total_collections_for_waste_type(:waste_type) AS total_collections");
        $stmt->execute(['waste_type' => $waste_type]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "<h3>Waste Type: {$waste_type}</h3>";
        echo "<p>Total Collections: {$result['total_collections']}</p>";
    } catch (PDOException $e) {
        echo "<p>Error fetching data: " . $e->getMessage() . "</p>";
    }
}

echo "<footer>
        <p>&copy; " . date('Y') . " Waste Management System</p>
      </footer>
</body>
</html>";
?>