<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<?php
/**
 * ATTENDANCE COMPONENT - MANAGER VIEW
 * ---------------------------------------------------
 * This script handles the display and management of 
 * daily employee attendance.
 */

// --- USER ROLE CHECK ---
// Note: In a production environment, $user would be pulled from session/context
$user = ['role' => 'Manager']; 

// If the user is an employee, handle accordingly (e.g., redirect or include employee view)
if ($user['role'] === 'Employee') {
    echo "<div class='att-container'><h3>Access Restricted: Redirecting to Employee Portal...</h3></div>";
    // header('Location: employee_attendance.php'); 
    exit;
}

// --- DATE LOGIC ---
$date_param = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$currentDate = new DateTime($date_param);

// --- MOCK DATA ---
$attendance = [
    ['id' => 'EMP001', 'name' => 'Varshith', 'role' => 'Sr. Developer', 'status' => 'Present', 'checkIn' => '09:30', 'checkOut' => '18:30', 'leaveType' => ''],
    ['id' => 'EMP002', 'name' => 'Aditi Rao', 'role' => 'UI/UX Designer', 'status' => 'Leave', 'checkIn' => '--:--', 'checkOut' => '--:--', 'leaveType' => 'Casual'],
    ['id' => 'EMP003', 'name' => 'Sanjay Kumar', 'role' => 'DevOps Engineer', 'status' => 'Absent', 'checkIn' => '--:--', 'checkOut' => '--:--', 'leaveType' => ''],
];

// --- CALCULATE STATS ---
$presentCount = 0; $leaveCount = 0; $absentCount = 0;
foreach ($attendance as $emp) {
    if ($emp['status'] === 'Present') $presentCount++;
    elseif ($emp['status'] === 'Leave') $leaveCount++;
    else $absentCount++;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Attendance Management</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* 1. LAYOUT & CONTAINER */
        .att-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
        }

        /* 2. HEADER & NAVIGATION */
        .att-header-row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        .att-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .att-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }
        
        .date-nav { 
            display: flex; align-items: center; background: white; padding: 10px 20px; 
            border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); 
            border: 1px solid #e1e1e1; gap: 15px; 
        }
        .nav-btn { background: none; border: none; cursor: pointer; color: #999; padding: 5px; display: flex; align-items: center; text-decoration: none; transition: color 0.2s; }
        .nav-btn:hover { color: #FF9B44; }
        .date-display { text-align: center; min-width: 140px; }
        .date-main { display: block; font-weight: 700; color: #333; font-size: 14px; }
        .date-sub { display: block; font-size: 12px; color: #999; }

        /* 3. STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { 
            background: white; padding: 20px; border-radius: 12px; border: 1px solid #e1e1e1; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.02); display: flex; align-items: center; justify-content: space-between; 
        }
        .stat-val { font-size: 24px; font-weight: 800; color: #333; margin-bottom: 4px; }
        .stat-label { font-size: 12px; font-weight: 600; text-transform: uppercase; color: #888; }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .bg-green { background: #dcfce7; color: #166534; }
        .bg-orange { background: #ffedd5; color: #c2410c; }
        .bg-red { background: #fee2e2; color: #991b1b; }

        /* 4. TABLE STYLING */
        .table-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1; overflow: hidden; }
        .table-header { padding: 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .save-btn { 
            background: #FF9B44; color: white; border: none; padding: 10px 20px; 
            border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; 
            display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.2); 
        }
        .save-btn:hover { background: #e88b3a; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th { text-align: left; padding: 15px 20px; background: #f9fafb; color: #666; font-size: 12px; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid #e5e7eb; }
        td { padding: 15px 20px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: #333; font-size: 14px; }
        tr:hover td { background-color: #fcfcfc; }

        /* 5. INPUTS & TOGGLES */
        .status-toggle { display: inline-flex; background: #f3f4f6; border-radius: 6px; padding: 3px; gap: 2px; }
        .toggle-btn { border: none; background: transparent; padding: 4px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; cursor: pointer; color: #999; transition: all 0.2s; }
        .active-present { background: white !important; color: #16a34a !important; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .active-leave { background: white !important; color: #ea580c !important; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        .active-absent { background: white !important; color: #dc2626 !important; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        
        .time-input { border: 1px solid #ddd; border-radius: 6px; padding: 6px 10px; font-size: 13px; width: 90px; text-align: center; outline: none; }
        .time-input:focus { border-color: #FF9B44; }
        .time-input:disabled { background: #f9fafb; color: #ccc; border-color: #f3f4f6; }
        .leave-select { width: 100%; padding: 6px; border-radius: 6px; border: 1px solid #fed7aa; background: #fff7ed; color: #c2410c; font-size: 13px; outline: none; }
    </style>
</head>
<body>

<div class="att-container">
    
    <div class="att-header-row">
        <div>
            <h2 class="att-title">Daily Attendance</h2>
            <div class="att-breadcrumb">
                Dashboard / <span style="color: #FF9B44; font-weight: bold;">Attendance</span>
            </div>
        </div>
        
        <div class="date-nav">
            <?php 
                $prev = clone $currentDate; $prev->modify('-1 day'); 
                $next = clone $currentDate; $next->modify('+1 day'); 
            ?>
            <a href="?date=<?php echo $prev->format('Y-m-d'); ?>" class="nav-btn">
                <i data-lucide="chevron-left"></i>
            </a>
            <div class="date-display">
                <span class="date-main"><?php echo $currentDate->format('d F Y'); ?></span>
                <span class="date-sub"><?php echo $currentDate->format('l'); ?></span>
            </div>
            <a href="?date=<?php echo $next->format('Y-m-d'); ?>" class="nav-btn">
                <i data-lucide="chevron-right"></i>
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div>
                <div class="stat-val"><?php echo $presentCount; ?></div>
                <span class="stat-label">Present Today</span>
            </div>
            <div class="stat-icon bg-green"><i data-lucide="calendar-check"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-val"><?php echo $leaveCount; ?></div>
                <span class="stat-label">On Leave</span>
            </div>
            <div class="stat-icon bg-orange"><i data-lucide="user-x"></i></div>
        </div>
        <div class="stat-card">
            <div>
                <div class="stat-val"><?php echo $absentCount; ?></div>
                <span class="stat-label">Absent</span>
            </div>
            <div class="stat-icon bg-red"><i data-lucide="clock"></i></div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h4 style="margin:0; display:flex; align-items:center; gap:10px; font-size:18px">
                <i data-lucide="clock" style="color:#FF9B44"></i> Employee Log
            </h4>
            <button class="save-btn" onclick="alert('Attendance saved to database!')">
                <i data-lucide="save"></i> Save Changes
            </button>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Status</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                        <th>Leave Type</th>
                        <th>Location</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attendance as $emp): ?>
                    <tr id="row-<?php echo $emp['id']; ?>">
                        <td>
                            <div style="font-weight:700; color:#333"><?php echo htmlspecialchars($emp['name']); ?></div>
                            <div style="font-size:12px; color:#888; marginTop:2px"><?php echo htmlspecialchars($emp['role']); ?></div>
                        </td>
                        
                        <td>
                            <div class="status-toggle">
                                <button onclick="setStatus('<?php echo $emp['id']; ?>', 'Present')" 
                                        id="btn-p-<?php echo $emp['id']; ?>" 
                                        class="toggle-btn <?php echo $emp['status'] === 'Present' ? 'active-present' : ''; ?>">P</button>
                                <button onclick="setStatus('<?php echo $emp['id']; ?>', 'Leave')" 
                                        id="btn-l-<?php echo $emp['id']; ?>" 
                                        class="toggle-btn <?php echo $emp['status'] === 'Leave' ? 'active-leave' : ''; ?>">L</button>
                                <button onclick="setStatus('<?php echo $emp['id']; ?>', 'Absent')" 
                                        id="btn-a-<?php echo $emp['id']; ?>" 
                                        class="toggle-btn <?php echo $emp['status'] === 'Absent' ? 'active-absent' : ''; ?>">A</button>
                            </div>
                        </td>

                        <td>
                            <input type="time" id="in-<?php echo $emp['id']; ?>" 
                                   value="<?php echo ($emp['status'] === 'Present') ? $emp['checkIn'] : '00:00'; ?>" 
                                   class="time-input" <?php echo $emp['status'] !== 'Present' ? 'disabled' : ''; ?>>
                        </td>
                        <td>
                            <input type="time" id="out-<?php echo $emp['id']; ?>" 
                                   value="<?php echo ($emp['status'] === 'Present') ? $emp['checkOut'] : '00:00'; ?>" 
                                   class="time-input" <?php echo $emp['status'] !== 'Present' ? 'disabled' : ''; ?>>
                        </td>

                        <td>
                            <div id="leave-box-<?php echo $emp['id']; ?>">
                                <?php if($emp['status'] === 'Leave'): ?>
                                    <select class="leave-select">
                                        <option <?php if($emp['leaveType']=='Casual') echo 'selected'; ?>>Casual</option>
                                        <option <?php if($emp['leaveType']=='Medical') echo 'selected'; ?>>Medical</option>
                                        <option <?php if($emp['leaveType']=='Emergency') echo 'selected'; ?>>Emergency</option>
                                        <option <?php if($emp['leaveType']=='LOP') echo 'selected'; ?>>Loss of Pay</option>
                                    </select>
                                <?php else: echo '<span style="color:#ccc">--</span>'; endif; ?>
                            </div>
                        </td>

                        <td>
                            <div style="display:flex; align-items:center; gap:5px; font-size:12px; color:#666; font-weight:500">
                                <i data-lucide="map-pin" style="width:14px; color:#999;"></i> Office
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    /**
     * JavaScript Frontend Logic 
     * Handles instant UI updates when status toggles change
     */
    function setStatus(id, status) {
        // Reset classes
        document.getElementById('btn-p-'+id).className = 'toggle-btn';
        document.getElementById('btn-l-'+id).className = 'toggle-btn';
        document.getElementById('btn-a-'+id).className = 'toggle-btn';

        const inInput = document.getElementById('in-'+id);
        const outInput = document.getElementById('out-'+id);
        const leaveBox = document.getElementById('leave-box-'+id);

        if (status === 'Present') {
            document.getElementById('btn-p-'+id).classList.add('active-present');
            inInput.disabled = false; outInput.disabled = false;
            inInput.value = '09:00'; outInput.value = '18:00';
            leaveBox.innerHTML = '<span style="color:#ccc">--</span>';
        } else if (status === 'Leave') {
            document.getElementById('btn-l-'+id).classList.add('active-leave');
            inInput.disabled = true; outInput.disabled = true;
            inInput.value = '00:00'; outInput.value = '00:00';
            leaveBox.innerHTML = `
                <select class="leave-select">
                    <option>Casual</option><option>Medical</option><option>Emergency</option><option>LOP</option>
                </select>`;
        } else {
            document.getElementById('btn-a-'+id).classList.add('active-absent');
            inInput.disabled = true; outInput.disabled = true;
            inInput.value = '00:00'; outInput.value = '00:00';
            leaveBox.innerHTML = '<span style="color:#ccc">--</span>';
        }
    }
</script>
</body>
</html>