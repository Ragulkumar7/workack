<?php
// --- 1. GLOBAL USER DATA ---
$user = [
    'name' => 'TL Manager',
    'role' => 'Team Lead', 
    'avatar_initial' => 'T'
];

// --- 2. REPORT DATA ---
// Employee Performance Metrics
$teamPerformance = [
    ['name' => 'Arun', 'tasks' => 45, 'efficiency' => '92%', 'rating' => 4.8],
    ['name' => 'Priya', 'tasks' => 38, 'efficiency' => '88%', 'rating' => 4.5],
    ['name' => 'John', 'tasks' => 50, 'efficiency' => '95%', 'rating' => 4.9],
    ['name' => 'Sarah', 'tasks' => 32, 'efficiency' => '75%', 'rating' => 3.8],
];

// Project Data for Pie Chart
$projectNames = ['HRMS Portal', 'E-commerce App', 'Internal API', 'Admin Dashboard'];
$projectTaskCounts = [12, 19, 7, 15]; 

// Monthly Trend for Bar Chart
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
$completedTasks = [65, 78, 90, 85, 92, 110];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Performance Reports | SmartHR</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* --- GLOBAL LAYOUT --- */
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 40px; }

        /* --- HEADER --- */
        .rep-header { display: flex; justify-content: space-between; align-items: center; background: white; padding: 20px 30px; border-radius: 12px; border: 1px solid #e1e1e1; margin-bottom: 30px; }
        .rep-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        
        /* --- GRID LAYOUT --- */
        .rep-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; margin-bottom: 25px; }
        .rep-card { background: white; padding: 25px; border-radius: 16px; border: 1px solid #e1e1e1; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .full-width { grid-column: span 2; }
        
        .card-title { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }

        /* --- TABLE STYLE --- */
        .rep-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .rep-table th { text-align: left; font-size: 12px; color: #9ca3af; text-transform: uppercase; padding: 12px; border-bottom: 2px solid #f3f4f6; }
        .rep-table td { padding: 15px 12px; font-size: 14px; color: #4b5563; border-bottom: 1px solid #f3f4f6; }
        .rating-badge { background: #fff7ed; color: #ea580c; padding: 4px 10px; border-radius: 6px; font-weight: 700; font-size: 12px; border: 1px solid #fed7aa; }

        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body>

    <?php 
    // Corrected Path: Go up from team_lead/ to reach include/
    include '../include/sidebar.php'; 
    ?>

    <div class="main-content-wrapper">
        <?php 
        // Corrected Path: Go up from team_lead/ to reach include/
        include '../include/header.php'; 
        ?>

        <div class="dashboard-scroll-area">
            <div class="rep-header">
                <div>
                    <h1 class="rep-title">Team Performance Insights</h1>
                    <p style="font-size:14px; color:#666;">Detailed metrics for employees and active projects</p>
                </div>
                <div style="width: 50px; height: 50px; background: #fff7ed; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #FF9B44; border: 1px solid #ffedd5;">
                    <i data-lucide="bar-chart-3"></i>
                </div>
            </div>

            <div class="rep-grid">
                <div class="rep-card">
                    <div class="card-title"><i data-lucide="pie-chart" color="#FF9B44"></i> Project Distribution</div>
                    <div class="chart-container">
                        <canvas id="projectPieChart"></canvas>
                    </div>
                </div>

                <div class="rep-card">
                    <div class="card-title"><i data-lucide="trending-up" color="#FF9B44"></i> Task Completion Trend</div>
                    <div class="chart-container">
                        <canvas id="taskBarChart"></canvas>
                    </div>
                </div>

                <div class="rep-card full-width">
                    <div class="card-title"><i data-lucide="users" color="#FF9B44"></i> Team Productivity Matrix</div>
                    <table class="rep-table">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Tasks Completed</th>
                                <th>Efficiency Score</th>
                                <th>Avg Rating</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($teamPerformance as $perf): ?>
                                <tr>
                                    <td style="font-weight:700; color:#1f2937;"><?= $perf['name'] ?></td>
                                    <td><?= $perf['tasks'] ?> Tasks</td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <div style="width:100px; height:8px; background:#f3f4f6; border-radius:10px; overflow:hidden;">
                                                <div style="width:<?= $perf['efficiency'] ?>; height:100%; background:#FF9B44;"></div>
                                            </div>
                                            <span style="font-size:12px; font-weight:600;"><?= $perf['efficiency'] ?></span>
                                        </div>
                                    </td>
                                    <td><span class="rating-badge">⭐ <?= $perf['rating'] ?></span></td>
                                    <td>
                                        <span style="color:#10b981; font-weight:700; font-size:12px;">Active</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        // PROJECT DISTRIBUTION CHART
        const pieCtx = document.getElementById('projectPieChart').getContext('2d');
        new Chart(pieCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($projectNames); ?>,
                datasets: [{
                    data: <?php echo json_encode($projectTaskCounts); ?>,
                    backgroundColor: ['#FF9B44', '#f97316', '#fdba74', '#ffedd5'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 20 } } }
            }
        });

        // TASK BAR CHART
        const barCtx = document.getElementById('taskBarChart').getContext('2d');
        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Completed Tasks',
                    data: <?php echo json_encode($completedTasks); ?>,
                    backgroundColor: '#FF9B44',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, grid: { display: false } }, x: { grid: { display: false } } },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>