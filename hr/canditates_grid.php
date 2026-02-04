<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. FETCH CANDIDATES
$candidates = [];
$sql = "SELECT * FROM candidates ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
if($result) {
    while($row = mysqli_fetch_assoc($result)) {
        $candidates[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Candidates Grid - SmartHR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; margin-left: 110px; transition: margin-left 0.3s; }
        .page-wrapper { flex: 1; padding: 25px; }
        
        /* Card & Grid Styles */
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-body { padding: 1.5rem; }
        .bg-light { background-color: #f8f9fa !important; }
        
        /* Status Badges */
        .badge { font-weight: 500; padding: 5px 10px; }
        .bg-purple { background-color: #7460ee !important; color: #fff; }
        .bg-pink { background-color: #fc6075 !important; color: #fff; }
        .bg-info { background-color: #00CFE8 !important; color: #fff; }
        .bg-warning { background-color: #ff9b44 !important; color: #fff; }
        .bg-success { background-color: #28C76F !important; color: #fff; }
        .bg-danger { background-color: #EA5455 !important; color: #fff; }
        
        /* Avatar */
        .avatar-lg { width: 48px; height: 48px; }
        .avatar-lg img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        
        /* Typography */
        .fs-16 { font-size: 16px; }
        .fs-14 { font-size: 14px; }
        .fs-13 { font-size: 13px; }
        .fs-10 { font-size: 10px; }
        .text-gray { color: #888; }
        .fw-semibold { font-weight: 600; }
        .fw-medium { font-weight: 500; }
        
        /* Buttons */
        .btn-icon { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; padding: 0; }
        .btn-white { background: #fff; border: 1px solid #e3e3e3; color: #333; }
        .bg-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        
        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php 
        if(file_exists('../../include/sidebar.php')) include '../../include/sidebar.php';
        elseif(file_exists('../include/sidebar.php')) include '../include/sidebar.php';
    ?>

    <div class="main-content-wrapper">
        
        <?php 
            if(file_exists('../../include/header.php')) include '../../include/header.php';
            elseif(file_exists('../workack/include/header.php')) include '../workack/include/header.php';
        ?>

        <div class="page-wrapper">
            <div class="content">
                
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Candidates</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.html"><i class="ti ti-smart-home"></i></a></li>
                                <li class="breadcrumb-item">Recruitment</li>
                                <li class="breadcrumb-item active" aria-current="page">Candidates Grid</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                        <div class="me-2 mb-2">
                            <div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
                                <a href="#" class="btn btn-icon btn-sm me-1"><i class="ti ti-layout-kanban"></i></a>
                                <a href="#" class="btn btn-icon btn-sm me-1"><i class="ti ti-list-tree"></i></a>
                                <a href="#" class="btn btn-icon btn-sm active bg-primary text-white"><i class="ti ti-layout-grid"></i></a>
                            </div>
                        </div>
                        <div class="me-2 mb-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a></li>
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                            <h5>Candidates Grid</h5>
                            <div class="d-flex align-items-center flex-wrap row-gap-3">
                                <div class="me-3">
                                    <div class="input-icon position-relative">
                                        <span class="input-icon-addon"><i class="ti ti-calendar text-gray-9"></i></span>
                                        <input type="text" class="form-control" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                    </div>
                                </div>
                                <div class="dropdown me-3">
                                    <a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">Role</a>
                                    <ul class="dropdown-menu dropdown-menu-end p-3">
                                        <li><a href="#" class="dropdown-item rounded-1">Accountant</a></li>
                                        <li><a href="#" class="dropdown-item rounded-1">Developer</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown me-3">
                                    <a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">Select Status</a>
                                    <ul class="dropdown-menu dropdown-menu-end p-3">
                                        <li><a href="#" class="dropdown-item rounded-1">Scheduled</a></li>
                                        <li><a href="#" class="dropdown-item rounded-1">Hired</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php if(empty($candidates)): ?>
                        <div class="col-12 text-center p-5"><p>No candidates found.</p></div>
                    <?php else: ?>
                        <?php foreach($candidates as $cand): 
                            // Status Color Logic
                            $statusClass = 'bg-secondary';
                            $icon = 'ti-point-filled';
                            switch($cand['status']) {
                                case 'New': $statusClass = 'bg-purple'; break;
                                case 'Scheduled': $statusClass = 'bg-pink'; break;
                                case 'Interviewed': $statusClass = 'bg-info'; break;
                                case 'Offered': $statusClass = 'bg-warning'; break;
                                case 'Hired': $statusClass = 'bg-success'; break;
                                case 'Rejected': $statusClass = 'bg-danger'; break;
                            }
                            // Default Image
                            $img = !empty($cand['image']) ? $cand['image'] : '../../assets/img/profiles/avatar-01.jpg';
                        ?>
                        <div class="col-xxl-3 col-xl-4 col-md-6">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="d-flex align-items-center flex-shrink-0">
                                            <a href="#" class="avatar avatar-lg rounded-circle me-2">
                                                <img src="<?= htmlspecialchars($img) ?>" alt="img" onerror="this.src='../../assets/img/profiles/avatar-01.jpg'">
                                            </a>
                                            <div class="d-flex flex-column">
                                                <div class="d-flex flex-wrap mb-1">
                                                    <h6 class="fs-16 fw-semibold me-1"><a href="#"><?= htmlspecialchars($cand['first_name'] . ' ' . $cand['last_name']) ?></a></h6>
                                                    <span class="badge bg-light text-primary border"><?= htmlspecialchars($cand['candidate_code']) ?></span>
                                                </div>
                                                <p class="text-gray fs-13 fw-normal"><?= htmlspecialchars($cand['email']) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="bg-light rounded p-2">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <h6 class="text-gray fs-14 fw-normal">Applied Role</h6>
                                            <span class="text-dark fs-14 fw-medium"><?= htmlspecialchars($cand['role']) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <h6 class="text-gray fs-14 fw-normal">Applied Date</h6>
                                            <span class="text-dark fs-14 fw-medium"><?= date('d M Y', strtotime($cand['applied_date'])) ?></span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <h6 class="text-gray fs-14 fw-normal">Status</h6>
                                            <span class="fs-10 fw-medium badge <?= $statusClass ?>"> <i class="ti <?= $icon ?>"></i> <?= htmlspecialchars($cand['status']) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    
                    <div class="col-md-12">
                        <div class="text-center mb-4">
                            <a href="#" class="btn btn-primary d-inline-flex align-items-center"><i class="ti ti-loader-3 me-1"></i>Load More</a>
                        </div>
                    </div>
                </div>
                </div>
            
            <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
                <p class="mb-0">2014 - 2026 © SmartHR.</p>
                <p>Designed & Developed By <a href="#" class="text-primary">Dreams</a></p>
            </div>
        </div>
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>lucide.createIcons();</script>
</body>
</html>