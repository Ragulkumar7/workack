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

// --- 4. DYNAMIC DATA FETCHING ---
$employeesUnderTL = [];

if (isset($conn) && $conn) {
    // Fetch all attendance records from the database table
    $sql = "SELECT * FROM team_attendance";
    $result = mysqli_query($conn, $sql);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
            $employeesUnderTL[] = $row;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Attendance Portal</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* --- GLOBAL LAYOUT STYLES --- */
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 30px; }

        /* --- DASHBOARD HEADER --- */
        .tl-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 12px; border: 1px solid #e1e1e1; margin-bottom: 30px; }
        
        .tl-card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e1e1e1; display: flex; flex-direction: column; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .card-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; margin-bottom: 15px; font-weight: 700; font-size: 18px; color: #333; }

        /* --- TABLE STYLE FOR ATTENDANCE LOG --- */
        .immersive-table { width: 100%; border-collapse: separate; border-spacing: 0 15px; }
        .immersive-row { background: white; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.02); transition: transform 0.2s; }
        .immersive-row:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .immersive-cell { padding: 15px 20px; vertical-align: middle; border-top: 1px solid #f0f0f0; border-bottom: 1px solid #f0f0f0; font-size: 14px; }
        .immersive-row td:first-child { border-top-left-radius: 12px; border-bottom-left-radius: 12px; border-left: 1px solid #f0f0f0; }
        .immersive-row td:last-child { border-top-right-radius: 12px; border-bottom-right-radius: 12px; border-right: 1px solid #f0f0f0; }

        /* --- MODAL STYLES --- */
        .modal-overlay { 
            position: fixed; top: 0; left: 0; width: 100%; height: 100%; 
            background: rgba(0,0,0,0.5); display: none; align-items: center; justify-content: center; z-index: 9999; 
        }
        .modal-overlay.active { display: flex; }
        .modal-box { 
            background: white; padding: 30px; border-radius: 16px; width: 400px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.2); position: relative;
        }
        .modal-details-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 20px; text-align: center; }
        .detail-label { font-size: 11px; color: #999; text-transform: uppercase; font-weight: 700; margin-bottom: 5px; }
        .detail-value { font-size: 14px; font-weight: 700; color: #333; }
        .prod-val { color: #FF9B44; }
        .close-btn { position: absolute; top: 20px; right: 20px; cursor: pointer; color: #999; }
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
                        <h1 style="font-size: 28px; font-weight: 800; margin: 0; color: #1a1a1a;">Team Attendance Portal</h1>
                        <p style="font-size: 14px; color: #666; margin: 0;">Monitoring attendance for <span style="color:#FF9B44; font-weight:bold;"><?= htmlspecialchars($tlProfile['name']) ?>'s</span> team</p>
                    </div>
                    <div style="width: 50px; height: 50px; background: #fff7ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FF9B44; border: 1px solid #ffedd5;">
                        <i data-lucide="calendar-check"></i>
                    </div>
                </div>

                <div class="tl-card" style="margin-top: 10px;">
                    <div class="card-header">
                        <div style="display:flex; align-items:center; gap:10px;"><i data-lucide="list" color="#FF9B44" size="20"></i> Team Attendance Log</div>
                    </div>
                    <table class="immersive-table">
                        <thead>
                            <tr style="text-align:left; font-size:12px; color:#9ca3af; text-transform:uppercase; letter-spacing: 0.5px;">
                                <th style="padding-left:20px;">Employee</th>
                                <th>Status</th>
                                <th>Work Type</th>
                                <th>Shift</th>
                                <th style="text-align:right; padding-right:20px;">Detail</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($employeesUnderTL as $emp): ?>
                                <tr class="immersive-row">
                                    <td class="immersive-cell" style="padding-left:20px;">
                                        <div style="display:flex; align-items:center; gap:12px;">
                                            <img src="<?= htmlspecialchars($emp['avatar']) ?>" style="width:35px; height:35px; border-radius:50%; object-fit: cover; border: 1px solid #eee;">
                                            <span style="font-weight:700; font-size:14px; color: #333;"><?= htmlspecialchars($emp['employee_name']) ?></span>
                                        </div>
                                    </td>
                                    <td class="immersive-cell">
                                        <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;"><?= htmlspecialchars($emp['status']) ?></span>
                                    </td>
                                    <td class="immersive-cell" style="font-weight: 600; color: #555;"><?= htmlspecialchars($emp['work_type']) ?></td>
                                    <td class="immersive-cell" style="color: #888;"><?= htmlspecialchars($emp['shift']) ?></td>
                                    <td class="immersive-cell" style="text-align:right; padding-right:20px;">
                                        <i data-lucide="chevron-right" 
                                           style="cursor:pointer; color:#FF9B44;" 
                                           onclick="openDetails('<?= addslashes($emp['employee_name']) ?>', '<?= $emp['clock_in'] ?>', '<?= $emp['clock_out'] ?>', '<?= $emp['production'] ?>')"></i>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>

    <div id="detailsModal" class="modal-overlay">
        <div class="modal-box">
            <i data-lucide="x" class="close-btn" onclick="closeDetails()"></i>
            <h3 style="margin: 0; font-size: 18px;" id="modalEmpName">Employee Details</h3>
            <p style="font-size: 12px; color: #999; margin: 5px 0 20px 0;">Attendance metrics for today</p>
            
            <div class="modal-details-grid">
                <div>
                    <div class="detail-label">Clock In</div>
                    <div class="detail-value" id="modalClockIn">-</div>
                </div>
                <div>
                    <div class="detail-label">Clock Out</div>
                    <div class="detail-value" id="modalClockOut">-</div>
                </div>
                <div>
                    <div class="detail-label">Production</div>
                    <div class="detail-value prod-val" id="modalProd">-</div>
                </div>
            </div>
            
            <button style="width: 100%; background: #FF9B44; color: white; border: none; border-radius: 8px; padding: 12px; margin-top: 30px; font-weight: 700; cursor: pointer;" onclick="closeDetails()">
                Close
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openDetails(name, clockIn, clockOut, prod) {
            document.getElementById('modalEmpName').innerText = name;
            document.getElementById('modalClockIn').innerText = clockIn;
            document.getElementById('modalClockOut').innerText = clockOut;
            document.getElementById('modalProd').innerText = prod;
            document.getElementById('detailsModal').classList.add('active');
        }

        function closeDetails() {
            document.getElementById('detailsModal').classList.remove('active');
        }

        window.onclick = function(event) {
            let modal = document.getElementById('detailsModal');
            if (event.target == modal) {
                closeDetails();
            }
        }
    </script>
</body>
</html>