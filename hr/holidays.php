<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// --- CREATE TABLE IF NOT EXISTS ---
$table_sql = "CREATE TABLE IF NOT EXISTS `holidays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `holiday_date` date NOT NULL,
  `description` text,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// --- HANDLE FORM SUBMISSIONS ---

// ADD HOLIDAY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_holiday_submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $date = NULL;
    if(!empty($_POST['holiday_date'])) {
        // Assuming input is dd/mm/yyyy from datetimepicker
        $date = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['holiday_date'])));
    }
    
    $sql = "INSERT INTO holidays (title, holiday_date, description, status) VALUES ('$title', '$date', '$desc', '$status')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='holidays.php?msg=added';</script>"; exit();
    }
}

// UPDATE HOLIDAY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_holiday_submit'])) {
    $id = intval($_POST['holiday_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $date = NULL;
    if(!empty($_POST['holiday_date'])) {
        $date = date('Y-m-d', strtotime(str_replace('/', '-', $_POST['holiday_date'])));
    }
    
    $sql = "UPDATE holidays SET title='$title', holiday_date='$date', description='$desc', status='$status' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='holidays.php?msg=updated';</script>"; exit();
    }
}

// DELETE HOLIDAY
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM holidays WHERE id=$id");
    echo "<script>window.location.href='holidays.php?msg=deleted';</script>"; exit();
}

// --- FETCH DATA ---
$holidays = [];
$res = mysqli_query($conn, "SELECT * FROM holidays ORDER BY holiday_date ASC");
if($res) { while($row = mysqli_fetch_assoc($res)) $holidays[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Holidays - HR</title>
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
                        <h2 class="mb-1">Holidays</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Holidays</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_holiday" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Holiday</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Holidays List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($holidays)): ?>
                                        <tr><td colspan="5" class="text-center p-4">No holidays found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($holidays as $hol): 
                                            $statusBadge = ($hol['status'] == 'Active') ? 'badge-success' : 'badge-danger';
                                            $holJson = htmlspecialchars(json_encode($hol), ENT_QUOTES, 'UTF-8');
                                            // Format date for display
                                            $displayDate = date('d M Y', strtotime($hol['holiday_date']));
                                            // Format date for input (dd/mm/yyyy) logic is handled by datetimepicker usually, 
                                            // but standard input[type=date] needs yyyy-mm-dd. 
                                            // We will pass the raw Y-m-d to JS for simplicity with standard inputs.
                                        ?>
                                        <tr>
                                            <td><h6 class="fw-medium text-dark mb-0"><?= htmlspecialchars($hol['title']) ?></h6></td>
                                            <td><?= $displayDate ?></td>
                                            <td><?= htmlspecialchars($hol['description']) ?></td>
                                            <td>
                                                <span class="badge <?= $statusBadge ?> d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i><?= htmlspecialchars($hol['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editHoliday(<?= $holJson ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="holidays.php?delete_id=<?= $hol['id'] ?>" onclick="return confirm('Are you sure?')" class="text-danger"><i class="ti ti-trash"></i></a>
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

    <div class="modal fade" id="add_holiday">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Holiday</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="holidays.php" method="POST">
                    <input type="hidden" name="add_holiday_submit" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="holiday_date" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
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

    <div class="modal fade" id="edit_holiday">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Holiday</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="holidays.php" method="POST">
                    <input type="hidden" name="edit_holiday_submit" value="1">
                    <input type="hidden" name="holiday_id" id="edit_id">
                    
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" id="edit_title" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" name="holiday_date" id="edit_date" class="form-control" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea>
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
        function editHoliday(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_date').value = data.holiday_date;
            document.getElementById('edit_desc').value = data.description;
            document.getElementById('edit_status').value = data.status;
            var myModal = new bootstrap.Modal(document.getElementById('edit_holiday'));
            myModal.show();
        }
    </script>
</body>
</html>