<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. ALTER EMPLOYEES TABLE (Add Notice Period Columns if they don't exist)
// We use the existing 'employees' table instead of creating a new one.
$cols = mysqli_query($conn, "SHOW COLUMNS FROM `employees` LIKE 'notice_status'");
if (mysqli_num_rows($cols) == 0) {
    mysqli_query($conn, "ALTER TABLE `employees` 
        ADD COLUMN `notice_start_date` DATE NULL,
        ADD COLUMN `notice_end_date` DATE NULL,
        ADD COLUMN `notice_status` ENUM('Active','Completed','Closing Soon') NULL
    ");
}

// 3. HANDLE FORM SUBMISSIONS

// ADD / UPDATE NOTICE DETAILS
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_notice'])) {
    $emp_id = intval($_POST['employee_id']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $status = 'Active'; // Default status when adding

    // We Update the existing Employee record
    $sql = "UPDATE employees SET notice_start_date='$start', notice_end_date='$end', notice_status='$status' WHERE id=$emp_id";
    
    if(mysqli_query($conn, $sql)) { header("Location: notice_period.php?msg=updated"); exit(); }
}

// REMOVE FROM TRACKER (Just clears the notice dates)
if (isset($_GET['remove_id'])) {
    $id = intval($_GET['remove_id']);
    mysqli_query($conn, "UPDATE employees SET notice_status=NULL, notice_start_date=NULL, notice_end_date=NULL WHERE id=$id");
    header("Location: notice_period.php?msg=removed"); exit();
}

// 4. FETCH DATA
$notices = [];
$today = new DateTime();

// Fetch only employees who are currently in Notice Period process
$res = mysqli_query($conn, "SELECT * FROM employees WHERE notice_status IS NOT NULL ORDER BY id DESC");
if($res) {
    while($row = mysqli_fetch_assoc($res)) {
        if($row['notice_start_date'] && $row['notice_end_date']) {
            $start = new DateTime($row['notice_start_date']);
            $end = new DateTime($row['notice_end_date']);
            
            $total_days = $start->diff($end)->days;
            // Avoid division by zero
            if($total_days == 0) $total_days = 1; 

            $completed = $start->diff($today)->days;
            
            if($today < $start) $completed = 0;
            if($today > $end) $completed = $total_days;
            
            $remaining = $total_days - $completed;
            if($remaining < 0) $remaining = 0;

            $row['total'] = $total_days;
            $row['completed'] = $completed;
            $row['remaining'] = $remaining;
            
            $notices[] = $row;
        }
    }
}

// Fetch All Employees for Dropdown
$all_emps = [];
$e_res = mysqli_query($conn, "SELECT id, name FROM employees WHERE status='Active'");
if($e_res) { while($row = mysqli_fetch_assoc($e_res)) $all_emps[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notice Period Tracker</title>
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
        
        .badge-pink-transparent { background: rgba(232, 62, 140, 0.1); color: #e83e8c; }
        .badge-purple-transparent { background: rgba(111, 66, 193, 0.1); color: #6f42c1; }
        .badge-soft-success { background-color: rgba(40, 199, 111, 0.1); color: #28c76f; }
        
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
                        <h2 class="mb-1">Notice Period Tracker</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Notice Period Tracker</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_modal" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Employee to Tracker</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Notice Period List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Designation</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Total Days</th>
                                        <th>Completed</th>
                                        <th>Remaining</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($notices)): ?>
                                        <tr><td colspan="9" class="text-center p-4">No employees in notice period.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($notices as $n): 
                                            $json = htmlspecialchars(json_encode($n), ENT_QUOTES, 'UTF-8');
                                            $badgeClass = 'badge-pink-transparent';
                                            if($n['notice_status'] == 'Completed') $badgeClass = 'badge-soft-success';
                                            elseif($n['notice_status'] == 'Closing Soon') $badgeClass = 'badge-purple-transparent';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-secondary rounded-circle text-white me-2"><?= strtoupper(substr($n['name'],0,2)) ?></span>
                                                    <h6 class="fw-medium mb-0"><a href="#" onclick="showDetails(<?= $json ?>)" data-bs-toggle="offcanvas" data-bs-target="#notice_details" class="text-dark"><?= htmlspecialchars($n['name']) ?></a></h6>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($n['role']) ?></td>
                                            <td><?= date('d M Y', strtotime($n['notice_start_date'])) ?></td>
                                            <td><?= date('d M Y', strtotime($n['notice_end_date'])) ?></td>
                                            <td><?= $n['total'] ?></td>
                                            <td><?= $n['completed'] ?></td>
                                            <td><?= $n['remaining'] ?></td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($n['notice_status']) ?></span></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="showDetails(<?= $json ?>)" data-bs-toggle="offcanvas" data-bs-target="#notice_details"><i class="ti ti-eye"></i></a>
                                                    <a href="#" class="me-2" onclick="editNotice(<?= $json ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="notice_period.php?remove_id=<?= $n['id'] ?>" onclick="return confirm('Remove from tracker?')" class="text-danger"><i class="ti ti-trash"></i></a>
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
                    <h4 class="modal-title">Notice Period Update</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="notice_period.php" method="POST">
                    <input type="hidden" name="update_notice" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Select Employee <span class="text-danger">*</span></label>
                                <select name="employee_id" class="form-select" required>
                                    <option value="">Select</option>
                                    <?php foreach($all_emps as $e): ?>
                                        <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Notice Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Notice End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_modal">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Notice Details</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="notice_period.php" method="POST">
                    <input type="hidden" name="update_notice" value="1">
                    <input type="hidden" name="employee_id" id="edit_id"> <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Employee Name</label>
                                <input type="text" id="edit_name" class="form-control" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date</label>
                                <input type="date" name="start_date" id="edit_start" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date</label>
                                <input type="date" name="end_date" id="edit_end" class="form-control" required>
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

    <div class="offcanvas offcanvas-end" tabindex="-1" id="notice_details">
        <div class="offcanvas-header border-bottom">
            <h4 class="offcanvas-title">Notice Details</h4>
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
            
            <div class="mb-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <p class="mb-0">Progress</p>
                    <p class="text-dark mb-0"><span id="view_completed">60</span> / <span id="view_total">90</span> Days</p>
                </div>
                <div class="progress progress-xs flex-grow-1 mb-2">
                    <div class="progress-bar bg-purple rounded" id="view_progress" style="width: 0%;"></div>
                </div>
            </div>

            <div class="mb-4">
                <div class="d-flex justify-content-between mb-2"><span>Start Date:</span> <strong id="view_start"></strong></div>
                <div class="d-flex justify-content-between mb-2"><span>End Date:</span> <strong id="view_end"></strong></div>
                <div class="d-flex justify-content-between"><span>Remaining:</span> <strong id="view_remaining" class="text-danger"></strong></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editNotice(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.name;
            document.getElementById('edit_start').value = data.notice_start_date;
            document.getElementById('edit_end').value = data.notice_end_date;
            var myModal = new bootstrap.Modal(document.getElementById('edit_modal'));
            myModal.show();
        }

        function showDetails(data) {
            document.getElementById('view_name').innerText = data.name;
            document.getElementById('view_role').innerText = data.role;
            document.getElementById('view_avatar').innerText = data.name.substring(0,2).toUpperCase();
            
            document.getElementById('view_start').innerText = data.notice_start_date;
            document.getElementById('view_end').innerText = data.notice_end_date;
            document.getElementById('view_completed').innerText = data.completed;
            document.getElementById('view_total').innerText = data.total;
            document.getElementById('view_remaining').innerText = data.remaining;

            let percent = (data.completed / data.total) * 100;
            if(percent > 100) percent = 100;
            document.getElementById('view_progress').style.width = percent + '%';
        }
    </script>
</body>
</html>