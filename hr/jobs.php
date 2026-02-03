<?php
session_start();

// 1. DATABASE CONNECTION
// Checking multiple paths to ensure connection works from 'hr' folder
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. HANDLE FORM SUBMISSION (ADD JOB)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_job_submit'])) {
    
    // Sanitize Basic Info
    $title = mysqli_real_escape_string($conn, $_POST['job_title']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);
    $cat   = mysqli_real_escape_string($conn, $_POST['category']);
    $type  = mysqli_real_escape_string($conn, $_POST['job_type']);
    $level = mysqli_real_escape_string($conn, $_POST['job_level']);
    $exp   = mysqli_real_escape_string($conn, $_POST['experience']);
    $qual  = mysqli_real_escape_string($conn, $_POST['qualification']);
    $gen   = mysqli_real_escape_string($conn, $_POST['gender']);
    $min_s = mysqli_real_escape_string($conn, $_POST['min_salary']);
    $max_s = mysqli_real_escape_string($conn, $_POST['max_salary']);
    
    // Handle Date (dd/mm/yyyy -> yyyy-mm-dd)
    $expiry = NULL;
    if(!empty($_POST['expiry_date'])) {
        $date = str_replace('/', '-', $_POST['expiry_date']);
        $expiry = date('Y-m-d', strtotime($date));
    }
    
    $skills= mysqli_real_escape_string($conn, $_POST['skills']);
    
    // Sanitize Location Info
    $addr  = mysqli_real_escape_string($conn, $_POST['address']);
    $country= mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city  = mysqli_real_escape_string($conn, $_POST['city']);
    $zip   = mysqli_real_escape_string($conn, $_POST['zipcode']);
    
    // Handle Image Upload
    $imagePath = 'assets/img/icons/apple.svg'; // Default fallback
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $target_dir = "../uploads/jobs/"; // Upload to workack/uploads/jobs/
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $fileExtension = strtolower(pathinfo($_FILES["profile_image"]["name"], PATHINFO_EXTENSION));
        $fileName = time() . "_" . uniqid() . "." . $fileExtension;
        $target_file = $target_dir . $fileName;
        
        if (move_uploaded_file($_FILES["profile_image"]["tmp_name"], $target_file)) {
            // Save path relative to the hr folder for easy display later
            $imagePath = "../uploads/jobs/" . $fileName;
        }
    }

    $sql = "INSERT INTO jobs (title, image, description, category, job_type, level, experience, qualification, gender, salary_min, salary_max, expiry_date, skills, address, country, state, city, zipcode) 
            VALUES ('$title', '$imagePath', '$desc', '$cat', '$type', '$level', '$exp', '$qual', '$gen', '$min_s', '$max_s', '$expiry', '$skills', '$addr', '$country', '$state', '$city', '$zip')";

    if (mysqli_query($conn, $sql)) {
        header("Location: jobs.php?msg=success"); exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// 3. FETCH JOBS
$jobs = [];
$res = mysqli_query($conn, "SELECT * FROM jobs ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $jobs[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Jobs - HR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; margin-left: 110px; transition: margin-left 0.3s; }
        .page-wrapper { flex: 1; padding: 25px; }
        
        /* Card Styles */
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-body { padding: 1.5rem; }
        .bg-light { background-color: #f8f9fa !important; }
        
        /* Badges */
        .badge-pink-transparent { background: rgba(252, 96, 117, 0.1); color: #fc6075; padding: 5px 10px; }
        .bg-secondary-transparent { background: rgba(116, 96, 238, 0.1); color: #7460ee; padding: 5px 10px; }
        
        /* Icons */
        .avatar-lg { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
        .bg-gray { background-color: #ffffff; border: 1px solid #e3e3e3; border-radius: 8px; }
        .avatar-lg img { width: 28px; height: 28px; object-fit: contain; }
        
        /* Progress */
        .progress-xs { height: 6px; }
        
        /* Modal Profile Upload */
        .profile-upload .avatar-xxl { width: 80px; height: 80px; }
        .profile-upload .avatar-xxl img { width: 100%; height: 100%; object-fit: cover; }
        
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        
        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php 
        if(file_exists('../include/sidebar.php')) include '../include/sidebar.php'; 
    ?>

    <div class="main-content-wrapper">
        
        <?php 
            if(file_exists('../include/header.php')) include '../include/header.php';
        ?>

        <div class="page-wrapper">
            <div class="content">
                
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Jobs</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="index.html"><i class="ti ti-smart-home"></i></a></li>
                                <li class="breadcrumb-item">Recruitment</li>
                                <li class="breadcrumb-item active" aria-current="page">Jobs</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                        <div class="me-2 mb-2">
                            <div class="d-flex align-items-center border bg-white rounded p-1 me-2 icon-list">
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
                                    <li><a href="#" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a></li>
                                    <li><a href="#" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_post" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Post Job</a>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <h5>Job Grid</h5>
                            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                                <div class="me-3">
                                    <div class="input-icon position-relative">
                                        <span class="input-icon-addon"><i class="ti ti-calendar text-gray-9"></i></span>
                                        <input type="text" class="form-control" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                    </div>
                                </div>
                                <div class="dropdown me-3">
                                    <a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">Role</a>
                                    <ul class="dropdown-menu dropdown-menu-end p-3">
                                        <li><a href="#" class="dropdown-item rounded-1">Senior IOS Developer</a></li>
                                        <li><a href="#" class="dropdown-item rounded-1">Junior PHP Developer</a></li>
                                    </ul>
                                </div>
                                <div class="dropdown me-3">
                                    <a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">Status</a>
                                    <ul class="dropdown-menu dropdown-menu-end p-3">
                                        <li><a href="#" class="dropdown-item rounded-1">Active</a></li>
                                        <li><a href="#" class="dropdown-item rounded-1">Inactive</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <?php foreach($jobs as $job): 
                        $total = $job['total_vacancies'] > 0 ? $job['total_vacancies'] : 1;
                        $filled = $job['filled_vacancies'];
                        $percent = ($filled / $total) * 100;
                        
                        // Image Path Logic
                        $img = !empty($job['image']) ? $job['image'] : '../assets/img/icons/apple.svg';
                    ?>
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <div class="card bg-light">
                                    <div class="card-body p-3">
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="me-2">
                                                <span class="avatar avatar-lg bg-gray">
                                                    <img src="<?= htmlspecialchars($img) ?>" class="w-auto h-auto" alt="icon" onerror="this.src='../assets/img/icons/apple.svg'">
                                                </span>
                                            </a>
                                            <div>
                                                <h6 class="fw-medium mb-1 text-truncate" style="max-width:150px;"><a href="#"><?= htmlspecialchars($job['title']) ?></a></h6>
                                                <p class="fs-12 text-gray-5 fw-normal"><?= $job['applicants'] ?> Applicants</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex flex-column mb-3">
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i class="ti ti-map-pin-check text-gray-5 me-2"></i>
                                        <?= htmlspecialchars($job['city'] . ', ' . $job['country']) ?>
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center mb-2">
                                        <i class="ti ti-currency-dollar text-gray-5 me-2"></i>
                                        <?= number_format((float)$job['salary_min']) ?> - <?= number_format((float)$job['salary_max']) ?>
                                    </p>
                                    <p class="text-dark d-inline-flex align-items-center">
                                        <i class="ti ti-briefcase text-gray-5 me-2"></i>
                                        <?= htmlspecialchars($job['experience']) ?>
                                    </p>
                                </div>
                                <div class="mb-3">
                                    <span class="badge badge-pink-transparent me-2"><?= htmlspecialchars($job['job_type']) ?></span>
                                    <span class="badge bg-secondary-transparent"><?= htmlspecialchars($job['level']) ?></span>
                                </div>
                                <div class="progress progress-xs mb-2">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $percent ?>%"></div>
                                </div>
                                <div><p class="fs-12 text-gray-5 fw-normal"><?= $filled ?> of <?= $total ?> filled</p></div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

            </div>
            
            <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
                <p class="mb-0">2014 - 2026 &copy; SmartHR.</p>
                <p>Designed &amp; Developed By <a href="#" class="text-primary">Dreams</a></p>
            </div>
        </div>
        </div>

    <div class="modal fade" id="add_post">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Post Job</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="jobs.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_job_submit" value="1">
                    <div class="modal-body pb-0">
                        
                        <div class="contact-grids-tab pt-0">
                            <ul class="nav nav-underline" id="myTab" role="tablist">
                                <li class="nav-item" role="presentation"><button class="nav-link active" id="info-tab" data-bs-toggle="tab" data-bs-target="#basic-info" type="button">Basic Information</button></li>
                                <li class="nav-item" role="presentation"><button class="nav-link" id="address-tab" data-bs-toggle="tab" data-bs-target="#address" type="button">Location</button></li>
                            </ul>
                        </div>

                        <div class="tab-content" id="myTabContent">
                            
                            <div class="tab-pane fade show active" id="basic-info" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <div class="d-flex align-items-center bg-light w-100 rounded p-3 mb-4">
                                            <div class="avatar avatar-xxl rounded-circle border border-dashed me-2 flex-shrink-0">
                                                <img src="../assets/img/profiles/avatar-30.jpg" alt="img" class="rounded-circle" style="width:60px; height:60px;">
                                            </div>
                                            <div class="profile-upload">
                                                <div class="mb-2"><h6 class="mb-1">Upload Profile Image</h6><p class="fs-12">Image should be below 4 mb</p></div>
                                                <input type="file" name="profile_image" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mb-3"><label class="form-label">Job Title *</label><input type="text" name="job_title" class="form-control" required></div>
                                    <div class="col-md-12 mb-3"><label class="form-label">Job Description</label><textarea name="description" rows="3" class="form-control"></textarea></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Job Category</label>
                                        <select name="category" class="form-select"><option>Select</option><option>IOS</option><option>Web & Application</option><option>Networking</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Job Type</label>
                                        <select name="job_type" class="form-select"><option>Full Time</option><option>Part Time</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Job Level</label>
                                        <select name="job_level" class="form-select"><option>Senior</option><option>Junior</option><option>Manager</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Experience</label>
                                        <select name="experience" class="form-select"><option>Entry Level</option><option>Mid Level</option><option>Expert</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Qualification</label>
                                        <select name="qualification" class="form-select"><option>Bachelor Degree</option><option>Master Degree</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Gender</label>
                                        <select name="gender" class="form-select"><option>Male</option><option>Female</option><option>Any</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Min. Salary</label>
                                        <select name="min_salary" class="form-select"><option>10000</option><option>20000</option><option>30000</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Max. Salary</label>
                                        <select name="max_salary" class="form-select"><option>40000</option><option>50000</option><option>60000</option></select>
                                    </div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Expired Date</label><input type="date" name="expiry_date" class="form-control"></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Required Skills</label><input type="text" name="skills" class="form-control"></div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary" onclick="document.getElementById('address-tab').click()">Next</button>
                                </div>
                            </div>

                            <div class="tab-pane fade" id="address" role="tabpanel">
                                <div class="row mt-3">
                                    <div class="col-md-12 mb-3"><label class="form-label">Address</label><input type="text" name="address" class="form-control"></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Country</label><select name="country" class="form-select"><option>USA</option><option>UK</option><option>India</option></select></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">State</label><select name="state" class="form-select"><option>California</option><option>New York</option><option>London</option></select></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">City</label><select name="city" class="form-select"><option>Los Angeles</option><option>New York</option><option>Bristol</option></select></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Zip Code</label><input type="text" name="zipcode" class="form-control"></div>
                                    <div class="col-md-12">
                                        <div class="map-grid mb-3"><iframe src="https://maps.google.com/maps?q=new+york&t=&z=13&ie=UTF8&iwloc=&output=embed" style="border:0; width:100%; height:200px;" allowfullscreen></iframe></div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Post Job</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple icon renderer (if using Lucide, otherwise Tabler icons work via CSS)
    </script>
</body>
</html>