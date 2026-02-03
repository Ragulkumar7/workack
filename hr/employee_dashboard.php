<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>
<?php
// --- MOCK DATA ---
$empDetails = [
    "name" => "Stephan Peralt",
    "initials" => "SP",
    "role" => "Senior Product Designer",
    "email" => "steperde124@example.com",
    "phone" => "+91 98765 43210",
    "joined" => "15 Jan 2024",
];

$leaveStats = [
    "onTime" => 1254,
    "absent" => 14,
    "total" => 16,
    "taken" => 10,
    "workedDays" => 240,
    "lop" => 2,
    "wfh" => 12,
    "late" => 5,
];

// Initial Tasks
$initialTasks = [
    ["id" => 1, "title" => "Submit Q1 Report", "status" => "Pending", "time" => "120", "empName" => "Arun"],
    ["id" => 2, "title" => "Update Client Assets", "status" => "Completed", "time" => "45", "empName" => "Arun"],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* 1. Layout Reset */
        .ed-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
        }

        /* 2. Header */
        .ed-header {
            display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;
        }
        .ed-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .ed-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }
        
        .export-btn {
            background: white; border: 1px solid #e1e1e1; color: #666;
            padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 8px; font-size: 13px; transition: all 0.2s;
        }
        .export-btn:hover { border-color: #FF9B44; color: #FF9B44; }

        /* 3. Notification Banner */
        .notif-banner {
            background: #eff6ff; border: 1px solid #dbeafe; border-radius: 8px;
            padding: 15px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;
        }
        .notif-text { color: #1e40af; font-weight: 500; font-size: 14px; display: flex; align-items: center; gap: 10px; }
        .close-notif { background: none; border: none; cursor: pointer; color: #60a5fa; }
        .close-notif:hover { color: #2563eb; }

        /* 4. Grid System */
        .grid-3 {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; margin-bottom: 30px;
        }

        /* 5. Cards */
        .ed-card {
            background: white; padding: 25px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1;
            display: flex; flex-direction: column; height: 100%;
        }
        .card-header { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px; }

        /* 6. Profile Styles */
        .profile-row { display: flex; gap: 20px; margin-bottom: 25px; }
        .avatar-circle {
            width: 70px; height: 70px; border-radius: 50%; background: #FF9B44; color: white;
            font-size: 24px; font-weight: 800; display: flex; align-items: center; justify-content: center;
            border: 4px solid #ffedd5; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.2);
        }
        .prof-name { font-size: 18px; font-weight: 800; color: #333; margin: 0 0 5px 0; }
        .prof-role { font-size: 11px; font-weight: 700; color: #FF9B44; text-transform: uppercase; margin-bottom: 10px; display: block; }
        .prof-detail { font-size: 12px; color: #666; display: flex; align-items: center; gap: 8px; margin-bottom: 4px; }

        .docs-section {
            margin-top: auto; background: #f9fafb; padding: 15px; border-radius: 8px; border: 1px solid #f0f0f0;
        }
        .docs-title { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
        .doc-item {
            display: flex; justify-content: space-between; align-items: center; font-size: 13px; color: #555;
            padding: 6px 0; border-bottom: 1px solid #eee; cursor: pointer; transition: color 0.2s;
        }
        .doc-item:last-child { border-bottom: none; }
        .doc-item:hover { color: #FF9B44; }

        /* 7. Attendance Styles */
        .att-circle {
            width: 70px; height: 70px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
            margin-bottom: 15px; transition: background 0.3s;
        }
        .att-active { background: #dcfce7; color: #16a34a; }
        .att-inactive { background: #e5e7eb; color: #9ca3af; }
        
        .att-status-box {
            background: #f9fafb; border: 1px solid #f0f0f0; border-radius: 12px; padding: 30px;
            display: flex; flex-direction: column; align-items: center; text-align: center; margin-bottom: 20px; flex: 1;
        }
        .status-txt { font-size: 20px; font-weight: 800; margin: 0; }
        .status-active { color: #16a34a; } .status-inactive { color: #9ca3af; }

        .punch-btn {
            width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 14px;
            cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px;
            transition: transform 0.1s; box-shadow: 0 4px 10px rgba(0,0,0,0.1); color: white;
        }
        .btn-in { background: #22c55e; } .btn-in:hover { background: #16a34a; }
        .btn-out { background: #ef4444; } .btn-out:hover { background: #dc2626; }

        /* 8. Stats List */
        .stat-list { display: flex; flex-direction: column; gap: 10px; flex: 1; margin-bottom: 20px; }
        .stat-row {
            display: flex; justify-content: space-between; align-items: center; padding: 12px;
            border-radius: 8px; border: 1px solid transparent; font-size: 13px;
        }
        .row-green { background: #f0fdf4; border-color: #dcfce7; color: #166534; }
        .row-blue { background: #eff6ff; border-color: #dbeafe; color: #1e40af; }
        .row-orange { background: #fff7ed; border-color: #ffedd5; color: #9a3412; }
        .row-red { background: #fef2f2; border-color: #fee2e2; color: #991b1b; }
        
        .row-label { display: flex; align-items: center; gap: 8px; font-weight: 600; }
        .row-val { font-weight: 800; font-size: 14px; }

        .apply-btn {
            width: 100%; padding: 12px; background: white; border: 1px solid #FF9B44; color: #FF9B44;
            border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s;
        }
        .apply-btn:hover { background: #FF9B44; color: white; }

        /* 9. Leave & Tasks (Bottom Row) */
        .pie-placeholder {
            width: 100px; height: 100px; border-radius: 50%;
            background: conic-gradient(#FF9B44 0% 25%, #10b981 25% 50%, #3b82f6 50% 100%);
            flex-shrink: 0;
        }
        .leave-legend { flex: 1; display: flex; flex-direction: column; gap: 10px; }
        .legend-item { display: flex; justify-content: space-between; align-items: center; font-size: 13px; }
        .dot { width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        
        .task-list { flex: 1; overflow-y: auto; display: flex; flex-direction: column; gap: 10px; max-height: 250px; }
        .task-item {
            padding: 12px; background: #f9fafb; border: 1px solid #f0f0f0; border-left: 4px solid #FF9B44;
            border-radius: 6px; display: flex; justify-content: space-between; align-items: center;
        }
        .task-title { font-size: 13px; font-weight: 700; color: #333; margin: 0 0 2px 0; }
        .task-time { font-size: 11px; color: #888; }
        
        .task-btn {
            padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; border: none; cursor: pointer;
        }
        .btn-done { background: #dcfce7; color: #166534; cursor: default; }
        .btn-mark { background: #FF9B44; color: white; }

        /* 10. Announcements & Support */
        .anno-box {
            padding: 12px; border-radius: 6px; font-size: 13px; margin-bottom: 10px;
            border-left: 4px solid;
        }
        .anno-orange { background: #fff7ed; border-color: #f97316; color: #333; }
        .anno-blue { background: #eff6ff; border-color: #3b82f6; color: #333; }
        .anno-highlight { display: block; font-weight: 700; margin-bottom: 2px; }
        .anno-orange .anno-highlight { color: #c2410c; } .anno-blue .anno-highlight { color: #1d4ed8; }

        .support-btn {
            width: 100%; padding: 14px; background: #1f2937; color: white; border: none;
            border-radius: 8px; font-weight: 700; cursor: pointer; display: flex;
            align-items: center; justify-content: center; gap: 8px; margin-top: auto;
        }
        .support-btn:hover { background: #374151; }

        /* 11. Modal Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 1000;
        }
        .modal-box {
            background: white; padding: 30px; border-radius: 12px; width: 400px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative;
        }
        .modal-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #666; margin-bottom: 6px; }
        .modal-input {
            width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 6px;
            font-size: 14px; outline: none; background: white; box-sizing: border-box; margin-bottom: 20px;
        }
        .modal-input:focus { border-color: #FF9B44; }
        .modal-actions { display: flex; gap: 10px; }
        .btn-cancel { flex: 1; padding: 12px; background: #f3f4f6; color: #666; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; }
        .btn-submit { flex: 1; padding: 12px; background: #FF9B44; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; }
    </style>
</head>
<body>

<div class="ed-container">
    <input type="file" id="fileInput" style="display: none;" onchange="onTaskFileSelect(event)">

    <div class="ed-header">
        <div>
            <h1 class="ed-title">Employee Dashboard</h1>
            <div class="ed-breadcrumb">Overview & Daily Tasks</div>
        </div>
        <button class="export-btn">
            <i data-lucide="download"></i> Export
        </button>
    </div>

    <div id="notifBanner" class="notif-banner">
        <div class="notif-text">
            <i data-lucide="alert-circle"></i>
            Your Leave Request for Jan 24th has been Approved!
        </div>
        <button onclick="document.getElementById('notifBanner').style.display='none'" class="close-notif">
            <i data-lucide="x"></i>
        </button>
    </div>

    <div class="grid-3">
        <div class="ed-card">
            <div class="profile-row">
                <div class="avatar-circle"><?php echo $empDetails['initials']; ?></div>
                <div>
                    <h2 class="prof-name"><?php echo $empDetails['name']; ?></h2>
                    <span class="prof-role"><?php echo $empDetails['role']; ?></span>
                    <div class="prof-detail"><i data-lucide="mail" style="width:12px"></i> <?php echo $empDetails['email']; ?></div>
                    <div class="prof-detail"><i data-lucide="phone" style="width:12px"></i> <?php echo $empDetails['phone']; ?></div>
                    <div class="prof-detail"><i data-lucide="calendar" style="width:12px"></i> Joined: <?php echo $empDetails['joined']; ?></div>
                </div>
            </div>
            <div class="docs-section">
                <p class="docs-title"><i data-lucide="file-text" style="width:12px"></i> My Documents</p>
                <div onclick="triggerPopup('Action: Accessing Aadhaar_Card.pdf')" class="doc-item">
                    <span>Aadhaar_Card.pdf</span><i data-lucide="download-cloud" style="width:14px"></i>
                </div>
                <div onclick="triggerPopup('Action: Accessing PAN_Card.pdf')" class="doc-item">
                    <span>PAN_Card.pdf</span><i data-lucide="download-cloud" style="width:14px"></i>
                </div>
                <div onclick="triggerPopup('Action: Accessing Offer_Letter.pdf')" class="doc-item">
                    <span>Offer_Letter.pdf</span><i data-lucide="download-cloud" style="width:14px"></i>
                </div>
            </div>
        </div>

        <div class="ed-card">
            <h3 class="card-header">Attendance Status</h3>
            <div class="att-status-box">
                 <div id="attIcon" class="att-circle att-inactive">
                   <i data-lucide="clock" style="width:32px; height:32px"></i>
                 </div>
                 <p style="fontSize:13px; color:#999; margin:0 0 5px 0">Current Status</p>
                 <h4 id="statusTxt" class="status-txt status-inactive">Checked Out</h4>
            </div>
            <button id="punchBtn" onclick="toggleCheckIn()" class="punch-btn btn-in">
                 <i data-lucide="log-in" style="width:18px"></i> Punch In
            </button>
        </div>

        <div class="ed-card">
            <h3 class="card-header">Statistics</h3>
            <div class="stat-list">
                <div class="stat-row row-green">
                    <span class="row-label"><i data-lucide="check-circle" style="width:16px"></i> On Time</span>
                    <span class="row-val"><?php echo $leaveStats['onTime']; ?></span>
                </div>
                <div class="stat-row row-blue">
                    <span class="row-label"><i data-lucide="briefcase" style="width:16px"></i> Work From Home</span>
                    <span class="row-val"><?php echo $leaveStats['wfh']; ?></span>
                </div>
                <div class="stat-row row-orange">
                    <span class="row-label"><i data-lucide="clock" style="width:16px"></i> Late Arrivals</span>
                    <span class="row-val"><?php echo $leaveStats['late']; ?></span>
                </div>
                <div class="stat-row row-red">
                    <span class="row-label"><i data-lucide="alert-circle" style="width:16px"></i> Absences</span>
                    <span class="row-val"><?php echo $leaveStats['absent']; ?></span>
                </div>
            </div>
            <button onclick="openModal('leaveModal')" class="apply-btn">Apply New Leave</button>
        </div>
    </div>

    <div class="grid-3">
        <div class="ed-card">
            <h3 class="card-header">Leave Distribution</h3>
            <div style="display:flex; gap:25px; align-items:center">
                <div class="pie-placeholder"></div>
                <div class="leave-legend">
                    <div class="legend-item">
                        <span style="color:#666"><span class="dot" style="background:#FF9B44"></span>Casual</span>
                        <span style="font-weight:bold; color:#333">4</span>
                    </div>
                    <div class="legend-item">
                        <span style="color:#666"><span class="dot" style="background:#10b981"></span>Sick</span>
                        <span style="font-weight:bold; color:#333">3</span>
                    </div>
                    <div class="legend-item">
                        <span style="color:#666"><span class="dot" style="background:#3b82f6"></span>Earned</span>
                        <span style="font-weight:bold; color:#333">3</span>
                    </div>
                    <div style="border-top:1px solid #eee; padding-top:10px; font-size:13px; font-weight:700; color:#FF9B44">
                        Total Taken: 10
                    </div>
                </div>
            </div>
        </div>

        <div class="ed-card">
            <h3 class="card-header">My Tasks</h3>
            <div class="task-list" id="taskList">
                </div>
        </div>

        <div class="ed-card">
            <h3 class="card-header">Announcements</h3>
            <div style="margin-bottom:20px">
                <div class="anno-box anno-orange">
                    <span class="anno-highlight">Town Hall</span> Q1 growth update on Jan 30.
                </div>
                <div class="anno-box anno-blue">
                    <span class="anno-highlight">System</span> Maintenance tonight at 11 PM.
                </div>
            </div>
            <button onclick="openSupportModal('Tech Ticket')" class="support-btn">
                <i data-lucide="message-square" style="width:18px"></i> Raise Tech Ticket
            </button>
        </div>
    </div>

    <div id="msgModal" class="modal-overlay">
        <div class="modal-box" style="text-align:center">
            <i data-lucide="info" style="width:40px; height:40px; color:#FF9B44; margin-bottom:15px"></i>
            <h2 class="card-header" style="justify-content:center">System Message</h2>
            <p id="msgModalText" style="color:#666; margin-bottom:25px"></p>
            <button onclick="closeModal('msgModal')" class="btn-submit" style="width:100%">Okay</button>
        </div>
    </div>

    <div id="supportModal" class="modal-overlay">
        <div class="modal-box">
            <h2 class="card-header"><i data-lucide="message-square" style="color:#FF9B44"></i> <span id="supportTitle"></span></h2>
            <div id="issueTypeGroup" style="margin-bottom:15px">
                <label class="modal-label">Issue Type</label>
                <select id="supportReason" class="modal-input">
                    <option value="" disabled selected>-- Select a Reason --</option>
                    <option value="Hardware Issue">Hardware Issue</option>
                    <option value="Software Access">Software / Access</option>
                    <option value="Network Connectivity">Network / Internet</option>
                    <option value="Others">Others</option>
                </select>
            </div>
            <label class="modal-label">Details</label>
            <textarea class="modal-input" style="height:100px; resize:none" placeholder="Describe your issue..."></textarea>
            <div class="modal-actions">
                <button onclick="closeModal('supportModal')" class="btn-cancel">Cancel</button>
                <button onclick="submitSupport()" class="btn-submit">Submit</button>
            </div>
        </div>
    </div>

    <div id="leaveModal" class="modal-overlay">
        <div class="modal-box">
            <h2 class="card-header">Apply New Leave</h2>
            <label class="modal-label">Reason</label>
            <textarea class="modal-input" style="height:100px; resize:none" placeholder="Specify reason for leave..."></textarea>
            <div class="modal-actions">
                <button onclick="closeModal('leaveModal')" class="btn-cancel">Cancel</button>
                <button onclick="submitLeave()" class="btn-submit">Submit</button>
            </div>
        </div>
    </div>

</div>

<script>
    // --- STATE SIMULATION ---
    let isCheckedIn = false;
    let activeTaskId = null;
    let tasks = <?php echo json_encode($initialTasks); ?>;

    // --- INITIALIZE ICONS ---
    lucide.createIcons();

    // --- HANDLERS ---
    function renderTasks() {
        const list = document.getElementById('taskList');
        list.innerHTML = tasks.map(t => `
            <div class="task-item">
                <div>
                    <p class="task-title">${t.title}</p>
                    <span class="task-time">${t.time} mins est.</span>
                </div>
                <button 
                    onclick="handleTaskAction(${t.id}, '${t.status}')" 
                    class="task-btn ${t.status === 'Completed' ? 'btn-done' : 'btn-mark'}"
                >
                    ${t.status === 'Completed' ? 'Done ✓' : 'Mark Done'}
                </button>
            </div>
        `).join('');
    }

    function handleTaskAction(id, status) {
        if (status === 'Completed') return;
        activeTaskId = id;
        document.getElementById('fileInput').click();
    }

    function onTaskFileSelect(e) {
        const file = e.target.files[0];
        if (file && activeTaskId) {
            tasks = tasks.map(t => t.id === activeTaskId ? { ...t, status: 'Completed' } : t);
            renderTasks();
            triggerPopup(`✅ File Attached: ${file.name}`);
            activeTaskId = null;
        }
    }

    function toggleCheckIn() {
        isCheckedIn = !isCheckedIn;
        const btn = document.getElementById('punchBtn');
        const icon = document.getElementById('attIcon');
        const txt = document.getElementById('statusTxt');

        if (isCheckedIn) {
            btn.className = "punch-btn btn-out";
            btn.innerHTML = '<i data-lucide="log-out"></i> Punch Out';
            icon.className = "att-circle att-active";
            txt.className = "status-txt status-active";
            txt.innerText = "Checked In";
        } else {
            btn.className = "punch-btn btn-in";
            btn.innerHTML = '<i data-lucide="log-in"></i> Punch In';
            icon.className = "att-circle att-inactive";
            txt.className = "status-txt status-inactive";
            txt.innerText = "Checked Out";
        }
        lucide.createIcons();
    }

    function triggerPopup(text) {
        document.getElementById('msgModalText').innerText = text;
        openModal('msgModal');
    }

    function openSupportModal(type) {
        document.getElementById('supportTitle').innerText = type;
        document.getElementById('supportReason').value = "";
        openModal('supportModal');
    }

    function submitSupport() {
        closeModal('supportModal');
        triggerPopup("Your request has been submitted successfully!");
    }

    function submitLeave() {
        closeModal('leaveModal');
        triggerPopup("Leave request sent for approval.");
    }

    function openModal(id) { document.getElementById(id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById(id).style.display = 'none'; }

    // Initial load
    renderTasks();
</script>

</body>
</html>