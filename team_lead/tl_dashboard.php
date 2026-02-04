<?php
// --- 1. TARGETED DATABASE CONNECTION ---
$db_path = '../login/db_connect.php';

if (file_exists($db_path)) {
    include_once($db_path);
} else {
    die("<div style='color:red; font-family:sans-serif; padding:20px;'><strong>Critical Error:</strong> Cannot find db_connect.php</div>");
}

// --- 2. DYNAMIC LIVE DATA FETCHING ---
$stats = ['members' => 0, 'tasks' => 0, 'present' => 0, 'efficiency' => 0];
$attendance_data = ['Present' => 0, 'Absent' => 0];

if (isset($conn) && $conn) {
    // A. Count unique team members
    $res = mysqli_query($conn, "SELECT COUNT(DISTINCT employee_name) as total FROM team_attendance");
    $stats['members'] = ($res) ? (mysqli_fetch_assoc($res)['total'] ?? 0) : 0;

    // B. Count active tasks
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks WHERE status != 'Completed'");
    $stats['tasks'] = ($res) ? (mysqli_fetch_assoc($res)['total'] ?? 0) : 0;

    // C. Today's presence count
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM team_attendance WHERE status='Present' AND attendance_date=CURDATE()");
    $stats['present'] = ($res) ? (mysqli_fetch_assoc($res)['total'] ?? 0) : 0;

    // D. Efficiency
    $res = mysqli_query($conn, "SELECT AVG(REPLACE(efficiency, '%', '')) as avg_eff FROM team_performance");
    $stats['efficiency'] = ($res) ? round(mysqli_fetch_assoc($res)['avg_eff'] ?? 0, 1) : 0;

    // E. Attendance Pie Chart Data
    $attendance_data['Present'] = $stats['present'];
    $attendance_data['Absent'] = max(0, $stats['members'] - $stats['present']);
}

$user = ['name' => 'TL Manager', 'role' => 'Team Lead', 'avatar_initial' => 'T'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional TL Dashboard | Workack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 30px; box-sizing: border-box; }
        
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e1e1e1; display: flex; align-items: center; gap: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); }
        .stat-icon { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; }
        .stat-info h3 { margin: 0; font-size: 22px; font-weight: 700; color: #111827; }
        .stat-info p { margin: 0; font-size: 12px; color: #6b7280; }

        .charts-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 25px; align-items: start; }
        .chart-card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e1e1e1; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .chart-header { margin-bottom: 20px; font-weight: 700; font-size: 16px; color: #374151; display: flex; align-items: center; gap: 8px; }
        
        .content-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px; }
        .content-card { background: white; border-radius: 12px; border: 1px solid #e1e1e1; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.02); }
        .table-header { padding: 15px 20px; background: #fafafa; border-bottom: 1px solid #eee; font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { text-align: left; padding: 12px 20px; font-size: 11px; color: #9ca3af; text-transform: uppercase; background: #fdfdfd; }
        .data-table td { padding: 12px 20px; font-size: 13px; border-top: 1px solid #f9fafb; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fff7ed; color: #c2410c; }
        .badge-info { background: #e0f2fe; color: #0369a1; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="dashboard-scroll-area">
            <div style="margin-bottom: 25px;">
                <h1 style="margin:0; font-size:26px; font-weight:800; color:#111827;">Team Dashboard</h1>
                <p style="margin:0; color:#6b7280; font-size:14px;">Live tracking of team performance and tasks.</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background:#fff7ed; color:#ea580c;"><i data-lucide="users" size="20"></i></div>
                    <div class="stat-info"><h3><?= $stats['members'] ?></h3><p>Team Size</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#f0fdf4; color:#16a34a;"><i data-lucide="check-circle" size="20"></i></div>
                    <div class="stat-info"><h3><?= $stats['tasks'] ?></h3><p>Active Tasks</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#eff6ff; color:#2563eb;"><i data-lucide="calendar-check" size="20"></i></div>
                    <div class="stat-info"><h3><?= $stats['present'] ?></h3><p>Present Today</p></div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" style="background:#faf5ff; color:#9333ea;"><i data-lucide="zap" size="20"></i></div>
                    <div class="stat-info"><h3><?= $stats['efficiency'] ?>%</h3><p>Efficiency</p></div>
                </div>
            </div>

            <div class="charts-row">
                <div class="chart-card">
                    <div class="chart-header"><i data-lucide="bar-chart-3" color="#FF9B44"></i> Task Progress</div>
                    <div style="height: 220px;"><canvas id="taskBarChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-header"><i data-lucide="pie-chart" color="#FF9B44"></i> Projects</div>
                    <div style="height: 220px;"><canvas id="projectPieChart"></canvas></div>
                </div>
                <div class="chart-card">
                    <div class="chart-header"><i data-lucide="user-check" color="#2563eb"></i> Attendance Overview</div>
                    <div style="height: 220px;"><canvas id="attendancePieChart"></canvas></div>
                </div>
            </div>

            <div class="content-grid">
                <div class="content-card">
                    <div class="table-header"><i data-lucide="users" size="16"></i> Team Members & Roles</div>
                    <table class="data-table">
                        <thead><tr><th>Employee Name</th><th>Role</th></tr></thead>
                        <tbody>
                            <?php
                            $res = mysqli_query($conn, "SELECT employee_name, role FROM team_attendance GROUP BY employee_name LIMIT 5");
                            if ($res) {
                                while($row = mysqli_fetch_assoc($res)) {
                                    echo "<tr><td style='font-weight:600;'>".htmlspecialchars($row['employee_name'])."</td>";
                                    echo "<td><span class='badge badge-info'>".htmlspecialchars($row['role'] ?? 'Staff')."</span></td></tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="content-card">
                    <div class="table-header"><i data-lucide="clock" size="16"></i> Attendance Time Logs (Today)</div>
                    <table class="data-table">
                        <thead><tr><th>Name</th><th>In</th><th>Break</th><th>Out</th></tr></thead>
                        <tbody>
                            <?php
                            // Fixed: Added error checking to prevent the Argument #1 Fatal Error
                            $res = mysqli_query($conn, "SELECT employee_name, check_in, break_time, check_out FROM team_attendance WHERE attendance_date = CURDATE() LIMIT 5");
                            if ($res) {
                                while($row = mysqli_fetch_assoc($res)) { ?>
                                    <tr>
                                        <td style="font-weight:600;"><?= htmlspecialchars($row['employee_name']) ?></td>
                                        <td style="color:#16a34a;"><?= $row['check_in'] ?? '--:--' ?></td>
                                        <td style="color:#ea580c;"><?= $row['break_time'] ?? '--:--' ?></td>
                                        <td style="color:#ef4444;"><?= $row['check_out'] ?? '--:--' ?></td>
                                    </tr>
                            <?php } 
                            } else {
                                echo "<tr><td colspan='4' style='text-align:center; padding:10px; color:red;'>Table or Columns not found.</td></tr>";
                            } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="content-card">
                <div class="table-header"><i data-lucide="clipboard-list" size="16"></i> Recent Performance Logs</div>
                <table class="data-table">
                    <thead><tr><th>Employee</th><th>Rating</th><th>Date</th><th>Comments</th></tr></thead>
                    <tbody>
                        <?php
                        $res = mysqli_query($conn, "SELECT * FROM team_reviews ORDER BY created_at DESC LIMIT 4");
                        if ($res) {
                            while($rev = mysqli_fetch_assoc($res)): ?>
                                <tr>
                                    <td style="font-weight:600;"><?= htmlspecialchars($rev['employee_name']) ?></td>
                                    <td><span class="badge <?= (strtoupper($rev['rating']) == 'EXCELLENT') ? 'badge-success' : 'badge-warning' ?>"><?= $rev['rating'] ?></span></td>
                                    <td><?= date('d M Y', strtotime($rev['created_at'])) ?></td>
                                    <td style="font-style:italic;">"<?= htmlspecialchars($rev['comments']) ?>"</td>
                                </tr>
                            <?php endwhile;
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
        // Charts
        new Chart(document.getElementById('taskBarChart'), { type: 'bar', data: { labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], datasets: [{ label: 'Completed', data: [12, 19, 3, 5, 2], backgroundColor: '#FF9B44', borderRadius: 6 }] }, options: { responsive: true, maintainAspectRatio: false } });
        new Chart(document.getElementById('projectPieChart'), { type: 'doughnut', data: { labels: ['Done', 'Pending', 'Delay'], datasets: [{ data: [60, 25, 15], backgroundColor: ['#10b981', '#FF9B44', '#ef4444'], borderWidth: 0 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
        new Chart(document.getElementById('attendancePieChart'), { type: 'pie', data: { labels: ['Present', 'Absent'], datasets: [{ data: [<?= $attendance_data['Present'] ?>, <?= $attendance_data['Absent'] ?>], backgroundColor: ['#2563eb', '#e2e8f0'] }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } });
    </script>
</body>
</html>