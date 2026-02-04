<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. FETCH EMPLOYEE DATA
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: employees.php"); 
    exit();
}

$emp_id = intval($_GET['id']);
$emp = null;

$sql = "SELECT * FROM employees WHERE id = $emp_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $emp = mysqli_fetch_assoc($result);
} else {
    echo "Employee not found.";
    exit();
}

// Initials for avatar
$initials = strtoupper(substr($emp['name'], 0, 2));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employee Profile - HR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; margin-left: 110px; transition: margin-left 0.3s; }
        .page-wrapper { flex: 1; padding: 25px; }
        
        /* Profile Header */
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-body { padding: 1.5rem; }
        
        .profile-view { position: relative; }
        .profile-img-wrap { height: 120px; width: 120px; position: absolute; border-radius: 50%; background: #fff; overflow: hidden; border: 5px solid #fff; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .profile-img { width: 100%; height: 100%; object-fit: cover; display: flex; align-items: center; justify-content: center; background: #f0f0f0; font-size: 40px; color: #555; font-weight: bold; }
        .profile-basic { margin-left: 140px; }
        
        .staff-msg { margin-top: 10px; }
        .btn-custom { background: #FF9B44; color: #fff; border: none; }
        .btn-custom:hover { background: #ff851a; color: #fff; }
        
        /* Personal Info List */
        .personal-info li { margin-bottom: 10px; display: flex; }
        .personal-info .title { font-weight: 500; color: #333; width: 30%; float: left; }
        .personal-info .text { color: #777; width: 70%; float: left; }
        
        @media (max-width: 991px) { 
            .main-content-wrapper { margin-left: 0; } 
            .profile-basic { margin-left: 0; margin-top: 130px; }
            .profile-img-wrap { left: 50%; transform: translateX(-50%); }
            .personal-info li { flex-direction: column; }
            .personal-info .title { width: 100%; }
            .personal-info .text { width: 100%; }
        }
    </style>
</head>
<body>

    <?php if(file_exists('../include/sidebar.php')) include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        
        <?php if(file_exists('../include/header.php')) include '../include/header.php'; ?>

        <div class="page-wrapper">
            <div class="content">
                
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <div>
                        <h3 class="mb-1">Employee Profile</h3>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="employees.php">Employees</a></li>
                                <li class="breadcrumb-item active">Profile</li>
                            </ol>
                        </nav>
                    </div>
                    <a href="employees.php" class="btn btn-light border"><i class="ti ti-arrow-left me-1"></i> Back to List</a>
                </div>

                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="profile-view">
                                    <div class="profile-img-wrap">
                                        <div class="profile-img">
                                            <?php if (!empty($emp['image']) && file_exists($emp['image'])): ?>
                                                <img src="<?= htmlspecialchars($emp['image']) ?>" alt="">
                                            <?php else: ?>
                                                <?= $initials ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="profile-basic">
                                        <div class="row">
                                            <div class="col-md-5">
                                                <div class="profile-info-left">
                                                    <h3 class="user-name m-t-0 mb-0"><?= htmlspecialchars($emp['name']) ?></h3>
                                                    <h6 class="text-muted"><?= htmlspecialchars($emp['role']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($emp['department']) ?></small>
                                                    <div class="staff-id mt-2">Employee ID : <span class="fw-bold text-primary"><?= htmlspecialchars($emp['emp_code']) ?></span></div>
                                                    <div class="small doj text-muted">Date of Join : <?= date('d M Y', strtotime($emp['joined_date'])) ?></div>
                                                    <div class="staff-msg">
                                                        <a href="#" class="btn btn-custom btn-sm"><i class="ti ti-mail me-1"></i> Send Email</a>
                                                        <a href="#" class="btn btn-light border btn-sm"><i class="ti ti-message-dots me-1"></i> Chat</a>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-7">
                                                <ul class="personal-info list-unstyled">
                                                    <li>
                                                        <div class="title">Phone:</div>
                                                        <div class="text"><a href="tel:<?= htmlspecialchars($emp['phone']) ?>"><?= htmlspecialchars($emp['phone']) ?></a></div>
                                                    </li>
                                                    <li>
                                                        <div class="title">Email:</div>
                                                        <div class="text"><a href="mailto:<?= htmlspecialchars($emp['email']) ?>"><?= htmlspecialchars($emp['email']) ?></a></div>
                                                    </li>
                                                    <li>
                                                        <div class="title">Birthday:</div>
                                                        <div class="text">24th July</div>
                                                    </li>
                                                    <li>
                                                        <div class="title">Address:</div>
                                                        <div class="text">1861 Bayonne Ave, Manchester Township, NJ, 08759</div>
                                                    </li>
                                                    <li>
                                                        <div class="title">Gender:</div>
                                                        <div class="text">Male</div>
                                                    </li>
                                                    <li>
                                                        <div class="title">Reports to:</div>
                                                        <div class="text">
                                                            <span class="avatar avatar-xs bg-secondary rounded-circle text-white d-inline-flex align-items-center justify-content-center" style="width:20px;height:20px;font-size:10px;">JM</span>
                                                            <a href="#">Jeffery Lalor</a>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card tab-box">
                    <div class="row user-tabs">
                        <div class="col-lg-12 col-md-12 col-sm-12 line-tabs">
                            <ul class="nav nav-tabs nav-tabs-bottom">
                                <li class="nav-item"><a href="#emp_profile" data-bs-toggle="tab" class="nav-link active">Profile</a></li>
                                <li class="nav-item"><a href="#emp_projects" data-bs-toggle="tab" class="nav-link">Projects</a></li>
                                <li class="nav-item"><a href="#bank_statutory" data-bs-toggle="tab" class="nav-link">Bank & Statutory <small class="text-danger">(Admin Only)</small></a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    
                    <div id="emp_profile" class="pro-overview tab-pane fade show active">
                        <div class="row">
                            <div class="col-md-6 d-flex">
                                <div class="card profile-box flex-fill">
                                    <div class="card-body">
                                        <h3 class="card-title">Personal Informations <a href="#" class="edit-icon text-primary float-end"><i class="ti ti-pencil"></i></a></h3>
                                        <ul class="personal-info list-unstyled">
                                            <li class="mb-2"><span class="title w-50">Passport No.</span> <span class="text">9876543210</span></li>
                                            <li class="mb-2"><span class="title w-50">Passport Exp Date.</span> <span class="text">9876543210</span></li>
                                            <li class="mb-2"><span class="title w-50">Tel</span> <span class="text">9876543210</span></li>
                                            <li class="mb-2"><span class="title w-50">Nationality</span> <span class="text">Indian</span></li>
                                            <li class="mb-2"><span class="title w-50">Religion</span> <span class="text">Christian</span></li>
                                            <li class="mb-2"><span class="title w-50">Marital status</span> <span class="text">Married</span></li>
                                            <li class="mb-2"><span class="title w-50">Employment of spouse</span> <span class="text">No</span></li>
                                            <li class="mb-2"><span class="title w-50">No. of children</span> <span class="text">2</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex">
                                <div class="card profile-box flex-fill">
                                    <div class="card-body">
                                        <h3 class="card-title">Emergency Contact <a href="#" class="edit-icon text-primary float-end"><i class="ti ti-pencil"></i></a></h3>
                                        <h5 class="section-title">Primary</h5>
                                        <ul class="personal-info list-unstyled">
                                            <li class="mb-2"><span class="title w-50">Name</span> <span class="text">John Doe</span></li>
                                            <li class="mb-2"><span class="title w-50">Relationship</span> <span class="text">Father</span></li>
                                            <li class="mb-2"><span class="title w-50">Phone</span> <span class="text">9876543210, 9876543210</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="emp_projects">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Project Information</h5>
                                <p class="text-muted">No projects assigned yet.</p>
                            </div>
                        </div>
                    </div>
                    </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>