<?php
// --- 1. TARGETED DATABASE CONNECTION ---
$db_path = '../login/db_connect.php';

if (file_exists($db_path)) {
    include_once($db_path);
} else {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'>
            <strong>Critical Error:</strong> Cannot find db_connect.php at: $db_path <br>
            Current Folder: " . __DIR__ . "
         </div>");
}

// --- 2. GLOBAL USER DATA ---
$user = [
    'name' => 'TL Manager',
    'role' => 'Team Lead', 
    'avatar_initial' => 'T'
];

// --- 3. DASHBOARD SPECIFIC DATA ---
$tlProfile = [
    'name' => 'TL Manager',
    'role' => 'Team Lead - Engineering',
    'email' => 'tl.manager@company.com'
];

// --- 4. DATA FETCHING LOGIC ---
$teamProgress = [];
$teamReviews = [];
$filedCharges = []; // Added for new section

if (isset($conn) && $conn) {
    // Handle File Charge Submission
    if (isset($_POST['file_charge'])) {
        $empName = mysqli_real_escape_string($conn, $_POST['employee_name']);
        $issueType = mysqli_real_escape_string($conn, $_POST['issue_type']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        
        // Assuming a table 'disciplinary_actions' exists or creating a local log
        $sqlCharge = "INSERT INTO disciplinary_actions (employee_name, issue_type, description, filed_by, status) 
                      VALUES ('$empName', '$issueType', '$description', '{$user['name']}', 'Pending Manager Review')";
        mysqli_query($conn, $sqlCharge);
    }

    // Fetch Team Progress
    $sqlProgress = "SELECT * FROM team_progress";
    $resProgress = mysqli_query($conn, $sqlProgress);
    if ($resProgress && mysqli_num_rows($resProgress) > 0) {
        while($row = mysqli_fetch_assoc($resProgress)) {
            $teamProgress[] = $row;
        }
    }

    // Fetch Team Reviews
    $sqlReviews = "SELECT * FROM team_reviews ORDER BY created_at DESC";
    $resReviews = mysqli_query($conn, $sqlReviews);
    if ($resReviews && mysqli_num_rows($resReviews) > 0) {
        while($row = mysqli_fetch_assoc($resReviews)) {
            $teamReviews[] = $row;
        }
    }
}

function getStatusStyles($status) {
    switch($status) {
        case "Completed": return ['bg' => "#dcfce7", 'text' => "#166534", 'border' => "#bbf7d0"];
        case "Delayed": return ['bg' => "#fee2e2", 'text' => "#991b1b", 'border' => "#fecaca"];
        default: return ['bg' => "#ffedd5", 'text' => "#9a3412", 'border' => "#fed7aa"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Lead Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 30px; }
        .tl-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 12px; border: 1px solid #e1e1e1; margin-bottom: 30px; }
        .tl-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e1e1e1; display: flex; flex-direction: column; min-height: 200px; margin-bottom: 20px; }
        .card-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; margin-bottom: 15px; font-weight: 700; }
        .immersive-table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        .immersive-row { background: #f9fafb; border-radius: 12px; transition: transform 0.2s; }
        .immersive-row:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .immersive-cell { padding: 15px 20px; vertical-align: middle; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; }
        .immersive-row td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 1px solid #f0f0f0; }
        .immersive-row td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-right: 1px solid #f0f0f0; }
        .progress-track { background: #eee; border-radius: 10px; height: 8px; width: 100px; overflow: hidden; }
        .progress-fill { height: 100%; background: linear-gradient(90deg, #FF9B44, #F59E0B); }
        .status-pill { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; display: flex; align-items: center; gap: 5px; width: fit-content; }
        .action-container { position: relative; display: inline-block; }
        .action-btn { background: none; border: none; cursor: pointer; color: #999; padding: 5px; border-radius: 50%; }
        .action-dropdown { display: none; position: absolute; right: 0; top: 100%; background: white; min-width: 160px; box-shadow: 0 8px 24px rgba(0,0,0,0.12); border-radius: 8px; z-index: 100; border: 1px solid #f0f0f0; padding: 8px 0; }
        .action-dropdown.active { display: block; }
        .action-item { display: flex; align-items: center; gap: 10px; padding: 10px 16px; font-size: 13px; color: #555; text-decoration: none; cursor: pointer; }
        .action-item:hover { background: #f9fafb; color: #FF9B44; }
        .modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 2000; }
        .modal-overlay.open { display: flex; }
        .modal-box { background: white; padding: 30px; border-radius: 12px; width: 500px; box-shadow: 0 20px 50px rgba(0,0,0,0.2); position: relative; }
        .review-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .review-table th, .review-table td { border: 1px solid #eee; padding: 10px; text-align: left; font-size: 13px; }
        .review-table th { background: #f9f9f9; }
        .tl-input, .tl-textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; margin-top: 5px; box-sizing: border-box; }
        .tl-select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; margin-top: 5px; background: white; }
        .badge { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; border: 1px solid; display: inline-block; }
        .badge-green { background: #f0fdf4; color: #16a34a; border-color: #bbf7d0; }
        .badge-orange { background: #fff7ed; color: #c2410c; border-color: #ffedd5; }
        .badge-red { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
        .tl-btn { padding: 10px 20px; border-radius: 6px; font-weight: 700; cursor: pointer; border: none; background: #FF9B44; color: white; }
        .btn-danger { background: #dc2626; }
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
                        <h1 style="font-size: 24px; font-weight: 800; margin: 0;">Team Lead Portal</h1>
                        <p style="font-size: 14px; color: #666; margin: 0;">Welcome back, <span style="color:#FF9B44; font-weight:bold;"><?= htmlspecialchars($tlProfile['name']) ?></span></p>
                    </div>
                    <div style="width: 50px; height: 50px; background: #fff7ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FF9B44; border: 1px solid #ffedd5;">
                        <i data-lucide="users"></i>
                    </div>
                </div>

                <div class="tl-card">
                    <div class="card-header">
                        <div style="display:flex; align-items:center; gap:10px;"><i data-lucide="activity" color="#FF9B44" size="20"></i> Active Sprints & Tasks</div>
                    </div>
                    <table class="immersive-table">
                        <thead>
                            <tr style="text-align:left; font-size:11px; color:#999; text-transform:uppercase;">
                                <th style="padding-left:20px;">Employee</th>
                                <th>Assignment</th>
                                <th>Progress</th>
                                <th>Status</th>
                                <th style="text-align:right; padding-right:20px;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($teamProgress)): ?>
                                <?php foreach($teamProgress as $emp): 
                                    $styles = getStatusStyles($emp['status']);
                                ?>
                                    <tr class="immersive-row">
                                        <td class="immersive-cell" style="padding-left:20px;">
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <div style="width:35px; height:35px; border-radius:50%; background:#eee; display:flex; align-items:center; justify-content:center;"><i data-lucide="user" size="16"></i></div>
                                                <span style="font-weight:600; font-size:14px;"><?= htmlspecialchars($emp['name']) ?></span>
                                            </div>
                                        </td>
                                        <td class="immersive-cell"><span style="background:#f3f4f6; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600;"><i data-lucide="briefcase" size="14" style="vertical-align:middle; margin-right:5px; color:#FF9B44;"></i><?= htmlspecialchars($emp['task']) ?></span></td>
                                        <td class="immersive-cell">
                                            <div style="display:flex; align-items:center; gap:10px;">
                                                <div class="progress-track"><div class="progress-fill" style="width:<?= $emp['progress'] ?>%;"></div></div>
                                                <span style="font-size:12px; font-weight:700;"><?= $emp['progress'] ?>%</span>
                                            </div>
                                        </td>
                                        <td class="immersive-cell">
                                            <span class="status-pill" style="background:<?= $styles['bg'] ?>; color:<?= $styles['text'] ?>; border:1px solid <?= $styles['border'] ?>;">
                                                <i data-lucide="clock" size="12"></i> <?= htmlspecialchars($emp['status']) ?>
                                            </span>
                                        </td>
                                        <td class="immersive-cell" style="text-align:right; padding-right:20px;">
                                            <div class="action-container">
                                                <button class="action-btn" onclick="toggleDropdown(event, <?= $emp['id'] ?>)">
                                                    <i data-lucide="more-horizontal"></i>
                                                </button>
                                                <div class="action-dropdown" id="dropdown-<?= $emp['id'] ?>">
                                                    <a class="action-item" onclick="openActionModal('view', '<?= addslashes($emp['name']) ?>', '<?= addslashes($emp['task']) ?>')">
                                                        <i data-lucide="eye"></i> View Details
                                                    </a>
                                                    <a class="action-item" onclick="openActionModal('edit', '<?= addslashes($emp['name']) ?>', '<?= addslashes($emp['task']) ?>')">
                                                        <i data-lucide="edit-3"></i> Edit Task
                                                    </a>
                                                    <a class="action-item" onclick="openActionModal('review', '<?= addslashes($emp['name']) ?>', '<?= addslashes($emp['task']) ?>')">
                                                        <i data-lucide="file-search"></i> Review Progress
                                                    </a>
                                                    <a class="action-item" style="color: #dc2626;" onclick="openActionModal('charge', '<?= addslashes($emp['name']) ?>', '')">
                                                        <i data-lucide="alert-octagon"></i> File Charge
                                                    </a>
                                                    <div style="border-top: 1px solid #f0f0f0; margin: 4px 0;"></div>
                                                    <a class="action-item" style="color: #10B981;" onclick="handleSuccess('Task marked as completed!')">
                                                        <i data-lucide="check-circle"></i> Mark Completed
                                                    </a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align:center; padding:20px; color:#999;">No data found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="tl-card">
                    <div class="card-header">
                        <div style="display:flex; align-items:center; gap:10px;"><i data-lucide="shield-alert" color="#dc2626" size="20"></i> Disciplinary Action Log</div>
                    </div>
                    <div class="card-body">
                        <table class="review-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Issue Type</th>
                                    <th>Details</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="chargeLogBody">
                                <tr id="noChargePlaceholder"><td colspan="4" style="text-align:center; color:#999;">No charges filed against team members.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="tl-card">
                    <h3 class="card-header">Team Reviews Log</h3>
                    <div class="card-body">
                        <table class="review-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Rating</th>
                                    <th>Comments</th>
                                </tr>
                            </thead>
                            <tbody id="reviewLogBody">
                                <?php if (!empty($teamReviews)): ?>
                                    <?php foreach($teamReviews as $review): 
                                        $badge = (strtoupper($review['rating']) == 'EXCELLENT') ? 'badge-green' : 'badge-orange';
                                    ?>
                                        <tr class="immersive-row">
                                            <td style="font-weight:600; padding:10px;"><?= htmlspecialchars($review['employee_name']) ?></td>
                                            <td style="padding:10px;"><span class="badge <?= $badge ?>"><?= htmlspecialchars($review['rating']) ?></span></td>
                                            <td style="font-size:12px; color:#666; padding:10px;"><?= htmlspecialchars($review['comments']) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr id="noReviewPlaceholder"><td colspan="3" style="text-align:center; color:#999;">No reviews found in database.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div id="actionModal" class="modal-overlay">
        <div class="modal-box">
            <i data-lucide="x" style="position:absolute; top:20px; right:20px; cursor:pointer; color:#999;" onclick="closeModal('actionModal')"></i>
            <h3 id="modalTitle">Details</h3>
            <div id="modalContent"></div>
            <div style="margin-top:25px; text-align:right;" id="modalFooter">
                <button onclick="closeModal('actionModal')" class="tl-btn">Done</button>
            </div>
        </div>
    </div>

    <div id="modalSuccess" class="modal-overlay">
        <div class="modal-box" style="text-align:center;">
            <i data-lucide="check-circle" color="#16a34a" width="40" height="40" style="margin:0 auto 15px;"></i>
            <p id="successText" style="font-weight:700;"></p>
            <button onclick="closeModal('modalSuccess')" class="tl-btn" style="margin-top:15px;">Close</button>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let activeReviewee = "";

        function toggleDropdown(event, id) {
            event.stopPropagation();
            document.querySelectorAll('.action-dropdown').forEach(d => {
                if (d.id !== 'dropdown-' + id) d.classList.remove('active');
            });
            document.getElementById('dropdown-' + id).classList.toggle('active');
        }

        window.onclick = () => document.querySelectorAll('.action-dropdown').forEach(d => d.classList.remove('active'));

        function closeModal(id) { document.getElementById(id).classList.remove('open'); }

        function handleSuccess(msg) {
            document.getElementById('successText').innerText = msg;
            document.getElementById('modalSuccess').classList.add('open');
        }

        function openActionModal(type, name, task) {
            const title = document.getElementById('modalTitle');
            const content = document.getElementById('modalContent');
            const footer = document.getElementById('modalFooter');
            activeReviewee = name;
            
            if(type === 'view') {
                title.innerText = "Task Details: " + name;
                content.innerHTML = `<p style="font-size:14px; color:#555;">Employee is currently working on: <strong>${task}</strong></p>`;
                footer.innerHTML = `<button onclick="closeModal('actionModal')" class="tl-btn">Done</button>`;
            } else if(type === 'edit') {
                title.innerText = "Edit Task for " + name;
                content.innerHTML = `<label style="font-size:12px; font-weight:600;">Update Assignment</label><input class="tl-input" id="editTaskInput" value="${task}">`;
                footer.innerHTML = `<button onclick="saveEdit()" class="tl-btn">Update</button>`;
            } else if(type === 'review') {
                title.innerText = "Review Progress: " + name;
                content.innerHTML = `<table class="review-table"><thead><tr><th>Criteria</th><th>Good</th><th>Bad</th></tr></thead><tbody><tr><td>Work Quality</td><td><input type="radio" name="work_quality" value="Good" checked></td><td><input type="radio" name="work_quality" value="Bad"></td></tr><tr><td>Timely Delivery</td><td><input type="radio" name="timely" value="Good" checked></td><td><input type="radio" name="timely" value="Bad"></td></tr></tbody></table><label style="margin-top:15px; display:block; font-size:12px;">Lead Comments</label><textarea class="tl-textarea" id="reviewComments" placeholder="Add feedback..."></textarea>`;
                footer.innerHTML = `<button onclick="submitReview()" class="tl-btn">Done</button>`;
            } else if(type === 'charge') {
                // NEW MODAL: FILE CHARGE
                title.innerText = "File Charge Against: " + name;
                content.innerHTML = `
                    <label style="font-size:12px; font-weight:600;">Issue Category</label>
                    <select class="tl-select" id="issueType">
                        <option value="Not Working">Not Working / Idle</option>
                        <option value="Behavioral Issue">Behavioral Issue</option>
                        <option value="Attendance Gap">Attendance Gap</option>
                        <option value="Quality Failure">Severe Quality Failure</option>
                    </select>
                    <label style="margin-top:15px; display:block; font-size:12px; font-weight:600;">Detailed Reason for Charge</label>
                    <textarea class="tl-textarea" id="chargeDescription" placeholder="Explain the issue for the manager..."></textarea>
                `;
                footer.innerHTML = `<button onclick="submitCharge()" class="tl-btn btn-danger">File Charge to Manager</button>`;
            }
            document.getElementById('actionModal').classList.add('open');
        }

        function saveEdit() {
            closeModal('actionModal');
            handleSuccess("Task updated successfully!");
        }

        function submitCharge() {
            const issue = document.getElementById('issueType').value;
            const desc = document.getElementById('chargeDescription').value || "No details provided";
            const tableBody = document.getElementById('chargeLogBody');
            const placeholder = document.getElementById('noChargePlaceholder');
            
            if (placeholder) placeholder.remove();
            
            const newRow = `
                <tr class="immersive-row">
                    <td style="font-weight:600; padding:10px;">${activeReviewee}</td>
                    <td style="padding:10px;"><span class="badge badge-red">${issue}</span></td>
                    <td style="font-size:12px; color:#666; padding:10px;">${desc}</td>
                    <td style="padding:10px;"><span class="badge" style="background:#fefce8; color:#854d0e; border-color:#fef08a;">Sent to Manager</span></td>
                </tr>`;
            tableBody.innerHTML = newRow + tableBody.innerHTML;
            
            closeModal('actionModal');
            handleSuccess("Charge has been successfully filed with the Manager.");
        }

        function submitReview() {
            const quality = document.querySelector('input[name="work_quality"]:checked').value;
            const timely = document.querySelector('input[name="timely"]:checked').value;
            const comments = document.getElementById('reviewComments').value || "No comments";
            const overall = (quality === "Good" && timely === "Good") ? "EXCELLENT" : "NEEDS IMPROVEMENT";
            const badgeClass = (overall === "EXCELLENT") ? "badge-green" : "badge-orange";
            const tableBody = document.getElementById('reviewLogBody');
            const placeholder = document.getElementById('noReviewPlaceholder');
            
            if (placeholder) placeholder.remove();
            
            const newRow = `<tr class="immersive-row"><td style="font-weight:600; padding:10px;">${activeReviewee}</td><td style="padding:10px;"><span class="badge ${badgeClass}">${overall}</span></td><td style="font-size:12px; color:#666; padding:10px;">${comments}</td></tr>`;
            tableBody.innerHTML = newRow + tableBody.innerHTML;
            
            closeModal('actionModal');
            handleSuccess("Review stored in portal log!");
        }
    </script>
</body>
</html>