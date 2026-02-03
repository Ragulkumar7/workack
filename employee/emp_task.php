<?php
// emp_task.php - Employee Task Management (Fixed & Clean Version)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../include/db_connect.php';

// Employee ID (from session or fallback)
$emp_id = $_SESSION['emp_id'] ?? 1;

// Message handling
$message = '';
$message_type = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_task') {
    $task_id = filter_input(INPUT_POST, 'task_id', FILTER_VALIDATE_INT);

    if ($task_id && $task_id > 0) {
        // Verify task belongs to employee and is still pending
        $check_sql = "SELECT id FROM tasks WHERE id = ? AND emp_id = ? AND status = 'Pending'";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $task_id, $emp_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 1) {
            // Mark as completed
            $update_sql = "UPDATE tasks SET status = 'Completed' WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $task_id);

            if ($update_stmt->execute()) {
                $message = "Task marked as completed successfully!";
                $message_type = 'success';
            } else {
                $message = "Database error: " . $conn->error;
                $message_type = 'danger';
            }

            $update_stmt->close();
        } else {
            $message = "Task not found or already completed.";
            $message_type = 'warning';
        }

        $check_stmt->close();
    } else {
        $message = "Invalid task ID.";
        $message_type = 'danger';
    }

    // Redirect to prevent form resubmit
    header("Location: emp_task.php?msg=" . urlencode($message) . "&type=$message_type");
    exit();
}

// ────────────────────────────────────────────────
// Fetch employee name
// ────────────────────────────────────────────────
$sql_emp = "SELECT name FROM employees WHERE id = ?";
$stmt_emp = $conn->prepare($sql_emp);
$stmt_emp->bind_param("i", $emp_id);
$stmt_emp->execute();
$employee = $stmt_emp->get_result()->fetch_assoc();
$employee_name = $employee['name'] ?? 'Employee';

$stmt_emp->close();

// ────────────────────────────────────────────────
// Fetch employee's tasks
// ────────────────────────────────────────────────
$sql_tasks = "SELECT id, title, estimated_minutes, status, created_at 
              FROM tasks 
              WHERE emp_id = ? 
              ORDER BY created_at DESC";
$stmt_tasks = $conn->prepare($sql_tasks);
$stmt_tasks->bind_param("i", $emp_id);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Tasks | Workack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            background: #f5f7ff;
            font-family: 'Segoe UI', system-ui, sans-serif;
        }
        .content-wrapper {
            padding: 2rem;
            max-width: 1400px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(90deg, #ed914cff, #ed914cff);
            color: white;
            border-bottom: none;
            padding: 1.25rem 1.5rem;
        }
        .task-item {
            transition: all 0.2s ease;
        }
        .task-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
            font-weight: 500;
        }
        .badge-completed {
            background-color: #d4edda;
            color: #155724;
            font-weight: 500;
        }
        .btn-complete {
            min-width: 140px;
        }
        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            min-width: 320px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
    </style>
</head>
<body>

<?php include '../include/sidebar.php'; ?>

<div class="content-wrapper">
    <!-- Floating Alert -->
    <?php if (isset($_GET['msg']) && !empty($_GET['msg'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_GET['type'] ?? 'info') ?> alert-dismissible fade show alert-floating" role="alert">
            <strong><?= htmlspecialchars(urldecode($_GET['msg'])) ?></strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">
            <i class="fas fa-tasks me-2 text-primary"></i>
            My Assigned Tasks
        </h3>
        <small class="text-muted">Welcome back, <?= htmlspecialchars($employee_name) ?></small>
    </div>

    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Tasks Overview</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width:45%">Task Title</th>
                            <th style="width:15%">Est. Time</th>
                            <th style="width:20%">Assigned On</th>
                            <th style="width:10%">Status</th>
                            <th style="width:10%">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_tasks->num_rows > 0): ?>
                            <?php while ($task = $result_tasks->fetch_assoc()): ?>
                                <?php
                                $status_class = ($task['status'] === 'Completed') ? 'badge-completed' : 'badge-pending';
                                $assigned = date('d M Y • h:i A', strtotime($task['created_at']));
                                ?>
                                <tr class="task-item">
                                    <td>
                                        <div class="fw-medium"><?= htmlspecialchars($task['title']) ?></div>
                                    </td>
                                    <td><?= htmlspecialchars($task['estimated_minutes']) ?> min</td>
                                    <td><small class="text-muted"><?= $assigned ?></small></td>
                                    <td>
                                        <span class="badge <?= $status_class ?> px-3 py-2">
                                            <?= htmlspecialchars($task['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($task['status'] === 'Pending'): ?>
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="complete_task">
                                                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-success btn-complete">
                                                    <i class="fas fa-check me-1"></i> Complete
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-success fw-medium"><i class="fas fa-check-circle me-1"></i>Done</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-tasks fa-3x mb-3 d-block text-muted opacity-50"></i>
                                    <h6>No tasks assigned yet</h6>
                                    <small>When your Team Lead assigns tasks, they will appear here.</small>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light text-center py-3">
            <small class="text-muted">Showing <?= $result_tasks->num_rows ?> task<?= $result_tasks->num_rows === 1 ? '' : 's' ?></small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Clean up
$stmt_tasks->close();
$conn->close();
?>