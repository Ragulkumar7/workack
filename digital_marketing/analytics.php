<?php
session_start();
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// --- FETCH DATA ---

// 1. Recent Leads (From Deals table for now, or Leads table if created)
$recent_leads = [];
$res = mysqli_query($conn, "SELECT d.*, c.company_name, c.image as company_image 
                            FROM deals d 
                            LEFT JOIN companies c ON d.company_id = c.id 
                            ORDER BY d.created_at DESC LIMIT 5");
if($res) { while($row = mysqli_fetch_assoc($res)) $recent_leads[] = $row; }

// 2. Recent Companies
$recent_companies = [];
$res = mysqli_query($conn, "SELECT * FROM companies ORDER BY created_at DESC LIMIT 5");
if($res) { while($row = mysqli_fetch_assoc($res)) $recent_companies[] = $row; }

// 3. Recent Contacts
$recent_contacts = [];
$res = mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5");
if($res) { while($row = mysqli_fetch_assoc($res)) $recent_contacts[] = $row; }

// 4. Recent Deals (Won)
$recent_deals = [];
$res = mysqli_query($conn, "SELECT * FROM deals WHERE status='Won' ORDER BY created_at DESC LIMIT 5");
if($res) { while($row = mysqli_fetch_assoc($res)) $recent_deals[] = $row; }

// 5. Activity Stream
$activities = [];
$res = mysqli_query($conn, "SELECT * FROM company_activities ORDER BY created_at DESC LIMIT 4");
if($res) { while($row = mysqli_fetch_assoc($res)) $activities[] = $row; }

// --- CHART DATA PREP ---
// Deals by Stage
$stages = ['New', 'Prospect', 'Proposal', 'Won'];
$stage_counts = [];
foreach($stages as $s) {
    $r = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM deals WHERE status='$s'"));
    $stage_counts[] = $r['c'];
}
$stage_json = json_encode($stage_counts);

// Leads by Source
$sources = ['Google', 'Paid', 'Campaigns', 'Referrals'];
$source_counts = [40, 35, 15, 10]; // Mock percentages for demo matching design
$source_json = json_encode($source_counts);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Analytics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .page-wrapper { flex: 1; padding: 25px; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-header { background: #fff; border-bottom: 1px solid #f0f0f0; padding: 15px 20px; font-weight: 600; }
        .avatar-md { width: 40px; height: 40px; }
        .table td { vertical-align: middle; }
        .bg-success-transparent { background-color: rgba(40, 167, 69, 0.1); color: #28a745; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .text-gray-9 { color: #333; }
        .breadcrumb-item a { text-decoration: none; color: #6c757d; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="page-wrapper">
            
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Analytics</h2>
                    <nav><ol class="breadcrumb mb-0"><li class="breadcrumb-item"><a href="dm_dashboard.php">Dashboard</a></li><li class="breadcrumb-item active">Analytics</li></ol></nav>
                </div>
                </div>

            <div class="row">
                
                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                                <h5>Recently Created Contacts</h5>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap mb-0">
                                    <thead><tr><th>Contact</th><th>Email</th><th>Phone</th><th>Created at</th></tr></thead>
                                    <tbody>
                                        <?php foreach($recent_contacts as $c): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center file-name-icon">
                                                    <a href="#" class="avatar avatar-md border avatar-rounded bg-light text-dark d-flex align-items-center justify-content-center fw-bold text-decoration-none">
                                                        <?= substr($c['first_name'],0,1) ?>
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium"><a href="#" class="text-dark text-decoration-none"><?= htmlspecialchars($c['first_name'].' '.$c['last_name']) ?></a></h6>
                                                        <span class="fs-12 fw-normal text-muted"><?= htmlspecialchars($c['job_title']) ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($c['email']) ?></td>
                                            <td><?= htmlspecialchars($c['phone_1']) ?></td>
                                            <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h6>Deals by Stage</h6></div>
                        <div class="card-body"><div id="deals_stage_chart"></div></div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h6>Won Deals Stage</h6></div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <p class="mb-1 fw-medium">Stages Won This Year</p>
                                <h3 class="me-2">$45,899.79</h3>
                            </div>
                            <div class="d-flex justify-content-center gap-3">
                                <div class="text-center p-3 bg-secondary text-white rounded-circle" style="width:80px;height:80px;display:flex;flex-direction:column;justify-content:center;"><h6>48%</h6><span style="font-size:10px;">Conv.</span></div>
                                <div class="text-center p-3 bg-danger text-white rounded-circle" style="width:80px;height:80px;display:flex;flex-direction:column;justify-content:center;"><h6>24%</h6><span style="font-size:10px;">Calls</span></div>
                                <div class="text-center p-3 bg-warning text-white rounded-circle" style="width:80px;height:80px;display:flex;flex-direction:column;justify-content:center;"><h6>39%</h6><span style="font-size:10px;">Email</span></div>
                                <div class="text-center p-3 bg-success text-white rounded-circle" style="width:80px;height:80px;display:flex;flex-direction:column;justify-content:center;"><h6>20%</h6><span style="font-size:10px;">Chats</span></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h6>Recent Activities</h6></div>
                        <div class="card-body">
                            <?php foreach($activities as $act): 
                                $bg = 'bg-info'; $icon = 'ti-message-circle-2';
                                if($act['type'] == 'Call') { $bg='bg-success'; $icon='ti-phone'; }
                            ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="avatar avatar-md avatar-rounded <?= $bg ?> flex-shrink-0 text-white d-flex align-items-center justify-content-center"><i class="<?= $icon ?> fs-20"></i></div>
                                <div class="flex-fill ps-3">
                                    <p class="fw-medium text-gray-9 mb-1"><?= htmlspecialchars($act['description']) ?></p>
                                    <span class="text-muted fs-12"><?= date('h:i A', strtotime($act['created_at'])) ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header"><h5>Recent Deals</h5></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap mb-0">
                                    <thead><tr><th>Deal Name</th><th>Stage</th><th>Deal Value</th><th>Owner</th></tr></thead>
                                    <tbody>
                                        <?php foreach($recent_deals as $d): ?>
                                        <tr>
                                            <td><h6><a href="#" class="text-dark text-decoration-none"><?= htmlspecialchars($d['deal_name']) ?></a></h6></td>
                                            <td><?= htmlspecialchars($d['status']) ?></td>
                                            <td>$<?= number_format($d['deal_value']) ?></td>
                                            <td><?= htmlspecialchars($d['assignee']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card flex-fill">
                        <div class="card-header"><h6>Leads by Source</h6></div>
                        <div class="card-body">
                            <div id="donut-chart-2"></div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <h5>Recent Leads</h5>
                            <div class="d-flex align-items-center"><div><a href="leads.php" class="btn btn-sm btn-light px-3">View All</a></div></div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Lead Name</th>
                                            <th>Company Name</th>
                                            <th>Stage</th>
                                            <th>Created Date</th>
                                            <th>Lead Owner</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_leads as $lead): 
                                            $badgeClass = 'badge-secondary';
                                            if($lead['status'] == 'Won') $badgeClass = 'badge-success';
                                            if($lead['status'] == 'Lost') $badgeClass = 'badge-danger';
                                            
                                            $compName = $lead['company_name'] ?? 'Unknown Company';
                                            $compImg = $lead['company_image'] ?? 'assets/img/company/company-01.svg';
                                        ?>
                                        <tr>
                                            <td><h6><a href="#" class="text-dark text-decoration-none"><?= htmlspecialchars($lead['deal_name']) ?></a></h6></td>
                                            <td>
                                                <div class="d-flex align-items-center file-name-icon">
                                                    <a href="#" class="avatar avatar-md border rounded-circle">
                                                        <img src="<?= $compImg ?>" class="img-fluid" alt="img" style="width:100%;height:100%;object-fit:cover;">
                                                    </a>
                                                    <div class="ms-2"><h6 class="fw-medium"><a href="#" class="text-dark text-decoration-none"><?= htmlspecialchars($compName) ?></a></h6></div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge <?= $badgeClass ?> d-inline-flex align-items-center">
                                                    <i class="ti ti-point-filled me-1"></i><?= htmlspecialchars($lead['status']) ?>
                                                </span>
                                            </td>
                                            <td><?= date('d M Y', strtotime($lead['created_at'])) ?></td>
                                            <td><?= htmlspecialchars($lead['assignee']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-6">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                                <h5>Recently Created Companies</h5>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Company Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Created at</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($recent_companies as $rc): 
                                            $cImg = !empty($rc['image']) ? $rc['image'] : 'assets/img/company/company-01.svg';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center file-name-icon">
                                                    <a href="company-details.php?id=<?= $rc['id'] ?>" class="avatar avatar-md border rounded-circle">
                                                        <img src="<?= $cImg ?>" class="img-fluid" alt="img" style="width:100%;height:100%;object-fit:cover;">
                                                    </a>
                                                    <div class="ms-2">
                                                        <h6 class="fw-medium"><a href="company-details.php?id=<?= $rc['id'] ?>" class="text-dark text-decoration-none"><?= htmlspecialchars($rc['company_name']) ?></a></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><a href="mailto:<?= htmlspecialchars($rc['email']) ?>" class="text-info"><?= htmlspecialchars($rc['email']) ?></a></td>
                                            <td><?= htmlspecialchars($rc['phone']) ?></td>
                                            <td><?= date('d M Y', strtotime($rc['created_at'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Deals Chart
        var options1 = { series: [{ name: 'Deals', data: <?= $stage_json ?> }], chart: { type: 'bar', height: 250 }, colors: ['#FF9B44'] };
        new ApexCharts(document.querySelector("#deals_stage_chart"), options1).render();

        // Leads Chart
        var options2 = { series: <?= $source_json ?>, chart: { type: 'donut', height: 280 }, labels: ['Google', 'Paid', 'Campaigns', 'Referrals'], colors: ['#FF9B44', '#FC6075', '#28C76F', '#00CFE8'] };
        new ApexCharts(document.querySelector("#donut-chart-2"), options2).render();
    </script>
</body>
</html>