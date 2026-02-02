<?php
session_start();

// --- 1. ROBUST DATABASE CONNECTION (MySQLi) ---
// We try multiple paths to find your connection file
$db_paths = [
    '../include/db_connect.php', 
    '../../include/db_connect.php',
    'db_connect.php'
];

$conn = null;
foreach ($db_paths as $path) {
    if (file_exists($path)) {
        include $path;
        // Check if the variable from db_connect.php is set
        if (isset($conn)) break; 
    }
}

if (!isset($conn)) {
    die("<div style='color:red;padding:20px;'><b>Error:</b> Database connection file not found or connection failed. Checked paths: " . implode(', ', $db_paths) . "</div>");
}

// --- 2. FETCH REAL DATA FROM DB ---
try {
    // Helper function for counts
    function get_db_count($conn, $sql) {
        $result = mysqli_query($conn, $sql);
        return ($result && $row = mysqli_fetch_array($result)) ? $row[0] : 0;
    }

    // A. KPI Metrics
    $total_leads = get_db_count($conn, "SELECT COUNT(*) FROM leads");
    $new_leads   = get_db_count($conn, "SELECT COUNT(*) FROM leads WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $lost_leads  = get_db_count($conn, "SELECT COUNT(*) FROM leads WHERE lead_stage = 'Lost'");
    $customers   = get_db_count($conn, "SELECT COUNT(*) FROM leads WHERE lead_stage = 'Closed'");

    // B. Charts Data (Dynamic from DB)
    // Pipeline
    $pipe_labels = []; $pipe_data = [];
    $res = mysqli_query($conn, "SELECT lead_stage, COUNT(*) as c FROM leads GROUP BY lead_stage");
    if($res) { while($r = mysqli_fetch_assoc($res)) { $pipe_labels[] = $r['lead_stage']; $pipe_data[] = $r['c']; } }

    // Source
    $src_labels = []; $src_data = [];
    $res = mysqli_query($conn, "SELECT lead_source, COUNT(*) as c FROM leads GROUP BY lead_source");
    if($res) { while($r = mysqli_fetch_assoc($res)) { $src_labels[] = $r['lead_source']; $src_data[] = $r['c']; } }

    // C. Recent Leads List
    $recent_leads_db = [];
    $res = mysqli_query($conn, "SELECT * FROM leads ORDER BY created_at DESC LIMIT 5");
    if($res) { while($r = mysqli_fetch_assoc($res)) { $recent_leads_db[] = $r; } }

} catch (Exception $e) {
    echo "Data Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leads Dashboard</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        /* --- LAYOUT FIXES --- */
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 25px; }

        /* --- CARDS & PANELS --- */
        .card { border: none; border-radius: 12px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); margin-bottom: 24px; background: #fff; }
        .card-header { background: transparent; border-bottom: 1px solid #f0f0f0; padding: 15px 20px; font-weight: 600; font-size: 16px; display:flex; justify-content:space-between; align-items:center; }
        .card-body { padding: 20px; }
        .card-fill { height: 100%; }

        /* --- METRICS (KPIs) --- */
        .avatar-md { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; border-radius: 10px; color: #fff; font-size: 22px; }
        .bg-orange { background: #FF9B44; } .bg-dark { background: #333; } .bg-red { background: #fc6075; } .bg-purple { background: #7460ee; }
        
        /* --- LISTS (Companies/FollowUp) --- */
        .border-dashed { border: 1px dashed #e3e3e3; border-radius: 8px; padding: 10px; margin-bottom: 10px; background: #fafafa; }
        .list-item { display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; }
        .avatar-sm { width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; background: #eee; font-weight: bold; color: #555; margin-right: 10px; font-size: 12px; }
        
        /* --- TIMELINE (Activities) --- */
        .activity-feed { position: relative; padding-left: 20px; }
        .feed-item { position: relative; padding-bottom: 20px; border-left: 2px solid #eee; padding-left: 20px; }
        .feed-item:last-child { border: none; }
        .feed-icon { position: absolute; left: -11px; top: 0; width: 20px; height: 20px; border-radius: 50%; color: white; display: flex; align-items: center; justify-content: center; font-size: 10px; }
        .bg-success { background: #28a745; } .bg-info { background: #009efb; }
        
        /* --- BADGES --- */
        .badge { padding: 5px 10px; font-weight: 500; font-size: 11px; border-radius: 4px; }
        .badge-Contacted { background: #e0f2fe; color: #0ea5e9; }
        .badge-Closed { background: #dcfce7; color: #16a34a; }
        .badge-Lost { background: #fee2e2; color: #ef4444; }
        .badge-New { background: #f3f4f6; color: #374151; }
        
        /* Header Overrides */
        .breadcrumb-item a { text-decoration: none; color: #6c757d; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        
        <?php include '../include/header.php'; ?>

        <div class="dashboard-scroll-area">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1 fw-bold">Leads Dashboard</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
                            <li class="breadcrumb-item active">Leads</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white border bg-white btn-sm">Export <i class="ti ti-download"></i></button>
                    <button class="btn btn-white border bg-white btn-sm"><i class="ti ti-calendar"></i> This Week</button>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body d-flex align-items-center"><div class="avatar-md bg-orange me-3"><i class="ti ti-chart-bar"></i></div><div><p class="text-muted mb-1 fs-13">Total Leads</p><h4 class="mb-0"><?= number_format($total_leads) ?></h4><small class="text-danger">-4% vs last week</small></div></div></div></div>
                <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body d-flex align-items-center"><div class="avatar-md bg-dark me-3"><i class="ti ti-user-plus"></i></div><div><p class="text-muted mb-1 fs-13">New Leads</p><h4 class="mb-0"><?= number_format($new_leads) ?></h4><small class="text-success">+20% vs last week</small></div></div></div></div>
                <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body d-flex align-items-center"><div class="avatar-md bg-red me-3"><i class="ti ti-user-x"></i></div><div><p class="text-muted mb-1 fs-13">Lost Leads</p><h4 class="mb-0"><?= number_format($lost_leads) ?></h4><small class="text-success">+5% vs last week</small></div></div></div></div>
                <div class="col-xl-3 col-sm-6"><div class="card"><div class="card-body d-flex align-items-center"><div class="avatar-md bg-purple me-3"><i class="ti ti-users"></i></div><div><p class="text-muted mb-1 fs-13">Customers</p><h4 class="mb-0"><?= number_format($customers) ?></h4><small class="text-success">+12% vs last week</small></div></div></div></div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Pipeline Stages</h5></div>
                        <div class="card-body"><div id="pipelineChart" style="height: 300px;"></div></div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card card-fill">
                        <div class="card-header"><h5>New Leads</h5></div>
                        <div class="card-body"><div id="heatmapChart" style="height: 300px;"></div></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Lost Leads</h5></div>
                        <div class="card-body"><div id="lostChart" style="height: 250px;"></div></div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Leads By Companies</h5></div>
                        <div class="card-body">
                            <?php foreach(['Pitch', 'Initech', 'Umbrella Corp'] as $comp): ?>
                            <div class="border-dashed d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-white border"><?= substr($comp,0,1) ?></div>
                                    <div><h6 class="mb-0 fs-13"><?= $comp ?></h6><small class="text-muted">$45,000</small></div>
                                </div>
                                <span class="badge badge-New">Contacted</span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Leads By Source</h5></div>
                        <div class="card-body d-flex align-items-center justify-content-center">
                            <div id="sourceChart" style="width: 100%;"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Recent Follow Up</h5><a href="#" class="fs-12">View All</a></div>
                        <div class="card-body">
                            <?php foreach(['Daniel Esbella', 'Doglas Martini', 'Alexander'] as $u): ?>
                            <div class="list-item">
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-light"><?= substr($u,0,1) ?></div>
                                    <div><h6 class="mb-0 fs-13"><?= $u ?></h6><small class="text-muted">Team Lead</small></div>
                                </div>
                                <i class="ti ti-message text-muted"></i>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Recent Activities</h5><a href="#" class="fs-12">View All</a></div>
                        <div class="card-body">
                            <div class="activity-feed">
                                <div class="feed-item">
                                    <div class="feed-icon bg-success"><i class="ti ti-phone"></i></div>
                                    <p class="mb-0 fs-13">Drain responded to your call.</p><small class="text-muted">09:25 PM</small>
                                </div>
                                <div class="feed-item">
                                    <div class="feed-icon bg-orange"><i class="ti ti-mail"></i></div>
                                    <p class="mb-0 fs-13">Sent email to James.</p><small class="text-muted">10:00 AM</small>
                                </div>
                                <div class="feed-item">
                                    <div class="feed-icon bg-purple"><i class="ti ti-user"></i></div>
                                    <p class="mb-0 fs-13">Meeting with Abraham.</p><small class="text-muted">11:00 AM</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Notifications</h5><a href="#" class="fs-12">View All</a></div>
                        <div class="card-body">
                            <div class="list-item">
                                <div class="d-flex">
                                    <div class="avatar-sm">L</div>
                                    <div><h6 class="mb-0 fs-13">Lex Murphy requested access</h6><small class="text-muted">Today at 9:42 AM</small></div>
                                </div>
                            </div>
                            <div class="list-item">
                                <div class="d-flex">
                                    <div class="avatar-sm">R</div>
                                    <div>
                                        <h6 class="mb-0 fs-13">Ray Arnold requested access</h6>
                                        <div class="mt-1">
                                            <button class="btn btn-sm btn-primary py-0 px-2 fs-11">Approve</button>
                                            <button class="btn btn-sm btn-outline-danger py-0 px-2 fs-11">Decline</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Top Countries</h5></div>
                        <div class="card-body d-flex align-items-center">
                            <div class="flex-grow-1">
                                <div class="d-flex align-items-center mb-3"><i class="ti ti-flag text-danger me-2"></i><div><h6 class="mb-0">USA</h6><small>350 Leads</small></div></div>
                                <div class="d-flex align-items-center mb-3"><i class="ti ti-flag text-primary me-2"></i><div><h6 class="mb-0">France</h6><small>589 Leads</small></div></div>
                                <div class="d-flex align-items-center"><i class="ti ti-flag text-warning me-2"></i><div><h6 class="mb-0">India</h6><small>221 Leads</small></div></div>
                            </div>
                            <div id="countriesChart" style="width: 150px;"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-7">
                    <div class="card card-fill">
                        <div class="card-header"><h5>Recent Leads</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table mb-0 align-middle">
                                    <thead class="bg-light"><tr><th>Company</th><th>Stage</th><th>Created</th><th>Owner</th></tr></thead>
                                    <tbody>
                                        <?php if(count($recent_leads_db) > 0): ?>
                                            <?php foreach($recent_leads_db as $l): ?>
                                            <tr>
                                                <td><span class="fw-medium"><?= htmlspecialchars($l['company_name']) ?></span></td>
                                                <td><span class="badge badge-<?= $l['lead_stage'] ?>"><?= $l['lead_stage'] ?></span></td>
                                                <td><?= date('d M', strtotime($l['created_at'])) ?></td>
                                                <td><?= htmlspecialchars($l['lead_owner']) ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr><td colspan="4" class="text-center p-3">No data available</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div> </div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();

        // Pass PHP Data to JS
        var pipeLabels = <?php echo json_encode($pipe_labels ?: ['No Data']); ?>;
        var pipeData = <?php echo json_encode($pipe_data ?: [0]); ?>;
        var srcLabels = <?php echo json_encode($src_labels ?: ['No Data']); ?>;
        var srcData = <?php echo json_encode($src_data ?: [1]); ?>;

        // 1. Pipeline Chart
        new ApexCharts(document.querySelector("#pipelineChart"), {
            series: [{ name: 'Leads', data: pipeData }],
            chart: { type: 'bar', height: 280, toolbar: {show:false} },
            colors: ['#FF9B44', '#333', '#7460ee'],
            plotOptions: { bar: { distributed: true, borderRadius: 4, columnWidth: '40%' } },
            xaxis: { categories: pipeLabels },
            legend: { show: false }
        }).render();

        // 2. Source Chart
        new ApexCharts(document.querySelector("#sourceChart"), {
            series: srcData.map(Number),
            chart: { type: 'donut', height: 260 },
            labels: srcLabels,
            colors: ['#FF9B44', '#7460ee', '#fc6075', '#333'],
            legend: { position: 'bottom' }
        }).render();

        // 3. Heatmap (Mock Data)
        new ApexCharts(document.querySelector("#heatmapChart"), {
            series: [{ name: 'Mon', data: [10,20,30,40,50] }, { name: 'Tue', data: [20,30,40,50,60] }, { name: 'Wed', data: [30,40,50,60,70] }],
            chart: { type: 'heatmap', height: 280, toolbar: {show:false} },
            dataLabels: { enabled: false },
            colors: ['#FF9B44']
        }).render();

        // 4. Lost Chart (Mock)
        new ApexCharts(document.querySelector("#lostChart"), {
            series: [{ name: 'Lost', data: [40, 20, 10, 30] }],
            chart: { type: 'bar', height: 230, toolbar: {show:false} },
            colors: ['#fc6075'],
            xaxis: { categories: ['Competitor', 'Budget', 'Timing', 'Other'] }
        }).render();

        // 5. Countries Donut (Mock)
        new ApexCharts(document.querySelector("#countriesChart"), {
            series: [350, 589, 221],
            chart: { type: 'donut', height: 140 },
            colors: ['#fc6075', '#FF9B44', '#009efb'],
            dataLabels: { enabled: false },
            legend: { show: false }
        }).render();
    </script>
</body>
</html>