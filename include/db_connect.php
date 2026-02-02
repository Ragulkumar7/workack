<?php
// Database Configuration
$host = "srv1507.hstgr.io"; // Hostinger MariaDB IP
$user = "u957189082_workack";
$pass = "Workack@2026";
$db   = "u957189082_workack";

// Establish Connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set Charset to match your SQL collation
mysqli_set_charset($conn, "utf8mb4");

// Start Session for User Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>