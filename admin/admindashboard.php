<?php
// 1. DATABASE & SESSION SETUP
require_once '../login/db_connect.php';

// Security Check
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'Admin') {
    header("Location: ../login/login.php");
    exit;
}

// 2. DATA FETCHING
$emp_query = mysqli_query($conn, "SELECT id FROM users");
$total_employees = mysqli_num_rows($emp_query);

// Data from screenshots
$total_projects  = 90;
$total_clients   = 69;
$total_tasks     = 96;
$earnings        = "$21,445";
$profit_weekly   = "$5,544";
$job_applicants  = 98;
$new_hires       = "45/48";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SmartHR Workack</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-orange: #FF9B44;
            --sidebar-bg: #34444c;
            --body-bg: #f7f7f7;
            --white: #ffffff;
            --text-dark: #333333;
            --text-muted: #6c757d;
            --border: #e3e3e3;
            --sidebar-width: 70px; 
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { font-family: 'Segoe UI', sans-serif; background: var(--body-bg); display: flex; color: var(--text-dark); min-height: 100vh; }

        .main-content { flex: 1; margin-left: var(--sidebar-width); padding: 25px; width: calc(100% - var(--sidebar-width)); transition: all 0.3s ease; }
        
        .breadcrumb { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .welcome-card { background: var(--white); padding: 20px; border-radius: 8px; display: flex; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 30px; border-left: 5px solid var(--primary-orange); }
        .welcome-card img { width: 60px; height: 60px; border-radius: 50%; margin-right: 20px; }

        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: var(--white); padding: 20px; border-radius: 10px; text-align: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .stat-card i { font-size: 24px; padding: 15px; border-radius: 50%; margin-bottom: 10px; display: inline-block; }
        .bg-orange { color: #ff9b44; background: #ff9b4415; }
        .bg-blue { color: #00c5fb; background: #00c5fb15; }
        .bg-green { color: #55ce63; background: #55ce6315; }
        .bg-red { color: #f62d51; background: #f62d5115; }

        .dashboard-row { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .triple-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 25px; margin-bottom: 25px; }
        .content-card { background: var(--white); padding: 20px; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        .card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
        .card-title { font-size: 16px; font-weight: bold; }
        .view-all { color: var(--primary-orange); text-decoration: none; font-size: 13px; font-weight: 600; }
        .add-btn { background: var(--primary-orange); color: white; border: none; width: 25px; height: 25px; border-radius: 50%; cursor: pointer; font-size: 16px; }

        .list-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f9f9f9; }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .user-info img { width: 38px; height: 38px; border-radius: 50%; }
        .badge { font-size: 11px; padding: 4px 8px; border-radius: 4px; color: white; }

        /* MODAL STYLES */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); align-items: center; justify-content: center; }
        .modal-content { background-color: white; padding: 30px; border-radius: 15px; width: 450px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .modal-header h2 { font-size: 20px; }
        .close-modal { cursor: pointer; font-size: 20px; color: #888; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-size: 14px; font-weight: 600; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; outline: none; }
        .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
        .btn-cancel { background: #eee; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }
        .btn-save { background: var(--primary-orange); color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }

        /* Sales Chart Mockup */
        .bar-container { display: flex; align-items: flex-end; gap: 10px; height: 150px; padding-top: 20px; }
        .bar { flex: 1; background: var(--primary-orange); border-radius: 3px 3px 0 0; }
        .status-unpaid { color: #f62d51; background: #f62d5115; padding: 2px 8px; border-radius: 4px; font-size: 11px; }

        @media (max-width: 1200px) { .dashboard-row, .triple-row, .stats-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php include '../include/sidebar.php'; ?>

<main class="main-content">
    <div class="container-fluid">
        <div class="breadcrumb">
            <div>
                <h2>Admin Dashboard</h2>
                <p style="color:var(--text-muted); font-size: 13px;">Dashboard / Admin Dashboard</p>
            </div>
            <button style="background:var(--primary-orange); color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer;">+ Add Schedule</button>
        </div>

        <div class="welcome-card">
            <img src="https://ui-avatars.com/api/?name=Admin+User&background=FF9B44&color=fff" alt="Admin">
            <div class="welcome-text">
                <h3>Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3>
                <p>You have <span style="color:#f62d51; font-weight:bold;">21</span> Pending Approvals & <span style="color:#f62d51; font-weight:bold;">14</span> Leave Requests.</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><i class="fa fa-user-check bg-orange"></i><h3>120/154</h3><p>Attendance Overview</p></div>
            <div class="stat-card"><i class="fa fa-cubes bg-blue"></i><h3>90</h3><p>Total No of Project's</p></div>
            <div class="stat-card"><i class="fa fa-gem bg-green"></i><h3>69</h3><p>Total No of Clients</p></div>
            <div class="stat-card"><i class="fa fa-tasks bg-red"></i><h3>96</h3><p>Total No of Tasks</p></div>
            <div class="stat-card"><i class="fa fa-wallet bg-purple"></i><h3>$21,445</h3><p>Earnings</p></div>
            <div class="stat-card"><i class="fa fa-chart-line bg-red"></i><h3>$5,544</h3><p>Profit This Week</p></div>
            <div class="stat-card"><i class="fa fa-user-tie bg-green"></i><h3>98</h3><p>Job Applicants</p></div>
            <div class="stat-card"><i class="fa fa-user-plus bg-blue"></i><h3>45/48</h3><p>New Hire</p></div>
        </div>

        <div class="triple-row">
            <div class="content-card">
                <div class="card-header"><span class="card-title">Jobs Applicants</span><a href="applicants_page.php" class="view-all">View All</a></div>
                <div class="list-item">
                    <div class="user-info">
                        <img src="https://i.pravatar.cc/150?u=1" alt="">
                        <div><p style="font-size:14px; font-weight:600;">Brian Villalobos</p><small>Exp: 5+ Years • USA</small></div>
                    </div>
                    <span style="background:#00c5fb; color:white;" class="badge">UI/UX Designer</span>
                </div>
                <div class="list-item">
                    <div class="user-info">
                        <img src="https://i.pravatar.cc/150?u=2" alt="">
                        <div><p style="font-size:14px; font-weight:600;">Anthony Lewis</p><small>Exp: 4+ Years • USA</small></div>
                    </div>
                    <span style="background:#ff9b44; color:white;" class="badge">Python Developer</span>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header"><span class="card-title">Employees</span><a href="employees_details.php" class="view-all">View All</a></div>
                <div class="list-item">
                    <div class="user-info">
                        <img src="https://i.pravatar.cc/150?u=3" alt="">
                        <div><p style="font-size:14px; font-weight:600;">John Doe</p><small>Software Engineer</small></div>
                    </div>
                    <span style="color:#00c5fb; font-size:11px; font-weight:600;">Development</span>
                </div>
                <div class="list-item">
                    <div class="user-info">
                        <img src="https://i.pravatar.cc/150?u=4" alt="">
                        <div><p style="font-size:14px; font-weight:600;">Richard Miles</p><small>Web Developer</small></div>
                    </div>
                    <span style="color:#ff9b44; font-size:11px; font-weight:600;">Marketing</span>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header"><span class="card-title">Todo List</span><button class="add-btn" id="openTodoModal">+</button></div>
                <div class="list-item"><label style="font-size:14px;"><input type="checkbox" style="margin-right:10px;"> Add Holidays</label> label></div>
                <div class="list-item"><label style="font-size:14px;"><input type="checkbox" checked style="margin-right:10px;"> Add Meeting to Client</label></div>
                <div class="list-item"><label style="font-size:14px;"><input type="checkbox" style="margin-right:10px;"> Chat with Adrian</label></div>
            </div>
        </div>

        <div class="dashboard-row">
            <div class="content-card">
                <div class="card-header"><span class="card-title">Sales Overview</span></div>
                <div class="bar-container">
                    <div class="bar" style="height: 40%;"></div>
                    <div class="bar" style="height: 70%;"></div>
                    <div class="bar" style="height: 55%;"></div>
                    <div class="bar" style="height: 90%;"></div>
                    <div class="bar" style="height: 65%;"></div>
                    <div class="bar" style="height: 80%;"></div>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header"><span class="card-title">Invoices</span><a href="#" class="view-all">View All</a></div>
                <div class="list-item">
                    <div class="user-info">
                        <div><p style="font-size:14px; font-weight:600;">#INV-0001</p><small>Global Technologies</small></div>
                    </div>
                    <div><span class="status-unpaid">Unpaid</span> <strong style="margin-left:10px;">$2,100</strong></div>
                </div>
            </div>
        </div>
    </div>
</main>

<div id="todoModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Add New Todo</h2>
            <span class="close-modal">&times;</span>
        </div>
        <form>
            <div class="form-group">
                <label>Todo Title</label>
                <input type="text" placeholder="Enter title">
            </div>
            <div style="display:flex; gap:10px;">
                <div class="form-group" style="flex:1;">
                    <label>Tag</label>
                    <select><option>Projects</option><option>Personal</option></select>
                </div>
                <div class="form-group" style="flex:1;">
                    <label>Priority</label>
                    <select><option>Select</option><option>High</option><option>Low</option></select>
                </div>
            </div>
            <div class="form-group">
                <label>Descriptions</label>
                <textarea rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Add Assignee</label>
                <select><option>Select</option></select>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select><option>Select</option></select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel close-modal">Cancel</button>
                <button type="submit" class="btn-save">Add New Todo</button>
            </div>
        </form>
    </div>
</div>

<script>
    // Sidebar dynamic width logic
    function checkSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const content = document.querySelector('.main-content');
        if(sidebar && sidebar.classList.contains('active')) {
            content.style.marginLeft = "240px";
            content.style.width = "calc(100% - 240px)";
        } else {
            content.style.marginLeft = "70px";
            content.style.width = "calc(100% - 70px)";
        }
    }
    window.onload = checkSidebar;

    // Modal Logic
    const modal = document.getElementById("todoModal");
    const btn = document.getElementById("openTodoModal");
    const closeBtns = document.querySelectorAll(".close-modal");

    btn.onclick = function() { modal.style.display = "flex"; }
    closeBtns.forEach(btn => {
        btn.onclick = function() { modal.style.display = "none"; }
    });
    window.onclick = function(event) {
        if (event.target == modal) { modal.style.display = "none"; }
    }
</script>

</body>
</html>