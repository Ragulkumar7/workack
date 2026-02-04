<?php
// --- 1. SESSION & SECURITY CHECK ---
session_start();

// If user is not logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login.php");
    exit();
}

// --- 2. GLOBAL USER DATA ---
$user = [
    'name' => $_SESSION['username'] ?? 'TL Manager', // Use session name if available
    'role' => 'Team Lead', 
    'avatar_initial' => 'T'
];

// --- 3. DASHBOARD SPECIFIC DATA ---
$tlProfile = [
    'name' => $_SESSION['username'] ?? 'TL Manager',
    'role' => 'Team Lead - Engineering',
    'email' => 'tl.manager@company.com'
];

// Employee Data for the "Employees" Section
$employeesUnderTL = [
    ['name' => 'Anthony Lewis', 'role' => 'Finance', 'avatar' => 'https://i.pravatar.cc/150?u=ant', 'dept' => 'Finance'],
    ['name' => 'Brian Villalobos', 'role' => 'PHP Developer', 'avatar' => 'https://i.pravatar.cc/150?u=bri', 'dept' => 'Development'],
    ['name' => 'Stephan Peralt', 'role' => 'Executive', 'avatar' => 'https://i.pravatar.cc/150?u=ste', 'dept' => 'Marketing'],
    ['name' => 'Doglas Martini', 'role' => 'Project Manager', 'avatar' => 'https://i.pravatar.cc/150?u=dog', 'dept' => 'Manager'],
    ['name' => 'Anthony Lewis', 'role' => 'UI/UX Designer', 'avatar' => 'https://i.pravatar.cc/150?u=ant2', 'dept' => 'UI/UX Design'],
];

// Data for Clock-In/Out Section
$clockInOutData = [
    ['name' => 'Daniel Esbella', 'role' => 'UI/UX Designer', 'avatar' => 'https://i.pravatar.cc/150?u=dan', 'time' => '09:15', 'type' => 'on-time'],
    ['name' => 'Doglas Martini', 'role' => 'Project Manager', 'avatar' => 'https://i.pravatar.cc/150?u=dog', 'time' => '09:36', 'type' => 'late'],
    ['name' => 'Brian Villalobos', 'role' => 'PHP Developer', 'avatar' => 'https://i.pravatar.cc/150?u=bri', 'time' => '09:15', 'type' => 'on-time', 'details' => true],
];

$projects = [
    ['name' => 'HR Management Web', 'working' => 4, 'pending' => 2, 'done' => 8]
];

$allTasks = [
    ['id' => 1, 'empName' => 'Arun', 'title' => 'Fix Login Bug', 'status' => 'Pending'],
];

// Stats Logic
$totalEmployeesCount = count($employeesUnderTL);
$completedProjectsCount = 12;
$activeProjectsCount = count($projects);

// Attendance Mock Data
$attendancePresent = 59;
$attendanceLate = 21;
$attendanceAbsent = 15;
$totalAttendanceNum = 120;
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
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 30px; }

        /* --- DASHBOARD SPECIFIC CSS --- */
        .tl-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 12px; border: 1px solid #e1e1e1; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
        .header-title { font-size: 24px; font-weight: 800; color: #1a1a1a; margin: 0; }
        
        .stats-bar { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-item { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e1e1e1; text-align: center; box-shadow: 0 4px 10px rgba(0,0,0,0.02); }
        .stat-item h2 { margin: 0; font-size: 28px; color: #FF9B44; }
        .stat-item p { margin: 5px 0 0; font-size: 11px; color: #666; font-weight: 700; text-transform: uppercase; }

        .tl-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px; padding-bottom: 40px; }
        .tl-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1; display: flex; flex-direction: column; min-height: 380px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; margin-bottom: 15px; font-size: 16px; font-weight: 700; color: #333; margin-top: 0; }
        .card-body { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 12px; }

        /* Gauge Visual */
        .gauge-container { text-align: center; padding: 20px 0; }
        .attendance-gauge { width: 220px; height: 110px; border: 18px solid #eee; border-bottom: none; border-radius: 220px 220px 0 0; margin: 0 auto; position: relative; }
        .gauge-fill { position: absolute; top: -18px; left: -18px; width: 100%; height: 100%; border: 18px solid #55ce63; border-bottom: none; border-radius: 220px 220px 0 0; clip-path: inset(0 40% 0 0); }
        .gauge-text { position: absolute; bottom: -5px; left: 50%; transform: translateX(-50%); }
        .gauge-text h2 { margin: 0; font-size: 28px; color: #333; }
        
        .clock-item { border: 1px solid #eee; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
        .clock-header { display: flex; align-items: center; justify-content: space-between; }
        .clock-user { display: flex; align-items: center; gap: 10px; }
        .clock-user img { width: 35px; height: 35px; border-radius: 50%; }
        .clock-status-tag { padding: 2px 8px; border-radius: 20px; font-size: 12px; font-weight: 700; color: white; display: flex; align-items: center; gap: 4px; }
        .bg-on-time { background: #55ce63; }
        .bg-late-tag { background: #f62d51; }
        .clock-details-grid { display: grid; grid-template-columns: repeat(3, 1fr); margin-top: 10px; padding-top: 10px; border-top: 1px solid #f9f9f9; text-align: center; }
        .clock-details-grid div p { font-size: 10px; color: #888; margin: 0; }
        .clock-details-grid div h4 { font-size: 13px; margin: 2px 0 0; }

        .tl-btn { padding: 10px; border-radius: 6px; font-weight: 700; cursor: pointer; border: none; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; transition: 0.2s; }
        .btn-primary { background: #FF9B44; color: white; }
        .badge { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; border: 1px solid; display: inline-block; }
        .badge-blue { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
        .badge-orange { background: #fff7ed; color: #c2410c; border-color: #ffedd5; }
        .badge-green { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
        .badge-red { background: #fef2f2; color: #dc2626; border-color: #fecaca; }

        /* Logout Button Style */
        .logout-btn {
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f62d51;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
            border: none;
        }
        .logout-btn:hover { background: #d61c3c; }

        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 2000; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: white; padding: 30px; border-radius: 12px; width: 450px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; }
        .announcement-item { padding: 10px; background: #fff7ed; border-left: 4px solid #FF9B44; border-radius: 4px; font-size: 12px; color: #444; }
        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
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
                        <h1 class="header-title">Team Lead Dashboard</h1>
                        <p style="font-size: 14px; color: #666; margin: 0;">Welcome back, <span style="color:#FF9B44; font-weight:bold;"><?= htmlspecialchars($tlProfile['name']) ?></span></p>
                    </div>
                    <div style="display:flex; align-items:center; gap: 15px;">
                        <a href="../login/login.php?logout=1" class="logout-btn">
                            <i data-lucide="log-out" width="18"></i> Logout
                        </a>
                        <div style="width: 50px; height: 50px; background: #fff7ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FF9B44;">
                            <i data-lucide="users"></i>
                        </div>
                    </div>
                </div>

                <div class="stats-bar">
                    <div class="stat-item"><h2><?= $totalEmployeesCount ?></h2><p>Employees Under You</p></div>
                    <div class="stat-item"><h2><?= $activeProjectsCount ?></h2><p>Active Projects</p></div>
                    <div class="stat-item"><h2><?= $completedProjectsCount ?></h2><p>Projects Completed</p></div>
                </div>

                <div class="tl-grid">
                    
                    <div class="tl-card">
                        <div class="card-header"><span>Attendance Overview</span><small style="color: #888;">Today</small></div>
                        <div class="gauge-container">
                            <div class="attendance-gauge">
                                <div class="gauge-fill" style="border-color: #55ce63;"></div>
                                <div class="gauge-text"><p>Total Attendance</p><h2><?= $totalAttendanceNum ?></h2></div>
                            </div>
                            <div style="display:flex; justify-content: space-around; font-size: 12px; margin-top: 15px;">
                                <span><i class="dot" style="background:#55ce63"></i> Present <?= $attendancePresent ?>%</span>
                                <span><i class="dot" style="background:#00c5fb"></i> Late <?= $attendanceLate ?>%</span>
                                <span><i class="dot" style="background:#f62d51"></i> Absent <?= $attendanceAbsent ?>%</span>
                            </div>
                        </div>
                    </div>

                    <div class="tl-card">
                        <div class="card-header"><span>Clock-In/Out</span><button style="border:none; background:none; color:#FF9B44; font-size:12px; font-weight:700;">View All</button></div>
                        <div class="card-body">
                            <?php foreach($clockInOutData as $entry): ?>
                                <div class="clock-item">
                                    <div class="clock-header">
                                        <div class="clock-user">
                                            <img src="<?= $entry['avatar'] ?>" alt="">
                                            <div><p style="font-size:13px; font-weight:600; margin:0;"><?= $entry['name'] ?></p><small style="color:#888;"><?= $entry['role'] ?></small></div>
                                        </div>
                                        <div class="clock-status-tag <?= $entry['type'] == 'on-time' ? 'bg-on-time' : 'bg-late-tag' ?>">
                                            <i data-lucide="history" size="12"></i> <?= $entry['time'] ?>
                                        </div>
                                    </div>
                                    <?php if(isset($entry['details'])): ?>
                                        <div class="clock-details-grid">
                                            <div><p>Clock In</p><h4>10:30 AM</h4></div>
                                            <div><p>Clock Out</p><h4>09:45 AM</h4></div>
                                            <div><p>Production</p><h4>09:21 Hrs</h4></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tl-card">
                        <div class="card-header"><span>Employees</span><button style="border:none; background:#f3f4f6; padding:5px 12px; border-radius:4px; font-size:11px; font-weight:600; cursor:pointer;">View All</button></div>
                        <div class="card-body">
                            <?php foreach($employeesUnderTL as $emp): ?>
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid #f9f9f9;">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img src="<?= $emp['avatar'] ?>" style="width:35px; height:35px; border-radius:50%;">
                                        <div><p style="font-size:13px; font-weight:600; margin:0;"><?= $emp['name'] ?></p><small style="color:#777;"><?= $emp['role'] ?></small></div>
                                    </div>
                                    <span class="badge badge-blue"><?= $emp['dept'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header">Project Breakdown</h3>
                        <div class="card-body" id="projectListDisplay">
                            <?php foreach($projects as $proj): ?>
                                <div style="background:#f9fafb; padding:15px; border-radius:8px; border:1px solid #f0f0f0;">
                                    <div style="font-weight:700; margin-bottom:10px; font-size: 14px;"><?= $proj['name'] ?></div>
                                    <div style="display:flex; gap:8px;">
                                        <span class="badge badge-blue">Working: <?= $proj['working'] ?></span>
                                        <span class="badge badge-orange">Pending: <?= $proj['pending'] ?></span>
                                        <span class="badge badge-green">Done: <?= $proj['done'] ?></span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header">Project Grouping</h3>
                        <div class="card-body">
                            <label style="font-size:12px; font-weight:600; color:#666;">Project Name</label>
                            <input id="projNameInput" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;" placeholder="Enter Project Name">
                            
                            <label style="font-size:12px; font-weight:600; color:#666; margin-top:10px;">Select Members</label>
                            <select id="projMemberSelect" onchange="handleMemberAdd(this.value)" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;">
                                <option value="">Add Members (Max 3)</option>
                                <?php foreach($employeesUnderTL as $emp): ?>
                                    <option value="<?= htmlspecialchars($emp['name']) ?>"><?= htmlspecialchars($emp['name']) ?></option>
                                <?php endforeach; ?>
                            </select>

                            <div id="selectedGroupMembers" style="display:flex; flex-wrap:wrap; gap:5px; min-height:30px; margin-top:10px;"></div>
                            <button onclick="assignAndBreakdown()" class="tl-btn btn-primary" style="margin-top:auto;">Assign Project</button>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header">Monitoring</h3>
                        <div class="card-body">
                            <?php foreach(['Arun', 'Priya', 'John'] as $name): ?>
                                <div style="display:flex; justify-content:space-between; align-items:center; padding:10px; background:#f9fafb; border-radius:6px;">
                                    <span style="font-weight:600; font-size:14px;"><?= $name ?></span>
                                    <button onclick="triggerReport('<?= $name ?>')" class="badge badge-red" style="cursor:pointer; background:white;">REPORT</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="megaphone" color="#FF9B44" size="18"></i> Announcements</h3>
                        <div class="card-body">
                            <div class="announcement-item">📢 Office Meeting scheduled for 4 PM today.</div>
                            <div class="announcement-item">📢 New 'Work From Home' policy updated.</div>
                        </div>
                    </div>

                    <div class="tl-card">
                        <h3 class="card-header"><i data-lucide="shield" color="#FF9B44" size="18"></i> IT & Admin</h3>
                        <div class="card-body" style="justify-content: center; gap: 10px;">
                            <button onclick="triggerITPopup('System Diagnostics')" style="background:white; border:1px solid #ddd; padding:10px; border-radius:6px; cursor:pointer;">System Diagnostics</button>
                            <button onclick="triggerITPopup('Software Request')" style="background:white; border:1px solid #ddd; padding:10px; border-radius:6px; cursor:pointer;">Software Request</button>
                            <button onclick="triggerITPopup('Contact Admin')" style="background:#333; color:white; padding:10px; border-radius:6px; border:none; cursor:pointer;">Contact Admin</button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div id="problemPopup" class="modal-overlay">
        <div class="modal-box">
            <i data-lucide="x" style="position:absolute; top:20px; right:20px; cursor:pointer; color:#999;" onclick="closeProblemPopup()"></i>
            <h3 id="popupTitle" style="margin-top:0; color:#333;">Report Issue</h3>
            <p id="popupSub" style="font-size:13px; color:#666;"></p>
            <div style="margin-top:15px;"><label style="font-size:12px; font-weight:600;">Problem Summary</label><input type="text" id="probSummary" style="width:100%; padding:10px; border:1px solid #ddd; border-radius:6px;"></div>
            <div style="margin-top:15px;"><label style="font-size:12px; font-weight:600;">Detailed Description</label><textarea id="probDesc" style="width:100%; height:100px; padding:10px; border:1px solid #ddd; border-radius:6px;"></textarea></div>
            <div style="display:flex; gap:10px; margin-top:20px;"><button onclick="closeProblemPopup()" style="flex:1; padding:10px; border:none; border-radius:6px; background:#f3f4f6;">Cancel</button><button onclick="submitProblem()" id="submitBtn" style="flex:1; padding:10px; border:none; border-radius:6px; background:#FF9B44; color:white;">Send</button></div>
        </div>
    </div>

    <div id="modalSuccess" class="modal-overlay">
        <div class="modal-box" style="text-align:center;">
            <i data-lucide="check-circle" color="#16a34a" width="40" height="40" style="margin:0 auto 15px;"></i>
            <p id="successText" style="font-weight:700;"></p>
            <button onclick="document.getElementById('modalSuccess').classList.remove('open')" class="btn-primary" style="padding:10px 20px; border-radius:6px; border:none; background:#FF9B44; color:white; cursor:pointer;">Close</button>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let currentGroup = [];
        let currentPopupType = "";

        function handleMemberAdd(val) {
            if(!val || currentGroup.length >= 3) return;
            if(!currentGroup.includes(val)) {
                currentGroup.push(val);
                renderGroup();
            }
            document.getElementById('projMemberSelect').value = "";
        }
        function renderGroup() {
            const container = document.getElementById('selectedGroupMembers');
            container.innerHTML = currentGroup.map(m => `<span class="badge badge-blue" style="margin-right:5px; margin-bottom:5px;">${m}</span>`).join('');
        }
        
        function assignAndBreakdown() {
            const name = document.getElementById('projNameInput').value;
            if(!name || currentGroup.length === 0) { 
                showSuccess("Please fill in project name and select members."); 
                return; 
            }
            
            const list = document.getElementById('projectListDisplay');
            const newProj = `<div style="background:#fff7ed; padding:15px; border-radius:8px; border:1px solid #fed7aa; margin-top:10px;"><div style="font-weight:700; margin-bottom:10px; font-size:14px;">${name}</div><div style="display:flex; gap:8px;"><span class="badge badge-blue">Working: ${currentGroup.length}</span><span class="badge badge-orange">Pending: 1</span><span class="badge badge-green">Done: 0</span></div></div>`;
            list.innerHTML = newProj + list.innerHTML;
            
            // Reset fields
            document.getElementById('projNameInput').value = "";
            currentGroup = [];
            renderGroup();
            
            // Open Popup Message instead of Alert/Localhost page
            showSuccess(`Project "${name}" assigned successfully!`);
        }

        function triggerReport(name) {
            currentPopupType = "MANAGER";
            document.getElementById('popupTitle').innerText = "Send Report to Manager";
            document.getElementById('popupSub').innerText = "Reporting: " + name;
            document.getElementById('problemPopup').classList.add('open');
        }

        function triggerITPopup(type) {
            currentPopupType = "IT";
            document.getElementById('popupTitle').innerText = type;
            document.getElementById('popupSub').innerText = "IT Support Request";
            document.getElementById('problemPopup').classList.add('open');
        }

        function closeProblemPopup() { document.getElementById('problemPopup').classList.remove('open'); }

        function submitProblem() {
            closeProblemPopup();
            showSuccess("Report/Request sent successfully.");
        }

        function showSuccess(msg) {
            document.getElementById('successText').innerText = msg;
            document.getElementById('modalSuccess').classList.add('open');
        }
    </script>
</body>
</html>