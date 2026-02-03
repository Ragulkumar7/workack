<?php
// teams_chat.php - Workack Teams Dashboard (with include/sidebar & header)

session_start();
include 'include/db_connect.php';

// Employee ID from session (fallback to 1)
$emp_id = $_SESSION['emp_id'] ?? 1;

// Optional: Fetch employee name (demo fallback)
$sql = "SELECT name FROM employees WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$result = $stmt->get_result();
$employee = $result->fetch_assoc();
$employee_name = $employee['name'] ?? 'Aparna';

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Workack Teams | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #FF9B44;        /* Orange accent */
            --primary-dark: #e88b3a;
            --light-bg: #f8f9fc;
            --card-bg: white;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }
        body {
            background: var(--light-bg);
            font-family: 'Segoe UI', system-ui, sans-serif;
            color: var(--text-dark);
        }
        .content-body {
            flex: 1;
            padding: 2rem;
        }
        .welcome-header {
            background: white;
            padding: 1.5rem 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
        }
        .welcome-title {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0;
        }
        .card-app {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(0,0,0,0.06);
            background: var(--card-bg);
            height: 100%;
        }
        .card-app:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255,155,68,0.18);
        }
        .card-icon {
            font-size: 3.2rem;
            margin-bottom: 1.25rem;
            color: var(--primary);
        }
        .card-title {
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 0.75rem;
            color: var(--text-dark);
        }
        .card-text {
            color: var(--text-muted);
            font-size: 0.95rem;
            margin-bottom: 1rem;
        }
        .badge-count {
            font-size: 0.8rem;
            padding: 0.45em 0.9em;
            border-radius: 50px;
        }
        .badge-chat { background: #0d6efd; }
        .badge-call { background: #28a745; }
        .badge-calendar { background: #6610f2; }
        .badge-email { background: #dc3545; }
        .badge-todo { background: #198754; }
    </style>
</head>
<body>

<?php 
include 'include/sidebar.php'; 
?>

<div class="content-body">
    <!-- Welcome Header -->
    <div class="welcome-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="welcome-title">
                <i class="fas fa-comments me-2" style="color: var(--primary);"></i>
                Workack Teams
            </h2>
            <p class="text-muted mb-0">Welcome back, <?= htmlspecialchars($employee_name) ?></p>
        </div>
        <small class="text-muted">Today: <?= date('d M Y') ?></small>
    </div>

    <!-- Applications Grid -->
    <h5 class="mb-4 fw-semibold text-muted">
        <i class="fas fa-th-large me-2" style="color: var(--primary);"></i>
        Quick Access
    </h5>

    <div class="row g-4">
        <!-- Chat -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card card-app text-center" onclick="window.location.href='chat_screen.php'">
                <div class="card-body py-5">
                    <i class="fas fa-comment-dots card-icon"></i>
                    <h5 class="card-title">Chat</h5>
                    <p class="card-text">Team conversations & direct messages</p>
                    <span class="badge badge-count badge-chat">8 new</span>
                </div>
            </div>
        </div>

        <!-- Calls -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card card-app text-center" onclick="window.location.href='calls.php'">
                <div class="card-body py-5">
                    <i class="fas fa-phone-volume card-icon" style="color: #28a745;"></i>
                    <h5 class="card-title">Calls</h5>
                    <p class="card-text">Voice & video calls with team</p>
                    <span class="badge badge-count badge-call">2 live</span>
                </div>
            </div>
        </div>

        <!-- Calendar -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card card-app text-center" onclick="window.location.href='calendar.php'">
                <div class="card-body py-5">
                    <i class="fas fa-calendar-check card-icon" style="color: #6610f2;"></i>
                    <h5 class="card-title">Calendar</h5>
                    <p class="card-text">Meetings, events & reminders</p>
                    <span class="badge badge-count badge-calendar">3 today</span>
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card card-app text-center" onclick="window.location.href='email.php'">
                <div class="card-body py-5">
                    <i class="fas fa-envelope-open-text card-icon" style="color: #dc3545;"></i>
                    <h5 class="card-title">Email</h5>
                    <p class="card-text">Work emails & group threads</p>
                    <span class="badge badge-count badge-email">4 unread</span>
                </div>
            </div>
        </div>

        <!-- To Do -->
        <div class="col-md-6 col-lg-4 col-xl-3">
            <div class="card card-app text-center" onclick="window.location.href='todo.php'">
                <div class="card-body py-5">
                    <i class="fas fa-list-check card-icon" style="color: #198754;"></i>
                    <h5 class="card-title">To Do</h5>
                    <p class="card-text">Personal & shared tasks</p>
                    <span class="badge badge-count badge-todo">5 due today</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>