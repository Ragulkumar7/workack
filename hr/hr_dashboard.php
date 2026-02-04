<?php
ob_start(); // Fixes "Headers already sent" errors
session_start();

// --- FIXED LOGOUT LOGIC ---
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // 1. Clear the session
    session_unset();
    session_destroy();
    
    // 2. Redirect to the correct path
    // Go UP one level (..), then INTO the 'login' folder, then select 'login.php'
    header("Location: ../login/login.php"); 
    exit;
}
// ---------------------------

// --- 1. DATABASE CONNECTION ---
// We try multiple paths to ensure we find the connection file
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { 
    if (file_exists($path)) { 
        include $path; 
        break; 
    } 
}

// --- 2. DATA FETCHING ---
$total_emp = 0; $new_joinees = 0; $payroll_cost = 0;
$full_time = 0; $contract = 0; $probation = 0;

if($conn) {
    // Basic Counts
    $total_emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE status='Active'"))['c'] ?? 0;
    $new_joinees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE MONTH(joined_date) = MONTH(CURRENT_DATE()) AND YEAR(joined_date) = YEAR(CURRENT_DATE())"))['c'] ?? 0;
    
    // Payroll
    $payroll_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(salary) as s FROM employees WHERE status='Active'"));
    $payroll_cost = $payroll_query['s'] ?? 0;

    // Charts
    $full_time = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE employee_type='Full-Time'"))['c'] ?? 0;
    $contract = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE employee_type='Contract'"))['c'] ?? 0;
    $probation = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE employee_type='Probation'"))['c'] ?? 0;
}

// Attendance
$late_today = 0;
if($conn) {
    $late_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE status='Late' AND work_date = CURDATE()"))['c'] ?? 0;
}

// Leaves
$sick_count = 0; $casual_count = 0; $unpaid_count = 0;
$pending_leaves = []; 
if($conn) {
    $sick_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM leaves WHERE leave_type='Sick'"))['c'] ?? 0;
    $casual_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM leaves WHERE leave_type='Casual'"))['c'] ?? 0;
    $unpaid_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM leaves WHERE leave_type='Unpaid'"))['c'] ?? 0; 

    $res_leave = mysqli_query($conn, "SELECT l.*, e.name FROM leaves l JOIN employees e ON l.emp_id = e.id WHERE l.status='Pending' LIMIT 3");
    if($res_leave) { while($row = mysqli_fetch_assoc($res_leave)) { $pending_leaves[] = $row; } }
}

// Interviews
$interviews = [];
if($conn) {
    $check_table = mysqli_query($conn, "SHOW TABLES LIKE 'interviews'");
    if(mysqli_num_rows($check_table) > 0) {
        $res_int = mysqli_query($conn, "SELECT * FROM interviews WHERE status != 'Completed' ORDER BY id DESC LIMIT 2");
        if($res_int) { while($row = mysqli_fetch_assoc($res_int)) { $interviews[] = $row; } }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .page-wrapper { flex: 1; padding: 25px; transition: margin-left 0.3s; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-body { padding: 1.5rem; }
        .border-start-primary { border-left: 4px solid #FF9B44 !important; }
        .avatar { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; object-fit: cover;}
        .avatar-lg { width: 48px; height: 48px; font-size: 24px; }
        .bg-primary { background-color: #FF9B44 !important; }
        .text-primary { color: #FF9B44 !important; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .chart-line { width: 3px; height: 12px; display: inline-block; border-radius: 10px; }
        .fs-13 { font-size: 13px; }
    </style>
    
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body>

    <?php if(file_exists('../include/sidebar.php')) include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php if(file_exists('../include/header.php')) include '../include/header.php'; ?>

        <div class="page-wrapper">
            <div class="content">
                
                <div class="d-flex align-items-center justify-content-between flex-wrap mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">HR Dashboard</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">HR Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                    
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-3 mb-2">
                        <a href="?action=logout" class="btn btn-danger d-flex align-items-center" onclick="return confirm('Are you sure you want to logout?');">
                            <i class="ti ti-power me-2"></i> Logout
                        </a>

                        <div class="input-icon position-relative">
                            <span class="input-icon-addon"><i class="ti ti-calendar"></i></span>
                            <input type="text" class="form-control" value="<?= date('d/m/Y') ?>" readonly>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-5 d-flex flex-column">
                        <div class="card flex-fill mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-3"><h5 class="card-title">Employee Status</h5></div>
                                <div id="status-chart"></div>
                            </div>
                        </div>
                        <div class="card flex-fill">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Leaves</h5>
                                <div id="leave-chart"></div>
                            </div> 
                        </div> 
                    </div> 

                    <div class="col-xl-7">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Overview</h5>
                                <div class="row g-3">
                                    <div class="col-md-6"><div class="card border mb-0 p-3"><h3><?= $total_emp ?></h3><p>Total Employees</p></div></div>
                                    <div class="col-md-6"><div class="card border mb-0 p-3"><h3><?= $new_joinees ?></h3><p>New Joinees</p></div></div>
                                    <div class="col-md-6"><div class="card border mb-0 p-3"><h3><?= $late_today ?></h3><p>Late Today</p></div></div>
                                    <div class="col-md-6"><div class="card border mb-0 p-3"><h3>$<?= number_format($payroll_cost/1000, 1) ?>K</h3><p>Payroll</p></div></div>
                                </div>
                            </div> 
                        </div> 
                        
                        <div class="card mt-3">
                            <div class="card-body">
                                <h5 class="card-title">Pending Leaves</h5>
                                <?php foreach($pending_leaves as $l): ?>
                                    <div class="border-bottom py-2">
                                        <strong><?= $l['name'] ?></strong>: <?= $l['leave_type'] ?> (<?= $l['days'] ?> days)
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div> 
                </div> 

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Charts Configuration
        var statusOptions = {
            series: [{ name: 'Employees', data: [<?= $full_time ?>, <?= $contract ?>, <?= $probation ?>] }],
            chart: { type: 'bar', height: 200, toolbar: { show: false } },
            colors: ['#FF9B44', '#7460ee', '#333'], 
            plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '40%' } },
            xaxis: { categories: ['Full-Time', 'Contract', 'Probation'] }
        };
        new ApexCharts(document.querySelector("#status-chart"), statusOptions).render();

        var leaveOptions = {
            series: [<?= $sick_count ?>, <?= $casual_count ?>, <?= $unpaid_count ?>],
            chart: { type: 'radialBar', height: 250, offsetY: -20 },
            colors: ['#FF9B44', '#7460ee', '#333'],
            labels: ['Sick', 'Casual', 'Unpaid'],
        };
        new ApexCharts(document.querySelector("#leave-chart"), leaveOptions).render();
    </script>
</body>
</html>