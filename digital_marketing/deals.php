<?php
session_start();

// --- 1. DATABASE CONNECTION ---
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// --- 2. HANDLE FORM SUBMISSIONS ---

// A. ADD DEAL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_deal_submit'])) {
    $name   = mysqli_real_escape_string($conn, $_POST['deal_name']);
    $pipe   = mysqli_real_escape_string($conn, $_POST['pipeline']);
    $stat   = mysqli_real_escape_string($conn, $_POST['status']);
    $val    = floatval($_POST['deal_value']);
    $curr   = mysqli_real_escape_string($conn, $_POST['currency']);
    $period = mysqli_real_escape_string($conn, $_POST['period']);
    $pval   = intval($_POST['period_value']);
    $cont   = mysqli_real_escape_string($conn, $_POST['contact']);
    $proj   = mysqli_real_escape_string($conn, $_POST['project']);
    $due    = !empty($_POST['due_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['due_date']))) : NULL;
    $close  = !empty($_POST['expected_closing']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['expected_closing']))) : NULL;
    $assign = mysqli_real_escape_string($conn, $_POST['assignee']);
    $tags   = mysqli_real_escape_string($conn, $_POST['tags']);
    $fdate  = !empty($_POST['followup_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['followup_date']))) : NULL;
    $src    = mysqli_real_escape_string($conn, $_POST['source']);
    $prio   = mysqli_real_escape_string($conn, $_POST['priority']);
    $desc   = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO deals (deal_name, pipeline, status, deal_value, currency, period, period_value, contact, project, due_date, expected_closing, assignee, tags, followup_date, source, priority, description, created_at) 
            VALUES ('$name', '$pipe', '$stat', '$val', '$curr', '$period', '$pval', '$cont', '$proj', '$due', '$close', '$assign', '$tags', '$fdate', '$src', '$prio', '$desc', NOW())";
    
    if(mysqli_query($conn, $sql)) {
        header("Location: deals.php?msg=added"); exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// B. EDIT DEAL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_deal_submit'])) {
    $id = intval($_POST['deal_id']);
    $name = mysqli_real_escape_string($conn, $_POST['deal_name']);
    $stat = mysqli_real_escape_string($conn, $_POST['status']);
    $val  = floatval($_POST['deal_value']);
    $curr = mysqli_real_escape_string($conn, $_POST['currency']);
    $assign = mysqli_real_escape_string($conn, $_POST['assignee']);
    
    // (Update all fields similarly - simplified for demo)
    $sql = "UPDATE deals SET deal_name='$name', status='$stat', deal_value='$val', currency='$curr', assignee='$assign' WHERE id=$id";
    
    if(mysqli_query($conn, $sql)) {
        header("Location: deals.php?msg=updated"); exit();
    }
}

// C. DELETE DEAL
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM deals WHERE id=$id");
    header("Location: deals.php?msg=deleted"); exit();
}

// --- 3. FETCH & GROUP DEALS FOR KANBAN ---
$kanban = [
    'Open' => ['title' => 'Open', 'color' => 'warning', 'leads' => [], 'total' => 0],
    'Won'  => ['title' => 'Won', 'color' => 'success', 'leads' => [], 'total' => 0],
    'Lost' => ['title' => 'Lost', 'color' => 'danger', 'leads' => [], 'total' => 0]
];

$res = mysqli_query($conn, "SELECT * FROM deals ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($res)) {
    $st = $row['status'];
    // Default to 'Open' if status doesn't match keys
    $target = isset($kanban[$st]) ? $st : 'Open';
    $kanban[$target]['leads'][] = $row;
    $kanban[$target]['total'] += $row['deal_value'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Deals Grid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 25px; }
        
        /* Kanban Layout */
        .kanban-board { display: flex; overflow-x: auto; gap: 24px; padding-bottom: 20px; align-items: flex-start; }
        .kanban-column { min-width: 320px; width: 320px; flex-shrink: 0; }
        
        /* Cards */
        .card { border: 0; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); background: #fff; margin-bottom: 20px; }
        .card-body { padding: 20px; }
        
        /* Status Colors */
        .border-warning { border-top: 3px solid #ffc107; }
        .border-success { border-top: 3px solid #28a745; }
        .border-danger { border-top: 3px solid #dc3545; }
        .text-warning { color: #ffc107 !important; }
        .text-success { color: #28a745 !important; }
        .text-danger { color: #dc3545 !important; }
        
        /* Components */
        .avatar { width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 50%; color: #555; font-weight: 600; font-size: 12px; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .breadcrumb-item a { text-decoration: none; color: #6c757d; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="dashboard-scroll-area">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3 class="mb-1 fw-bold">Deals</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dm_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Deals Grid</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-white btn-sm">Sort By: Last 7 Days</button>
                    <button class="btn btn-primary btn-sm d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add_deals">
                        <i class="ti ti-circle-plus me-1"></i> Add Deal
                    </button>
                </div>
            </div>

            <div class="kanban-board">
                <?php foreach($kanban as $key => $col): 
                    $count = count($col['leads']);
                    $borderClass = "border-" . $col['color'];
                    $dotClass = "text-" . $col['color'];
                ?>
                <div class="kanban-column">
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="mb-1 fw-bold fs-16"><i class="ti ti-circle-filled fs-10 <?= $dotClass ?> me-2"></i><?= $col['title'] ?></h5>
                                    <span class="text-muted fs-13"><?= $count ?> Deals - $<?= number_format($col['total']) ?></span>
                                </div>
                                <a href="#" class="text-muted"><i class="ti ti-plus"></i></a>
                            </div>
                        </div>
                    </div>

                    <?php foreach($col['leads'] as $deal): 
                         $dJson = htmlspecialchars(json_encode($deal), ENT_QUOTES, 'UTF-8');
                         $initial = strtoupper(substr($deal['deal_name'], 0, 1));
                         $currSymbol = ($deal['currency'] == 'Euro') ? '€' : '$';
                    ?>
                    <div class="card <?= $borderClass ?>">
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="avatar me-2 bg-light text-dark"><?= $initial ?></span>
                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($deal['deal_name']) ?></h6>
                                </div>
                                <div class="dropdown">
                                    <a href="#" class="text-muted" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="openEditModal(<?= $dJson ?>)"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                        <li><a class="dropdown-item text-danger" href="deals.php?delete_id=<?= $deal['id'] ?>" onclick="return confirm('Delete this deal?')"><i class="ti ti-trash me-2"></i>Delete</a></li>
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <h5 class="fw-bold mb-2"><?= $currSymbol ?><?= number_format($deal['deal_value']) ?></h5>
                                <p class="text-muted fs-13 mb-1"><i class="ti ti-mail me-1"></i><?= htmlspecialchars($deal['contact'] ?: 'No Contact') ?></p>
                                <p class="text-muted fs-13 mb-1"><i class="ti ti-briefcase me-1"></i><?= htmlspecialchars($deal['project'] ?: 'No Project') ?></p>
                                <p class="text-muted fs-13 mb-0"><i class="ti ti-map-pin me-1"></i>USA</p>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                <div class="d-flex align-items-center">
                                    <span class="avatar avatar-sm rounded-circle me-2 bg-primary text-white">
                                        <?= substr($deal['assignee'], 0, 1) ?>
                                    </span>
                                    <span class="fs-13 fw-medium"><?= htmlspecialchars($deal['assignee']) ?></span>
                                </div>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($deal['priority']) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <div class="modal fade" id="add_deals" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Deals</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_deal_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Deal Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="deal_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pipeline <span class="text-danger">*</span></label>
                                <select class="form-select" name="pipeline">
                                    <option>Sales</option><option>Marketing</option><option>Calls</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select" name="status">
                                    <option value="Open">Open</option>
                                    <option value="Won">Won</option>
                                    <option value="Lost">Lost</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Deal Value <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="deal_value">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Currency <span class="text-danger">*</span></label>
                                <select class="form-select" name="currency">
                                    <option>Dollar</option><option>Euro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Period</label>
                                <select class="form-select" name="period"><option>Days</option><option>Months</option></select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Period Value</label>
                                <input type="text" class="form-control" name="period_value">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Contact</label>
                                <input class="form-control" type="text" name="contact" value="Vaughan Lewis">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Project</label>
                                <input class="form-control" type="text" name="project" value="Office Management App">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expected Closing Date</label>
                                <input type="date" class="form-control" name="expected_closing">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Assignee</label>
                                <input class="form-control" type="text" name="assignee" value="Vaughan Lewis">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tags</label>
                                <input class="form-control" type="text" name="tags" value="Collab">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Followup Date</label>
                                <input type="date" class="form-control" name="followup_date">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select" name="source">
                                    <option>Phone Calls</option><option>Social Media</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select class="form-select" name="priority">
                                    <option>High</option><option>Low</option><option>Medium</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Deal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_deals" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Deal</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="edit_deal_submit" value="1">
                    <input type="hidden" name="deal_id" id="edit_id">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3"><label class="form-label">Deal Name</label><input type="text" name="deal_name" id="edit_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Status</label><select name="status" id="edit_status" class="form-select"><option value="Open">Open</option><option value="Won">Won</option><option value="Lost">Lost</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Value</label><input type="text" name="deal_value" id="edit_value" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Currency</label><select name="currency" id="edit_curr" class="form-select"><option>Dollar</option><option>Euro</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Assignee</label><input type="text" name="assignee" id="edit_assign" class="form-control"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Deal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.deal_name;
            document.getElementById('edit_status').value = data.status;
            document.getElementById('edit_value').value = data.deal_value;
            document.getElementById('edit_curr').value = data.currency;
            document.getElementById('edit_assign').value = data.assignee;
            var modal = new bootstrap.Modal(document.getElementById('edit_deals'));
            modal.show();
        }
    </script>
</body>
</html>