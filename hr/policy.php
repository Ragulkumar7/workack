<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// --- CREATE TABLE IF NOT EXISTS ---
$table_sql = "CREATE TABLE IF NOT EXISTS `policies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `policy_name` varchar(255) NOT NULL,
  `department` varchar(100) NOT NULL,
  `description` text,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// --- HANDLE FORM SUBMISSIONS ---

// ADD POLICY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_policy_submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['policy_name']);
    $dept = mysqli_real_escape_string($conn, $_POST['department']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    // File Upload
    $file_path = '';
    if (isset($_FILES['policy_file']) && $_FILES['policy_file']['error'] == 0) {
        $target_dir = "../uploads/policies/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        
        $fileName = time() . "_" . basename($_FILES["policy_file"]["name"]);
        $target_file = $target_dir . $fileName;
        
        if (move_uploaded_file($_FILES["policy_file"]["tmp_name"], $target_file)) {
            $file_path = $target_file;
        }
    }
    
    $sql = "INSERT INTO policies (policy_name, department, description, file_path) VALUES ('$name', '$dept', '$desc', '$file_path')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='policy.php?msg=added';</script>"; exit();
    }
}

// UPDATE POLICY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_policy_submit'])) {
    $id = intval($_POST['policy_id']);
    $name = mysqli_real_escape_string($conn, $_POST['policy_name']);
    $dept = mysqli_real_escape_string($conn, $_POST['department']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Check for new file
    $file_update = "";
    if (isset($_FILES['policy_file']) && $_FILES['policy_file']['error'] == 0) {
        $target_dir = "../uploads/policies/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $fileName = time() . "_" . basename($_FILES["policy_file"]["name"]);
        $target_file = $target_dir . $fileName;
        if (move_uploaded_file($_FILES["policy_file"]["tmp_name"], $target_file)) {
            $file_update = ", file_path='$target_file'";
        }
    }
    
    $sql = "UPDATE policies SET policy_name='$name', department='$dept', description='$desc' $file_update WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='policy.php?msg=updated';</script>"; exit();
    }
}

// DELETE POLICY
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM policies WHERE id=$id");
    echo "<script>window.location.href='policy.php?msg=deleted';</script>"; exit();
}

// --- FETCH DATA ---

// 1. Fetch Departments for Dropdown (Fixing your doubt)
$dept_list = [];
$dept_res = mysqli_query($conn, "SELECT dept_name FROM departments WHERE status='Active' ORDER BY dept_name ASC");
if($dept_res) { 
    while($r = mysqli_fetch_assoc($dept_res)) {
        $dept_list[] = $r['dept_name'];
    }
}

// 2. Fetch Policies
$policies = [];
$res = mysqli_query($conn, "SELECT * FROM policies ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $policies[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Policies - HR</title>
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
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .policy-upload-bg { width: 40px; height: 40px; background: rgba(255, 155, 68, 0.1); }
        
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
                        <h2 class="mb-1">Policies</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Policies</li>
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
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_policy" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Policy</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Policies List</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            <div class="dropdown me-3">
                                <a href="#" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">Department</a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <?php foreach($dept_list as $d): ?>
                                        <li><a href="#" class="dropdown-item rounded-1"><?= htmlspecialchars($d) ?></a></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Department</th>
                                        <th>Description</th>
                                        <th>Created Date</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($policies)): ?>
                                        <tr><td colspan="5" class="text-center p-4">No policies found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($policies as $pol): 
                                            $polJson = htmlspecialchars(json_encode($pol), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td>
                                                <h6 class="fs-14 fw-medium text-dark mb-0"><?= htmlspecialchars($pol['policy_name']) ?></h6>
                                                <?php if(!empty($pol['file_path'])): ?>
                                                    <a href="<?= htmlspecialchars($pol['file_path']) ?>" target="_blank" class="fs-12 text-primary"><i class="ti ti-download"></i> Download</a>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= htmlspecialchars($pol['department']) ?></td>
                                            <td><?= htmlspecialchars($pol['description']) ?></td>
                                            <td><?= date('d M Y', strtotime($pol['created_at'])) ?></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editPolicy(<?= $polJson ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="policy.php?delete_id=<?= $pol['id'] ?>" onclick="return confirm('Are you sure?')" class="text-danger"><i class="ti ti-trash"></i></a>
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

    <div class="modal fade" id="add_policy">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Policy</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="policy.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_policy_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Policy Name</label>
                                <input type="text" name="policy_name" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Department</label>
                                <select name="department" class="form-select">
                                    <option value="All Department">All Department</option>
                                    <?php foreach($dept_list as $d): ?>
                                        <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Upload Policy</label>
                                <input type="file" name="policy_file" class="form-control">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Policy</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_policy">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Policy</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="policy.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_policy_submit" value="1">
                    <input type="hidden" name="policy_id" id="edit_id">
                    
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Policy Name</label>
                                <input type="text" name="policy_name" id="edit_name" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Department</label>
                                <select name="department" id="edit_dept" class="form-select">
                                    <option value="All Department">All Department</option>
                                    <?php foreach($dept_list as $d): ?>
                                        <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Upload New File (Optional)</label>
                                <input type="file" name="policy_file" class="form-control">
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
        function editPolicy(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.policy_name;
            document.getElementById('edit_dept').value = data.department;
            document.getElementById('edit_desc').value = data.description;
            var myModal = new bootstrap.Modal(document.getElementById('edit_policy'));
            myModal.show();
        }
    </script>
</body>
</html>