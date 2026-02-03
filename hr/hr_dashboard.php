<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

// --- DATA FETCHING ---

// A. Employee Statistics
$total_emp = 0; $new_joinees = 0; $payroll_cost = 0;
$full_time = 0; $contract = 0; $probation = 0;

if($conn) {
    // Counts
    $total_emp = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE status='Active'"))['c'] ?? 0;
    
    // New Joinees (Joined in current month)
    $new_joinees = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE MONTH(joined_date) = MONTH(CURRENT_DATE()) AND YEAR(joined_date) = YEAR(CURRENT_DATE())"))['c'] ?? 0;
    
    // Payroll (Sum of salaries)
    $payroll_query = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(salary) as s FROM employees WHERE status='Active'"));
    $payroll_cost = $payroll_query['s'] ?? 0;

    // Employee Types for Chart
    $full_time = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE employee_type='Full-Time'"))['c'] ?? 0;
    $contract = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE employee_type='Contract'"))['c'] ?? 0;
    $probation = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE employee_type='Probation'"))['c'] ?? 0;
}

// B. Attendance & Late Arrivals
$late_today = 0;
if($conn) {
    $late_today = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM attendance WHERE status='Late' AND work_date = CURDATE()"))['c'] ?? 0;
}

// C. Leaves
$sick_count = 0; $casual_count = 0; $unpaid_count = 0;
$pending_leaves = []; 

if($conn) {
    // Counts for Chart
    $sick_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM leaves WHERE leave_type='Sick'"))['c'] ?? 0;
    $casual_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM leaves WHERE leave_type='Casual'"))['c'] ?? 0;
    $unpaid_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM leaves WHERE leave_type='Unpaid'"))['c'] ?? 0; 

    // Fetch Pending Approvals List (REMOVED e.image FROM QUERY)
    $res_leave = mysqli_query($conn, "SELECT l.*, e.name FROM leaves l JOIN employees e ON l.emp_id = e.id WHERE l.status='Pending' LIMIT 3");
    if($res_leave) { while($row = mysqli_fetch_assoc($res_leave)) $pending_leaves[] = $row; }
}

// D. Recruitment
$applicants = 0; $hired = 0; 
if($conn) {
    $applicants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM resumes"))['c'] ?? 0;
    $hired = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM resumes WHERE status='Hired'"))['c'] ?? 0;
}

// E. Interviews
$interviews = [];
if($conn) {
    $res_int = mysqli_query($conn, "SELECT * FROM interviews WHERE status != 'Completed' ORDER BY id DESC LIMIT 2");
    if($res_int) { while($row = mysqli_fetch_assoc($res_int)) $interviews[] = $row; }
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
        
        /* Specific SmartHR Card Styles */
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-body { padding: 1.5rem; }
        .border-start-primary { border-left: 4px solid #FF9B44 !important; }
        .border-start-secondary { border-left: 4px solid #7460ee !important; }
        
        /* Avatars & Text */
        .avatar { width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; object-fit: cover;}
        .avatar-lg { width: 48px; height: 48px; font-size: 24px; }
        .avatar-group-md .avatar { width: 36px; height: 36px; margin-right: -10px; border: 2px solid #fff; }
        .avatar-sm { width: 30px; height: 30px; font-size: 12px; }
        
        /* Utilities */
        .text-truncate { white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .fs-13 { font-size: 13px; }
        .fs-14 { font-size: 14px; }
        .fs-20 { font-size: 20px; }
        .fs-24 { font-size: 24px; }
        .fw-semibold { font-weight: 600; }
        
        /* Colors */
        .bg-primary { background-color: #FF9B44 !important; }
        .bg-secondary { background-color: #7460ee !important; }
        .text-primary { color: #FF9B44 !important; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .btn-light { background-color: #f8f9fa; border-color: #f8f9fa; }
        
        /* Chart Container */
        .chart-line { width: 3px; height: 12px; display: inline-block; border-radius: 10px; }
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
                        <div class="input-icon position-relative">
                            <span class="input-icon-addon"><i class="ti ti-calendar text-gray-9"></i></span>
                            <input type="text" class="form-control" value="<?= date('d/m/Y') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-5 d-flex flex-column">
                        
                        <div class="card flex-fill mb-3">
                            <div class="card-body">
                                <div class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
                                    <h2 class="card-title mb-0">Employee Status & Type</h2>
                                    <a href="employees.php" class="btn btn-md btn-light">View All</a>
                                </div>
                                <div id="status-chart" class="mb-3"></div>
                                <div class="row">
                                    <div class="col-4">
                                        <div class="text-center">
                                            <h3 class="main-title mb-1"><?= $full_time ?></h3>
                                            <p class="d-inline-flex align-items-center mb-0"><span class="chart-line bg-primary me-1"></span>Full-Time</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <h3 class="main-title mb-1"><?= $contract ?></h3>
                                            <p class="d-inline-flex align-items-center mb-0"><span class="chart-line bg-secondary me-1"></span>Contract</p>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-center">
                                            <h3 class="main-title mb-1"><?= $probation ?></h3>
                                            <p class="d-inline-flex align-items-center mb-0"><span class="chart-line bg-dark me-1"></span>Probation</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card flex-fill">
                            <div class="card-body pb-sm-2">
                                <div class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
                                    <h2 class="card-title mb-0">Leave Type Distribution</h2>
                                    <div class="dropdown">
                                        <a href="#" class="border btn btn-white btn-md d-inline-flex align-items-center"><i class="ti ti-calendar-due me-1 fs-14"></i>Monthly</a>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-5">
                                        <div id="leave-chart"></div>
                                    </div>
                                    <div class="col-sm-7">
                                        <div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <p class="d-inline-flex align-items-center text-dark mb-0"><i class="ti ti-circle-filled text-danger fs-7 me-1"></i>Sick Leave</p>
                                                <span class="badge fw-normal bg-light text-dark border rounded-pill fs-13"><?= $sick_count ?></span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <p class="d-inline-flex align-items-center text-dark mb-0"><i class="ti ti-circle-filled text-warning fs-7 me-1"></i>Casual Leave</p>
                                                <span class="badge fw-normal bg-light text-dark border rounded-pill fs-13"><?= $casual_count ?></span>
                                            </div>
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <p class="d-inline-flex align-items-center text-dark mb-0"><i class="ti ti-circle-filled text-dark fs-7 me-1"></i>Unpaid</p>
                                                <span class="badge fw-normal bg-light text-dark border rounded-pill fs-13"><?= $unpaid_count ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div> 
                        </div> 
                    </div> 

                    <div class="col-xl-7">
                        <div class="card">
                            <div class="card-body">
                                <div class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
                                    <h2 class="card-title mb-0">Overview Statistics</h2>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6 d-flex">
                                        <div class="card shadow-none mb-0 flex-fill border">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar avatar-lg bg-primary rounded-circle flex-shrink-0">
                                                        <i class="ti ti-users-group text-white fs-24"></i>
                                                    </div>
                                                    <div class="ms-2"><p class="fw-semibold text-truncate mb-0">Total Employees</p></div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h3 class="main-title mb-1"><?= number_format($total_emp) ?></h3>
                                                        <p class="fs-13 mb-0">Headcount Overview</p>
                                                    </div>
                                                    <div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">+18% <i class="ti ti-arrow-up-right text-success ms-1"></i></div>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div> 

                                    <div class="col-md-6 d-flex">
                                        <div class="card shadow-none mb-0 flex-fill border">
                                            <div class="card-body">
                                                <div class="d-flex avatar-lg align-items-center mb-3">
                                                    <div class="avatar bg-secondary rounded-circle flex-shrink-0">
                                                        <i class="ti ti-users-plus text-white fs-24"></i>
                                                    </div>
                                                    <div class="ms-2"><p class="fw-semibold text-truncate mb-0">New Joinees</p></div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h3 class="main-title mb-1"><?= $new_joinees ?></h3>
                                                        <p class="fs-13 mb-0">All Department</p>
                                                    </div>
                                                    <div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">+22% <i class="ti ti-arrow-up-right text-success ms-1"></i></div>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div> 

                                    <div class="col-md-6 d-flex">
                                        <div class="card shadow-none mb-0 flex-fill border">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar avatar-lg bg-dark rounded-circle flex-shrink-0">
                                                        <i class="ti ti-clock-x text-white fs-24"></i>
                                                    </div>
                                                    <div class="ms-2"><p class="fw-semibold text-truncate mb-0">Late Arrivals Today</p></div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h3 class="main-title mb-1"><?= $late_today ?></h3>
                                                        <p class="fs-13 mb-0">Delayed Logins Today</p>
                                                    </div>
                                                    <div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">-16% <i class="ti ti-arrow-down-right text-danger ms-1"></i></div>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div> 

                                    <div class="col-md-6 d-flex">
                                        <div class="card shadow-none mb-0 flex-fill border">
                                            <div class="card-body">
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="avatar avatar-lg bg-primary rounded-circle flex-shrink-0" style="background-color: #9c27b0 !important;">
                                                        <i class="ti ti-report-money text-white fs-24"></i>
                                                    </div>
                                                    <div class="ms-2"><p class="fw-semibold text-truncate mb-0">Total Payroll Cost</p></div>
                                                </div>
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <div>
                                                        <h3 class="main-title mb-1">$<?= number_format($payroll_cost/1000, 1) ?>K</h3>
                                                        <p class="fs-13 mb-0">Payroll Outflow</p>
                                                    </div>
                                                    <div class="d-inline-flex align-items-center bg-light border rounded-pill text-dark p-1 ps-2">+16% <i class="ti ti-arrow-up-right text-success ms-1"></i></div>
                                                </div>
                                            </div> 
                                        </div> 
                                    </div> 
                                </div>
                            </div> 
                        </div> 
                    </div> 
                </div> 
                <div class="row">
                    <div class="col-xl-8 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body pb-0">
                                <div class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
                                    <h2 class="card-title mb-0">Attendance Trend</h2>
                                </div>
                                <div id="attendance-chart" class="w-100"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-4 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body pb-0">
                                <div class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-0">
                                    <h2 class="card-title mb-0">Top Employee Distribution</h2>
                                    <a href="employees.php" class="btn btn-md btn-light">View All</a>
                                </div>
                                <div id="employee-distribution" style="margin-top: 20px;"></div>
                            </div> 
                        </div> 
                    </div> 
                </div>
                <div class="row">
                    <div class="col-xl-5 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body">
                                <div class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
                                    <h2 class="card-title mb-0">Pending Approvals</h2>
                                    <a href="leaves.php" class="btn btn-md btn-light">View All</a>
                                </div>
                                
                                <?php if(empty($pending_leaves)): ?>
                                    <p class="text-center text-muted">No pending approvals.</p>
                                <?php else: ?>
                                    <?php foreach($pending_leaves as $l): ?>
                                    <div class="p-2 rounded border d-flex align-items-sm-center justify-content-between gap-2 flex-column flex-sm-row mb-2">
                                        <div>
                                            <div class="d-flex align-items-center mb-1">
                                                <span class="avatar avatar-sm bg-light text-primary fw-bold me-2"><?= strtoupper(substr($l['name'],0,1)) ?></span>
                                                <div class="ms-2">
                                                    <p class="fs-14 fw-semibold text-truncate mb-0"><?= htmlspecialchars($l['name']) ?></p>
                                                </div>
                                            </div>
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <p class="fs-13 d-inline-flex align-items-center mb-0"><i class="ti ti-calendar-up me-1 fs-14"></i><?= date('d M', strtotime($l['start_date'])) ?></p>
                                                <span>|</span>
                                                <p class="fs-13 d-inline-flex align-items-center mb-0"><?= $l['days'] ?> days</p>
                                            </div>
                                            <p class="fs-13 mb-0 mt-1 text-muted">Reason: <?= htmlspecialchars($l['reason']) ?></p>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <button class="btn btn-sm btn-primary">Approve</button>
                                            <button class="btn btn-sm btn-light border">Decline</button>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div> 
                        </div> 
                    </div> 
                    
                    <div class="col-xxl-4 col-xl-12 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body d-flex justify-content-between flex-column">
                                <div>
                                    <div class="border rounded border-start border-start-primary d-flex align-items-center justify-content-between p-2 gap-2 flex-wrap mb-3">
                                        <h2 class="card-title mb-0">Upcoming Interview</h2>
                                    </div>
                                    
                                    <?php if(empty($interviews)): ?>
                                        <p class="text-center text-muted">No scheduled interviews.</p>
                                    <?php else: ?>
                                        <?php foreach($interviews as $int): ?>
                                        <div class="p-3 rounded border border-start border-start-4 border-start-primary mb-3">
                                            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap mb-3">
                                                <div>
                                                    <p class="text-dark fw-semibold mb-1"><?= htmlspecialchars($int['role']) ?></p>
                                                    <p class="fs-13 mb-0"><?= htmlspecialchars($int['interview_time']) ?></p>
                                                </div>
                                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($int['candidate_name']) ?></span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-sm-7"><a href="#" class="btn btn-white border d-flex align-items-center justify-content-center w-100">Calendar</a></div>
                                                <div class="col-sm-5"><a href="#" class="btn btn-light d-flex align-items-center justify-content-center w-100">Join</a></div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 1. Status Chart (Bar)
        var statusOptions = {
            series: [{ name: 'Employees', data: [<?= $full_time ?>, <?= $contract ?>, <?= $probation ?>] }],
            chart: { type: 'bar', height: 200, toolbar: { show: false } },
            colors: ['#FF9B44', '#7460ee', '#333'], // Matches Primary, Secondary, Dark
            plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '40%' } },
            dataLabels: { enabled: false },
            xaxis: { categories: ['Full-Time', 'Contract', 'Probation'], labels: { style: { fontSize: '12px' } } },
            legend: { show: false },
            grid: { show: false }
        };
        new ApexCharts(document.querySelector("#status-chart"), statusOptions).render();

        // 2. Leave Chart (Radial Semi-Circle)
        var leaveOptions = {
            series: [<?= $sick_count ?>, <?= $casual_count ?>, <?= $unpaid_count ?>],
            chart: { type: 'radialBar', height: 250, offsetY: -20 },
            plotOptions: {
                radialBar: {
                    startAngle: -90, endAngle: 90,
                    track: { background: "#e7e7e7", strokeWidth: '97%', margin: 5 },
                    dataLabels: { name: { show: false }, value: { offsetY: -2, fontSize: '22px' } }
                }
            },
            colors: ['#FF9B44', '#7460ee', '#333'],
            labels: ['Sick', 'Casual', 'Unpaid'],
        };
        new ApexCharts(document.querySelector("#leave-chart"), leaveOptions).render();

        // 3. Attendance Chart (Bar)
        var attOptions = {
            series: [
                { name: 'Present', data: [44, 55, 57, 56, 61, 58, 63] },
                { name: 'Late', data: [16, 25, 21, 18, 17, 15, 11] },
                { name: 'Absent', data: [5, 4, 6, 2, 5, 8, 2] }
            ],
            chart: { type: 'bar', height: 250, stacked: false, toolbar: { show: false } },
            colors: ['#FF9B44', '#7460ee', '#ffc107'],
            plotOptions: { bar: { columnWidth: '50%', borderRadius: 3 } },
            dataLabels: { enabled: false },
            xaxis: { categories: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] }
        };
        new ApexCharts(document.querySelector("#attendance-chart"), attOptions).render();

        // 4. Employee Distribution (Donut)
        var distOptions = {
            series: [44, 55, 41, 17, 15],
            chart: { type: 'donut', height: 250 },
            colors: ['#FF9B44', '#7460ee', '#28C76F', '#EA5455', '#333'],
            labels: ['IT', 'HR', 'Sales', 'Marketing', 'Finance'],
            legend: { position: 'bottom' }
        };
        new ApexCharts(document.querySelector("#employee-distribution"), distOptions).render();
    </script>
</body>
</html>