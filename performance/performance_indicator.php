<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// --- CREATE TABLE IF NOT EXISTS ---
$table_sql = "CREATE TABLE IF NOT EXISTS `performance_indicators` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `designation` varchar(255) NOT NULL,
  `department` varchar(255) NOT NULL,
  `approved_by` varchar(255) DEFAULT 'Admin',
  `technical` text, -- JSON stored
  `organizational` text, -- JSON stored
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $table_sql);

// --- HANDLE FORM SUBMISSIONS ---

// ADD INDICATOR
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_indicator_submit'])) {
    $desig = mysqli_real_escape_string($conn, $_POST['designation']);
    
    // Fetch department based on designation (assuming relational logic, or just input)
    // For simplicity, we can fetch it from designations table if exists, or hardcode/input
    $dept = "All Department"; // Placeholder or fetch logic
    
    // Technical JSON
    $tech = json_encode([
        'customer_experience' => $_POST['customer_experience'],
        'marketing' => $_POST['marketing'],
        'management' => $_POST['management'],
        'administration' => $_POST['administration'],
        'presentation' => $_POST['presentation'],
        'quality' => $_POST['quality'],
        'efficiency' => $_POST['efficiency']
    ]);
    
    // Organizational JSON
    $org = json_encode([
        'integrity' => $_POST['integrity'],
        'professionalism' => $_POST['professionalism'],
        'team_work' => $_POST['team_work'],
        'critical_thinking' => $_POST['critical_thinking'],
        'conflict_mgmt' => $_POST['conflict_mgmt'],
        'attendance' => $_POST['attendance'],
        'deadline' => $_POST['deadline']
    ]);
    
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $sql = "INSERT INTO performance_indicators (designation, department, technical, organizational, status) VALUES ('$desig', '$dept', '$tech', '$org', '$status')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='performance_indicator.php?msg=added';</script>"; exit();
    }
}

// UPDATE INDICATOR
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_indicator_submit'])) {
    $id = intval($_POST['indicator_id']);
    $desig = mysqli_real_escape_string($conn, $_POST['designation']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $tech = json_encode([
        'customer_experience' => $_POST['customer_experience'],
        'marketing' => $_POST['marketing'],
        'management' => $_POST['management'],
        'administration' => $_POST['administration'],
        'presentation' => $_POST['presentation'],
        'quality' => $_POST['quality'],
        'efficiency' => $_POST['efficiency']
    ]);
    
    $org = json_encode([
        'integrity' => $_POST['integrity'],
        'professionalism' => $_POST['professionalism'],
        'team_work' => $_POST['team_work'],
        'critical_thinking' => $_POST['critical_thinking'],
        'conflict_mgmt' => $_POST['conflict_mgmt'],
        'attendance' => $_POST['attendance'],
        'deadline' => $_POST['deadline']
    ]);
    
    $sql = "UPDATE performance_indicators SET designation='$desig', technical='$tech', organizational='$org', status='$status' WHERE id=$id";
    if (mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='performance_indicator.php?msg=updated';</script>"; exit();
    }
}

// DELETE INDICATOR
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM performance_indicators WHERE id=$id");
    echo "<script>window.location.href='performance_indicator.php?msg=deleted';</script>"; exit();
}

// --- FETCH DATA ---
$indicators = [];
$res = mysqli_query($conn, "SELECT * FROM performance_indicators ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $indicators[] = $row; }

// Fetch Designations for Dropdown
$designations = [];
$res_desig = mysqli_query($conn, "SELECT designation_name FROM designations WHERE status='Active'");
if($res_desig) { while($row = mysqli_fetch_assoc($res_desig)) $designations[] = $row['designation_name']; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Performance Indicator - HR</title>
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
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        
        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php 
        if(file_exists('../../include/sidebar.php')) include '../../include/sidebar.php';
        elseif(file_exists('../include/sidebar.php')) include '../include/sidebar.php';
    ?>

    <div class="main-content-wrapper">
        
        <?php 
            if(file_exists('../../include/header.php')) include '../../include/header.php';
            elseif(file_exists('../workack/include/header.php')) include '../workack/include/header.php';
        ?>

        <div class="page-wrapper">
            <div class="content">
                
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Performance Indicator</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Performance Indicator</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_indicator" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Indicator</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Performance Indicator List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Designation</th>
                                        <th>Department</th>
                                        <th>Approved By</th>
                                        <th>Created Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($indicators)): ?>
                                        <tr><td colspan="6" class="text-center p-4">No indicators found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($indicators as $ind): 
                                            $statusBadge = ($ind['status'] == 'Active') ? 'badge-success' : 'badge-danger';
                                            // Prepare JSON for edit
                                            // Decode JSON first to re-encode safely with htmlspecialchars
                                            $tech = json_decode($ind['technical'], true);
                                            $org = json_decode($ind['organizational'], true);
                                            $fullData = array_merge($ind, ['tech' => $tech, 'org' => $org]);
                                            $indJson = htmlspecialchars(json_encode($fullData), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td><h6 class="fw-medium text-dark mb-0"><?= htmlspecialchars($ind['designation']) ?></h6></td>
                                            <td><?= htmlspecialchars($ind['department']) ?></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-light text-dark rounded-circle me-2">AD</span>
                                                    <div>
                                                        <h6 class="fw-medium mb-0"><?= htmlspecialchars($ind['approved_by']) ?></h6>
                                                        <p class="fs-12 text-muted">Admin</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= date('d M Y', strtotime($ind['created_at'])) ?></td>
                                            <td>
                                                <span class="badge <?= $statusBadge ?> d-inline-flex align-items-center badge-xs">
                                                    <i class="ti ti-point-filled me-1"></i><?= htmlspecialchars($ind['status']) ?>
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editIndicator(<?= $indJson ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="performance_indicator.php?delete_id=<?= $ind['id'] ?>" onclick="return confirm('Are you sure?')" class="text-danger"><i class="ti ti-trash"></i></a>
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

    <div class="modal fade" id="add_indicator">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Indicator</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="performance_indicator.php" method="POST">
                    <input type="hidden" name="add_indicator_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Designation</label>
                                <select name="designation" class="form-select">
                                    <?php foreach($designations as $d): ?>
                                        <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mb-2"><h5 class="fw-medium">Technical</h5></div>
                            <?php 
                            $tech_fields = ['Customer Experience', 'Marketing', 'Management', 'Administration', 'Presentation', 'Quality', 'Efficiency'];
                            foreach($tech_fields as $tf): 
                                $slug = strtolower(str_replace(' ', '_', $tf)); 
                                // Simplify key matching for Presentation Skills -> presentation
                                if($slug == 'presentation_skills') $slug = 'presentation';
                                if($slug == 'quality_of_work') $slug = 'quality';
                            ?>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><?= $tf ?></label>
                                <select name="<?= $slug ?>" class="form-select">
                                    <option value="None">None</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <?php endforeach; ?>

                            <div class="col-md-12 mb-2 mt-2"><h5 class="fw-medium">Organizational</h5></div>
                            <?php 
                            $org_fields = ['Integrity', 'Professionalism', 'Team Work', 'Critical Thinking', 'Conflict Mgmt', 'Attendance', 'Deadline'];
                            foreach($org_fields as $of): 
                                $slug = strtolower(str_replace(' ', '_', $of));
                            ?>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><?= $of ?></label>
                                <select name="<?= $slug ?>" class="form-select">
                                    <option value="None">None</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <?php endforeach; ?>

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
                        <button type="submit" class="btn btn-primary">Add Indicator</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_indicator">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Indicator</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"><i class="ti ti-x"></i></button>
                </div>
                <form action="performance_indicator.php" method="POST">
                    <input type="hidden" name="edit_indicator_submit" value="1">
                    <input type="hidden" name="indicator_id" id="edit_id">
                    
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Designation</label>
                                <select name="designation" id="edit_desig" class="form-select">
                                    <?php foreach($designations as $d): ?>
                                        <option value="<?= htmlspecialchars($d) ?>"><?= htmlspecialchars($d) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-12"><p class="text-muted">Note: Technical & Organizational values reset on edit for simplicity in this demo. Please re-select.</p></div>
                            
                            <div class="col-md-12 mb-2"><h5 class="fw-medium">Technical</h5></div>
                            <?php 
                            foreach($tech_fields as $tf): 
                                $slug = strtolower(str_replace(' ', '_', $tf)); 
                                if($slug == 'presentation_skills') $slug = 'presentation';
                                if($slug == 'quality_of_work') $slug = 'quality';
                            ?>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><?= $tf ?></label>
                                <select name="<?= $slug ?>" id="edit_<?= $slug ?>" class="form-select">
                                    <option value="None">None</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <?php endforeach; ?>

                            <div class="col-md-12 mb-2 mt-2"><h5 class="fw-medium">Organizational</h5></div>
                            <?php foreach($org_fields as $of): $slug = strtolower(str_replace(' ', '_', $of)); ?>
                            <div class="col-md-3 mb-3">
                                <label class="form-label"><?= $of ?></label>
                                <select name="<?= $slug ?>" id="edit_<?= $slug ?>" class="form-select">
                                    <option value="None">None</option>
                                    <option value="Beginner">Beginner</option>
                                    <option value="Intermediate">Intermediate</option>
                                    <option value="Advanced">Advanced</option>
                                    <option value="Expert">Expert</option>
                                </select>
                            </div>
                            <?php endforeach; ?>

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
        function editIndicator(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_desig').value = data.designation;
            document.getElementById('edit_status').value = data.status;
            
            // Populate Technical
            if(data.tech) {
                for (const [key, value] of Object.entries(data.tech)) {
                    let el = document.getElementById('edit_' + key);
                    if(el) el.value = value;
                }
            }
            // Populate Organizational
            if(data.org) {
                for (const [key, value] of Object.entries(data.org)) {
                    let el = document.getElementById('edit_' + key);
                    if(el) el.value = value;
                }
            }

            var myModal = new bootstrap.Modal(document.getElementById('edit_indicator'));
            myModal.show();
        }
    </script>
</body>
</html>