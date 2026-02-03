<?php 
// 1. Setup includes (Common for your project structure)
include '../include/header.php'; 
include '../include/sidebar.php'; 

// 2. Mock User Session (In a real app, this comes from session_start())
$user = [
    'name' => 'Varshith',
    'role' => 'Manager' // Change to 'Employee' to test role-based view
];

// 3. Mock Data (Simulating the 'tasks' state)
$tasks = [
    ['id' => 1, 'title' => 'Fix Login Bug', 'assignedTo' => 'Varshith', 'department' => 'IT', 'priority' => 'High', 'deadline' => '2026-02-01', 'status' => 'Pending', 'description' => 'Login fails when special characters are used.'],
    ['id' => 2, 'title' => 'Design Dashboard', 'assignedTo' => 'Aditi Rao', 'department' => 'Design', 'priority' => 'Medium', 'deadline' => '2026-02-05', 'status' => 'In Progress', 'description' => 'Create wireframes for the new admin panel.'],
    ['id' => 3, 'title' => 'Server Maintenance', 'assignedTo' => 'Sanjay', 'department' => 'IT', 'priority' => 'Low', 'deadline' => '2026-01-28', 'status' => 'Completed', 'description' => 'Routine check of AWS instances.'],
    ['id' => 4, 'title' => 'Update API Docs', 'assignedTo' => 'Varshith', 'department' => 'IT', 'priority' => 'Medium', 'deadline' => '2026-02-10', 'status' => 'Pending', 'description' => 'Document the new employee endpoints.'],
];

// 4. Department logic
$employeesByDept = [
    'IT' => ['Varshith', 'Sanjay', 'Rahul'],
    'Design' => ['Aditi Rao', 'Meera'],
    'Accounts' => ['Priya', 'Amit'],
    'HR' => ['Kavya', 'Deepak']
];

// 5. Filter Logic (Simulating the 'filter' state via GET)
$currentFilter = isset($_GET['status']) ? $_GET['status'] : 'All';

$visibleTasks = array_filter($tasks, function($task) use ($user, $currentFilter) {
    $roleMatch = ($user['role'] === 'Manager' || $user['role'] === 'TL') ? true : ($task['assignedTo'] === $user['name'] || $task['assignedTo'] === 'Varshith');
    $statusMatch = ($currentFilter === 'All') ? true : ($task['status'] === $currentFilter);
    return $roleMatch && $statusMatch;
});

// 6. Stats Calculation
$stats = [
    'pending' => count(array_filter($visibleTasks, fn($t) => $t['status'] === 'Pending')),
    'progress' => count(array_filter($visibleTasks, fn($t) => $t['status'] === 'In Progress')),
    'completed' => count(array_filter($visibleTasks, fn($t) => $t['status'] === 'Completed')),
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Task Management Portal</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* CSS Exactly as provided in React component */
        .task-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
        }
        .task-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; flex-wrap: wrap; gap: 20px; }
        .header-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .header-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }
        .btn-add {
            background-color: #FF9B44; color: white; padding: 12px 24px; border-radius: 8px;
            font-weight: 700; font-size: 14px; border: none; cursor: pointer; display: flex;
            align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.2);
        }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .stat-card {
            background: white; padding: 20px; border-radius: 12px; border: 1px solid #e1e1e1;
            display: flex; align-items: center; gap: 20px;
        }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .bg-orange { background: #fff7ed; color: #f97316; }
        .bg-blue { background: #eff6ff; color: #3b82f6; }
        .bg-green { background: #f0fdf4; color: #22c55e; }
        .stat-label { font-size: 11px; font-weight: 700; color: #999; text-transform: uppercase; display: block; margin-bottom: 4px; }
        .stat-value { font-size: 24px; font-weight: 800; color: #333; }
        .board-container { background: white; padding: 30px; border-radius: 16px; border: 1px solid #e5e7eb; }
        .board-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #f0f0f0; padding-bottom: 20px; }
        .board-title { font-size: 18px; font-weight: 700; color: #333; display: flex; align-items: center; gap: 10px; }
        .filter-box { display: flex; align-items: center; gap: 8px; background: #f9fafb; padding: 8px 15px; border-radius: 8px; border: 1px solid #e5e7eb; }
        .filter-select { background: transparent; border: none; font-size: 13px; font-weight: 600; outline: none; cursor: pointer; }
        .task-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 25px; }
        .task-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; padding: 20px; position: relative; }
        .priority-badge { padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 700; text-transform: uppercase; border: 1px solid; }
        .p-high { background: #fef2f2; color: #dc2626; border-color: #fee2e2; }
        .p-med { background: #fffbeb; color: #d97706; border-color: #fef3c7; }
        .p-low { background: #f0fdf4; color: #16a34a; border-color: #dcfce7; }
        .task-title { font-size: 16px; font-weight: 700; color: #1f2937; margin: 15px 0 8px 0; }
        .task-desc { font-size: 13px; color: #6b7280; line-height: 1.5; margin-bottom: 20px; min-height: 40px; }
        .card-meta { border-top: 1px solid #f3f4f6; padding-top: 15px; margin-bottom: 15px; display: flex; flex-direction: column; gap: 8px; }
        .meta-row { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: #4b5563; }
        .status-select { padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; outline: none; border: 1px solid transparent; }
        .s-pending { background: #f3f4f6; color: #4b5563; border-color: #e5e7eb; }
        .s-progress { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
        .s-completed { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
        
        /* Modal Styles */
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000; }
        .modal-box { background: white; padding: 30px; border-radius: 16px; width: 500px; }
        .form-input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; box-sizing: border-box; margin-bottom: 15px; }
        .form-label { display: block; font-size: 12px; font-weight: 700; color: #666; margin-bottom: 6px; text-transform: uppercase; }
        .submit-btn { width: 100%; padding: 12px; background: #FF9B44; color: white; font-weight: 700; border: none; border-radius: 8px; cursor: pointer; }
    </style>
</head>
<body>

<div class="task-container">
    <div class="task-header">
        <div>
            <h2 class="header-title">Task Management</h2>
            <div class="header-breadcrumb">
                Dashboard / <span style="color: #FF9B44; font-weight: bold;">Tasks</span>
            </div>
        </div>
        <?php if ($user['role'] === 'Manager' || $user['role'] === 'TL'): ?>
            <button class="btn-add" onclick="toggleModal(true)">
                <i data-lucide="plus" size="18"></i> Assign New Task
            </button>
        <?php endif; ?>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-orange"><i data-lucide="alert-circle"></i></div>
            <div>
                <span class="stat-label">Pending</span>
                <span class="stat-value"><?php echo $stats['pending']; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i data-lucide="clock"></i></div>
            <div>
                <span class="stat-label">In Progress</span>
                <span class="stat-value"><?php echo $stats['progress']; ?></span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-green"><i data-lucide="check-circle"></i></div>
            <div>
                <span class="stat-label">Completed</span>
                <span class="stat-value"><?php echo $stats['completed']; ?></span>
            </div>
        </div>
    </div>

    <div class="board-container">
        <div class="board-header">
            <h4 class="board-title">
                <i data-lucide="clipboard-list" color="#FF9B44"></i> Team Task Board
            </h4>
            <div class="filter-box">
                <i data-lucide="filter" size="16" color="#9ca3af"></i>
                <select onchange="window.location.href='?status='+this.value" class="filter-select">
                    <option value="All" <?php echo $currentFilter === 'All' ? 'selected' : ''; ?>>All Status</option>
                    <option value="Pending" <?php echo $currentFilter === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="In Progress" <?php echo $currentFilter === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Completed" <?php echo $currentFilter === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>
        </div>

        <div class="task-grid">
            <?php foreach ($visibleTasks as $task): ?>
            <div class="task-card">
                <div class="card-top">
                    <span class="priority-badge <?php echo 'p-' . strtolower(substr($task['priority'], 0, 4)); ?>">
                        <?php echo $task['priority']; ?>
                    </span>
                    <button class="delete-btn" onclick="confirmDelete(<?php echo $task['id']; ?>)">
                        <i data-lucide="trash-2" size="16"></i>
                    </button>
                </div>
                
                <h3 class="task-title"><?php echo htmlspecialchars($task['title']); ?></h3>
                <p class="task-desc"><?php echo htmlspecialchars($task['description']); ?></p>
                
                <div class="card-meta">
                    <div class="meta-row"><i data-lucide="user" size="14"></i> <?php echo $task['assignedTo']; ?> (<?php echo $task['department']; ?>)</div>
                    <div class="meta-row"><i data-lucide="calendar" size="14"></i> Due: <?php echo $task['deadline']; ?></div>
                </div>

                <div class="card-footer">
                    <span class="status-label">Status</span>
                    <select class="status-select <?php echo 's-' . ($task['status'] === 'In Progress' ? 'progress' : strtolower($task['status'])); ?>">
                        <option value="Pending" <?php echo $task['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="In Progress" <?php echo $task['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="Completed" <?php echo $task['status'] === 'Completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal-overlay" id="taskModal">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="margin:0">Assign New Task</h3>
            <button onclick="toggleModal(false)" style="background:none; border:none; cursor:pointer;"><i data-lucide="x"></i></button>
        </div>
        <form action="process_task.php" method="POST">
            <label class="form-label">Task Title</label>
            <input type="text" name="title" required class="form-input">

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
                <div>
                    <label class="form-label">Department</label>
                    <select name="department" class="form-input" onchange="updateEmployees(this.value)">
                        <option value="" disabled selected>Select Dept</option>
                        <?php foreach (array_keys($employeesByDept) as $dept): ?>
                            <option value="<?php echo $dept; ?>"><?php echo $dept; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Assign To</label>
                    <select name="assignedTo" id="employeeSelect" class="form-input" required>
                        <option value="">Select Employee</option>
                    </select>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px">
                <div>
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-input">
                        <option value="High">High</option>
                        <option value="Medium">Medium</option>
                        <option value="Low">Low</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">Deadline</label>
                    <input type="date" name="deadline" required class="form-input">
                </div>
            </div>

            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-input" style="resize:none"></textarea>

            <button type="submit" class="submit-btn">Assign Task</button>
        </form>
    </div>
</div>

<script>
    // Initialize Icons
    lucide.createIcons();

    // Modal Control
    function toggleModal(show) {
        document.getElementById('taskModal').style.display = show ? 'flex' : 'none';
    }

    // Confirm Delete
    function confirmDelete(id) {
        if(confirm('Delete this task?')) {
            // In real app: window.location.href = 'delete_task.php?id=' + id;
            alert('Delete Task ID: ' + id);
        }
    }

    // Dynamic Employee Dropdown (Simulating React's employeesByDept)
    const empData = <?php echo json_encode($employeesByDept); ?>;
    function updateEmployees(dept) {
        const select = document.getElementById('employeeSelect');
        select.innerHTML = '<option value="">Select Employee</option>';
        if(empData[dept]) {
            empData[dept].forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp;
                opt.innerHTML = emp;
                select.appendChild(opt);
            });
        }
    }
</script>

</body>
</html>