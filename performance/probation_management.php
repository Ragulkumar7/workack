<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE TABLE IF NOT EXISTS
$table_sql = "CREATE TABLE IF NOT EXISTS `probation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(100),
  `designation` varchar(100),
  `joining_date` date,
  `probation_end_date` date,
  `reviewer` varchar(100),
  `status` enum('Pending','Completed','In Review','Failed','Extended') DEFAULT 'Pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// 3. HANDLE FORM SUBMISSIONS

// ADD PROBATION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_probation'])) {
    $emp_id = intval($_POST['employee_id']);
    $join_date = $_POST['join_date'];
    $end_date = $_POST['end_date'];
    $reviewer = mysqli_real_escape_string($conn, $_POST['reviewer']);
    
    // Fetch details
    $e_q = mysqli_query($conn, "SELECT name, role FROM employees WHERE id=$emp_id");
    $e_row = mysqli_fetch_assoc($e_q);
    $name = $e_row['name'];
    $role = $e_row['role'];

    $sql = "INSERT INTO probation (employee_id, employee_name, designation, joining_date, probation_end_date, reviewer, status) 
            VALUES ('$emp_id', '$name', '$role', '$join_date', '$end_date', '$reviewer', 'Pending')";
    
    if(mysqli_query($conn, $sql)) { header("Location: probation_management.php?msg=added"); exit(); }
}

// EDIT PROBATION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_probation'])) {
    $id = intval($_POST['probation_id']);
    $join_date = $_POST['join_date'];
    $end_date = $_POST['end_date'];
    $reviewer = mysqli_real_escape_string($conn, $_POST['reviewer']);
    $status = mysqli_real_escape_string($conn, $_POST['status']); // Added status update capability

    $sql = "UPDATE probation SET joining_date='$join_date', probation_end_date='$end_date', reviewer='$reviewer', status='$status' WHERE id=$id";
    
    if(mysqli_query($conn, $sql)) { header("Location: probation_management.php?msg=updated"); exit(); }
}

// DELETE PROBATION
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM probation WHERE id=$id");
    header("Location: probation_management.php?msg=deleted"); exit();
}

// 4. FETCH DATA
$probations = [];
$res = mysqli_query($conn, "SELECT * FROM probation ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $probations[] = $row; }

$employees = [];
$e_res = mysqli_query($conn, "SELECT id, name FROM employees WHERE status='Active'");
if($e_res) { while($row = mysqli_fetch_assoc($e_res)) $employees[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Probation Management - HR</title>
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
        
        /* Status Badges */
        .badge-soft-info { background-color: rgba(0, 207, 232, 0.1); color: #00cfe8; }
        .badge-soft-success { background-color: rgba(40, 199, 111, 0.1); color: #28c76f; }
        .badge-soft-warning { background-color: rgba(255, 159, 67, 0.1); color: #ff9f43; }
        .badge-soft-danger { background-color: rgba(234, 84, 85, 0.1); color: #ea5455; }
        .badge-soft-purple { background-color: rgba(115, 103, 240, 0.1); color: #7367f0; }
        
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
                        <h2 class="mb-1">Probation Management</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Probation Management</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_modal" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add New Employee</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Probation Management</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Joining Date</th>
                                        <th>End Date</th>
                                        <th>Reviewer</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($probations)): ?>
                                        <tr><td colspan="7" class="text-center p-4">No records found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($probations as $p): 
                                            $json = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
                                            $statusClass = 'badge-soft-info'; // default Pending
                                            if($p['status'] == 'Completed') $statusClass = 'badge-soft-success';
                                            elseif($p['status'] == 'In Review') $statusClass = 'badge-soft-warning';
                                            elseif($p['status'] == 'Failed') $statusClass = 'badge-soft-danger';
                                            elseif($p['status'] == 'Extended') $statusClass = 'badge-soft-purple';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-secondary rounded-circle text-white me-2"><?= strtoupper(substr($p['employee_name'],0,2)) ?></span>
                                                    <h6 class="fw-medium mb-0"><a href="#" onclick="showDetails(<?= $json ?>)" data-bs-toggle="offcanvas" data-bs-target="#probation_details" class="text-dark"><?= htmlspecialchars($p['employee_name']) ?></a></h6>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($p['designation']) ?></td>
                                            <td><?= date('d M Y', strtotime($p['joining_date'])) ?></td>
                                            <td><?= date('d M Y', strtotime($p['probation_end_date'])) ?></td>
                                            <td><?= htmlspecialchars($p['reviewer']) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($p['status']) ?></span></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="showDetails(<?= $json ?>)" data-bs-toggle="offcanvas" data-bs-target="#probation_details"><i class="ti ti-eye"></i></a>
                                                    <a href="#" class="me-2" onclick="editProbation(<?= $json ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="probation_management.php?delete_id=<?= $p['id'] ?>" onclick="return confirm('Are you sure?')" class="text-danger"><i class="ti ti-trash"></i></a>
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

    <div class="modal fade" id="add_modal">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Employee</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="probation_management.php" method="POST">
                    <input type="hidden" name="add_probation" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Employee Name <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select</option>
                                    <?php foreach($employees as $e): ?>
                                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Joining Date <span class="text-danger">*</span></label>
                                <input type="date" name="join_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Probation End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Reviewer <span class="text-danger">*</span></label>
                                <select name="reviewer" class="form-select">
                                    <option value="William Parsons">William Parsons</option>
                                    <option value="Thomas Miller">Thomas Miller</option>
                                    <option value="Sarah Henry">Sarah Henry</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_modal">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Probation</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="probation_management.php" method="POST">
                    <input type="hidden" name="edit_probation" value="1">
                    <input type="hidden" name="probation_id" id="edit_id">
                    
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Employee Name</label>
                                <input type="text" id="edit_emp_name" class="form-control" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Joining Date</label>
                                <input type="date" name="join_date" id="edit_join" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Probation End Date</label>
                                <input type="date" name="end_date" id="edit_end" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Reviewer</label>
                                <select name="reviewer" id="edit_reviewer" class="form-select">
                                    <option value="William Parsons">William Parsons</option>
                                    <option value="Thomas Miller">Thomas Miller</option>
                                    <option value="Sarah Henry">Sarah Henry</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" id="edit_status" class="form-select">
                                    <option value="Pending">Pending</option>
                                    <option value="In Review">In Review</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Failed">Failed</option>
                                    <option value="Extended">Extended</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="offcanvas offcanvas-end" tabindex="-1" id="probation_details">
        <div class="offcanvas-header border-bottom">
            <h4 class="offcanvas-title">Employee Details</h4>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <div class="card bg-light mb-4">
                <div class="card-body d-flex align-items-center">
                    <span class="avatar avatar-lg bg-secondary rounded-circle text-white me-3" id="view_avatar">AL</span>
                    <div>
                        <h5 class="mb-1" id="view_name">Anthony Lewis</h5>
                        <span class="badge bg-dark" id="view_role">Accountant</span>
                    </div>
                </div>
            </div>
            
            <div class="mb-4">
                <h6 class="border-bottom pb-2 mb-3">Probation Info</h6>
                <div class="d-flex justify-content-between mb-2"><span>Start Date:</span> <strong id="view_start"></strong></div>
                <div class="d-flex justify-content-between mb-2"><span>End Date:</span> <strong id="view_end"></strong></div>
                <div class="d-flex justify-content-between mb-2"><span>Reviewer:</span> <strong id="view_reviewer"></strong></div>
                <div class="d-flex justify-content-between"><span>Status:</span> <strong id="view_status"></strong></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editProbation(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_emp_name').value = data.employee_name;
            document.getElementById('edit_join').value = data.joining_date;
            document.getElementById('edit_end').value = data.probation_end_date;
            document.getElementById('edit_reviewer').value = data.reviewer;
            document.getElementById('edit_status').value = data.status;
            
            var myModal = new bootstrap.Modal(document.getElementById('edit_modal'));
            myModal.show();
        }

        function showDetails(data) {
            document.getElementById('view_name').innerText = data.employee_name;
            document.getElementById('view_role').innerText = data.designation;
            document.getElementById('view_avatar').innerText = data.employee_name.substring(0,2).toUpperCase();
            
            document.getElementById('view_start').innerText = data.joining_date;
            document.getElementById('view_end').innerText = data.probation_end_date;
            document.getElementById('view_reviewer').innerText = data.reviewer;
            document.getElementById('view_status').innerText = data.status;
        }
    </script>
</body>
</html>