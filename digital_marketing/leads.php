<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// --- HANDLE FORM SUBMISSIONS ---

// A. ADD LEAD
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_lead'])) {
    $name     = mysqli_real_escape_string($conn, $_POST['lead_name'] ?? '');
    $type     = mysqli_real_escape_string($conn, $_POST['lead_type'] ?? 'Organization');
    $company  = mysqli_real_escape_string($conn, $_POST['company_name'] ?? '');
    $value    = floatval($_POST['lead_value'] ?? 0);
    $currency = mysqli_real_escape_string($conn, $_POST['lead_currency'] ?? 'USD');
    $phone    = mysqli_real_escape_string($conn, $_POST['lead_phone'] ?? '');
    $email    = mysqli_real_escape_string($conn, $_POST['lead_email'] ?? '');
    $source   = mysqli_real_escape_string($conn, $_POST['lead_source'] ?? '');
    $industry = mysqli_real_escape_string($conn, $_POST['lead_industry'] ?? '');
    $owner    = mysqli_real_escape_string($conn, $_POST['lead_owner'] ?? '');
    $desc     = mysqli_real_escape_string($conn, $_POST['lead_description'] ?? '');
    $vis      = mysqli_real_escape_string($conn, $_POST['lead_visibility'] ?? 'Public');
    $stage    = 'New'; // Default stage

    $sql = "INSERT INTO leads (lead_name, lead_type, company_name, lead_value, lead_currency, lead_phone, lead_email, lead_source, lead_industry, lead_owner, lead_stage, lead_description, lead_visibility, created_at) 
            VALUES ('$name', '$type', '$company', '$value', '$currency', '$phone', '$email', '$source', '$industry', '$owner', '$stage', '$desc', '$vis', NOW())";
    mysqli_query($conn, $sql);
    header("Location: leads.php"); exit();
}

// B. EDIT LEAD
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_lead'])) {
    $id       = intval($_POST['lead_id']);
    $name     = mysqli_real_escape_string($conn, $_POST['lead_name']);
    $company  = mysqli_real_escape_string($conn, $_POST['company_name']);
    $value    = floatval($_POST['lead_value']);
    $currency = mysqli_real_escape_string($conn, $_POST['lead_currency']);
    $phone    = mysqli_real_escape_string($conn, $_POST['lead_phone']);
    $email    = mysqli_real_escape_string($conn, $_POST['lead_email']);
    $source   = mysqli_real_escape_string($conn, $_POST['lead_source']);
    $industry = mysqli_real_escape_string($conn, $_POST['lead_industry']);
    $owner    = mysqli_real_escape_string($conn, $_POST['lead_owner']);
    $desc     = mysqli_real_escape_string($conn, $_POST['lead_description']);
    $stage    = mysqli_real_escape_string($conn, $_POST['lead_stage']); // Important for Kanban movement

    $sql = "UPDATE leads SET 
            lead_name='$name', company_name='$company', lead_value='$value', lead_currency='$currency',
            lead_phone='$phone', lead_email='$email', lead_source='$source', lead_industry='$industry',
            lead_owner='$owner', lead_description='$desc', lead_stage='$stage'
            WHERE id=$id";
    
    mysqli_query($conn, $sql);
    header("Location: leads.php"); exit();
}

// C. DELETE LEAD
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM leads WHERE id=$id");
    header("Location: leads.php"); exit();
}

// --- FETCH & GROUP DATA ---
$kanban = [
    'Contacted' => ['title' => 'Contacted', 'color' => 'warning', 'leads' => [], 'total' => 0],
    'New'       => ['title' => 'Not Contacted', 'color' => 'purple', 'leads' => [], 'total' => 0],
    'Closed'    => ['title' => 'Closed', 'color' => 'success', 'leads' => [], 'total' => 0],
    'Lost'      => ['title' => 'Lost', 'color' => 'danger', 'leads' => [], 'total' => 0]
];

$result = mysqli_query($conn, "SELECT * FROM leads ORDER BY created_at DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $stage = $row['lead_stage'];
        if (isset($kanban[$stage])) {
            $kanban[$stage]['leads'][] = $row;
            $kanban[$stage]['total'] += $row['lead_value'];
        } else {
            $kanban['New']['leads'][] = $row;
            $kanban['New']['total'] += $row['lead_value'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leads Grid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">

    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 25px; }

        /* Card Styles */
        .card { border: 0; border-radius: 8px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); background: #fff; margin-bottom: 20px; }
        .card-body { padding: 20px; }
        
        /* Kanban specific */
        .kanban-board { display: flex; overflow-x: auto; align-items: flex-start; gap: 24px; padding-bottom: 20px; }
        .kanban-list-items { min-width: 310px; width: 310px; flex-shrink: 0; }
        
        /* Colors & Borders */
        .border-warning { border-color: #ffc107 !important; }
        .border-purple { border-color: #7460ee !important; }
        .border-success { border-color: #28a745 !important; }
        .border-danger { border-color: #dc3545 !important; }
        
        /* Avatars & Text */
        .avatar { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #555; background: #f4f7fc; }
        .text-default { color: #777; font-size: 13px; }
        .text-dark { color: #333; }
        .fs-8 { font-size: 10px; }
        .fw-semibold { font-weight: 600; }
        .fw-medium { font-weight: 500; }
        
        /* Header Overrides */
        .btn-white { background: #fff; border: 1px solid #e3e3e3; color: #333; }
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
                    <h3 class="mb-1 fw-bold">Leads</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dm_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Leads Grid</li>
                        </ol>
                    </nav>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add_leads"><i class="ti ti-plus"></i> Add Lead</button>
                </div>
            </div>

            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5>Leads Grid</h5>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-white dropdown-toggle" type="button" data-bs-toggle="dropdown">Sort By : Last 7 Days</button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#">Recently Added</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="kanban-board">
                <?php foreach($kanban as $key => $col): ?>
                <div class="kanban-list-items">
                    <div class="card mb-3">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="fw-semibold d-flex align-items-center mb-1" style="font-size:16px;">
                                        <i class="ti ti-circle-filled fs-8 text-<?= $col['color'] ?> me-2"></i><?= $col['title'] ?>
                                    </h4>
                                    <span class="fw-medium text-default"><?= count($col['leads']) ?> Leads - $<?= number_format($col['total']) ?></span>
                                </div>
                                <a href="#" class="text-muted"><i class="ti ti-circle-plus"></i></a>
                            </div>
                        </div>
                    </div>

                    <div class="kanban-drag-wrap">
                        <?php foreach($col['leads'] as $lead): 
                             // Encode lead data for JS Edit function
                             $leadJson = htmlspecialchars(json_encode($lead), ENT_QUOTES, 'UTF-8');
                             $initials = strtoupper(substr($lead['lead_name'], 0, 2));
                        ?>
                        <div class="card kanban-card">
                            <div class="card-body">
                                <div class="d-block">
                                    <div class="border-<?= $col['color'] ?> border border-2 mb-3"></div>
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <a href="#" class="avatar flex-shrink-0 me-2 text-decoration-none">
                                                <span class="text-dark" style="font-size:12px;"><?= $initials ?></span>
                                            </a>
                                            <h6 class="fw-medium mb-0">
                                                <a href="#" class="text-dark text-decoration-none"><?= htmlspecialchars($lead['lead_name']) ?></a>
                                            </h6>
                                        </div>
                                        <div class="d-flex gap-2">
                                            <a href="javascript:void(0);" onclick="openEditModal(<?= $leadJson ?>)" class="text-muted"><i class="ti ti-edit"></i></a>
                                            <a href="leads.php?delete_id=<?= $lead['id'] ?>" onclick="return confirm('Delete this lead?')" class="text-muted"><i class="ti ti-trash"></i></a>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3 d-flex flex-column gap-2">
                                    <p class="text-default mb-0"><i class="ti ti-report-money text-dark me-2"></i><?= $lead['lead_currency'] . ' ' . number_format($lead['lead_value']) ?></p>
                                    <p class="text-default mb-0"><i class="ti ti-mail text-dark me-2"></i><?= htmlspecialchars($lead['lead_email']) ?></p>
                                    <p class="text-default mb-0"><i class="ti ti-phone text-dark me-2"></i><?= htmlspecialchars($lead['lead_phone']) ?></p>
                                    <p class="text-default mb-0"><i class="ti ti-building-skyscraper text-dark me-2"></i><?= htmlspecialchars($lead['company_name']) ?></p>
                                </div>
                                <div class="d-flex align-items-center justify-content-between border-top pt-3 mt-2">
                                    <div class="d-flex align-items-center">
                                        <span class="avatar rounded-circle bg-light border text-xs me-2" style="width:24px;height:24px; font-size:10px;"><?= substr($lead['lead_owner'],0,1) ?></span>
                                        <span class="text-muted fs-8"><?= htmlspecialchars($lead['lead_source']) ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <div class="modal fade" id="add_leads">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Lead</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="submit_lead" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-12 mb-3"><label class="form-label">Lead Name *</label><input type="text" name="lead_name" class="form-control" required></div>
                            <div class="col-12 mb-3"><label class="form-label">Company *</label><input type="text" name="company_name" class="form-control" required></div>
                            <div class="col-6 mb-3"><label class="form-label">Value</label><input type="number" name="lead_value" class="form-control"></div>
                            <div class="col-6 mb-3"><label class="form-label">Currency</label><select name="lead_currency" class="form-select"><option>USD</option><option>Euro</option><option>INR</option></select></div>
                            <div class="col-12 mb-3"><label class="form-label">Phone</label><input type="text" name="lead_phone" class="form-control"></div>
                            <div class="col-12 mb-3"><label class="form-label">Email</label><input type="email" name="lead_email" class="form-control"></div>
                            <div class="col-6 mb-3"><label class="form-label">Source</label><select name="lead_source" class="form-select"><option>Google</option><option>Social Media</option><option>Referral</option></select></div>
                            <div class="col-6 mb-3"><label class="form-label">Industry</label><select name="lead_industry" class="form-select"><option>Tech</option><option>Finance</option><option>Retail</option></select></div>
                            <div class="col-12 mb-3"><label class="form-label">Owner</label><input type="text" name="lead_owner" class="form-control" value="Admin User"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Lead</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_leads">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Lead</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="POST">
                    <input type="hidden" name="update_lead" value="1">
                    <input type="hidden" name="lead_id" id="edit_id">
                    
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Lead Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="lead_name" id="edit_name" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Company <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="company_name" id="edit_company">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Value</label>
                                <input type="text" class="form-control" name="lead_value" id="edit_value">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Currency</label>
                                <select class="form-select" name="lead_currency" id="edit_currency">
                                    <option value="$">USD</option><option value="€">Euro</option><option value="₹">INR</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Phone</label>
                                <input type="text" class="form-control" name="lead_phone" id="edit_phone">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" name="lead_email" id="edit_email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Source</label>
                                <select class="form-select" name="lead_source" id="edit_source">
                                    <option value="Phone Calls">Phone Calls</option>
                                    <option value="Social Media">Social Media</option>
                                    <option value="Referral Sites">Referral Sites</option>
                                    <option value="Google">Google</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Industry</label>
                                <select class="form-select" name="lead_industry" id="edit_industry">
                                    <option value="Retail">Retail</option><option value="Banking">Banking</option><option value="Hotels">Hotels</option><option value="Technology">Technology</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Stage (Kanban Column)</label>
                                <select class="form-select" name="lead_stage" id="edit_stage">
                                    <option value="New">Not Contacted</option>
                                    <option value="Contacted">Contacted</option>
                                    <option value="Closed">Closed</option>
                                    <option value="Lost">Lost</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Owner</label>
                                <select class="form-select" name="lead_owner" id="edit_owner">
                                    <option value="Darlee Robertson">Darlee Robertson</option>
                                    <option value="Sharon Roy">Sharon Roy</option>
                                    <option value="Vaughan Lewis">Vaughan Lewis</option>
                                    <option value="Admin User">Admin User</option>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="lead_description" id="edit_desc" rows="3"></textarea>
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
        // Open Edit Modal & Populate Data
        function openEditModal(lead) {
            document.getElementById('edit_id').value = lead.id;
            document.getElementById('edit_name').value = lead.lead_name;
            document.getElementById('edit_company').value = lead.company_name;
            document.getElementById('edit_value').value = lead.lead_value;
            document.getElementById('edit_currency').value = lead.lead_currency;
            document.getElementById('edit_phone').value = lead.lead_phone;
            document.getElementById('edit_email').value = lead.lead_email;
            document.getElementById('edit_source').value = lead.lead_source;
            document.getElementById('edit_industry').value = lead.lead_industry;
            document.getElementById('edit_owner').value = lead.lead_owner;
            document.getElementById('edit_desc').value = lead.lead_description;
            document.getElementById('edit_stage').value = lead.lead_stage; // This moves it in Kanban!

            var modal = new bootstrap.Modal(document.getElementById('edit_leads'));
            modal.show();
        }
    </script>

</body>
</html>