<?php
// --- 1. GLOBAL USER DATA (Shared by Sidebar/Header) ---
$user = [
    'name' => 'TL Manager',
    'role' => 'Team Lead', 
    'avatar_initial' => 'T'
];

// --- 2. DASHBOARD SPECIFIC DATA ---
$tlProfile = [
    'name' => 'TL Manager',
    'role' => 'Team Lead - Engineering',
    'email' => 'tl.manager@company.com'
];

$teamAttendance = [
    ['name' => 'Arun', 'status' => 'In Office'],
    ['name' => 'Priya', 'status' => 'WFH']
];

$projects = [
    ['name' => 'HR Management Web', 'working' => 4, 'pending' => 2, 'done' => 8]
];

$allTasks = [
    ['id' => 1, 'empName' => 'Arun', 'title' => 'Fix Login Bug', 'status' => 'Pending', 'type' => 'Manager'],
    ['id' => 2, 'empName' => 'Priya', 'title' => 'API Integration', 'status' => 'Verified', 'type' => 'Manager']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Lead Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* --- GLOBAL LAYOUT STYLES --- */
        body {
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7fc;
            display: flex; /* Creates the Side-by-Side layout */
            height: 100vh;
            overflow: hidden; /* Prevents body scroll, allows inner scroll */
        }

        /* The Right Side Container (Header + Dashboard) */
        .main-content-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0; 
            height: 100vh;
        }

        /* Scrollable Area for the Dashboard Content */
        .dashboard-scroll-area {
            flex: 1;
            overflow-y: auto;
            padding: 30px;
        }

        /* --- DASHBOARD SPECIFIC CSS --- */
        
        /* Header inside Dashboard content (Title area) */
        .tl-header {
            display: flex; justify-content: space-between; align-items: center;
            background: white; padding: 20px 30px; border-radius: 12px;
            border: 1px solid #e1e1e1; margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .header-title { font-size: 24px; font-weight: 800; color: #1a1a1a; margin: 0 0 5px 0; }
        .header-sub { font-size: 14px; color: #666; margin: 0; }
        .header-icon {
            width: 50px; height: 50px; background: #fff7ed; border-radius: 50%;
            display: flex; align-items: center; justify-content: center; color: #FF9B44;
            border: 1px solid #ffedd5;
        }

        /* Grid System */
        .tl-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
            padding-bottom: 40px; 
        }

        /* Cards */
        .tl-card {
            background: white; padding: 25px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1;
            display: flex; flex-direction: column; height: 320px;
        }
        .card-header {
            display: flex; align-items: center; gap: 10px; padding-bottom: 15px;
            border-bottom: 1px solid #f0f0f0; margin-bottom: 15px; font-size: 16px; font-weight: 700; color: #333;
            margin-top: 0;
        }
        .card-body { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; }

        /* Inputs & Buttons */
        .tl-input, .tl-select, .tl-textarea {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;
            font-size: 14px; outline: none; background: white; box-sizing: border-box;
            font-family: inherit;
        }
        .tl-input:focus, .tl-select:focus, .tl-textarea:focus { border-color: #FF9B44; }
        
        .tl-btn {
            padding: 10px; border-radius: 6px; font-weight: 700; cursor: pointer; text-align: center;
            border: none; transition: all 0.2s; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 14px;
        }
        .btn-primary { background: #FF9B44; color: white; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.2); }
        .btn-primary:hover { background: #e88b3a; }
        .btn-secondary { background: #f3f4f6; color: #333; border: 1px solid #e5e7eb; }
        .btn-secondary:hover { background: #e5e7eb; }
        .btn-outline { background: white; border: 1px solid #e5e7eb; color: #555; }
        .btn-outline:hover { border-color: #FF9B44; color: #FF9B44; }
        .btn-danger { background: #fee2e2; color: #dc2626; border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fecaca; }

        /* List Items & Badges */
        .list-item {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px; background: #f9fafb; border-radius: 6px; border: 1px solid #f0f0f0;
        }
        .badge {
            font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; border: 1px solid;
            display: inline-block;
        }
        .badge-blue { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
        .badge-orange { background: #fff7ed; color: #c2410c; border-color: #ffedd5; }
        .badge-green { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
        .badge-red { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

        /* Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); display: none;
            align-items: center; justify-content: center; z-index: 2000;
        }
        .modal-overlay.open { display: flex; }
        
        .modal-box {
            background: white; padding: 30px; border-radius: 12px; width: 400px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative;
        }
        .modal-title { margin-top: 0; font-size: 20px; color: #333; margin-bottom: 20px; }
        .close-icon { position: absolute; top: 20px; right: 20px; cursor: pointer; color: #999; }

        /* Helpers */
        .profile-row { display: flex; justify-content: space-between; font-size: 14px; padding: 5px 0; border-bottom: 1px solid #f9f9f9; }
        .profile-label { color: #666; }
        .profile-val { font-weight: 600; color: #333; }

        .att-status { font-size: 14px; margin-bottom: 20px; text-align: center; }
        .att-active { color: #16a34a; font-weight: 800; }
        .att-inactive { color: #dc2626; font-weight: 800; }
        
        .mt-auto { margin-top: auto; }
        .text-center { text-align: center; }
        .text-bold { font-weight: bold; }
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
                        <h1 class="header-title">Team Lead Portal</h1>
                        <p class="header-sub">Welcome back, <span style="color:#FF9B44; font-weight:bold;"><?= htmlspecialchars($tlProfile['name']) ?></span></p>
                    </div>
                    <div class="header-icon">
                        <i data-lucide="users" style="width:24px; height:24px;"></i>
                    </div>
                </div>

                <div class="tl-grid">
                    
                    <div class="tl-card">
                        <h3 class="card-header">Profile Details</h3>
                        <div class="card-body" style="justify-content:center;">
                            <div class="profile-row">
                                <span class="profile-label">Name:</span>
                                <span class="profile-val"><?= htmlspecialchars($tlProfile['name']) ?></span>
                            </div>
                            <div class="profile-row">
                                <span class="profile-label">Role:</span>
                                <span class="profile-val"><?= htmlspecialchars($tlProfile['role']) ?></span>
                            </div>
                            <div class="profile-row">
                                <span class="profile-label">Email:</span>
                                <span class="profile-val"><?= htmlspecialchars($tlProfile['email']) ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="clock" color="#FF9B44" width="20"></i> Attendance</h3>
                        <div class="card-body" style="justify-content:center;">
                            <p class="att-status">
                                Status: <span id="attStatusText" class="att-inactive">Offline</span>
                            </p>
                            <div style="display:flex; gap:10px;">
                                <button onclick="updateAttendance(true)" id="btnCheckIn" class="tl-btn btn-outline">Check In</button>
                                <button onclick="updateAttendance(false)" id="btnCheckOut" class="tl-btn btn-danger">Check Out</button>
                            </div>
                            <button onclick="openModal('modalVerify')" class="tl-btn btn-secondary" style="margin-top:10px;">
                                Verify Team Attendance
                            </button>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="clipboard-list" color="#FF9B44" width="20"></i> Project Status</h3>
                        <div class="card-body" id="projectList">
                            <?php foreach($projects as $proj): ?>
                                <div class="list-item" style="display:block;">
                                    <div style="font-weight:700; margin-bottom:8px;"><?= $proj['name'] ?></div>
                                    <div style="display:flex; gap:5px;">
                                        <span class="badge badge-blue">Working: <?= $proj['working'] ?></span>
                                        <span class="badge badge-orange">Pending: <?= $proj['pending'] ?></span>
                                        <span class="badge badge-green">Done: <?= $proj['done'] ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="check-square" color="#FF9B44" width="20"></i> Approvals</h3>
                        <div class="card-body">
                            <?php if (empty($allTasks)): ?>
                                <div style="text-align:center; color:#999; font-size:13px; margin-top:20px;">No pending approvals</div>
                            <?php else: ?>
                                <?php foreach($allTasks as $t): ?>
                                    <div class="list-item" id="task-<?= $t['id'] ?>">
                                        <div>
                                            <div style="font-size:12px; font-weight:700; color:#333;"><?= $t['empName'] ?></div>
                                            <div style="font-size:12px; color:#666;"><?= $t['title'] ?></div>
                                        </div>
                                        <div style="text-align:right;">
                                            <span class="badge <?= $t['status'] === 'Verified' ? 'badge-green' : 'badge-orange' ?>">
                                                <?= $t['status'] ?>
                                            </span>
                                            <?php if($t['status'] !== 'Verified'): ?>
                                                <div onclick="verifyTask(<?= $t['id'] ?>, this)" style="font-size:10px; color:#FF9B44; font-weight:bold; cursor:pointer; margin-top:4px;">VERIFY</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="folder-plus" color="#FF9B44" width="20"></i> Project Grouping</h3>
                        <div class="card-body">
                            <input id="projNameInput" class="tl-input" placeholder="Project Name">
                            <select class="tl-select" id="projMemberSelect" onchange="addMemberToGroup(this.value)">
                                <option value="">Add Members (Max 3)</option>
                                <option value="Arun">Arun</option>
                                <option value="Priya">Priya</option>
                                <option value="John">John</option>
                            </select>
                            
                            <div id="groupMembersContainer" style="display:flex; flex-wrap:wrap; gap:5px; min-height:30px;">
                                </div>

                            <button onclick="createGroup()" class="tl-btn btn-primary mt-auto">Create Group</button>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="user-x" color="#FF9B44" width="20"></i> Monitoring</h3>
                        <div class="card-body">
                            <?php foreach(['Arun', 'Priya', 'John'] as $name): ?>
                                <div class="list-item">
                                    <span style="font-weight:500;"><?= $name ?></span>
                                    <button onclick="openReportModal('<?= $name ?>')" class="badge badge-red" style="cursor:pointer; border:1px solid #fecaca; background:none;">REPORT</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="plus" color="#FF9B44" width="20"></i> Assign Task</h3>
                        <div class="card-body">
                            <select id="taskEmpSelect" class="tl-select">
                                <option value="">Select Employee</option>
                                <option value="Arun">Arun</option>
                                <option value="Priya">Priya</option>
                            </select>
                            <textarea id="taskDescInput" class="tl-textarea" placeholder="Task Description..." style="height:80px; resize:none;"></textarea>
                            <button onclick="assignTask()" class="tl-btn btn-primary mt-auto">Assign</button>
                        </div>
                    </div>

                    <div class="tl-card" style="background:#fff7ed; border-color:#ffedd5;">
                        <h3 class="card-header" style="border-bottom-color:#fed7aa;">
                            <i data-lucide="bell" color="#FF9B44" width="20"></i> Announcements
                        </h3>
                        <div class="card-body">
                            <div class="list-item" style="background:white; border-left:4px solid #FF9B44;">📢 Server migration this Sunday.</div>
                            <div class="list-item" style="background:white; border-left:4px solid #FF9B44;">📢 Office closed Friday.</div>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="cpu" color="#FF9B44" width="20"></i> IT & Admin</h3>
                        <div class="card-body" style="justify-content:center;">
                            <button onclick="openITModal('System Diagnostics')" class="tl-btn btn-outline">System Diagnostics</button>
                            <button onclick="openITModal('Software Requests')" class="tl-btn btn-outline" style="margin-top:10px;">Software Request</button>
                            <button onclick="openHRModal()" class="tl-btn" style="background:#333; color:white; margin-top:10px;">Contact Admin</button>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <div id="modalMsg" class="modal-overlay">
        <div class="modal-box text-center">
            <div style="display:flex; justify-content:center; margin-bottom:15px;">
                <i data-lucide="info" color="#FF9B44" width="40" height="40"></i>
            </div>
            <p id="popupMsgText" class="text-bold" style="margin-bottom:20px;"></p>
            <button onclick="closeModal('modalMsg')" class="tl-btn btn-primary">Close</button>
        </div>
    </div>

    <div id="modalVerify" class="modal-overlay">
        <div class="modal-box">
            <h3 class="modal-title">Adjust Team Attendance</h3>
            <div style="margin-bottom:20px; display:flex; flex-direction:column; gap:10px;">
                <?php foreach($teamAttendance as $member): ?>
                    <div class="list-item">
                        <span><?= $member['name'] ?></span>
                        <select class="tl-select" style="width:auto; padding:5px;">
                            <option value="In Office" <?= $member['status'] == 'In Office' ? 'selected' : '' ?>>In Office</option>
                            <option value="WFH" <?= $member['status'] == 'WFH' ? 'selected' : '' ?>>WFH</option>
                            <option value="Absent" <?= $member['status'] == 'Absent' ? 'selected' : '' ?>>Absent</option>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display:flex; gap:10px;">
                <button onclick="closeModal('modalVerify')" class="tl-btn btn-secondary">Cancel</button>
                <button onclick="closeModal('modalVerify'); showPopup('Attendance Confirmed.');" class="tl-btn btn-primary">Confirm</button>
            </div>
        </div>
    </div>

    <div id="modalReport" class="modal-overlay">
        <div class="modal-box">
            <h3 class="modal-title" id="reportTitle">Report</h3>
            <textarea class="tl-textarea" placeholder="Provide reason..." style="height:100px; margin-bottom:20px;"></textarea>
            <div style="display:flex; gap:10px;">
                <button onclick="closeModal('modalReport')" class="tl-btn btn-secondary">Cancel</button>
                <button onclick="closeModal('modalReport'); showPopup('Report sent successfully.');" class="tl-btn btn-danger">Send Report</button>
            </div>
        </div>
    </div>

    <div id="modalGenericInput" class="modal-overlay">
        <div class="modal-box">
            <i data-lucide="x" class="close-icon" onclick="closeModal('modalGenericInput')"></i>
            <h3 class="modal-title" id="genericInputTitle">Query</h3>
            <textarea class="tl-textarea" placeholder="Describe your query..." style="height:100px; margin-bottom:20px;"></textarea>
            <button onclick="closeModal('modalGenericInput'); showPopup('Request submitted.');" class="tl-btn btn-primary">Submit Request</button>
        </div>
    </div>

    <script>
        // 1. Initialize Icons
        lucide.createIcons();

        // 2. Global State for Grouping
        let groupMembers = [];

        // --- INTERACTIVITY FUNCTIONS ---

        function openModal(id) {
            document.getElementById(id).classList.add('open');
        }

        function closeModal(id) {
            document.getElementById(id).classList.remove('open');
        }

        function showPopup(msg) {
            document.getElementById('popupMsgText').innerText = msg;
            openModal('modalMsg');
        }

        // Attendance Logic
        function updateAttendance(isPresent) {
            const statusText = document.getElementById('attStatusText');
            const btnIn = document.getElementById('btnCheckIn');
            const btnOut = document.getElementById('btnCheckOut');

            if (isPresent) {
                statusText.innerText = "Active";
                statusText.className = "att-active";
                btnIn.classList.add('btn-primary');
                btnIn.classList.remove('btn-outline');
                btnOut.classList.add('btn-outline');
                btnOut.classList.remove('btn-danger');
            } else {
                statusText.innerText = "Offline";
                statusText.className = "att-inactive";
                btnIn.classList.add('btn-outline');
                btnIn.classList.remove('btn-primary');
                btnOut.classList.add('btn-danger');
                btnOut.classList.remove('btn-outline');
            }
        }

        // Task Verification
        function verifyTask(id, element) {
            // Visual feedback only
            element.parentElement.querySelector('.badge').className = 'badge badge-green';
            element.parentElement.querySelector('.badge').innerText = 'Verified';
            element.remove(); // Remove the verify link
            showPopup('Task Verified.');
        }

        // Project Grouping Logic
        function addMemberToGroup(name) {
            if(!name) return;
            if(groupMembers.length >= 3) {
                showPopup("Max 3 members per group.");
                return;
            }
            if(!groupMembers.includes(name)) {
                groupMembers.push(name);
                renderGroupMembers();
            }
            // Reset select
            document.getElementById('projMemberSelect').value = "";
        }

        function removeMember(name) {
            groupMembers = groupMembers.filter(m => m !== name);
            renderGroupMembers();
        }

        function renderGroupMembers() {
            const container = document.getElementById('groupMembersContainer');
            container.innerHTML = groupMembers.map(name => `
                <span class="badge badge-blue" style="display:flex; alignItems:center; gap:5px">
                    ${name} <i data-lucide="x" width="10" style="cursor:pointer" onclick="removeMember('${name}')"></i>
                </span>
            `).join('');
            lucide.createIcons(); // Re-init icons for new elements
        }

        function createGroup() {
            const name = document.getElementById('projNameInput').value;
            if(!name || groupMembers.length === 0) {
                showPopup("Enter project name and members.");
                return;
            }

            // Add to list visually
            const list = document.getElementById('projectList');
            const newHTML = `
                <div class="list-item" style="display:block;">
                    <div style="font-weight:700; margin-bottom:8px;">${name}</div>
                    <div style="display:flex; gap:5px;">
                        <span class="badge badge-blue">Working: 0</span>
                        <span class="badge badge-orange">Pending: ${groupMembers.length}</span>
                        <span class="badge badge-green">Done: 0</span>
                    </div>
                </div>
            `;
            list.innerHTML = newHTML + list.innerHTML;
            
            // Reset
            document.getElementById('projNameInput').value = "";
            groupMembers = [];
            renderGroupMembers();
            showPopup(`Project "${name}" Created.`);
        }

        // Assign Task
        function assignTask() {
            const emp = document.getElementById('taskEmpSelect').value;
            const desc = document.getElementById('taskDescInput').value;

            if(!emp || !desc) {
                showPopup("Please select an employee and enter description.");
                return;
            }

            showPopup(`Task assigned to ${emp}`);
            document.getElementById('taskDescInput').value = "";
            document.getElementById('taskEmpSelect').value = "";
        }

        // Modals specific logic
        function openReportModal(name) {
            document.getElementById('reportTitle').innerText = "Report " + name;
            openModal('modalReport');
        }

        function openITModal(type) {
            document.getElementById('genericInputTitle').innerText = type;
            openModal('modalGenericInput');
        }

        function openHRModal() {
            document.getElementById('genericInputTitle').innerText = "Contact Admin";
            openModal('modalGenericInput');
        }

    </script>
</body>
</html>