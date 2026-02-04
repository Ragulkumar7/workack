<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE TABLE
$table_sql = "CREATE TABLE IF NOT EXISTS `terminations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `employee_name` varchar(100),
  `department` varchar(100),
  `termination_type` varchar(100),
  `reason` text,
  `notice_date` date,
  `resignation_date` date,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// 3. HANDLE FORM SUBMISSIONS

// ADD TERMINATION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_termination'])) {
    $emp_id = intval($_POST['employee_id']);
    $type = mysqli_real_escape_string($conn, $_POST['termination_type']);
    $notice_date = $_POST['notice_date'];
    $resig_date = $_POST['resignation_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    // Fetch emp details
    $e_q = mysqli_query($conn, "SELECT name, department FROM employees WHERE id=$emp_id");
    $e_row = mysqli_fetch_assoc($e_q);
    $name = $e_row['name'];
    $dept = $e_row['department'];

    $sql = "INSERT INTO terminations (employee_id, employee_name, department, termination_type, reason, notice_date, resignation_date) 
            VALUES ('$emp_id', '$name', '$dept', '$type', '$reason', '$notice_date', '$resig_date')";
    
    if(mysqli_query($conn, $sql)) { header("Location: termination.php?msg=added"); exit(); }
}

// EDIT TERMINATION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_termination'])) {
    $id = intval($_POST['termination_id']);
    $type = mysqli_real_escape_string($conn, $_POST['termination_type']);
    $notice_date = $_POST['notice_date'];
    $resig_date = $_POST['resignation_date'];
    $reason = mysqli_real_escape_string($conn, $_POST['reason']);

    $sql = "UPDATE terminations SET termination_type='$type', notice_date='$notice_date', resignation_date='$resig_date', reason='$reason' WHERE id=$id";
    if(mysqli_query($conn, $sql)) { header("Location: termination.php?msg=updated"); exit(); }
}

// DELETE TERMINATION
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM terminations WHERE id=$id");
    header("Location: termination.php?msg=deleted"); exit();
}

// 4. FETCH DATA
$terminations = [];
$res = mysqli_query($conn, "SELECT * FROM terminations ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $terminations[] = $row; }

// Fetch Employees
$employees = [];
$e_res = mysqli_query($conn, "SELECT id, name FROM employees WHERE status='Active'");
if($e_res) { while($row = mysqli_fetch_assoc($e_res)) $employees[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Termination - HR</title>
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
                        <h2 class="mb-1">Termination</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Termination</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#new_termination" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Termination</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Termination List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Resigning Employee</th>
                                        <th>Department</th>
                                        <th>Termination Type</th>
                                        <th>Notice Date</th>
                                        <th>Resignation Date</th>
                                        <th>Reason</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($terminations)): ?>
                                        <tr><td colspan="7" class="text-center p-4">No records found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($terminations as $term): 
                                            $json = htmlspecialchars(json_encode($term), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-secondary rounded-circle text-white me-2"><?= strtoupper(substr($term['employee_name'],0,2)) ?></span>
                                                    <h6 class="fw-medium mb-0"><?= htmlspecialchars($term['employee_name']) ?></h6>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($term['department']) ?></td>
                                            <td><?= htmlspecialchars($term['termination_type']) ?></td>
                                            <td><?= date('d M Y', strtotime($term['notice_date'])) ?></td>
                                            <td><?= date('d M Y', strtotime($term['resignation_date'])) ?></td>
                                            <td><?= htmlspecialchars($term['reason']) ?></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editTermination(<?= $json ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="termination.php?delete_id=<?= $term['id'] ?>" onclick="return confirm('Are you sure?')" class="text-danger"><i class="ti ti-trash"></i></a>
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

    <div class="modal fade" id="new_termination">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Termination</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="termination.php" method="POST">
                    <input type="hidden" name="add_termination" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Terminated Employee</label>
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select</option>
                                    <?php foreach($employees as $e): ?>
                                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Termination Type</label>
                                <select name="termination_type" class="form-select">
                                    <option value="Retirement">Retirement</option>
                                    <option value="Insubordination">Insubordination</option>
                                    <option value="Lack of Skills">Lack of Skills</option>
                                    <option value="Layoff">Layoff</option>
                                    <option value="Breach of Contract">Breach of Contract</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notice Date</label>
                                <input type="date" name="notice_date" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Resignation Date</label>
                                <input type="date" name="resignation_date" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Reason</label>
                                <textarea name="reason" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Termination</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_termination">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Termination</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="termination.php" method="POST">
                    <input type="hidden" name="edit_termination" value="1">
                    <input type="hidden" name="termination_id" id="edit_id">
                    
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Terminated Employee</label>
                                <input type="text" id="edit_name" class="form-control" readonly>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Termination Type</label>
                                <select name="termination_type" id="edit_type" class="form-select">
                                    <option value="Retirement">Retirement</option>
                                    <option value="Insubordination">Insubordination</option>
                                    <option value="Lack of Skills">Lack of Skills</option>
                                    <option value="Layoff">Layoff</option>
                                    <option value="Breach of Contract">Breach of Contract</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Notice Date</label>
                                <input type="date" name="notice_date" id="edit_notice" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Resignation Date</label>
                                <input type="date" name="resignation_date" id="edit_resig" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Reason</label>
                                <textarea name="reason" id="edit_reason" class="form-control" rows="3" required></textarea>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editTermination(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.employee_name;
            document.getElementById('edit_type').value = data.termination_type;
            document.getElementById('edit_notice').value = data.notice_date;
            document.getElementById('edit_resig').value = data.resignation_date;
            document.getElementById('edit_reason').value = data.reason;
            
            var myModal = new bootstrap.Modal(document.getElementById('edit_termination'));
            myModal.show();
        }
    </script>
</body>
</html>