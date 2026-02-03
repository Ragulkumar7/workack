<?php
session_start();
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// 1. GET COMPANY ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) { die("Invalid Company ID"); }
$id = intval($_GET['id']);

// Fetch Company Data
$res = mysqli_query($conn, "SELECT * FROM companies WHERE id = $id");
$company = mysqli_fetch_assoc($res);
if (!$company) die("Company not found");

// --- FORM HANDLERS ---

// A. ADD DEAL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_deal_submit'])) {
    $deal   = mysqli_real_escape_string($conn, $_POST['deal_name']);
    $pipe   = mysqli_real_escape_string($conn, $_POST['pipeline']);
    $stat   = mysqli_real_escape_string($conn, $_POST['status']);
    $val    = floatval($_POST['deal_value']);
    $curr   = mysqli_real_escape_string($conn, $_POST['currency']);
    $period = mysqli_real_escape_string($conn, $_POST['period']);
    $p_val  = intval($_POST['period_value']);
    $cont   = mysqli_real_escape_string($conn, $_POST['contact']);
    $proj   = mysqli_real_escape_string($conn, $_POST['project']);
    $due    = !empty($_POST['due_date']) ? date('Y-m-d', strtotime($_POST['due_date'])) : NULL;
    $close  = !empty($_POST['expected_closing']) ? date('Y-m-d', strtotime($_POST['expected_closing'])) : NULL;
    $assign = mysqli_real_escape_string($conn, $_POST['assignee']);
    $tags   = mysqli_real_escape_string($conn, $_POST['tags']);
    $f_date = !empty($_POST['followup_date']) ? date('Y-m-d', strtotime($_POST['followup_date'])) : NULL;
    $src    = mysqli_real_escape_string($conn, $_POST['source']);
    $prio   = mysqli_real_escape_string($conn, $_POST['priority']);
    $desc   = mysqli_real_escape_string($conn, $_POST['description']);

    $sql = "INSERT INTO deals (company_id, deal_name, pipeline, status, deal_value, currency, period, period_value, contact, project, due_date, expected_closing, assignee, tags, followup_date, source, priority, description, created_at) 
            VALUES ($id, '$deal', '$pipe', '$stat', '$val', '$curr', '$period', '$p_val', '$cont', '$proj', '$due', '$close', '$assign', '$tags', '$f_date', '$src', '$prio', '$desc', NOW())";
    
    if(mysqli_query($conn, $sql)) {
        mysqli_query($conn, "INSERT INTO company_activities (company_id, type, title, description, created_at) VALUES ($id, 'Deal', 'New Deal', 'Deal $deal added', NOW())");
        header("Location: company-details.php?id=$id&msg=deal_added"); exit();
    } else { echo "Error: " . mysqli_error($conn); }
}

// B. ADD NOTE
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_note_submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['note_title']);
    $note  = mysqli_real_escape_string($conn, $_POST['note_desc']);
    $file_path = "";
    
    // File Upload
    if(isset($_FILES['note_file']) && $_FILES['note_file']['error'] == 0){
        $target_dir = "uploads/notes/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $file_path = $target_dir . time() . "_" . basename($_FILES['note_file']['name']);
        move_uploaded_file($_FILES['note_file']['tmp_name'], $file_path);
    }

    $sql = "INSERT INTO company_notes (company_id, title, note, attachment, created_at) VALUES ($id, '$title', '$note', '$file_path', NOW())";
    mysqli_query($conn, $sql);
    mysqli_query($conn, "INSERT INTO company_activities (company_id, type, title, description, created_at) VALUES ($id, 'Note', 'Note Added', '$title', NOW())");
    header("Location: company-details.php?id=$id"); exit();
}

// C. ADD CALL
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_call_submit'])) {
    $status = mysqli_real_escape_string($conn, $_POST['call_status']);
    $date   = !empty($_POST['call_date']) ? date('Y-m-d', strtotime($_POST['call_date'])) : NULL;
    $note   = mysqli_real_escape_string($conn, $_POST['call_note']);
    $task   = isset($_POST['call_task']) ? 1 : 0;

    $sql = "INSERT INTO company_calls (company_id, status, followup_date, note, create_task, created_at) VALUES ($id, '$status', '$date', '$note', $task, NOW())";
    mysqli_query($conn, $sql);
    mysqli_query($conn, "INSERT INTO company_activities (company_id, type, title, description, created_at) VALUES ($id, 'Call', 'Call Logged', 'Status: $status', NOW())");
    header("Location: company-details.php?id=$id"); exit();
}

// D. ADD FILE (Create Document)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_file_submit'])) {
    $c_id   = $company_id;
    $title  = mysqli_real_escape_string($conn, $_POST['file_title']);
    $deal   = mysqli_real_escape_string($conn, $_POST['file_deal']);
    $type   = mysqli_real_escape_string($conn, $_POST['file_type']);
    $owner  = mysqli_real_escape_string($conn, $_POST['file_owner']);
    // ... (Add other fields similarly)

    $sql = "INSERT INTO company_files (company_id, title, deal_id, document_type, owner, created_at) 
            VALUES ($c_id, '$title', '$deal', '$type', '$owner', NOW())";
    
    if(mysqli_query($conn, $sql)) {
        // Add to Activity
        $desc = "Document '$title' created.";
        mysqli_query($conn, "INSERT INTO company_activities (company_id, type, title, description, created_at) VALUES ($c_id, 'File', 'File Created', '$desc', NOW())");
        header("Location: company-details.php?id=$c_id&msg=file_added"); exit();
    }
}

// FETCH FILES
$files = []; 
$res = mysqli_query($conn, "SELECT * FROM company_files WHERE company_id=$id ORDER BY created_at DESC"); 
while($r=mysqli_fetch_assoc($res)) $files[]=$r;

// --- FETCH DATA ---
$activities = []; $res = mysqli_query($conn, "SELECT * FROM company_activities WHERE company_id=$id ORDER BY created_at DESC"); while($r=mysqli_fetch_assoc($res)) $activities[]=$r;
$notes = []; $res = mysqli_query($conn, "SELECT * FROM company_notes WHERE company_id=$id ORDER BY created_at DESC"); while($r=mysqli_fetch_assoc($res)) $notes[]=$r;
$calls = []; $res = mysqli_query($conn, "SELECT * FROM company_calls WHERE company_id=$id ORDER BY created_at DESC"); while($r=mysqli_fetch_assoc($res)) $calls[]=$r;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($company['company_name']) ?> - Details</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .page-content { padding: 25px; flex: 1; }

        /* Card Styles */
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .card-body { padding: 20px; }
        .card-bg-1 { background-color: #fff; }

        /* Avatar */
        .avatar-xl { width: 100px; height: 100px; display: flex; align-items: center; justify-content: center; background: #fff; border-radius: 50%; }
        .avatar-md { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white; }

        /* Sticky Sidebar */
        .theiaStickySidebar { position: sticky; top: 20px; }

        /* Tabs */
        .nav-tabs-bottom .nav-link { border: 0; border-bottom: 2px solid transparent; color: #555; padding: 10px 0; margin: 0 15px; }
        .nav-tabs-bottom .nav-link.active { color: #FF9B44; border-bottom-color: #FF9B44; }
        .nav-justified .nav-item { flex-basis: 0; flex-grow: 1; text-align: center; }

        /* Icons & Colors */
        .bg-skyblue { background: #00c5fb !important; }
        .bg-success { background: #28a745 !important; }
        .bg-warning { background: #ffc107 !important; }
        .bg-purple { background: #7460ee !important; }
        .text-gray { color: #888; }
        
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .text-primary { color: #FF9B44 !important; }
        .bg-light-500 { background-color: #f8f9fa; }
        
        /* Breadcrumb */
        .breadcrumb-item a { text-decoration: none; color: #6c757d; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="page-wrapper">
            <div class="page-content">
                <div class="row align-items-center mb-4">
                    <div class="col-sm-6">
                        <h6 class="fw-medium d-inline-flex align-items-center mb-3 mb-sm-0">
                            <a href="companies.php" class="text-decoration-none text-dark">
                                <i class="ti ti-arrow-left me-2"></i>Companies
                            </a>
                            <span class="text-gray d-inline-flex ms-2">/ <?= htmlspecialchars($company['company_name']) ?></span>
                        </h6>
                    </div>
                    <div class="col-sm-6">
                        <div class="d-flex align-items-center justify-content-sm-end">
                            <a href="javascript:void(0);" class="btn btn-primary d-inline-flex align-items-center me-2" data-bs-toggle="modal" data-bs-target="#add_deals">
                                <i class="ti ti-circle-plus me-2"></i>Add Deal
                            </a>
                            <a href="mailto:<?= $company['email'] ?>" class="btn btn-dark d-inline-flex align-items-center"><i class="ti ti-mail me-2"></i>Send Email</a>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xl-4 theiaStickySidebar">
                        <div class="card card-bg-1">
                            <div class="card-body p-0">
                                <span class="avatar avatar-xl border bg-white rounded-circle m-auto d-flex mb-2">
                                    <?php $img = !empty($company['image']) ? $company['image'] : 'assets/img/company/company-01.svg'; ?>
                                    <img src="<?= $img ?>" class="w-auto h-auto" alt="Img" style="max-width:60px;">
                                </span>
                                <div class="text-center px-3 pb-3 border-bottom">
                                    <h5 class="d-flex align-items-center justify-content-center mb-1"><?= htmlspecialchars($company['company_name']) ?> <i class="ti ti-discount-check-filled text-success ms-1"></i></h5>
                                    <p class="text-dark"><?= htmlspecialchars($company['address'] ?? 'No Address') ?></p>
                                </div>
                                <div class="p-3 border-bottom">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <h6>Basic information</h6>
                                        <a href="javascript:void(0);" class="btn btn-icon btn-sm"><i class="ti ti-edit"></i></a>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center"><i class="ti ti-phone me-2"></i>Phone</span>
                                        <p class="text-dark"><?= htmlspecialchars($company['phone']) ?></p>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="d-inline-flex align-items-center"><i class="ti ti-mail-check me-2"></i>Email</span>
                                        <a href="#" class="text-info"><?= htmlspecialchars($company['email']) ?></a>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span class="d-inline-flex align-items-center"><i class="ti ti-gender-male me-2"></i>Created On</span>
                                        <p class="text-dark"><?= date('d M Y, h:i A', strtotime($company['created_at'])) ?></p>
                                    </div>
                                </div>
                                <div class="p-3 border-bottom">
                                    <h5 class="mb-3">Tags</h5>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-success-subtle text-success me-3">Collab</span>
                                        <span class="badge bg-warning-subtle text-warning">Rated</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-8">
                        <div>
                            <div class="bg-white rounded">
                                <ul class="nav nav-tabs nav-tabs-bottom nav-justified flex-wrap mb-3" role="tablist">
                                    <li class="nav-item"><a class="nav-link active fw-medium" data-bs-toggle="tab" href="#tab_activities"><i class="ti ti-activity me-1"></i> Activities</a></li>
                                    <li class="nav-item"><a class="nav-link fw-medium" data-bs-toggle="tab" href="#tab_notes"><i class="ti ti-file-description me-1"></i> Notes</a></li>
                                    <li class="nav-item"><a class="nav-link fw-medium" data-bs-toggle="tab" href="#tab_calls"><i class="ti ti-phone-call me-1"></i> Calls</a></li>
                                    <li class="nav-item"><a class="nav-link fw-medium" data-bs-toggle="tab" href="#tab_files"><i class="ti ti-files me-1"></i> Files</a></li>
                                </ul>
                            </div>
                            <div class="tab-content">
                                
                                <div class="tab-pane active show" id="tab_activities">
                                    <div class="card border-0">
                                        <div class="card-header"><h5>Activities</h5></div>
                                        <div class="card-body">
                                            <?php foreach($activities as $act): 
                                                $icon = 'ti-message-circle-2'; $bg = 'bg-skyblue';
                                                if($act['type'] == 'Call') { $icon='ti-phone'; $bg='bg-success'; }
                                                if($act['type'] == 'Note') { $icon='ti-file-description'; $bg='bg-warning'; }
                                                if($act['type'] == 'Deal') { $icon='ti-star'; $bg='bg-purple'; }
                                            ?>
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex align-items-start">
                                                    <span class="avatar avatar-md avatar-rounded flex-shrink-0 <?= $bg ?> me-2"><i class="<?= $icon ?> fs-20"></i></span>
                                                    <div>
                                                        <h6 class="fw-medium mb-1"><?= htmlspecialchars($act['title']) ?></h6>
                                                        <p class="mb-1"><?= htmlspecialchars($act['description']) ?></p>
                                                        <span class="fs-12 text-muted"><?= date('h:i A', strtotime($act['created_at'])) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab_notes">
                                    <div class="card border-0">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5>Notes</h5>
                                            <a href="javascript:void(0);" class="d-inline-flex align-items-center text-primary fw-medium" data-bs-toggle="modal" data-bs-target="#add_notes">
                                                <i class="ti ti-circle-plus me-1"></i> Add Note
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach($notes as $note): ?>
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-md avatar-rounded flex-shrink-0 bg-warning me-2"><i class="ti ti-file-description fs-20"></i></span>
                                                        <div>
                                                            <h6 class="fw-medium mb-1"><?= htmlspecialchars($note['title']) ?></h6>
                                                            <span><?= date('d M Y, h:i A', strtotime($note['created_at'])) ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div>
                                                    <p class="mb-3"><?= nl2br(htmlspecialchars($note['note'])) ?></p>
                                                    <?php if($note['attachment']): ?>
                                                        <div class="border rounded d-flex align-items-center p-2" style="width:fit-content;">
                                                            <i class="ti ti-paperclip fs-18 me-2"></i>
                                                            <a href="<?= $note['attachment'] ?>" target="_blank" class="text-dark">View Attachment</a>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab_calls">
                                    <div class="card border-0">
                                        <div class="card-header d-flex justify-content-between align-items-center">
                                            <h5>Calls</h5>
                                            <a href="javascript:void(0);" class="d-inline-flex align-items-center text-primary fw-medium" data-bs-toggle="modal" data-bs-target="#add_call">
                                                <i class="ti ti-circle-plus me-1"></i> Add New
                                            </a>
                                        </div>
                                        <div class="card-body">
                                            <?php foreach($calls as $call): ?>
                                            <div class="border rounded p-3 mb-3">
                                                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-3">
                                                    <div class="d-flex align-items-center">
                                                        <span class="avatar avatar-md avatar-rounded flex-shrink-0 bg-success me-2"><i class="ti ti-phone fs-20"></i></span>
                                                        <div>
                                                            <p class="fw-medium"><span class="text-dark">Log: </span> <?= htmlspecialchars($call['note']) ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-light text-dark border me-2"><?= htmlspecialchars($call['status']) ?></span>
                                                    </div>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane" id="tab_files">
    <div class="card border-0">
        <div class="card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                <h5>Files</h5>
            </div>
        </div>
        <div class="card-body">
            <div class="border rounded p-3 mb-3">
                <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <div>
                        <h6 class="fw-medium mb-1">Manage Documents</h6>
                        <p class="mb-0 text-muted">Send customizable quotes, proposals and contracts to close deals faster.</p>
                    </div>
                    <div>
                        <a href="#" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#create_file">Create Document</a>
                    </div>
                </div>
            </div>

            <?php if (count($files) > 0): ?>
                <?php foreach($files as $file): 
                    $badgeColor = 'bg-pink-transparent'; // Default
                    if($file['document_type'] == 'Quote') $badgeColor = 'bg-soft-info';
                    $avatar = 'assets/img/profiles/avatar-0' . rand(1,9) . '.jpg'; // Random avatar for demo
                ?>
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3 mb-2">
                        <div>
                            <h6 class="fw-medium mb-1"><?= htmlspecialchars($file['title']) ?></h6>
                            <p class="mb-0 text-muted">Send customizable quotes, proposals and contracts to close deals faster.</p>
                        </div>
                        <div class="d-flex align-items-center">
                            <a href="#" class="btn btn-icon btn-sm fs-20"><i class="ti ti-download"></i></a>
                            <a href="#" class="btn btn-icon btn-sm fs-20"><i class="ti ti-edit"></i></a>
                            <a href="#" class="btn btn-icon btn-sm fs-20 text-danger"><i class="ti ti-trash"></i></a>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-md avatar-rounded flex-shrink-0 me-2">
                                <img src="<?= $avatar ?>" alt="Img">
                            </span>
                            <div>
                                <span class="d-inline-flex mb-1 text-muted fs-12">Owner</span>
                                <h6 class="fw-medium mb-0"><?= htmlspecialchars($file['owner']) ?></h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <span class="badge <?= $badgeColor ?> me-2"><?= htmlspecialchars($file['document_type']) ?></span>
                            <span class="badge badge-dark-transparent"><i class="ti ti-point-filled"></i>Proposal</span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center p-5 text-muted">
                    <i class="ti ti-folder-off fs-30 mb-2"></i>
                    <p>No documents created yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_deals">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Deal</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x"></i></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_deal_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3"><label class="form-label">Deal Name *</label><input type="text" name="deal_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Pipeline</label><select name="pipeline" class="form-select"><option>Sales</option><option>Marketing</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Status</label><select name="status" class="form-select"><option>Open</option><option>Won</option><option>Lost</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Value</label><input type="text" name="deal_value" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Currency</label><select name="currency" class="form-select"><option>Dollar</option><option>Euro</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Period</label><select name="period" class="form-select"><option>Days</option><option>Months</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Period Value</label><input type="text" name="period_value" class="form-control"></div>
                            <div class="col-md-12 mb-3"><label class="form-label">Contact</label><input type="text" name="contact" class="form-control"></div>
                            <div class="col-md-12 mb-3"><label class="form-label">Project</label><input type="text" name="project" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Expected Closing</label><input type="date" name="expected_closing" class="form-control"></div>
                            <div class="col-md-12 mb-3"><label class="form-label">Assignee</label><input type="text" name="assignee" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Tags</label><input type="text" name="tags" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Followup Date</label><input type="date" name="followup_date" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Source</label><select name="source" class="form-select"><option>Phone</option><option>Email</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Priority</label><select name="priority" class="form-select"><option>High</option><option>Medium</option><option>Low</option></select></div>
                            <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control"></textarea></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Add Deal</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_notes" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header header-border">
                    <h5 class="modal-title">Add New Note</h5>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x"></i></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_note_submit" value="1">
                    <div class="modal-body">
                        <div class="mb-3"><label class="form-label">Title *</label><input class="form-control" type="text" name="note_title" required></div>
                        <div class="mb-3"><label class="form-label">Note *</label><textarea class="form-control" rows="4" name="note_desc" required></textarea></div>
                        <div class="mb-3"><label class="form-label">Attachment</label><input type="file" name="note_file" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary" type="submit">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_call" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header header-border">
                    <h5 class="modal-title">Create Call Log</h5>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close"><i class="ti ti-x"></i></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_call_submit" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Status</label><select class="select form-control" name="call_status"><option>Busy</option><option>Unavailable</option><option>No Answer</option><option>Connected</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Followup Date</label><input type="date" class="form-control" name="call_date"></div>
                            <div class="col-md-12 mb-3"><label class="form-label">Note</label><textarea class="form-control" rows="3" name="call_note"></textarea></div>
                            <div class="col-md-12"><div class="form-check"><input class="form-check-input" type="checkbox" name="call_task" id="chk"><label class="form-check-label" for="chk">Create task</label></div></div>
                        </div>
                    </div>
                    <div class="modal-footer"><button class="btn btn-primary" type="submit">Save</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="create_file" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header header-border align-items-center justify-content-between">
                <h5 class="modal-title">Create New File</h5>
                <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="ti ti-x"></i>
                </button>
            </div>
            <form method="POST">
                <input type="hidden" name="create_file_submit" value="1">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Title <span class="text-danger">*</span></label>
                            <input class="form-control" type="text" name="file_title" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Choose Deal <span class="text-danger">*</span></label>
                            <select class="select form-control" name="file_deal">
                                <option>Select</option>
                                <option value="Deal 1">Collins Deal</option>
                                <option value="Deal 2">Wisozk Deal</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                            <select class="select form-control" name="file_type">
                                <option value="Contract">Contract</option>
                                <option value="Proposal">Proposal</option>
                                <option value="Quote">Quote</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Owner <span class="text-danger">*</span></label>
                            <select class="select form-control" name="file_owner">
                                <option value="Admin">Admin</option>
                                <option value="Jackson">Jackson Daniel</option>
                            </select>
                        </div>
                        </div>
                </div>
                <div class="modal-footer">
                    <div class="d-flex align-items-center justify-content-end">
                        <button type="button" class="btn btn-outline-light border me-2" data-bs-dismiss="modal">Cancel</button>
                        <button class="btn btn-primary" type="submit">Create</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>