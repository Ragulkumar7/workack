<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE TABLE
$table_sql = "CREATE TABLE IF NOT EXISTS `performance_appraisals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(100),
  `designation` varchar(100),
  `department` varchar(100),
  `appraisal_date` date,
  `technical_competencies` JSON,
  `organizational_competencies` JSON,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// 3. HANDLE FORM SUBMISSIONS

// ADD APPRAISAL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_appraisal'])) {
    $emp_id = intval($_POST['employee_id']);
    $date = $_POST['appraisal_date'];
    $status = $_POST['status'];
    
    // Fetch emp details
    $emp_q = mysqli_query($conn, "SELECT name, role, department FROM employees WHERE id = $emp_id");
    $emp_data = mysqli_fetch_assoc($emp_q);
    $emp_name = $emp_data['name'];
    $desig = $emp_data['role'];
    $dept = $emp_data['department'];

    // JSON Data
    $tech = json_encode($_POST['tech'] ?? []);
    $org = json_encode($_POST['org'] ?? []);

    $sql = "INSERT INTO performance_appraisals (employee_id, employee_name, designation, department, appraisal_date, technical_competencies, organizational_competencies, status) 
            VALUES ('$emp_id', '$emp_name', '$desig', '$dept', '$date', '$tech', '$org', '$status')";
    
    if(mysqli_query($conn, $sql)) { header("Location: performance_appraisal.php?msg=added"); exit(); }
}

// EDIT APPRAISAL
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_appraisal'])) {
    $id = intval($_POST['appraisal_id']);
    $emp_id = intval($_POST['employee_id']);
    $date = $_POST['appraisal_date'];
    $status = $_POST['status'];

    // Update details if employee changed
    $emp_q = mysqli_query($conn, "SELECT name, role, department FROM employees WHERE id = $emp_id");
    $emp_data = mysqli_fetch_assoc($emp_q);
    $emp_name = $emp_data['name'];
    $desig = $emp_data['role'];
    $dept = $emp_data['department'];

    $tech = json_encode($_POST['tech'] ?? []);
    $org = json_encode($_POST['org'] ?? []);

    $sql = "UPDATE performance_appraisals SET 
            employee_id='$emp_id', employee_name='$emp_name', designation='$desig', department='$dept', 
            appraisal_date='$date', technical_competencies='$tech', organizational_competencies='$org', status='$status' 
            WHERE id=$id";

    if(mysqli_query($conn, $sql)) { header("Location: performance_appraisal.php?msg=updated"); exit(); }
}

// DELETE APPRAISAL
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM performance_appraisals WHERE id=$id");
    header("Location: performance_appraisal.php?msg=deleted"); exit();
}

// 4. FETCH DATA
// Fetch Employees for Dropdown
$employees = [];
$e_res = mysqli_query($conn, "SELECT id, name FROM employees WHERE status='Active'");
if($e_res) { while($row = mysqli_fetch_assoc($e_res)) $employees[] = $row; }

// Fetch Appraisals
$appraisals = [];
$res = mysqli_query($conn, "SELECT * FROM performance_appraisals ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $appraisals[] = $row; }

// Configuration Arrays (To generate tables dynamically)
$tech_indicators = [
    "Customer Experience" => "Intermediate",
    "Marketing" => "Advanced",
    "Management" => "Advanced",
    "Administration" => "Advanced",
    "Presentation Skill" => "Expert / Leader",
    "Quality Of Work" => "Expert / Leader",
    "Efficiency" => "Expert / Leader"
];

$org_indicators = [
    "Integrity" => "Beginner",
    "Professionalism" => "Beginner",
    "Team Work" => "Intermediate",
    "Critical Thinking" => "Advanced",
    "Conflict Management" => "Intermediate",
    "Attendance" => "Intermediate",
    "Ability To Meet Deadline" => "Advanced"
];

$options = ["None", "Beginner", "Intermediate", "Advanced", "Expert / Leader"];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Performance Appraisal - HR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; margin-left: 110px; transition: margin-left 0.3s; }
        .page-wrapper { flex: 1; padding: 25px; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .badge-success { background-color: #28c76f !important; color: #fff; }
        .nav-pills .nav-link.active { background-color: #FF9B44; }
        .nav-pills .nav-link { color: #333; }
        
        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php if(file_exists('../include/sidebar.php')) include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php if(file_exists('../include/header.php')) include '../include/header.php'; ?>

        <div class="page-wrapper">
            <div class="content">
                
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Performance Appraisal</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Appraisal List</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_performance_appraisal" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Appraisal</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Performance Appraisal List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <th>Appraisal Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($appraisals)): ?>
                                        <tr><td colspan="6" class="text-center p-4">No appraisals found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($appraisals as $app): 
                                            $appJson = htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-secondary rounded-circle text-white me-2"><?= strtoupper(substr($app['employee_name'],0,2)) ?></span>
                                                    <h6 class="fw-medium mb-0"><?= htmlspecialchars($app['employee_name']) ?></h6>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($app['designation']) ?></td>
                                            <td><?= htmlspecialchars($app['department']) ?></td>
                                            <td><?= date('d M Y', strtotime($app['appraisal_date'])) ?></td>
                                            <td><span class="badge badge-success"><?= htmlspecialchars($app['status']) ?></span></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editAppraisal(<?= $appJson ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="performance_appraisal.php?delete_id=<?= $app['id'] ?>" onclick="return confirm('Delete this appraisal?')" class="text-danger"><i class="ti ti-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="add_performance_appraisal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Appraisal</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="performance_appraisal.php" method="POST">
                    <input type="hidden" name="add_appraisal" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee</label>
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach($employees as $e): ?>
                                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Appraisal Date</label>
                                <input type="date" name="appraisal_date" class="form-control" required>
                            </div>
                            
                            <div class="col-md-12">
                                <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tech_add" type="button">Technical</button></li>
                                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#org_add" type="button">Organizational</button></li>
                                </ul>
                            </div>

                            <div class="col-md-12">
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="tech_add">
                                        <div class="card"><div class="card-body p-0 table-responsive">
                                            <table class="table"><thead class="thead-light"><tr><th>Indicator</th><th>Expected</th><th>Set Value</th></tr></thead>
                                            <tbody>
                                                <?php foreach($tech_indicators as $key => $expect): $slug = str_replace(' ','_',$key); ?>
                                                <tr>
                                                    <td><?= $key ?></td><td><?= $expect ?></td>
                                                    <td>
                                                        <select name="tech[<?= $slug ?>]" class="form-select">
                                                            <?php foreach($options as $opt): ?><option value="<?= $opt ?>"><?= $opt ?></option><?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody></table>
                                        </div></div>
                                    </div>
                                    <div class="tab-pane fade" id="org_add">
                                        <div class="card"><div class="card-body p-0 table-responsive">
                                            <table class="table"><thead class="thead-light"><tr><th>Indicator</th><th>Expected</th><th>Set Value</th></tr></thead>
                                            <tbody>
                                                <?php foreach($org_indicators as $key => $expect): $slug = str_replace(' ','_',$key); ?>
                                                <tr>
                                                    <td><?= $key ?></td><td><?= $expect ?></td>
                                                    <td>
                                                        <select name="org[<?= $slug ?>]" class="form-select">
                                                            <?php foreach($options as $opt): ?><option value="<?= $opt ?>"><?= $opt ?></option><?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody></table>
                                        </div></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Appraisal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_performance_appraisal">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Appraisal</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="performance_appraisal.php" method="POST">
                    <input type="hidden" name="edit_appraisal" value="1">
                    <input type="hidden" name="appraisal_id" id="edit_id">
                    
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Employee</label>
                                <select name="employee_id" id="edit_emp_id" class="form-select" required>
                                    <?php foreach($employees as $e): ?>
                                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Appraisal Date</label>
                                <input type="date" name="appraisal_date" id="edit_date" class="form-control" required>
                            </div>
                            
                            <div class="col-md-12">
                                <ul class="nav nav-pills mb-3" role="tablist">
                                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tech_edit" type="button">Technical</button></li>
                                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#org_edit" type="button">Organizational</button></li>
                                </ul>
                            </div>

                            <div class="col-md-12">
                                <div class="tab-content">
                                    <div class="tab-pane fade show active" id="tech_edit">
                                        <div class="card"><div class="card-body p-0 table-responsive">
                                            <table class="table"><thead class="thead-light"><tr><th>Indicator</th><th>Set Value</th></tr></thead>
                                            <tbody>
                                                <?php foreach($tech_indicators as $key => $expect): $slug = str_replace(' ','_',$key); ?>
                                                <tr>
                                                    <td><?= $key ?></td>
                                                    <td>
                                                        <select name="tech[<?= $slug ?>]" id="edit_tech_<?= $slug ?>" class="form-select">
                                                            <?php foreach($options as $opt): ?><option value="<?= $opt ?>"><?= $opt ?></option><?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody></table>
                                        </div></div>
                                    </div>
                                    <div class="tab-pane fade" id="org_edit">
                                        <div class="card"><div class="card-body p-0 table-responsive">
                                            <table class="table"><thead class="thead-light"><tr><th>Indicator</th><th>Set Value</th></tr></thead>
                                            <tbody>
                                                <?php foreach($org_indicators as $key => $expect): $slug = str_replace(' ','_',$key); ?>
                                                <tr>
                                                    <td><?= $key ?></td>
                                                    <td>
                                                        <select name="org[<?= $slug ?>]" id="edit_org_<?= $slug ?>" class="form-select">
                                                            <?php foreach($options as $opt): ?><option value="<?= $opt ?>"><?= $opt ?></option><?php endforeach; ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody></table>
                                        </div></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editAppraisal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_emp_id').value = data.employee_id;
            document.getElementById('edit_date').value = data.appraisal_date;
            document.getElementById('edit_status').value = data.status;

            // Populate Technical
            if(data.technical_competencies) {
                const tech = JSON.parse(data.technical_competencies);
                for (const [key, value] of Object.entries(tech)) {
                    let el = document.getElementById('edit_tech_' + key);
                    if(el) el.value = value;
                }
            }
            // Populate Organizational
            if(data.organizational_competencies) {
                const org = JSON.parse(data.organizational_competencies);
                for (const [key, value] of Object.entries(org)) {
                    let el = document.getElementById('edit_org_' + key);
                    if(el) el.value = value;
                }
            }

            var myModal = new bootstrap.Modal(document.getElementById('edit_performance_appraisal'));
            myModal.show();
        }
    </script>
</body>
</html>