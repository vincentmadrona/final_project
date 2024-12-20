<?php
$host = 'localhost';
$db = 'ssu_db';
$user = 'root'; // Replace with your username
$pass = ''; // Replace with your password

try {
    $pdo = new PDO("mysql:localhost=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(['error' => 'Database connection failed: ' . $e->getMessage()]));
}
?>
