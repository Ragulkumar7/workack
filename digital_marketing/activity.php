<?php
session_start();
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// --- 0. EXPORT TO EXCEL (CSV) LOGIC ---
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=activity_list.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Title', 'Activity Type', 'Due Date', 'Time', 'Owner', 'Description', 'Created Date'));
    $query = "SELECT title, activity_type, due_date, time, owner, description, created_at FROM activities ORDER BY created_at DESC";
    $result = mysqli_query($conn, $query);
    while($row = mysqli_fetch_assoc($result)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit();
}

// --- ACTIONS ---

// 1. ADD ACTIVITY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_activity_submit'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type  = isset($_POST['activity_type']) ? mysqli_real_escape_string($conn, $_POST['activity_type']) : 'Calls'; 
    $due   = !empty($_POST['due_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['due_date']))) : NULL;
    $time  = mysqli_real_escape_string($conn, $_POST['time']);
    $rem_amt = mysqli_real_escape_string($conn, $_POST['reminder_amt']);
    $rem_typ = mysqli_real_escape_string($conn, $_POST['reminder_type']);
    $owner = mysqli_real_escape_string($conn, $_POST['owner']);
    $guests = mysqli_real_escape_string($conn, $_POST['guests']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);
    $deal = mysqli_real_escape_string($conn, $_POST['related_deal']);
    $contact = mysqli_real_escape_string($conn, $_POST['related_contact']);
    $company = mysqli_real_escape_string($conn, $_POST['related_company']);
    
    $sql = "INSERT INTO activities (title, activity_type, due_date, time, reminder_amt, reminder_type, owner, guests, description, related_deal, related_contact, related_company, created_at) 
            VALUES ('$title', '$type', '$due', '$time', '$rem_amt', '$rem_typ', '$owner', '$guests', '$desc', '$deal', '$contact', '$company', NOW())";
            
    if(mysqli_query($conn, $sql)) {
        header("Location: activity.php?msg=added"); exit();
    } else { echo "Error: " . mysqli_error($conn); }
}

// 2. EDIT ACTIVITY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_activity_submit'])) {
    $id    = intval($_POST['activity_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type  = mysqli_real_escape_string($conn, $_POST['activity_type']);
    $due   = !empty($_POST['due_date']) ? date('Y-m-d', strtotime(str_replace('/', '-', $_POST['due_date']))) : NULL;
    $time  = mysqli_real_escape_string($conn, $_POST['time']);
    $rem_amt = mysqli_real_escape_string($conn, $_POST['reminder_amt']);
    $rem_typ = mysqli_real_escape_string($conn, $_POST['reminder_type']);
    $owner = mysqli_real_escape_string($conn, $_POST['owner']);
    $guests = mysqli_real_escape_string($conn, $_POST['guests']);
    $desc  = mysqli_real_escape_string($conn, $_POST['description']);
    $deal = mysqli_real_escape_string($conn, $_POST['related_deal']);
    $contact = mysqli_real_escape_string($conn, $_POST['related_contact']);
    $company = mysqli_real_escape_string($conn, $_POST['related_company']);

    $sql = "UPDATE activities SET 
            title='$title', activity_type='$type', due_date='$due', time='$time', 
            reminder_amt='$rem_amt', reminder_type='$rem_typ', owner='$owner', 
            guests='$guests', description='$desc', related_deal='$deal', 
            related_contact='$contact', related_company='$company' 
            WHERE id=$id";
            
    mysqli_query($conn, $sql);
    header("Location: activity.php?msg=updated"); exit();
}

// 3. DELETE
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM activities WHERE id=$id");
    header("Location: activity.php?msg=deleted"); exit();
}

// --- FETCH DATA FOR DROPDOWNS ---

// Fetch Users (Using 'username' from your existing table)
$all_users = [];
$u_res = mysqli_query($conn, "SELECT id, username FROM users ORDER BY username ASC");
if($u_res){ while($u = mysqli_fetch_assoc($u_res)){ $all_users[] = $u; } }

// Fetch Deals
$all_deals = [];
$d_res = mysqli_query($conn, "SELECT id, deal_name FROM deals ORDER BY deal_name ASC");
if($d_res){ while($d = mysqli_fetch_assoc($d_res)){ $all_deals[] = $d; } }

// Fetch Contacts
$all_contacts = [];
$c_res = mysqli_query($conn, "SELECT id, first_name, last_name FROM contacts ORDER BY first_name ASC");
if($c_res){ while($c = mysqli_fetch_assoc($c_res)){ $all_contacts[] = $c; } }

// Fetch Companies
$all_companies = [];
$co_res = mysqli_query($conn, "SELECT id, company_name FROM companies ORDER BY company_name ASC");
if($co_res){ while($co = mysqli_fetch_assoc($co_res)){ $all_companies[] = $co; } }

// Fetch Activities List
$activities = [];
$res = mysqli_query($conn, "SELECT * FROM activities ORDER BY created_at DESC");
if ($res) { while($row = mysqli_fetch_assoc($res)) { $activities[] = $row; } }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Activity List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .page-wrapper { padding: 25px; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .activity-items a { width: 100px; height: 40px; border: 1px solid #e3e3e3; color: #555; text-decoration: none; border-radius: 5px; font-size: 14px; display: flex; align-items: center; justify-content: center; transition: all 0.3s; }
        .activity-items a.active { background: #FF9B44; color: #fff; border-color: #FF9B44; }
        .activity-items a:hover { border-color: #FF9B44; color: #FF9B44; }
        .activity-items a.active:hover { color: #fff; }
        .badge-pink-transparent { background: rgba(236, 72, 153, 0.1); color: #ec4899; }
        .badge-purple-transparent { background: rgba(116, 96, 238, 0.1); color: #7460ee; }
        .badge-info-transparent { background: rgba(0, 197, 251, 0.1); color: #00c5fb; }
        .badge-warning-transparent { background: rgba(255, 193, 7, 0.1); color: #ffc107; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        .text-primary { color: #FF9B44 !important; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="page-wrapper">
            <div class="content">
                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Activity</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="dm_dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item">CRM</li>
                                <li class="breadcrumb-item active">Activity List</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                        <div class="me-2 mb-2">
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="ti ti-file-export me-1"></i>Export
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="activity.php?export=excel" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="mb-2">
                            <a href="#" data-bs-toggle="modal" data-bs-target="#add_activity" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Activity</a>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Activity List</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            <div class="me-3">
                                <div class="input-icon position-relative">
                                    <span class="input-icon-addon"><i class="ti ti-calendar text-gray-9"></i></span>
                                    <input type="text" class="form-control" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="custom-datatable-filter table-responsive">
                            <table class="table datatable">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Title</th>
                                        <th>Activity Type</th>
                                        <th>Due Date</th>
                                        <th>Owner</th>
                                        <th>Created Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($activities as $act): 
                                        $actJson = htmlspecialchars(json_encode($act), ENT_QUOTES, 'UTF-8');
                                        $icon = 'ti-activity'; $badgeClass = 'badge-info-transparent';
                                        if($act['activity_type'] == 'Meeting') { $icon='ti-device-computer-camera'; $badgeClass='badge-pink-transparent'; }
                                        if($act['activity_type'] == 'Calls') { $icon='ti-phone'; $badgeClass='badge-purple-transparent'; }
                                        if($act['activity_type'] == 'Email') { $icon='ti-mail'; $badgeClass='badge-warning-transparent'; }
                                        if($act['activity_type'] == 'Task') { $icon='ti-list-check'; $badgeClass='badge-info-transparent'; }
                                    ?>
                                    <tr>
                                        <td><p class="fs-14 text-dark fw-medium"><?= htmlspecialchars($act['title']) ?></p></td>
                                        <td><span class="badge <?= $badgeClass ?>"><i class="ti <?= $icon ?> me-1"></i><?= htmlspecialchars($act['activity_type']) ?></span></td>
                                        <td><?= date('d M Y', strtotime($act['due_date'])) ?> <span class="text-muted fs-12 ms-1"><?= $act['time'] ?></span></td>
                                        <td><?= htmlspecialchars($act['owner']) ?></td>
                                        <td><?= date('d M Y', strtotime($act['created_at'])) ?></td>
                                        <td>
                                            <div class="action-icon d-inline-flex">
                                                <a href="#" class="me-2" onclick="openEditModal(<?= $actJson ?>)"><i class="ti ti-edit"></i></a>
                                                <a href="activity.php?delete_id=<?= $act['id'] ?>" onclick="return confirm('Delete?')"><i class="ti ti-trash"></i></a>
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

    <div class="modal fade" id="add_activity">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add New Activity</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_activity_submit" value="1">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Activity Type *</label>
                                <div class="activity-items d-flex align-items-center">
                                    <input type="hidden" name="activity_type" id="add_activity_type" value="Calls">
                                    <a href="javascript:void(0);" onclick="setAddType(this, 'Calls')" class="me-2 active"><i class="ti ti-phone me-1"></i>Calls</a>
                                    <a href="javascript:void(0);" onclick="setAddType(this, 'Email')" class="me-2"><i class="ti ti-mail me-1"></i>Email</a>
                                    <a href="javascript:void(0);" onclick="setAddType(this, 'Meeting')" class="me-2"><i class="ti ti-user-circle me-1"></i>Meeting</a>
                                    <a href="javascript:void(0);" onclick="setAddType(this, 'Task')" class="me-2"><i class="ti ti-list-check me-1"></i>Task</a>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label">Due Date *</label><input type="date" name="due_date" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Time</label><input type="time" name="time" class="form-control"></div>
                            <div class="col-lg-8 mb-3"><label class="form-label">Reminder</label><div class="input-icon-start position-relative"><input type="text" name="reminder_amt" class="form-control"><span class="input-icon-addon"><i class="ti ti-bell"></i></span></div></div>
                            <div class="col-lg-4 mb-3"><label class="form-label">Type</label><select name="reminder_type" class="form-select"><option>Before Use</option><option>After Use</option></select></div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Owner</label>
                                <select name="owner" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_users as $u): ?>
                                        <option value="<?= htmlspecialchars($u['username']) ?>"><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Guests</label>
                                <select name="guests" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_users as $u): ?>
                                        <option value="<?= htmlspecialchars($u['username']) ?>"><?= htmlspecialchars($u['username']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                            
                            <div class="col-md-12 mb-3"><label class="form-label">Deals</label>
                                <select name="related_deal" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_deals as $d): ?><option value="<?= $d['deal_name'] ?>"><?= $d['deal_name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3"><label class="form-label">Contacts</label>
                                <select name="related_contact" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_contacts as $c): ?><option value="<?= $c['first_name'].' '.$c['last_name'] ?>"><?= $c['first_name'].' '.$c['last_name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3"><label class="form-label">Companies</label>
                                <select name="related_company" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_companies as $co): ?><option value="<?= $co['company_name'] ?>"><?= $co['company_name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Add Activity</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_activity">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Activity</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="edit_activity_submit" value="1">
                    <input type="hidden" name="activity_id" id="edit_id">
                    <div class="modal-body pb-0">
                        <div class="row">
                            <div class="col-md-12 mb-3"><label class="form-label">Title *</label><input type="text" name="title" id="edit_title" class="form-control" required></div>
                            
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Activity Type *</label>
                                <div class="activity-items d-flex align-items-center" id="edit_type_container">
                                    <input type="hidden" name="activity_type" id="edit_activity_type">
                                    <a href="javascript:void(0);" onclick="setEditType(this, 'Calls')" class="me-2" id="btn_type_Calls"><i class="ti ti-phone me-1"></i>Calls</a>
                                    <a href="javascript:void(0);" onclick="setEditType(this, 'Email')" class="me-2" id="btn_type_Email"><i class="ti ti-mail me-1"></i>Email</a>
                                    <a href="javascript:void(0);" onclick="setEditType(this, 'Meeting')" class="me-2" id="btn_type_Meeting"><i class="ti ti-user-circle me-1"></i>Meeting</a>
                                    <a href="javascript:void(0);" onclick="setEditType(this, 'Task')" class="me-2" id="btn_type_Task"><i class="ti ti-list-check me-1"></i>Task</a>
                                </div>
                            </div>

                            <div class="col-md-6 mb-3"><label class="form-label">Due Date *</label><input type="date" name="due_date" id="edit_due" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Time</label><input type="time" name="time" id="edit_time" class="form-control"></div>
                            
                            <div class="col-lg-8 mb-3"><label class="form-label">Reminder</label><div class="input-icon-start position-relative"><input type="text" name="reminder_amt" id="edit_rem_amt" class="form-control"><span class="input-icon-addon"><i class="ti ti-bell"></i></span></div></div>
                            <div class="col-lg-4 mb-3"><label class="form-label">Type</label><select name="reminder_type" id="edit_rem_typ" class="form-select"><option>Before Use</option><option>After Use</option></select></div>
                            
                            <div class="col-md-6 mb-3"><label class="form-label">Owner</label>
                                <select name="owner" id="edit_owner" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_users as $u): ?><option value="<?= $u['username'] ?>"><?= $u['username'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3"><label class="form-label">Guests</label>
                                <select name="guests" id="edit_guests" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_users as $u): ?><option value="<?= $u['username'] ?>"><?= $u['username'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea name="description" id="edit_desc" class="form-control" rows="3"></textarea></div>
                            
                            <div class="col-md-12 mb-3"><label class="form-label">Deals</label>
                                <select name="related_deal" id="edit_deal" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_deals as $d): ?><option value="<?= $d['deal_name'] ?>"><?= $d['deal_name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3"><label class="form-label">Contacts</label>
                                <select name="related_contact" id="edit_contact" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_contacts as $c): ?><option value="<?= $c['first_name'].' '.$c['last_name'] ?>"><?= $c['first_name'].' '.$c['last_name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-12 mb-3"><label class="form-label">Companies</label>
                                <select name="related_company" id="edit_company" class="form-select">
                                    <option value="">Select</option>
                                    <?php foreach($all_companies as $co): ?><option value="<?= $co['company_name'] ?>"><?= $co['company_name'] ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer"><button type="submit" class="btn btn-primary">Save Changes</button></div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="delete_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h3>Are you sure?</h3>
                    <p>Do you really want to delete this activity?</p>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <a href="#" id="confirm_delete_btn" class="btn btn-danger">Yes, Delete</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();
        
        function setAddType(element, typeValue) {
            var items = element.parentElement.querySelectorAll('a');
            items.forEach(i => i.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('add_activity_type').value = typeValue;
        }

        function setEditType(element, typeValue) {
            var items = document.getElementById('edit_type_container').querySelectorAll('a');
            items.forEach(i => i.classList.remove('active'));
            element.classList.add('active');
            document.getElementById('edit_activity_type').value = typeValue;
        }

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_title').value = data.title;
            document.getElementById('edit_due').value = data.due_date;
            document.getElementById('edit_time').value = data.time;
            document.getElementById('edit_rem_amt').value = data.reminder_amt;
            document.getElementById('edit_rem_typ').value = data.reminder_type;
            document.getElementById('edit_owner').value = data.owner;
            document.getElementById('edit_guests').value = data.guests;
            document.getElementById('edit_desc').value = data.description;
            document.getElementById('edit_deal').value = data.related_deal;
            document.getElementById('edit_contact').value = data.related_contact;
            document.getElementById('edit_company').value = data.related_company;

            // Handle Type
            var types = document.getElementById('edit_type_container').querySelectorAll('a');
            types.forEach(t => t.classList.remove('active'));
            var targetBtn = document.getElementById('btn_type_' + data.activity_type);
            if(targetBtn) {
                targetBtn.classList.add('active');
                document.getElementById('edit_activity_type').value = data.activity_type;
            } else {
                document.getElementById('btn_type_Calls').classList.add('active');
                document.getElementById('edit_activity_type').value = 'Calls';
            }

            var modal = new bootstrap.Modal(document.getElementById('edit_activity'));
            modal.show();
        }
        
        function openDeleteModal(id) {
            document.getElementById('confirm_delete_btn').href = "activity.php?delete_id=" + id;
            var modal = new bootstrap.Modal(document.getElementById('delete_modal'));
            modal.show();
        }
    </script>
</body>
</html>