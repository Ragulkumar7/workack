<?php
// --- 1. GLOBAL USER DATA ---
$user = [
    'name' => 'TL Manager',
    'role' => 'Team Lead', 
    'avatar_initial' => 'T'
];

// --- 2. TEAM LEAD PROFILE ---
$tlProfile = [
    'name' => 'TL Manager',
    'role' => 'Team Lead - Engineering',
    'email' => 'tl.manager@company.com'
];

// --- 3. EMPLOYEE LIST FOR ASSIGNMENT ---
$employees = [
    ['name' => 'Anthony Lewis', 'avatar' => 'https://i.pravatar.cc/150?u=ant'],
    ['name' => 'Brian Villalobos', 'avatar' => 'https://i.pravatar.cc/150?u=bri'],
    ['name' => 'Stephan Peralt', 'avatar' => 'https://i.pravatar.cc/150?u=ste'],
    ['name' => 'Doglas Martini', 'avatar' => 'https://i.pravatar.cc/150?u=dog'],
];

// --- 4. EXISTING TASKS DATA ---
$activeTasks = [
    ['id' => 1, 'emp' => 'Brian Villalobos', 'title' => 'Fix Dashboard CSS', 'priority' => 'High', 'deadline' => '2026-02-05', 'status' => 'In Progress', 'desc' => 'Align the sidebar and header containers to be flush.'],
    ['id' => 2, 'emp' => 'Anthony Lewis', 'title' => 'API Integration', 'priority' => 'Medium', 'deadline' => '2026-02-07', 'status' => 'Pending', 'desc' => 'Connect the recruitment module to the central SQL database.'],
];

function getPriorityStyle($priority) {
    switch($priority) {
        case "High": return ['bg' => "#fee2e2", 'text' => "#dc2626"];
        case "Medium": return ['bg' => "#fef3c7", 'text' => "#d97706"];
        default: return ['bg' => "#dcfce7", 'text' => "#166534"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Management | Workack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* --- LAYOUT STYLES --- */
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 30px; }

        /* --- DASHBOARD HEADER --- */
        .tl-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 12px; border: 1px solid #e1e1e1; margin-bottom: 30px; }
        
        /* --- TASK GRID --- */
        .task-grid { display: grid; grid-template-columns: 1fr 2fr; gap: 25px; align-items: start; }
        .tl-card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e1e1e1; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .card-header { font-weight: 700; font-size: 18px; margin-bottom: 20px; color: #333; display: flex; align-items: center; gap: 10px; }

        /* --- FORM ELEMENTS --- */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; font-size: 13px; font-weight: 600; color: #666; margin-bottom: 5px; }
        .tl-input, .tl-select, .tl-textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; transition: border 0.2s; box-sizing: border-box; }
        .tl-input:focus { border-color: #FF9B44; }

        .btn-assign { width: 100%; background: #FF9B44; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-top: 10px; display: flex; align-items: center; justify-content: center; gap: 10px; }

        /* --- IMMERSIVE TABLE --- */
        .immersive-table { width: 100%; border-collapse: separate; border-spacing: 0 12px; }
        .immersive-row { background: #f9fafb; border-radius: 12px; transition: 0.2s; }
        .immersive-row:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .immersive-cell { padding: 15px 20px; vertical-align: middle; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .immersive-row td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 1px solid #f0f0f0; }
        .immersive-row td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-right: 1px solid #f0f0f0; }

        .priority-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        
        /* --- ACTION DROPDOWN --- */
        .action-container { position: relative; display: inline-block; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; background: white; border: 1px solid #eee; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); z-index: 100; min-width: 140px; }
        .dropdown-menu.active { display: block; }
        .dropdown-item { padding: 10px 15px; font-size: 13px; color: #555; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .dropdown-item:hover { background: #f9fafb; color: #FF9B44; }

        /* --- MODAL POPUP --- */
        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: white; padding: 30px; border-radius: 16px; width: 450px; position: relative; box-shadow: 0 20px 40px rgba(0,0,0,0.2); }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="dashboard-scroll-area">
            <div class="tl-dashboard">
                
                <div class="tl-header">
                    <div>
                        <h1 style="font-size: 28px; font-weight: 800; margin: 0;">Task Management</h1>
                        <p style="font-size: 14px; color: #666; margin: 0;">Assign and track team workflow</p>
                    </div>
                    <div style="width: 50px; height: 50px; background: #fff7ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FF9B44; border: 1px solid #ffedd5;">
                        <i data-lucide="layout-list"></i>
                    </div>
                </div>

                <div class="task-grid">
                    
                    <div class="tl-card">
                        <div class="card-header"><i data-lucide="plus-circle" size="20" color="#FF9B44"></i> Assign New Work</div>
                        <div class="form-group">
                            <label>Employee Name</label>
                            <select id="taskEmp" class="tl-select">
                                <option value="">Select Employee</option>
                                <?php foreach($employees as $e): ?>
                                    <option value="<?= $e['name'] ?>"><?= $e['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Task Title</label>
                            <input type="text" id="taskTitle" class="tl-input" placeholder="e.g. Database Optimization">
                        </div>
                        <div class="form-group">
                            <label>Priority</label>
                            <select id="taskPriority" class="tl-select">
                                <option value="Low">Low</option>
                                <option value="Medium" selected>Medium</option>
                                <option value="High">High</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Deadline</label>
                            <input type="date" id="taskDeadline" class="tl-input">
                        </div>
                        <div class="form-group">
                            <label>Description</label>
                            <textarea id="taskDesc" class="tl-textarea" rows="3" placeholder="Briefly explain the task..."></textarea>
                        </div>
                        <button class="btn-assign" onclick="assignTask()">
                            <i data-lucide="send" size="18"></i> Assign to Employee
                        </button>
                    </div>

                    <div class="tl-card">
                        <div class="card-header"><i data-lucide="activity" size="20" color="#FF9B44"></i> Team Task Board</div>
                        <table class="immersive-table">
                            <thead>
                                <tr style="text-align:left; font-size:12px; color:#9ca3af; text-transform:uppercase;">
                                    <th style="padding-left:20px;">Employee</th>
                                    <th>Task</th>
                                    <th>Priority</th>
                                    <th>Deadline</th>
                                    <th style="text-align:right; padding-right:20px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="taskBoardBody">
                                <?php foreach($activeTasks as $task): 
                                    $pStyle = getPriorityStyle($task['priority']);
                                ?>
                                    <tr class="immersive-row" id="task-row-<?= $task['id'] ?>">
                                        <td class="immersive-cell" style="padding-left:20px;">
                                            <span style="font-weight:700; color:#333;"><?= $task['emp'] ?></span>
                                        </td>
                                        <td class="immersive-cell">
                                            <div style="background:#f3f4f6; padding:6px 12px; border-radius:6px; font-weight:600; font-size:13px;"><?= $task['title'] ?></div>
                                        </td>
                                        <td class="immersive-cell">
                                            <span class="priority-badge" style="background:<?= $pStyle['bg'] ?>; color:<?= $pStyle['text'] ?>;">
                                                <?= $task['priority'] ?>
                                            </span>
                                        </td>
                                        <td class="immersive-cell" style="color:#666; font-size:12px;"><?= $task['deadline'] ?></td>
                                        <td class="immersive-cell" style="text-align:right; padding-right:20px;">
                                            <div class="action-container">
                                                <i data-lucide="more-vertical" style="cursor:pointer; color:#ccc;" onclick="toggleDropdown(<?= $task['id'] ?>)"></i>
                                                <div class="dropdown-menu" id="dropdown-<?= $task['id'] ?>">
                                                    <div class="dropdown-item" onclick="viewDetails(<?= $task['id'] ?>)"><i data-lucide="eye" size="14"></i> View Details</div>
                                                    <div class="dropdown-item" onclick="editTask(<?= $task['id'] ?>)"><i data-lucide="edit-3" size="14"></i> Edit Task</div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div id="modalOverlay" class="modal-overlay">
        <div class="modal-box" id="modalContent">
            </div>
    </div>

    <script>
        lucide.createIcons();

        // Sample Data for Client-Side Interactivity
        let tasks = <?= json_encode($activeTasks) ?>;

        function toggleDropdown(id) {
            document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.remove('active'));
            document.getElementById('dropdown-'+id).classList.add('active');
        }

        window.onclick = function(event) {
            if (!event.target.matches('[data-lucide="more-vertical"]')) {
                document.querySelectorAll('.dropdown-menu').forEach(el => el.classList.remove('active'));
            }
        }

        function assignTask() {
            const emp = document.getElementById('taskEmp').value;
            const title = document.getElementById('taskTitle').value;
            const priority = document.getElementById('taskPriority').value;
            const deadline = document.getElementById('taskDeadline').value;
            const desc = document.getElementById('taskDesc').value;

            if(!emp || !title || !deadline) {
                alert("Please fill in all assignment details.");
                return;
            }

            showModal('success', {title: "Task Assigned!"});
            
            // In a real app, you would send a POST request here.
            // For now, we simulate visual update.
            const newId = Date.now();
            const pColors = priority === 'High' ? {bg:'#fee2e2', text:'#dc2626'} : 
                           (priority === 'Medium' ? {bg:'#fef3c7', text:'#d97706'} : {bg:'#dcfce7', text:'#166534'});

            const newRow = `
                <tr class="immersive-row" id="task-row-${newId}">
                    <td class="immersive-cell" style="padding-left:20px;"><span style="font-weight:700;">${emp}</span></td>
                    <td class="immersive-cell"><div style="background:#fff7ed; padding:6px 12px; border-radius:6px; font-weight:600; border:1px solid #fed7aa;">${title}</div></td>
                    <td class="immersive-cell"><span class="priority-badge" style="background:${pColors.bg}; color:${pColors.text};">${priority}</span></td>
                    <td class="immersive-cell" style="color:#666;">${deadline}</td>
                    <td class="immersive-cell" style="text-align:right; padding-right:20px;"><i data-lucide="more-vertical" style="color:#ccc;"></i></td>
                </tr>
            `;
            document.getElementById('taskBoardBody').insertAdjacentHTML('afterbegin', newRow);
            lucide.createIcons();
        }

        function viewDetails(id) {
            const task = tasks.find(t => t.id === id);
            showModal('view', task);
        }

        function editTask(id) {
            const task = tasks.find(t => t.id === id);
            showModal('edit', task);
        }

        function showModal(type, data) {
            const overlay = document.getElementById('modalOverlay');
            const box = document.getElementById('modalContent');
            
            if(type === 'success') {
                box.innerHTML = `
                    <i data-lucide="check-circle" color="#16a34a" size="48" style="margin-bottom: 15px;"></i>
                    <h3 style="margin:0;">${data.title}</h3>
                    <p style="font-size:13px; color:#666; margin: 10px 0 20px;">The task has been saved permanently.</p>
                    <button class="btn-assign" onclick="closeModal()">Great!</button>
                `;
            } else if(type === 'view') {
                box.innerHTML = `
                    <div class="card-header"><i data-lucide="eye" size="20" color="#FF9B44"></i> Task Details</div>
                    <p style="font-size:13px; text-align:left; color:#666;"><strong>Assignee:</strong> ${data.emp}</p>
                    <p style="font-size:13px; text-align:left; color:#666;"><strong>Subject:</strong> ${data.title}</p>
                    <p style="font-size:13px; text-align:left; color:#666;"><strong>Status:</strong> ${data.status}</p>
                    <div style="background:#f9fafb; padding:15px; border-radius:8px; text-align:left; font-size:13px; margin:15px 0;">
                        ${data.desc}
                    </div>
                    <button class="btn-assign" onclick="closeModal()">Close</button>
                `;
            } else if(type === 'edit') {
                box.innerHTML = `
                    <div class="card-header"><i data-lucide="edit-3" size="20" color="#FF9B44"></i> Edit Task</div>
                    <div class="form-group" style="text-align:left;">
                        <label>Update Title</label>
                        <input type="text" class="tl-input" value="${data.title}">
                        <label style="margin-top:10px;">Update Deadline</label>
                        <input type="date" class="tl-input" value="${data.deadline}">
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button class="btn-assign" style="background:#eee; color:#333;" onclick="closeModal()">Cancel</button>
                        <button class="btn-assign" onclick="closeModal()">Save Changes</button>
                    </div>
                `;
            }
            overlay.classList.add('active');
            lucide.createIcons();
        }

        function closeModal() {
            document.getElementById('modalOverlay').classList.remove('active');
        }
    </script>
</body>
</html>