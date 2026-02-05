<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE TAXES TABLE
$sql = "CREATE TABLE IF NOT EXISTS `taxes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tax_name` varchar(100),
  `percentage` decimal(5,2),
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $sql);

// 3. HANDLE FORM SUBMISSIONS

// ADD TAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_tax'])) {
    $name = mysqli_real_escape_string($conn, $_POST['tax_name']);
    $percent = floatval($_POST['percentage']);
    
    $ins = "INSERT INTO taxes (tax_name, percentage) VALUES ('$name', '$percent')";
    if(mysqli_query($conn, $ins)) {
        header("Location: taxes.php?msg=added");
        exit();
    }
}

// EDIT TAX
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_tax'])) {
    $id = intval($_POST['tax_id']);
    $name = mysqli_real_escape_string($conn, $_POST['tax_name']);
    $percent = floatval($_POST['percentage']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $upd = "UPDATE taxes SET tax_name='$name', percentage='$percent', status='$status' WHERE id=$id";
    if(mysqli_query($conn, $upd)) {
        header("Location: taxes.php?msg=updated");
        exit();
    }
}

// DELETE TAX
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM taxes WHERE id=$id");
    header("Location: taxes.php?msg=deleted");
    exit();
}

// 4. FETCH DATA
$taxes = [];
$res = mysqli_query($conn, "SELECT * FROM taxes ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $taxes[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Taxes - Sales</title>
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
        .badge-soft-success { background-color: rgba(40, 199, 111, 0.1); color: #28c76f; }
        .badge-soft-danger { background-color: rgba(234, 84, 85, 0.1); color: #ea5455; }
        
        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php 
        $sidebar_paths = ['../include/sidebar.php', '../../include/sidebar.php', 'include/sidebar.php'];
        foreach ($sidebar_paths as $path) { if (file_exists($path)) { include $path; break; } }
        
        $header_paths = ['../include/header.php', '../../include/header.php', 'include/header.php'];
        foreach ($header_paths as $path) { if (file_exists($path)) { include $path; break; } }
    ?>

    <div class="main-content-wrapper">
        <div class="page-wrapper">
            <div class="content">
                
                <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Action completed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Taxes</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item">Sales</li>
                                <li class="breadcrumb-item active">Taxes</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add_tax">
                            <i class="ti ti-plus me-2"></i>Add Tax
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Tax List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tax Name</th>
                                        <th>Percentage (%)</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($taxes)): ?>
                                        <tr><td colspan="4" class="text-center p-4">No taxes recorded.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($taxes as $tax): 
                                            $json = htmlspecialchars(json_encode($tax), ENT_QUOTES, 'UTF-8');
                                            $badgeClass = ($tax['status'] == 'Active') ? 'badge-soft-success' : 'badge-soft-danger';
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($tax['tax_name']) ?></td>
                                            <td><?= htmlspecialchars($tax['percentage']) ?>%</td>
                                            <td><span class="badge <?= $badgeClass ?>"><?= $tax['status'] ?></span></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editTax(<?= $json ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="taxes.php?delete_id=<?= $tax['id'] ?>" onclick="return confirm('Delete this tax?')" class="text-danger"><i class="ti ti-trash"></i></a>
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
            
            <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_tax">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Tax</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="taxes.php" method="POST">
                    <input type="hidden" name="add_tax" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tax Name</label>
                            <input type="text" name="tax_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Percentage (%)</label>
                            <input type="number" name="percentage" class="form-control" step="0.01" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Tax</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_tax">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Tax</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="taxes.php" method="POST">
                    <input type="hidden" name="edit_tax" value="1">
                    <input type="hidden" name="tax_id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tax Name</label>
                            <input type="text" name="tax_name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Percentage (%)</label>
                            <input type="number" name="percentage" id="edit_percent" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
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
        function editTax(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.tax_name;
            document.getElementById('edit_percent').value = data.percentage;
            document.getElementById('edit_status').value = data.status;
            
            var myModal = new bootstrap.Modal(document.getElementById('edit_tax'));
            myModal.show();
        }
    </script>
</body>
</html>