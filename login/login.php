<?php
// login.php

// 1. START SESSION
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'db_connect.php'; 

// 2. CHECK IF ALREADY LOGGED IN
if (isset($_SESSION['username']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'Admin') {
        // Redirect to Admin Dashboard
        header("Location: ../admin/admindashboard.php"); 
        exit;
    }
}

$error = null;

// 3. HANDLE LOGIN / REGISTER FORM SUBMISSION
if (isset($_POST['auth_action'])) {
    
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $role     = isset($_POST['role']) ? $_POST['role'] : '';
    $mode     = $_POST['auth_mode']; 

    // --- REGISTER LOGIC ---
    if ($mode === 'register') {
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $error = "Passwords do not match!";
        } else {
            // Set Session
            $_SESSION['username'] = $username;
            $_SESSION['role'] = $role;
            
            // Redirect
            if ($role === 'Admin') {
                header("Location: ../admin/admindashboard.php");
                exit;
            }
        }
    } 
    // --- LOGIN LOGIC ---
    elseif ($mode === 'login') {
        // Set Session
        $_SESSION['username'] = $username;
        $_SESSION['role'] = $role;

        // Redirect
        if ($role === 'Admin') {
            header("Location: ../admin/admindashboard.php");
            exit;
        } else {
             $error = "Employee Dashboard not created yet.";
        }
    }
}
?>