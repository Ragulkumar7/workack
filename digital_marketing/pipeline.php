<?php
session_start();
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// --- ACTIONS ---

// 1. ADD PIPELINE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_pipeline_submit'])) {
    $name = mysqli_real_escape_string($conn, $_POST['pipeline_name']);
    // Logic to handle the 'Access' radio button
    $access = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'All'; 
    
    $sql = "INSERT INTO pipelines (pipeline_name, access, status, created_date) VALUES ('$name', '$access', 'Active', NOW())";
    mysqli_query($conn, $sql);
    header("Location: pipeline.php?msg=added"); exit();
}

// 2. EDIT PIPELINE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_pipeline_submit'])) {
    $id = intval($_POST['pipeline_id']);
    $name = mysqli_real_escape_string($conn, $_POST['pipeline_name']);
    $access = isset($_POST['status']) ? mysqli_real_escape_string($conn, $_POST['status']) : 'All'; 
    
    $sql = "UPDATE pipelines SET pipeline_name='$name', access='$access' WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: pipeline.php?msg=updated"); exit();
}

// 3. ADD STAGE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_stage_submit'])) {
    // For now, just redirecting as stage logic depends on parent pipeline selection
    header("Location: pipeline.php?msg=stage_added"); exit();
}

// 4. DELETE
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM pipelines WHERE id=$id");
    header("Location: pipeline.php?msg=deleted"); exit();
}

// FETCH DATA
$pipelines = [];
$res = mysqli_query($conn, "SELECT * FROM pipelines ORDER BY created_date DESC");
if ($res) { while($row = mysqli_fetch_assoc($res)) { $pipelines[] = $row; } }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pipeline</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .page-wrapper { flex: 1; padding: 25px; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        /* Add your theme specific CSS here if missing */
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .text-primary { color: #FF9B44 !important; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="page-wrapper">
            <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                <div class="my-auto mb-2">
                    <h2 class="mb-1">Pipeline</h2>
                    <nav>
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="index.html"><i class="ti ti-smart-home"></i></a></li>
                            <li class="breadcrumb-item">CRM</li>
                            <li class="breadcrumb-item active" aria-current="page">Pipeline List</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex my-xl-auto right-content align-items-center flex-wrap">
                    <div class="mb-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_pipeline" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Pipeline</a>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                    <h5 class="mb-0">Pipeline List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Pipeline Name</th>
                                    <th>Total Deal Value</th>
                                    <th>No of Deals</th>
                                    <th>Stages</th>
                                    <th>Created Date</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($pipelines as $p): 
                                    $pJson = htmlspecialchars(json_encode($p), ENT_QUOTES, 'UTF-8');
                                ?>
                                <tr>
                                    <td><h6 class="fs-14 fw-medium"><?= htmlspecialchars($p['pipeline_name']) ?></h6></td>
                                    <td>$<?= number_format($p['total_deal_value']) ?></td>
                                    <td><?= number_format($p['no_of_deals']) ?></td>
                                    <td>
                                        <div class="progress progress-xs mb-1" style="width: 100px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 80%"></div>
                                        </div>
                                        <span class="fs-12">Won</span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($p['created_date'])) ?></td>
                                    <td><span class="badge badge-soft-success">Active</span></td>
                                    <td>
                                        <div class="dropdown">
                                            <a href="javascript:void(0);" class="btn btn-icon btn-sm" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></a>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="javascript:void(0);" onclick="openEditPipeline(<?= $pJson ?>)"><i class="ti ti-edit me-1"></i>Edit</a></li>
                                                <li><a class="dropdown-item" href="pipeline.php?delete_id=<?= $p['id'] ?>" onclick="return confirm('Delete?')"><i class="ti ti-trash me-1"></i>Delete</a></li>
                                            </ul>
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

    <div class="modal fade" id="add_pipeline">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Pipeline</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_pipeline_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Pipeline Name <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="pipeline_name" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="input-block mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Pipeline Stages <span class="text-danger"> *</span></label>
                                        <a href="#" class="add-new text-primary" data-bs-toggle="modal" data-bs-target="#add_stage"><i class="ti ti-plus text-primary me-1"></i>Add New</a>
                                    </div>
                                    <div class="p-3 border border-gray br-5 mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><i class="ti ti-grip-vertical"></i></span>
                                                <h6 class="fs-14 fw-normal">Inpipline</h6>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="text-default" data-bs-toggle="modal" data-bs-target="#edit_stage"><span class="me-2"><i class="ti ti-edit"></i></span></a>
                                                <a href="#" class="text-default" data-bs-toggle="modal" data-bs-target="#delete_modal"><span><i class="ti ti-trash"></i></span></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 border border-gray br-5 mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><i class="ti ti-grip-vertical"></i></span>
                                                <h6 class="fs-14 fw-normal">Follow Up</h6>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="text-default" data-bs-toggle="modal" data-bs-target="#edit_stage"><span class="me-2"><i class="ti ti-edit"></i></span></a>
                                                <a href="#" class="text-default" data-bs-toggle="modal" data-bs-target="#delete_modal"><span><i class="ti ti-trash"></i></span></a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-3 border border-gray br-5">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><i class="ti ti-grip-vertical"></i></span>
                                                <h6 class="fs-14 fw-normal">Schedule Service</h6>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="text-default" data-bs-toggle="modal" data-bs-target="#edit_stage"><span class="me-2"><i class="ti ti-edit"></i></span></a>
                                                <a href="#" class="text-default"><span><i class="ti ti-trash" data-bs-toggle="modal" data-bs-target="#delete_modal"></i></span></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Access</label>
                                    <div class="d-flex access-item nav">
                                        <div class="d-flex align-items-center">
                                            <div class="radio-btn d-flex align-items-center " data-bs-toggle="tab" data-bs-target="#all">
                                                <input type="radio" class="status-radio me-2" id="all" name="status" value="All" checked >
                                                <label for="all">All</label>
                                            </div>
                                            <div class="radio-btn d-flex align-items-center " data-bs-toggle="tab" data-bs-target="#select-person">
                                                <input type="radio" class="status-radio me-2" id="select" name="status" value="Select Person">
                                                <label for="select">Select Person</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-content">
                                        <div class="tab-pane fade" id="select-person">
                                            <div class="access-wrapper">
                                                <div class="p-3 border border-gray br-5 mb-2">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center file-name-icon">
                                                            <a href="#" class="avatar avatar-md border avatar-rounded">
                                                                <img src="assets/img/profiles/avatar-20.jpg" class="img-fluid" alt="img">
                                                            </a>
                                                            <div class="ms-2">
                                                                <h6 class="fw-medium"><a href="#">Sharon Roy</a></h6>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <a href="#" class="text-danger">Remove</a>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="p-3 border border-gray br-5 mb-2">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center file-name-icon">
                                                            <a href="#" class="avatar avatar-md border avatar-rounded">
                                                                <img src="assets/img/profiles/avatar-21.jpg" class="img-fluid" alt="img">
                                                            </a>
                                                            <div class="ms-2">
                                                                <h6 class="fw-medium"><a href="#">Sharon Roy</a></h6>
                                                            </div>
                                                        </div>
                                                        <div class="d-flex align-items-center">
                                                            <a href="#" class="text-danger">Remove</a>
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
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" >Add Pipeline</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="edit_pipeline">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Pipeline</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="edit_pipeline_submit" value="1">
                    <input type="hidden" name="pipeline_id" id="edit_id">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Pipeline Name <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="pipeline_name" id="edit_name" value="Marketing">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="input-block mb-3">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <label class="form-label">Pipeline Stages <span class="text-danger"> *</span></label>
                                        <a href="#" class="add-new text-primary" data-bs-toggle="modal" data-bs-target="#add_stage"><i class="ti ti-plus text-primary me-1"></i>Add New</a>
                                    </div>
                                    <div class="p-3 border border-gray br-5 mb-2">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="d-flex align-items-center">
                                                <span class="me-2"><i class="ti ti-grip-vertical"></i></span>
                                                <h6 class="fs-14 fw-normal">Inpipline</h6>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <a href="#" class="text-default"><span class="me-2"><i class="ti ti-edit"></i></span></a>
                                                <a href="#" class="text-default"><span><i class="ti ti-trash"></i></span></a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Access</label>
                                    <div class="d-flex access-item nav">
                                        <div class="d-flex align-items-center">
                                            <div class="radio-btn d-flex align-items-center " data-bs-toggle="tab" data-bs-target="#all2">
                                                <input type="radio" class="status-radio me-2" id="all2" name="status" value="All" checked >
                                                <label for="all2">All</label>
                                            </div>
                                            <div class="radio-btn d-flex align-items-center " data-bs-toggle="tab" data-bs-target="#select-person2">
                                                <input type="radio" class="status-radio me-2" id="select2" name="status" value="Select Person">
                                                <label for="select2">Select Person</label>
                                            </div>
                                        </div>
                                    </div>
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" >Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="pipeline-access">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Pipeline Access</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form action="pipeline.html">
                    <div class="modal-body pb-0">
                        </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="add_stage">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Stage</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_stage_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Stage Name <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" name="stage_name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Stage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="edit_stage">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Stage</h4>
                    <button type="button" class="btn-close custom-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <i class="ti ti-x"></i>
                    </button>
                </div>
                <form method="POST">
                    <input type="hidden" name="edit_stage_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Edit Name <span class="text-danger"> *</span></label>
                                    <input type="text" class="form-control" value="Inpipeline" name="stage_name">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Simple JS to pass data to Edit Modal
        function openEditPipeline(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.pipeline_name;
            // You can add logic here to select the correct radio button based on data.access
            var modal = new bootstrap.Modal(document.getElementById('edit_pipeline'));
            modal.show();
        }
    </script>
</body>
</html>