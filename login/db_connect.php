<?php
// --- DATABASE CONFIGURATION ---
$host = "82.197.82.27"; 
$user = "u957189082_workack";
$pass = "Workack@2026";
$db   = "u957189082_workack";

// Establish Connection
$conn = mysqli_connect($host, $user, $pass, $db);

// Check Connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set Charset
mysqli_set_charset($conn, "utf8mb4");

// Start Session for User Management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>