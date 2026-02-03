<?php
// self_tasks.php - Fixed (Forced emp_id until login is ready)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../include/db_connect.php';

// Force emp_id to existing user (id=1 exists in your DB)
$emp_id = 1;  // Change this to your real logged-in ID later

// Debug: Show what emp_id is being used
echo "<pre style='background:#e3f2fd; padding:1rem; border-radius:8px; margin:1rem 0;'>
DEBUG: Using emp_id = $emp_id
</pre>";

// Message handling
$message = $_GET['msg'] ?? '';
$message_type = $_GET['type'] ?? 'info';

// Handle Save Task
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_task') {
    $task_name = trim($_POST['taskName'] ?? '');
    $task_date = $_POST['date'] ?? '';
    $task_time = $_POST['time'] ?? '';

    if (empty($task_name) || empty($task_date) || empty($task_time)) {
        header("Location: self_tasks.php?msg=" . urlencode("All fields are required.") . "&type=danger");
        exit();
    }

    $sql = "INSERT INTO self_tasks (emp_id, task_name, task_date, task_time, status, created_at) 
            VALUES (?, ?, ?, ?, 'Pending', NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $emp_id, $task_name, $task_date, $task_time);

    if ($stmt->execute()) {
        header("Location: self_tasks.php?msg=" . urlencode("Task added successfully!") . "&type=success");
    } else {
        header("Location: self_tasks.php?msg=" . urlencode("Error: " . $conn->error) . "&type=danger");
    }
    exit();
}

// Handle Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    $task_id = (int)($_POST['task_id'] ?? 0);
    $new_status = $_POST['new_status'] ?? '';

    if ($task_id > 0 && in_array($new_status, ['Pending','Working','Completed'])) {
        $sql = "UPDATE self_tasks SET status = ? WHERE id = ? AND emp_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $new_status, $task_id, $emp_id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            header("Location: self_tasks.php?msg=" . urlencode("Status updated!") . "&type=success");
        } else {
            header("Location: self_tasks.php?msg=" . urlencode("Failed to update.") . "&type=danger");
        }
        exit();
    }
}

// GET FILTER & TASKS
$filter = $_GET['filter'] ?? 'All';
$where = ($filter !== 'All') ? "AND status = ?" : "";

$sql = "SELECT * FROM self_tasks WHERE emp_id = ? $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if ($filter !== 'All') {
    $stmt->bind_param("is", $emp_id, $filter);
} else {
    $stmt->bind_param("i", $emp_id);
}
$stmt->execute();
$tasks = $stmt->get_result();

// CALCULATE STATS
$total = $pending = $working = $completed = 0;

$stats_sql = "SELECT status FROM self_tasks WHERE emp_id = ?";
$stats_stmt = $conn->prepare($stats_sql);
$stats_stmt->bind_param("i", $emp_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();

while ($row = $stats_result->fetch_assoc()) {
    $total++;
    if ($row['status'] === 'Pending') $pending++;
    if ($row['status'] === 'Working') $working++;
    if ($row['status'] === 'Completed') $completed++;
}

$progress = $total > 0 ? round(($completed / $total) * 100) : 0;
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Self Assigned Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Your existing styles remain unchanged */
        :root {
            --primary: #de8f1fff;
            --primary-dark: #e88b3a;
            --light: #f8f9fc;
            --gray: #64748b;
            --dark: #1f2937;
        }
        body { background: var(--light); font-family: 'Segoe UI', system-ui, sans-serif; }
        .content-body { padding: 2rem; max-width: 1400px; margin: 0 auto; }
        .page-header { margin-bottom: 2rem; }
        .page-title { font-size: 1.8rem; font-weight: 700; color: var(--dark); }
        .card { border: none; border-radius: 14px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
        .form-panel, .history-panel { background: white; padding: 2rem; }
        .form-label { font-size: 0.8rem; font-weight: 700; text-transform: uppercase; color: var(--gray); margin-bottom: 0.5rem; }
        .form-control, .form-select { border-radius: 10px; padding: 0.75rem 1rem; border: 1px solid #d1d5db; }
        .form-control:focus, .form-select:focus { border-color: var(--primary); box-shadow: 0 0 0 0.25rem rgba(255,155,68,0.15); }
        .btn-save { background: var(--primary); border: none; padding: 0.9rem; font-weight: 600; border-radius: 10px; transition: all 0.25s; }
        .btn-save:hover { background: var(--primary-dark); transform: translateY(-2px); }
        .filter-btn { padding: 0.55rem 1.4rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; border: 1px solid #d1d5db; background: white; color: var(--gray); transition: all 0.25s; }
        .filter-btn.active, .filter-btn:hover { background: var(--primary); color: white; border-color: var(--primary); }
        .task-card { border-radius: 12px; border: 1px solid #e5e7eb; padding: 1.25rem; margin-bottom: 1rem; transition: all 0.2s; }
        .task-card:hover { border-color: var(--primary); box-shadow: 0 6px 20px rgba(255,155,68,0.08); }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-working { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .progress { height: 10px; border-radius: 10px; background: #e5e7eb; }
        .progress-bar { background: linear-gradient(90deg, var(--primary), var(--primary-dark)); }
        .empty-state { text-align: center; padding: 5rem 1rem; color: var(--gray); }
        .empty-icon { font-size: 4rem; opacity: 0.4; margin-bottom: 1rem; }
        .alert-floating { position: fixed; top: 1.5rem; right: 1.5rem; z-index: 1050; min-width: 340px; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); }
    </style>
</head>
<body>

<?php include '../include/sidebar.php'; ?>

<div class="content-body">
    <!-- Floating Message -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show alert-floating" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-clipboard-list me-2" style="color: var(--primary);"></i>
            Self Assigned Tasks
        </h1>
    </div>

    <!-- Stats Row -->
    <div class="row g-4 mb-5">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-body text-center">
                    <h6 class="mb-3 text-muted">Completion Progress</h6>
                    <div class="position-relative d-inline-block">
                        <div class="bg-light rounded-circle p-4" style="width: 120px; height: 120px; margin: 0 auto;">
                            <div class="fw-bold fs-3"><?= $progress ?>%</div>
                            <small class="text-muted">Done</small>
                        </div>
                        <svg class="position-absolute top-0 start-0" width="120" height="120">
                            <circle cx="60" cy="60" r="54" fill="none" stroke="#e5e7eb" stroke-width="12"></circle>
                            <circle cx="60" cy="60" r="54" fill="none" stroke="var(--primary)" stroke-width="12" 
                                    stroke-dasharray="339" stroke-dashoffset="<?= 339 - (339 * $progress / 100) ?>"
                                    transform="rotate(-90 60 60)"></circle>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body">
                    <div class="row text-center h-100 align-items-center">
                        <div class="col">
                            <h3 class="fw-bold text-warning mb-0"><?= $pending ?></h3>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col">
                            <h3 class="fw-bold text-primary mb-0"><?= $working ?></h3>
                            <small class="text-muted">Working</small>
                        </div>
                        <div class="col">
                            <h3 class="fw-bold text-success mb-0"><?= $completed ?></h3>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col">
                            <h3 class="fw-bold text-secondary mb-0"><?= $total ?></h3>
                            <small class="text-muted">Total Tasks</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Form Column -->
        <div class="col-lg-5">
            <div class="card form-panel">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-4">
                        <div class="icon-box me-3">
                            <i class="fas fa-plus-circle fa-2x" style="color: var(--primary);"></i>
                        </div>
                        <h4 class="mb-0">Add New Task</h4>
                    </div>

                    <p class="text-muted mb-4">Create personal tasks and track them easily.</p>

                    <form method="POST">
                        <input type="hidden" name="action" value="save_task">

                        <div class="mb-3">
                            <label class="form-label">Task Name</label>
                            <input type="text" name="taskName" class="form-control" placeholder="Enter task title..." required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Date</label>
                                <input type="date" name="date" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Time</label>
                                <input type="time" name="time" class="form-control" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-save w-100 mt-4">
                            <i class="fas fa-save me-2"></i> Save Task
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- History Column -->
        <div class="col-lg-7">
            <div class="card history-panel">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>Task History
                        </h5>
                        <div class="filter-tabs">
                            <button type="button" onclick="window.location.href='?filter=All'" 
                                    class="filter-btn <?= $filter === 'All' ? 'active' : '' ?>">All</button>
                            <button type="button" onclick="window.location.href='?filter=Pending'" 
                                    class="filter-btn <?= $filter === 'Pending' ? 'active' : '' ?>">Pending</button>
                            <button type="button" onclick="window.location.href='?filter=Working'" 
                                    class="filter-btn <?= $filter === 'Working' ? 'active' : '' ?>">Working</button>
                            <button type="button" onclick="window.location.href='?filter=Completed'" 
                                    class="filter-btn <?= $filter === 'Completed' ? 'active' : '' ?>">Completed</button>
                        </div>
                    </div>

                    <div class="task-list">
                        <?php if ($tasks->num_rows > 0): ?>
                            <?php while ($task = $tasks->fetch_assoc()): ?>
                                <div class="task-card">
                                    <div class="flex-grow-1">
                                        <div class="task-name mb-2"><?= htmlspecialchars($task['task_name']) ?></div>
                                        <div class="task-meta">
                                            <span><i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($task['task_date']) ?></span>
                                            <span class="mx-2">•</span>
                                            <span><i class="fas fa-clock me-1"></i><?= htmlspecialchars($task['task_time']) ?></span>
                                        </div>
                                    </div>

                                    <form method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="action" value="update_status">
                                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                        <select name="new_status" class="form-select status-select status-<?= strtolower($task['status']) ?>" onchange="this.form.submit()" style="width: auto;">
                                            <option value="Pending" <?= $task['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Working" <?= $task['status'] === 'Working' ? 'selected' : '' ?>>Working</option>
                                            <option value="Completed" <?= $task['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                        </select>
                                    </form>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                <div class="empty-icon"><i class="fas fa-tasks"></i></div>
                                <h6 class="mt-3">No tasks yet</h6>
                                <p class="text-muted">Start adding your personal tasks above.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>