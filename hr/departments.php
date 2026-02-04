<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// --- CREATE TABLE IF NOT EXISTS ---
$table_sql = "CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// --- HANDLE FORM SUBMISSIONS ---

// ADD DEPARTMENT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_dept_submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['dept_name']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "INSERT INTO departments (dept_name, status) VALUES ('$name', '$status')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='departments.php?msg=added';</script>"; exit();
    }
}

// UPDATE DEPARTMENT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_dept_submit'])) {
    $id = intval($_POST['dept_id']);
    $name = mysqli_real_escape_string($conn, $_POST['dept_name']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "UPDATE departments SET dept_name='$name', status='$status' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='departments.php?msg=updated';</script>"; exit();
    }
}

// DELETE DEPARTMENT
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM departments WHERE id=$id");
    echo "<script>window.location.href='departments.php?msg=deleted';</script>"; exit();
}

// --- FETCH DATA ---
$departments = [];
$res = mysqli_query($conn, "SELECT * FROM departments ORDER BY id DESC");
if($res) { 
    while($row = mysqli_fetch_assoc($res)) {
        // Count employees in this department (assuming 'employees' table has 'department' column)
        $dept_name = mysqli_real_escape_string($conn, $row['dept_name']);
        $count_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE department='$dept_name'");
        $row['emp_count'] = ($count_res) ? mysqli_fetch_assoc($count_res)['c'] : 0;
        $departments[] = $row; 
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Departments - HR</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; margin-left: 110px; transition: margin-left 0.3s; }
        .page-wrapper { flex: 1; padding: 25px; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-body { padding: 1.5rem; }
        .badge-success { background-color: #28c76f !important; color: #fff; }
        .badge-danger { background-color: #ea5455 !important; color: #fff; }
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
                        <h2 class="mb-1">Departments</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Departments</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown"><i class="ti ti-file-export me-1"></i>Export</a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li><a href="#" class="dropdown-item rounded-1">PDF</a></li>
                                <li><a href="#" class="dropdown-item rounded-1">Excel</a></li>
                            </ul>
                        </div>
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_department" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Department</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Department List</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            <div class="dropdown me-3">
                                <a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">Status</a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="#" class="dropdown-item rounded-1">Active</a></li>
                                    <li><a href="#" class="dropdown-item rounded-1">Inactive</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Department Name</th>
                                        <th>No of Employees</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($departments)): ?>
                                        <tr><td colspan="4" class="text-center p-4">No departments found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($departments as $dept): 
                                            $statusBadge = ($dept['status'] == 'Active') ? 'badge-success' : 'badge-danger';
                                            // Prepare JSON for edit
                                            $deptJson = htmlspecialchars(json_encode($dept), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td><h6 class="fw-medium text-dark mb-0"><?= htmlspecialchars($dept['dept_name']) ?></h6></td>
                                            <td><?= $dept['emp_count'] ?></td>
                                            <td>
                                                <span class="badge <?= $statusBadge ?> d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i><?= htmlspecialchars($dept['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editDept(<?= $deptJson ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="departments.php?delete_id=<?= $dept['id'] ?>" onclick="return confirm('Are you sure?')" class="text-danger"><i class="ti ti-trash"></i></a>
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

    <div class="modal fade" id="add_department">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Department</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="departments.php" method="POST">
                    <input type="hidden" name="add_dept_submit" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Department Name</label>
                                <input type="text" name="dept_name" class="form-control" required>
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
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_department">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Department</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="departments.php" method="POST">
                    <input type="hidden" name="edit_dept_submit" value="1">
                    <input type="hidden" name="dept_id" id="edit_id">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Department Name</label>
                                <input type="text" name="dept_name" id="edit_name" class="form-control" required>
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
        function editDept(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.dept_name;
            document.getElementById('edit_status').value = data.status;
            var myModal = new bootstrap.Modal(document.getElementById('edit_department'));
            myModal.show();
        }
    </script>
</body>
</html>