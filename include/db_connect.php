<?php
$host = 'srv1507.hstgr.io ';
$db   = 'user_system';
$user = ' u957189082_workack';
$pass = 'Workack@2026'; // Default for XAMPP is empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>