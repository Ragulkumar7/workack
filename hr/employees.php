<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// --- HANDLE FORM SUBMISSIONS ---

// A. ADD EMPLOYEE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_employee_submit'])) {
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $name       = $first_name . ' ' . $last_name;
    $emp_id     = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $phone      = mysqli_real_escape_string($conn, $_POST['phone']);
    $dept       = mysqli_real_escape_string($conn, $_POST['department']);
    $role       = mysqli_real_escape_string($conn, $_POST['designation']);
    $password   = mysqli_real_escape_string($conn, $_POST['password']); 
    
    $join_date = NULL;
    if(!empty($_POST['joining_date'])) {
        $join_date = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['joining_date'])));
    }

    $sql = "INSERT INTO employees (emp_code, name, email, phone, department, role, joined_date, password, status) 
            VALUES ('$emp_id', '$name', '$email', '$phone', '$dept', '$role', '$join_date', '$password', 'Active')";

    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='employees.php?msg=added';</script>"; exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// B. UPDATE EMPLOYEE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_employee_submit'])) {
    $id         = intval($_POST['id']); // Hidden ID field
    $first_name = mysqli_real_escape_string($conn, $_POST['first_name']);
    $last_name  = mysqli_real_escape_string($conn, $_POST['last_name']);
    $name       = $first_name . ' ' . $last_name;
    $emp_id     = mysqli_real_escape_string($conn, $_POST['employee_id']);
    $email      = mysqli_real_escape_string($conn, $_POST['email']);
    $phone      = mysqli_real_escape_string($conn, $_POST['phone']);
    $dept       = mysqli_real_escape_string($conn, $_POST['department']);
    $role       = mysqli_real_escape_string($conn, $_POST['designation']);
    
    // Only update password if user typed a new one
    $pass_sql = "";
    if(!empty($_POST['password'])) {
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $pass_sql = ", password='$password'";
    }

    $join_date = NULL;
    if(!empty($_POST['joining_date'])) {
        $join_date = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['joining_date'])));
    }

    $sql = "UPDATE employees SET 
            name='$name', emp_code='$emp_id', email='$email', phone='$phone', 
            department='$dept', role='$role', joined_date='$join_date' $pass_sql
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='employees.php?msg=updated';</script>"; exit();
    } else {
        echo "<script>alert('Error: " . mysqli_error($conn) . "');</script>";
    }
}

// C. DELETE EMPLOYEE
if (isset($_GET['delete_id'])) {
    $del_id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM employees WHERE id=$del_id");
    echo "<script>window.location.href='employees.php?msg=deleted';</script>"; exit();
}

// --- FETCH DATA ---

// Stats
$stats = ['total' => 0, 'active' => 0, 'inactive' => 0, 'new' => 0];
if($conn) {
    $stats['total']    = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees"))['c'];
    $stats['active']   = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE status='Active'"))['c'];
    $stats['inactive'] = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE status!='Active'"))['c'];
    $stats['new']      = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM employees WHERE joined_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['c'];
}

// Employees List
$employees = [];
$res = mysqli_query($conn, "SELECT * FROM employees ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $employees[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Employees - HR</title>
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
        .avatar-lg { width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; }
        .bg-dark { background-color: #4b4b4b !important; }
        .bg-success { background-color: #28c76f !important; }
        .bg-danger { background-color: #ea5455 !important; }
        .bg-info { background-color: #00cfe8 !important; }
        .badge-soft-success { background-color: rgba(40, 199, 111, 0.1); color: #28c76f; }
        .badge-soft-danger { background-color: rgba(234, 84, 85, 0.1); color: #ea5455; }
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
                        <h2 class="mb-1">Employees List</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Employees</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_employee" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Employee</a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <div><span class="avatar avatar-lg bg-dark rounded-circle"><i class="ti ti-users"></i></span></div>
                                    <div class="ms-2"><p class="fs-12 fw-medium mb-1 text-truncate">Total</p><h4><?= $stats['total'] ?></h4></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <div><span class="avatar avatar-lg bg-success rounded-circle"><i class="ti ti-user-check"></i></span></div>
                                    <div class="ms-2"><p class="fs-12 fw-medium mb-1 text-truncate">Active</p><h4><?= $stats['active'] ?></h4></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <div><span class="avatar avatar-lg bg-danger rounded-circle"><i class="ti ti-user-off"></i></span></div>
                                    <div class="ms-2"><p class="fs-12 fw-medium mb-1 text-truncate">Inactive</p><h4><?= $stats['inactive'] ?></h4></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6 d-flex">
                        <div class="card flex-fill">
                            <div class="card-body d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center overflow-hidden">
                                    <div><span class="avatar avatar-lg bg-info rounded-circle"><i class="ti ti-user-plus"></i></span></div>
                                    <div class="ms-2"><p class="fs-12 fw-medium mb-1 text-truncate">New</p><h4><?= $stats['new'] ?></h4></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($employees as $emp): 
                                        $initials = strtoupper(substr($emp['name'], 0, 2));
                                        $statusClass = ($emp['status'] == 'Active') ? 'bg-success' : 'bg-danger';
                                        
                                        // Prepare data for JSON (safe for JS)
                                        $empJson = htmlspecialchars(json_encode($emp), ENT_QUOTES, 'UTF-8');
                                    ?>
                                    <tr>
                                        <td><a href="employee_profile.php?id=<?= $emp['id'] ?>" class="text-primary fw-medium"><?= htmlspecialchars($emp['emp_code'] ?? 'ID') ?></a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="avatar avatar-md bg-light text-dark me-2 rounded-circle fw-bold d-flex align-items-center justify-content-center" style="width:35px;height:35px;font-size:12px;">
                                                    <?= $initials ?>
                                                </span>
                                                <div class="ms-2">
                                                    <p class="text-dark mb-0 fw-medium"><?= htmlspecialchars($emp['name']) ?></p>
                                                    <span class="fs-12 text-muted"><?= htmlspecialchars($emp['department'] ?? '') ?></span>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($emp['email']) ?></td>
                                        <td><?= htmlspecialchars($emp['phone']) ?></td>
                                        <td><?= htmlspecialchars($emp['role'] ?? '-') ?></td>
                                        <td>
                                            <span class="badge <?= $statusClass ?> badge-sm d-inline-flex align-items-center">
                                                <i class="ti ti-point-filled me-1"></i><?= htmlspecialchars($emp['status']) ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" onclick="editEmployee(<?= $empJson ?>)"><i class="ti ti-edit"></i></a>
                                                <a href="employees.php?delete_id=<?= $emp['id'] ?>" onclick="return confirm('Are you sure?')" class="text-danger"><i class="ti ti-trash"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="add_employee">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Employee</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="employees.php" method="POST">
                    <input type="hidden" name="add_employee_submit" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Last Name *</label><input type="text" name="last_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Employee ID *</label><input type="text" name="employee_id" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Joining Date</label><input type="date" name="joining_date" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Password *</label><input type="password" name="password" class="form-control" value="123456" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Department</label>
                                <select name="department" class="form-select">
                                    <option value="Finance">Finance</option><option value="Developer">Developer</option><option value="HR">HR</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label">Designation</label>
                                <select name="designation" class="form-select">
                                    <option value="Manager">Manager</option><option value="Executive">Executive</option><option value="Developer">Developer</option>
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

    <div class="modal fade" id="edit_employee">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Employee</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="employees.php" method="POST">
                    <input type="hidden" name="edit_employee_submit" value="1">
                    <input type="hidden" name="id" id="edit_id"> <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">First Name *</label><input type="text" name="first_name" id="edit_first_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Last Name *</label><input type="text" name="last_name" id="edit_last_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Employee ID *</label><input type="text" name="employee_id" id="edit_emp_id" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Joining Date</label><input type="date" name="joining_date" id="edit_joining_date" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input type="email" name="email" id="edit_email" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" id="edit_phone" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Password (Leave empty to keep current)</label><input type="password" name="password" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Department</label>
                                <select name="department" id="edit_dept" class="form-select">
                                    <option value="Finance">Finance</option><option value="Developer">Developer</option><option value="HR">HR</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label">Designation</label>
                                <select name="designation" id="edit_role" class="form-select">
                                    <option value="Manager">Manager</option><option value="Executive">Executive</option><option value="Developer">Developer</option>
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
        // JS Function to populate Edit Modal
        function editEmployee(data) {
            // Split name into First/Last
            const nameParts = data.name.split(" ");
            const firstName = nameParts[0];
            const lastName = nameParts.slice(1).join(" ");

            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_first_name').value = firstName;
            document.getElementById('edit_last_name').value = lastName;
            document.getElementById('edit_emp_id').value = data.emp_code;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_phone').value = data.phone;
            document.getElementById('edit_joining_date').value = data.joined_date;
            document.getElementById('edit_dept').value = data.department;
            document.getElementById('edit_role').value = data.role;

            // Open Modal
            var myModal = new bootstrap.Modal(document.getElementById('edit_employee'));
            myModal.show();
        }
    </script>
</body>
</html>