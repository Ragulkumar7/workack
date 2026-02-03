<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<?php
// --- Data Structures ---
$stats = [
    ['label' => 'Open Positions', 'value' => '47', 'icon' => 'fa-briefcase', 'color' => '#ff8a65', 'bg' => '#fff5f2'],
    ['label' => 'Total Candidates', 'value' => '2,384', 'icon' => 'fa-users', 'color' => '#4db6ac', 'bg' => '#f0f7f7'],
    ['label' => 'Interviews Today', 'value' => '12', 'icon' => 'fa-calendar-days', 'color' => '#757575', 'bg' => '#f5f5f5'],
    ['label' => 'Offers Released', 'value' => '28', 'icon' => 'fa-file-lines', 'color' => '#42a5f5', 'bg' => '#eef6fe'],
];

$hiringData = [
    ['dept' => 'Marketing', 'role' => 'Product Manager', 'apps' => 14, 'short' => 8, 'int' => 0, 'off' => 0, 'hired' => 0],
    ['dept' => 'Data Analyst', 'role' => 'Jr Data Analyst', 'apps' => 16, 'short' => 12, 'int' => 0, 'off' => 0, 'hired' => 0],
    ['dept' => 'Project Coordinator', 'role' => 'Jr Level', 'apps' => 24, 'short' => 6, 'int' => 0, 'off' => 0, 'hired' => 0],
    ['dept' => 'Design Lead', 'role' => 'UI Designer', 'apps' => 12, 'short' => 8, 'int' => 6, 'off' => 5, 'hired' => 0],
    ['dept' => 'Project Manager', 'role' => 'Senior Manager', 'apps' => 22, 'short' => 20, 'int' => 16, 'off' => 12, 'hired' => 10],
];

$schedules = [
    ['month' => 'Mar', 'day' => '02', 'role' => 'Product Designer', 'time' => '09:00 AM - 10:30 AM', 'img' => 'https://i.pravatar.cc/100?u=1'],
    ['month' => 'Apr', 'day' => '22', 'role' => 'Marketing Manager', 'time' => '01:00 PM - 02:00 PM', 'img' => 'https://i.pravatar.cc/100?u=2'],
    ['month' => 'May', 'day' => '11', 'role' => 'Sr. Data Science', 'time' => '11:00 AM - 12:30 PM', 'img' => 'https://i.pravatar.cc/100?u=3'],
    ['month' => 'Jun', 'day' => '07', 'role' => 'Software Engineer', 'time' => '02:00 PM - 03:30 PM', 'img' => 'https://i.pravatar.cc/100?u=4'],
    ['month' => 'Aug', 'day' => '18', 'role' => 'Financial Analyst', 'time' => '03:00 PM - 04:00 PM', 'img' => 'https://i.pravatar.cc/100?u=5'],
];

$performanceCards = [
    ['label' => 'Applied', 'value' => '1,848', 'type' => 'Overall Progress', 'percent' => '36.3%', 'color' => '#f26d21', 'icon' => 'fa-id-card'],
    ['label' => 'Shortlisted', 'value' => '2,384', 'type' => 'Conversion rate', 'percent' => '37.4%', 'color' => '#0b4c5f', 'icon' => 'fa-hourglass-half'],
    ['label' => 'Interviewed', 'value' => '892', 'type' => 'Conversion rate', 'percent' => '36.3%', 'color' => '#212529', 'icon' => 'fa-calendar-check'],
    ['label' => 'Offered', 'value' => '324', 'type' => 'Conversion rate', 'percent' => '26.5%', 'color' => '#2b84ff', 'icon' => 'fa-file-lines'],
    ['label' => 'Hired', 'value' => '64', 'type' => 'Conversion rate', 'percent' => '41.2%', 'color' => '#00c985', 'icon' => 'fa-user-check'],
];

$recentApplications = [
    ['name' => 'Andrew Stuart', 'role' => 'Frontend Developer', 'exp' => '7 years exp', 'applied' => 'Dec 27, 2025', 'status' => 'Interview', 'status_color' => '#9c27b0', 'bg' => '#f3e5f5', 'img' => 'https://i.pravatar.cc/100?u=Andrew'],
    ['name' => 'Jessica Brown', 'role' => 'UI/UX Designer', 'exp' => '7 years exp', 'applied' => 'Dec 27, 2025', 'status' => 'Shortlisted', 'status_color' => '#006064', 'bg' => '#e0f2f1', 'img' => 'https://i.pravatar.cc/100?u=Jessica'],
];

$activeJobs = [
    ['id' => 'JOB-001', 'date' => 'Jan 03, 2026', 'title' => 'Frontend Developer', 'priority' => 'High Priority', 'p_class' => 'danger', 'loc' => 'Remote', 'dept' => 'Engineering', 'apps' => '1452'],
    ['id' => 'JOB-002', 'date' => 'Jan 02, 2026', 'title' => 'Product Manager', 'priority' => 'High Priority', 'p_class' => 'danger', 'loc' => 'Office', 'dept' => 'Product', 'apps' => '1342'],
    ['id' => 'JOB-003', 'date' => 'Jan 02, 2026', 'title' => 'UX Designer', 'priority' => 'High Priority', 'p_class' => 'danger', 'loc' => 'Hybrid', 'dept' => 'Design', 'apps' => '1287'],
    ['id' => 'JOB-004', 'date' => 'Jan 01, 2026', 'title' => 'Sales Executive', 'priority' => 'Medium', 'p_class' => 'info', 'loc' => 'Office', 'dept' => 'Sales', 'apps' => '1198'],
    ['id' => 'JOB-005', 'date' => 'Jan 01, 2026', 'title' => 'DevOps Engineer', 'priority' => 'Medium', 'p_class' => 'info', 'loc' => 'Office', 'dept' => 'Engineering', 'apps' => '1134'],
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recruitment Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary-orange: #f26d21; --bg-gray: #f8f9fa; --teal-dark: #0b4c5f; }
        body { background-color: var(--bg-gray); font-family: 'Segoe UI', sans-serif; overflow-x: hidden; }
        
        /* UPDATED LAYOUT: Snapping content to sidebar */
        .main-content { 
            margin-left: 100px; /* Aligned for the collapsed icon sidebar view */
            padding: 20px 20px 20px 0px; 
            transition: all 0.3s; 
        }

        .main-content .container-fluid {
            padding-left: 10px !important;
            margin-left: 0 !important;
        }

        @media (max-width: 991px) { 
            .main-content { margin-left: 0; padding: 15px; } 
        }

        .card { border: none; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); margin-bottom: 20px; background: #fff; }
        .status-container { display: flex; background: #fff; border-radius: 12px; padding: 25px 10px; margin: 24px 0; border: 1px solid #eee; }
        .status-item { flex: 1; text-align: center; border-right: 1px solid #f0f0f0; }
        .status-item:last-child { border-right: none; }
        .icon-box { width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; font-size: 20px; }
        .perf-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: white; font-size: 18px; }
        .progress-thin { height: 4px; border-radius: 2px; background-color: #f0f0f0; margin-top: 8px; }
        .progress-bar-custom { height: 100%; border-radius: 2px; }
        .badge-pill-stat { width: 80px; padding: 8px 0; border-radius: 20px; color: white; font-weight: bold; display: inline-block; }
        .ai-avatar { width: 70px; height: 70px; background: #ff6b35; border-radius: 18px; display: flex; align-items: center; justify-content: center; color: white; font-size: 32px; margin: 0 auto 15px; }
        .chat-bubble { background: #f1f3f5; border-radius: 20px; padding: 10px 18px; display: block; margin-bottom: 10px; font-size: 0.9rem; text-align: left; }
        .bar-stack { width: 40px; display: flex; flex-direction: column-reverse; gap: 2px; }
        .segment { width: 100%; border-radius: 3px; }
        .profile-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
        .app-card { border: 1px solid #f0f0f0; border-radius: 12px; padding: 15px; margin-bottom: 15px; }
        .app-badge { font-size: 0.75rem; padding: 5px 12px; border-radius: 15px; font-weight: bold; }
        .btn-schedule { background: var(--primary-orange); color: white; border: none; border-radius: 8px; width: 100%; padding: 12px; font-weight: bold; }
        .job-table thead th { background-color: #f1f3f5; border: none; color: #495057; font-weight: 700; font-size: 0.9rem; }
        .job-table td { vertical-align: middle; border-bottom: 1px solid #eee; padding: 15px 10px; }
        .priority-badge { font-size: 0.65rem; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
        .job-icon { color: #888; font-size: 0.8rem; margin-right: 5px; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-lg-8">
                <div class="card p-4">
                    <h5 class="fw-bold mb-4">Candidates Hiring Analysis</h5>
                    <div class="table-responsive">
                        <table class="table align-middle text-center">
                            <thead><tr><th class="text-start">Department</th><th>Applicants</th><th>Shortlisted</th><th>Interviewed</th><th>Hired</th></tr></thead>
                            <tbody>
                                <?php foreach ($hiringData as $row): ?>
                                <tr>
                                    <td class="text-start"><div class="fw-bold"><?= $row['dept'] ?></div><small class="text-muted"><?= $row['role'] ?></small></td>
                                    <td><span class="badge-pill-stat" style="background:#f26d21"><?= sprintf("%02d", $row['apps']) ?></span></td>
                                    <td><span class="badge-pill-stat" style="background:#0b4c5f"><?= sprintf("%02d", $row['short']) ?></span></td>
                                    <td><span class="badge-pill-stat" style="background:<?= $row['int']>0?'#212529':'#e9ecef' ?>"><?= sprintf("%02d", $row['int']) ?></span></td>
                                    <td><span class="badge-pill-stat" style="background:<?= $row['hired']>0?'#00c985':'#e9ecef' ?>"><?= sprintf("%02d", $row['hired']) ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-4">
                    <h6 class="fw-bold">Recruitment Overview</h6>
                    <div class="row text-center my-3"><div class="col-6"><small class="text-muted d-block">Adoption Rate</small><span class="fw-bold fs-5">74.4%</span></div><div class="col-6"><small class="text-muted d-block">Hire Rate</small><span class="fw-bold fs-5">2.7%</span></div></div>
                    <div class="text-center"><div class="fw-bold fs-4">2,384</div><small class="text-muted">Applications</small></div>
                </div>
                <div class="card p-4 text-white" style="background:#0b4c5f">
                    <small class="opacity-75">Quick Reminder</small><h5 class="mt-2">You have <b>21 Interviews</b> scheduled today!</h5>
                </div>
            </div>
        </div>

        <div class="status-container">
            <?php foreach ($stats as $s): ?>
            <div class="status-item">
                <div class="icon-box" style="background: <?= $s['bg'] ?>; color: <?= $s['color'] ?>;"><i class="fa-solid <?= $s['icon'] ?>"></i></div>
                <h4 class="fw-bold mb-0"><?= $s['value'] ?></h4><small class="text-muted"><?= $s['label'] ?></small>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card p-4 text-center h-100">
                    <div class="ai-avatar"><i class="fa-solid fa-robot"></i></div><h5 class="fw-bold">How can I help you today</h5>
                    <div class="chat-bubble mt-3">Analyze top candidates for Senior Developer role</div>
                    <div class="chat-bubble">Generate hiring report</div>
                    <div class="input-group mt-4 bg-light rounded-pill p-1">
                        <input type="text" class="form-control border-0 bg-transparent ps-3" placeholder="Ask me anything..."><button class="btn btn-orange rounded-circle" style="background:#f26d21; color:white;"><i class="fa-solid fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-4 h-100"><h6 class="fw-bold mb-4">Average Time To Hire</h6>
                    <div class="d-flex align-items-end justify-content-around" style="height: 150px; border-bottom: 1px solid #eee;">
                        <div class="bar-stack"><div class="segment" style="height:80px; background:#0b4c5f"></div></div>
                        <div class="bar-stack"><div class="segment" style="height:110px; background:#0b4c5f"></div></div>
                        <div class="bar-stack"><div class="segment" style="height:90px; background:#0b4c5f"></div></div>
                    </div>
                    <div class="alert alert-success mt-4 text-center small py-2">12% faster than industry avg</div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card p-4 h-100"><h6 class="fw-bold mb-4">Upcoming Schedules</h6>
                    <?php foreach ($schedules as $s): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light rounded p-2 text-center me-3" style="min-width: 50px;"><small class="d-block text-muted" style="font-size: 0.6rem;"><?= $s['month'] ?></small><b class="d-block"><?= $s['day'] ?></b></div>
                        <div class="flex-grow-1"><div class="fw-bold small"><?= $s['role'] ?></div><small class="text-muted"><?= $s['time'] ?></small></div><img src="<?= $s['img'] ?>" class="profile-img" alt="">
                    </div>
                    <?php endforeach; ?><button class="btn w-100 mt-2 text-white fw-bold" style="background:#f26d21; border-radius:10px;">View All Schedule</button>
                </div>
            </div>
        </div>

        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4"><h5 class="fw-bold mb-0">Stage Performance</h5><button class="btn btn-sm btn-light border dropdown-toggle"><i class="fa fa-calendar-alt me-1"></i> Last 30 Days</button></div>
            <div class="row g-3">
                <?php foreach ($performanceCards as $card): ?>
                <div class="col"><div class="card p-3 h-100 border" style="box-shadow: none;">
                        <div class="d-flex justify-content-between"><small class="text-muted fw-bold"><?= $card['label'] ?></small><div class="perf-icon" style="background: <?= $card['color'] ?>;"><i class="fa <?= $card['icon'] ?>"></i></div></div>
                        <h3 class="fw-bold my-2"><?= $card['value'] ?></h3>
                        <div class="d-flex justify-content-between small text-muted" style="font-size: 0.75rem;"><span><?= $card['type'] ?></span><span><?= $card['percent'] ?></span></div>
                        <div class="progress-thin"><div class="progress-bar-custom" style="width: <?= $card['percent'] ?>; background: <?= $card['color'] ?>;"></div></div>
                    </div></div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card p-4">
            <h5 class="fw-bold mb-4">Recent Applications</h5>
            <?php foreach ($recentApplications as $app): ?>
            <div class="app-card">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= $app['img'] ?>" class="profile-img me-3" style="width: 50px; height: 50px;">
                    <div class="flex-grow-1"><div class="fw-bold mb-0"><?= $app['name'] ?></div><div class="text-muted small"><?= $app['role'] ?></div></div>
                    <span class="app-badge" style="background: <?= $app['bg'] ?>; color: <?= $app['status_color'] ?>;"><i class="fa fa-circle small me-1"></i> <?= $app['status'] ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center text-muted small">
                    <span><i class="fa fa-user me-2"></i> <?= $app['exp'] ?></span><span><i class="fa fa-calendar-alt me-2"></i> Applied: <?= $app['applied'] ?></span>
                </div>
            </div>
            <?php endforeach; ?>
            <button class="btn-schedule mt-2">View All Schedule</button>
        </div>

        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="fw-bold mb-0">Active Job Openings</h5>
                <button class="btn btn-sm btn-light border px-3">View All</button>
            </div>
            <div class="table-responsive">
                <table class="table job-table">
                    <thead>
                        <tr><th>Job ID</th><th>Date</th><th>Job Title</th><th>Location</th><th>Department</th><th>Applicants</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activeJobs as $job): ?>
                        <tr>
                            <td class="text-muted fw-bold"><?= $job['id'] ?></td>
                            <td><i class="fa-solid fa-calendar-check job-icon"></i> <?= $job['date'] ?></td>
                            <td>
                                <div class="fw-bold mb-1"><?= $job['title'] ?></div>
                                <span class="badge bg-<?= $job['p_class'] ?>-subtle text-<?= $job['p_class'] ?> priority-badge"><?= $job['priority'] ?></span>
                            </td>
                            <td class="text-muted"><i class="fa-solid fa-earth-americas job-icon"></i> <?= $job['loc'] ?></td>
                            <td class="text-muted"><?= $job['dept'] ?></td>
                            <td><i class="fa-solid fa-users job-icon"></i> <?= $job['apps'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>