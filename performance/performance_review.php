<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE TABLE
$table_sql = "CREATE TABLE IF NOT EXISTS `performance_reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `emp_code` varchar(50) NOT NULL,
  `emp_name` varchar(100),
  `department` varchar(100),
  `designation` varchar(100),
  `doj` date,
  `ro_name` varchar(100),
  `data_payload` JSON,
  `status` enum('Draft','Submitted') DEFAULT 'Submitted',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// 3. FETCH EMPLOYEE LOGIC
$emp_data = [
    'name' => '', 'department' => '', 'role' => '', 'joined_date' => '', 'emp_code' => ''
];
if (isset($_POST['search_emp'])) {
    $search_id = mysqli_real_escape_string($conn, $_POST['search_id']);
    $sql = "SELECT * FROM employees WHERE emp_code = '$search_id'";
    $result = mysqli_query($conn, $sql);
    if ($result && mysqli_num_rows($result) > 0) {
        $emp_data = mysqli_fetch_assoc($result);
    } else {
        echo "<script>alert('Employee not found!');</script>";
    }
}

// 4. SAVE REVIEW LOGIC
if (isset($_POST['save_review'])) {
    $emp_code = mysqli_real_escape_string($conn, $_POST['emp_code']);
    $emp_name = mysqli_real_escape_string($conn, $_POST['emp_name']);
    $dept = mysqli_real_escape_string($conn, $_POST['department']);
    $desig = mysqli_real_escape_string($conn, $_POST['designation']);
    $ro_name = mysqli_real_escape_string($conn, $_POST['ro_name']);
    $doj = $_POST['doj'] ?? NULL;

    $json_payload = json_encode($_POST, JSON_UNESCAPED_UNICODE);
    $json_payload_safe = mysqli_real_escape_string($conn, $json_payload);

    $sql = "INSERT INTO performance_reviews (emp_code, emp_name, department, designation, doj, ro_name, data_payload) 
            VALUES ('$emp_code', '$emp_name', '$dept', '$desig', '$doj', '$ro_name', '$json_payload_safe')";

    if (mysqli_query($conn, $sql)) {
        // Redirect with success message param
        header("Location: performance_review.php?msg=saved"); 
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Performance Review</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; margin-left: 110px; transition: margin-left 0.3s; }
        .page-wrapper { flex: 1; padding: 25px; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .table input.form-control, .table select.form-select, .table select.form-control { border: 1px solid #e3e3e3; padding: 5px; height: 35px; min-width: 80px; }
        .width-pixel { width: 50px; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        
        .grade-span .badge { margin: 2px; padding: 8px 12px; font-weight: normal; }
        .bg-inverse-danger { background: rgba(234, 84, 85, 0.1); color: #ea5455; }
        .bg-inverse-warning { background: rgba(255, 159, 67, 0.1); color: #ff9f43; }
        .bg-inverse-info { background: rgba(0, 207, 232, 0.1); color: #00cfe8; }
        .bg-inverse-purple { background: rgba(115, 103, 240, 0.1); color: #7367f0; }
        .bg-inverse-success { background: rgba(40, 199, 111, 0.1); color: #28c76f; }

        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php if(file_exists('../include/sidebar.php')) include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php if(file_exists('../include/header.php')) include '../include/header.php'; ?>

        <div class="page-wrapper">
            <div class="content">
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check me-2"></i> Review Saved Successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>

                <div class="card mb-3">
                    <div class="card-body">
                        <form method="POST" class="d-flex gap-2 align-items-center">
                            <label class="fw-bold">Fetch Employee:</label>
                            <input type="text" name="search_id" class="form-control w-auto" placeholder="Enter Emp ID (e.g. Emp-001)" required>
                            <button type="submit" name="search_emp" class="btn btn-dark"><i class="ti ti-search"></i> Fetch</button>
                        </form>
                    </div>
                </div>

                <form method="POST" action="performance_review.php">
                    <input type="hidden" name="save_review" value="1">

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center">
                            <h3 class="mb-2">Employee Basic Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3"><label class="form-label">Name</label><input type="text" name="emp_name" class="form-control" value="<?= htmlspecialchars($emp_data['name']) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Department</label><input type="text" name="department" class="form-control" value="<?= htmlspecialchars($emp_data['department']) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Designation</label><input type="text" name="designation" class="form-control" value="<?= htmlspecialchars($emp_data['role']) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Qualification</label><input type="text" name="qualification" class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3"><label class="form-label">Emp ID</label><input type="text" name="emp_code" class="form-control" value="<?= htmlspecialchars($emp_data['emp_code']) ?>"></div>
                                    <div class="mb-3"><label class="form-label">Date of Join</label><input type="date" name="doj" class="form-control" value="<?= $emp_data['joined_date'] ?>"></div>
                                    <div class="mb-3"><label class="form-label">Date of Confirmation</label><input type="date" name="doc" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">Previous Exp</label><input type="text" name="prev_exp" class="form-control"></div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3"><label class="form-label">RO's Name</label><input type="text" name="ro_name" class="form-control"></div>
                                    <div class="mb-3"><label class="form-label">RO Designation</label><input type="text" name="ro_designation" class="form-control"></div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Professional Excellence</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th><th>KRA</th><th>KPI</th><th>Weight</th>
                                            <th>Self %</th><th>Self Pts</th><th>RO %</th><th>RO Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="2">1</td><td rowspan="2">Production</td><td>Quality</td>
                                            <td><input type="text" class="form-control weight-prof" value="30" readonly></td>
                                            <td><input type="number" name="prof_exc[0][self]" class="form-control self-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control self-points-prof" readonly></td>
                                            <td><input type="number" name="prof_exc[0][ro]" class="form-control ro-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control ro-points-prof" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>TAT</td>
                                            <td><input type="text" class="form-control weight-prof" value="30" readonly></td>
                                            <td><input type="number" name="prof_exc[1][self]" class="form-control self-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control self-points-prof" readonly></td>
                                            <td><input type="number" name="prof_exc[1][ro]" class="form-control ro-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control ro-points-prof" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>2</td><td>Process Imp</td><td>PMS, New Ideas</td>
                                            <td><input type="text" class="form-control weight-prof" value="10" readonly></td>
                                            <td><input type="number" name="prof_exc[2][self]" class="form-control self-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control self-points-prof" readonly></td>
                                            <td><input type="number" name="prof_exc[2][ro]" class="form-control ro-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control ro-points-prof" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>3</td><td>Team Mgmt</td><td>Productivity</td>
                                            <td><input type="text" class="form-control weight-prof" value="5" readonly></td>
                                            <td><input type="number" name="prof_exc[3][self]" class="form-control self-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control self-points-prof" readonly></td>
                                            <td><input type="number" name="prof_exc[3][ro]" class="form-control ro-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control ro-points-prof" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>4</td><td>Knowledge Sharing</td><td>Team Productivity</td>
                                            <td><input type="text" class="form-control weight-prof" value="5" readonly></td>
                                            <td><input type="number" name="prof_exc[4][self]" class="form-control self-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control self-points-prof" readonly></td>
                                            <td><input type="number" name="prof_exc[4][ro]" class="form-control ro-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control ro-points-prof" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>5</td><td>Communication</td><td>Emails/Reports</td>
                                            <td><input type="text" class="form-control weight-prof" value="5" readonly></td>
                                            <td><input type="number" name="prof_exc[5][self]" class="form-control self-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control self-points-prof" readonly></td>
                                            <td><input type="number" name="prof_exc[5][ro]" class="form-control ro-score-prof" oninput="calcProf(this)"></td>
                                            <td><input type="text" class="form-control ro-points-prof" readonly></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-center">Total</td>
                                            <td><input type="text" class="form-control" value="85" readonly></td>
                                            <td></td>
                                            <td><input type="text" id="prof_self_total" class="form-control" readonly value="0"></td>
                                            <td></td>
                                            <td><input type="text" id="prof_ro_total" class="form-control" readonly value="0"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Personal Excellence</h3></div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th><th>Attribute</th><th>Indicator</th><th>Weight</th>
                                            <th>Self %</th><th>Self Pts</th><th>RO %</th><th>RO Pts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td rowspan="2">1</td><td rowspan="2">Attendance</td><td>Leaves</td>
                                            <td><input type="text" class="form-control weight-pers" value="2" readonly></td>
                                            <td><input type="number" name="pers_exc[0][self]" class="form-control self-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control self-points-pers" readonly></td>
                                            <td><input type="number" name="pers_exc[0][ro]" class="form-control ro-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control ro-points-pers" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Time Conscious</td>
                                            <td><input type="text" class="form-control weight-pers" value="2" readonly></td>
                                            <td><input type="number" name="pers_exc[1][self]" class="form-control self-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control self-points-pers" readonly></td>
                                            <td><input type="number" name="pers_exc[1][ro]" class="form-control ro-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control ro-points-pers" readonly></td>
                                        </tr>
                                        <tr>
                                            <td rowspan="2">2</td><td rowspan="2">Attitude</td><td>Collaboration</td>
                                            <td><input type="text" class="form-control weight-pers" value="2" readonly></td>
                                            <td><input type="number" name="pers_exc[2][self]" class="form-control self-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control self-points-pers" readonly></td>
                                            <td><input type="number" name="pers_exc[2][ro]" class="form-control ro-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control ro-points-pers" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>Professionalism</td>
                                            <td><input type="text" class="form-control weight-pers" value="2" readonly></td>
                                            <td><input type="number" name="pers_exc[3][self]" class="form-control self-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control self-points-pers" readonly></td>
                                            <td><input type="number" name="pers_exc[3][ro]" class="form-control ro-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control ro-points-pers" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>3</td><td>Policy</td><td>Adherence</td>
                                            <td><input type="text" class="form-control weight-pers" value="2" readonly></td>
                                            <td><input type="number" name="pers_exc[4][self]" class="form-control self-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control self-points-pers" readonly></td>
                                            <td><input type="number" name="pers_exc[4][ro]" class="form-control ro-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control ro-points-pers" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>4</td><td>Initiatives</td><td>Special Efforts</td>
                                            <td><input type="text" class="form-control weight-pers" value="2" readonly></td>
                                            <td><input type="number" name="pers_exc[5][self]" class="form-control self-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control self-points-pers" readonly></td>
                                            <td><input type="number" name="pers_exc[5][ro]" class="form-control ro-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control ro-points-pers" readonly></td>
                                        </tr>
                                        <tr>
                                            <td>5</td><td>Skills</td><td>Training</td>
                                            <td><input type="text" class="form-control weight-pers" value="3" readonly></td>
                                            <td><input type="number" name="pers_exc[6][self]" class="form-control self-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control self-points-pers" readonly></td>
                                            <td><input type="number" name="pers_exc[6][ro]" class="form-control ro-score-pers" oninput="calcPers(this)"></td>
                                            <td><input type="text" class="form-control ro-points-pers" readonly></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-center">Total</td>
                                            <td><input type="text" class="form-control" value="15" readonly></td>
                                            <td></td>
                                            <td><input type="text" id="pers_self_total" class="form-control" readonly value="0"></td>
                                            <td></td>
                                            <td><input type="text" id="pers_ro_total" class="form-control" readonly value="0"></td>
                                        </tr>
                                        <tr>
                                            <td colspan="3" class="text-center"><b>Total Percentage (%)</b></td>
                                            <td colspan="5"><input type="text" id="grand_total" class="form-control text-center" readonly value="0"></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <div class="text-center mt-3 grade-span">
                                    <h4>Grade</h4>
                                    <span class="badge bg-inverse-danger">Below 65 Poor</span> 
                                    <span class="badge bg-inverse-warning">65-74 Average</span> 
                                    <span class="badge bg-inverse-info">75-84 Satisfactory</span> 
                                    <span class="badge bg-inverse-purple">85-92 Good</span> 
                                    <span class="badge bg-inverse-success">Above 92 Excellent</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Special Initiatives, Achievements</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered" id="tbl_spec">
                                <thead><tr><th>#</th><th>By Self</th><th>RO Comment</th><th>HOD Comment</th><th><button type="button" class="btn btn-primary btn-sm" onclick="addRow('tbl_spec','spec')"><i class="fa fa-plus"></i></button></th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td><input type="text" name="spec[0][self]" class="form-control"></td><td><input type="text" name="spec[0][ro]" class="form-control"></td><td><input type="text" name="spec[0][hod]" class="form-control"></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Comments on Role (Alterations)</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered" id="tbl_alt">
                                <thead><tr><th>#</th><th>By Self</th><th>RO Comment</th><th>HOD Comment</th><th><button type="button" class="btn btn-primary btn-sm" onclick="addRow('tbl_alt','alt')"><i class="fa fa-plus"></i></button></th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td><input type="text" name="alt[0][self]" class="form-control"></td><td><input type="text" name="alt[0][ro]" class="form-control"></td><td><input type="text" name="alt[0][hod]" class="form-control"></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <?php 
                    $sections = [
                        ['title' => "Comments on Role (Self)", 'name' => 'str_self'],
                        ['title' => "Appraisee's Strengths (RO)", 'name' => 'str_ro'],
                        ['title' => "Appraisee's Strengths (HOD)", 'name' => 'str_hod']
                    ];
                    foreach($sections as $sec):
                    ?>
                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2"><?= $sec['title'] ?></h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered">
                                <thead><tr><th>#</th><th>Strengths</th><th>Areas for Improvement</th></tr></thead>
                                <tbody>
                                    <?php for($i=0; $i<3; $i++): ?>
                                    <tr><td><?=$i+1?></td><td><input type="text" name="<?=$sec['name']?>[<?=$i?>][st]" class="form-control"></td><td><input type="text" name="<?=$sec['name']?>[<?=$i?>][im]" class="form-control"></td></tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
                    <?php endforeach; ?>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Personal Goals</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered">
                                <thead><tr><th>#</th><th>Goal Achieved (Last Year)</th><th>Goal Set (Current Year)</th></tr></thead>
                                <tbody>
                                    <?php for($i=0; $i<3; $i++): ?>
                                    <tr><td><?=$i+1?></td><td><input type="text" name="pers_goal[<?=$i?>][last]" class="form-control"></td><td><input type="text" name="pers_goal[<?=$i?>][curr]" class="form-control"></td></tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Personal Updates</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered">
                                <thead><tr><th>#</th><th>Last Year</th><th>Yes/No</th><th>Details</th><th>Current Year</th><th>Yes/No</th><th>Details</th></tr></thead>
                                <tbody>
                                    <tr>
                                        <td>1</td><td>Married/Engaged?</td>
                                        <td><select name="upd[0][ly_yn]" class="form-select"><option>No</option><option>Yes</option></select></td>
                                        <td><input type="text" name="upd[0][ly_det]" class="form-control"></td>
                                        <td>Marriage Plans</td>
                                        <td><select name="upd[0][cy_yn]" class="form-select"><option>No</option><option>Yes</option></select></td>
                                        <td><input type="text" name="upd[0][cy_det]" class="form-control"></td>
                                    </tr>
                                    <tr>
                                        <td>2</td><td>Higher Studies?</td>
                                        <td><select name="upd[1][ly_yn]" class="form-select"><option>No</option><option>Yes</option></select></td>
                                        <td><input type="text" name="upd[1][ly_det]" class="form-control"></td>
                                        <td>Plans?</td>
                                        <td><select name="upd[1][cy_yn]" class="form-select"><option>No</option><option>Yes</option></select></td>
                                        <td><input type="text" name="upd[1][cy_det]" class="form-control"></td>
                                    </tr>
                                    </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Professional Goals (Last Year)</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered" id="tbl_prof_last">
                                <thead><tr><th>#</th><th>By Self</th><th>RO Comment</th><th>HOD Comment</th><th><button type="button" class="btn btn-primary btn-sm" onclick="addRow('tbl_prof_last','prof_last')"><i class="fa fa-plus"></i></button></th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td><input type="text" name="prof_last[0][self]" class="form-control"></td><td><input type="text" name="prof_last[0][ro]" class="form-control"></td><td><input type="text" name="prof_last[0][hod]" class="form-control"></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Professional Goals (Forthcoming)</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered" id="tbl_prof_curr">
                                <thead><tr><th>#</th><th>By Self</th><th>RO Comment</th><th>HOD Comment</th><th><button type="button" class="btn btn-primary btn-sm" onclick="addRow('tbl_prof_curr','prof_curr')"><i class="fa fa-plus"></i></button></th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td><input type="text" name="prof_curr[0][self]" class="form-control"></td><td><input type="text" name="prof_curr[0][ro]" class="form-control"></td><td><input type="text" name="prof_curr[0][hod]" class="form-control"></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">Training Requirements</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered" id="tbl_train">
                                <thead><tr><th>#</th><th>By Self</th><th>RO Comment</th><th>HOD Comment</th><th><button type="button" class="btn btn-primary btn-sm" onclick="addRow('tbl_train','train')"><i class="fa fa-plus"></i></button></th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td><input type="text" name="train[0][self]" class="form-control"></td><td><input type="text" name="train[0][ro]" class="form-control"></td><td><input type="text" name="train[0][hod]" class="form-control"></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">General Comments</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered" id="tbl_gen">
                                <thead><tr><th>#</th><th>Self</th><th>RO</th><th>HOD</th><th><button type="button" class="btn btn-primary btn-sm" onclick="addRow('tbl_gen','gen')"><i class="fa fa-plus"></i></button></th></tr></thead>
                                <tbody>
                                    <tr><td>1</td><td><input type="text" name="gen[0][self]" class="form-control"></td><td><input type="text" name="gen[0][ro]" class="form-control"></td><td><input type="text" name="gen[0][hod]" class="form-control"></td><td></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">For RO's Use Only</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered">
                                <thead><tr><th>Question</th><th>Yes/No</th><th>If Yes - Details</th></tr></thead>
                                <tbody>
                                    <?php 
                                    $ro_q = ["Work related Issues", "Leave Issues", "Stability Issues", "Non-supportive attitude", "Any other points", "Overall Comment"];
                                    foreach($ro_q as $i => $q): 
                                    ?>
                                    <tr>
                                        <td><?= $q ?></td>
                                        <td><select name="ro_use[<?=$i?>][yn]" class="form-select"><option>No</option><option>Yes</option></select></td>
                                        <td><input type="text" name="ro_use[<?=$i?>][det]" class="form-control"></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0">
                        <div class="card-header border-bottom-0 text-center"><h3 class="mb-2">For HRD's Use Only</h3></div>
                        <div class="card-body p-0">
                            <table class="table table-bordered">
                                <thead><tr><th>Parameter</th><th>Avail Pts</th><th>Pts Scored</th><th>RO Comment</th></tr></thead>
                                <tbody>
                                    <?php 
                                    $hrd_q = ["KRAs Target", "Professional Skills", "Personal Skills", "Special Achievements", "Overall Total Score"];
                                    foreach($hrd_q as $i => $q): 
                                    ?>
                                    <tr>
                                        <td><?= $q ?></td>
                                        <td><input type="text" name="hrd[<?=$i?>][avail]" class="form-control"></td>
                                        <td><input type="text" name="hrd[<?=$i?>][score]" class="form-control"></td>
                                        <td><input type="text" name="hrd[<?=$i?>][comm]" class="form-control"></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section class="card border-0 mb-4">
                        <div class="card-body p-0">
                            <table class="table table-bordered">
                                <thead><tr><th>Role</th><th>Name</th><th>Date</th></tr></thead>
                                <tbody>
                                    <tr><td>Employee</td><td><input type="text" name="sig_emp_name" class="form-control"></td><td><input type="date" name="sig_emp_date" class="form-control"></td></tr>
                                    <tr><td>RO</td><td><input type="text" name="sig_ro_name" class="form-control"></td><td><input type="date" name="sig_ro_date" class="form-control"></td></tr>
                                    <tr><td>HOD</td><td><input type="text" name="sig_hod_name" class="form-control"></td><td><input type="date" name="sig_hod_date" class="form-control"></td></tr>
                                    <tr><td>HRD</td><td><input type="text" name="sig_hrd_name" class="form-control"></td><td><input type="date" name="sig_hrd_date" class="form-control"></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div class="text-center mb-5">
                        <button type="submit" class="btn btn-primary btn-lg"><i class="ti ti-device-floppy me-2"></i> Submit Review</button>
                    </div>

                </form>
            </div>
            
            <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
                <p class="mb-0">2014 - 2026 © SmartHR.</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Professional Calculation
        function calcProf(input) {
            let row = input.closest('tr');
            let weight = parseFloat(row.querySelector('.weight-prof').value) || 0;
            let val = parseFloat(input.value) || 0;
            let points = (val * weight) / 100;
            
            if(input.classList.contains('self-score-prof')) {
                row.querySelector('.self-points-prof').value = points.toFixed(1);
                updateSum('self-points-prof', 'prof_self_total');
            } else {
                row.querySelector('.ro-points-prof').value = points.toFixed(1);
                updateSum('ro-points-prof', 'prof_ro_total');
            }
            updateGrandTotal();
        }

        // Personal Calculation
        function calcPers(input) {
            let row = input.closest('tr');
            let weight = parseFloat(row.querySelector('.weight-pers').value) || 0;
            let val = parseFloat(input.value) || 0;
            let points = (val * weight) / 100;
            
            if(input.classList.contains('self-score-pers')) {
                row.querySelector('.self-points-pers').value = points.toFixed(1);
                updateSum('self-points-pers', 'pers_self_total');
            } else {
                row.querySelector('.ro-points-pers').value = points.toFixed(1);
                updateSum('ro-points-pers', 'pers_ro_total');
            }
            updateGrandTotal();
        }

        function updateSum(cls, id) {
            let s=0; document.querySelectorAll('.'+cls).forEach(e=>s+=parseFloat(e.value)||0);
            document.getElementById(id).value = s.toFixed(1);
        }

        function updateGrandTotal() {
            let t = (parseFloat(document.getElementById('prof_self_total').value)||0) + 
                    (parseFloat(document.getElementById('pers_self_total').value)||0);
            document.getElementById('grand_total').value = t.toFixed(1);
        }

        // Add Row Function
        function addRow(tableId, namePrefix) {
            let tbody = document.querySelector('#'+tableId+' tbody');
            let count = tbody.rows.length;
            let row = tbody.insertRow();
            row.innerHTML = `
                <td>${count + 1}</td>
                <td><input type="text" name="${namePrefix}[${count}][self]" class="form-control"></td>
                <td><input type="text" name="${namePrefix}[${count}][ro]" class="form-control"></td>
                <td><input type="text" name="${namePrefix}[${count}][hod]" class="form-control"></td>
                <td></td>
            `;
        }
    </script>
</body>
</html>