<?php
$host = 'localhost';
$port = '5432';
$dbname = 'WW';
$user = 'collector';
$password = 'collector';

try {
    // PDO connection
    $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// To use the session user_id for logging activities, set it for each request
if (isset($_SESSION['user_id'])) {
    $logged_in_user_id = $_SESSION['user_id'];
    // Set the user_id in a session variable or in the database connection
    $pdo->exec("SET myapp.user_id = {$logged_in_user_id}");
}

?>