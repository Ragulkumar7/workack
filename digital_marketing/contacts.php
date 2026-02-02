<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// --- HANDLE FORM ACTIONS ---

// Helper function to handle image upload
function uploadImage($file) {
    if(isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true); // Create folder if missing
        
        $filename = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $filename;
        $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
        
        // Allow certain file formats
        if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
            return null;
        }

        if (move_uploaded_file($file["tmp_name"], $target_file)) {
            return $target_file;
        }
    }
    return null;
}

// A. ADD CONTACT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_contact'])) {
    // Basic Info
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $job   = mysqli_real_escape_string($conn, $_POST['job_title']);
    $comp  = mysqli_real_escape_string($conn, $_POST['company_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $ph1   = mysqli_real_escape_string($conn, $_POST['phone_1']);
    // ... (other fields) ...
    $imagePath = uploadImage($_FILES['image']); // Handle Image

    $sql = "INSERT INTO contacts (first_name, last_name, job_title, company_name, email, phone_1, image, created_at) 
            VALUES ('$fname', '$lname', '$job', '$comp', '$email', '$ph1', '$imagePath', NOW())";

    if (mysqli_query($conn, $sql)) {
        header("Location: contacts.php?msg=added"); exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// B. EDIT CONTACT (UPDATE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_contact_submit'])) {
    $id    = intval($_POST['contact_id']);
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $job   = mysqli_real_escape_string($conn, $_POST['job_title']);
    $comp  = mysqli_real_escape_string($conn, $_POST['company_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $ph1   = mysqli_real_escape_string($conn, $_POST['phone_1']);
    
    // Handle Image Update
    $imagePath = uploadImage($_FILES['image']);
    $imageSql = "";
    if ($imagePath) {
        $imageSql = ", image='$imagePath'";
    }

    $sql = "UPDATE contacts SET 
            first_name='$fname', last_name='$lname', job_title='$job', company_name='$comp', 
            email='$email', phone_1='$ph1' $imageSql
            WHERE id=$id";

    if (mysqli_query($conn, $sql)) {
        header("Location: contacts.php?msg=updated"); exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// C. DELETE CONTACT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_contact_submit'])) {
    $id = intval($_POST['contact_id']);
    mysqli_query($conn, "DELETE FROM contacts WHERE id=$id");
    header("Location: contacts.php?msg=deleted"); exit();
}

// 3. FETCH CONTACTS
$contacts = [];
$res = mysqli_query($conn, "SELECT * FROM contacts ORDER BY created_at DESC");
if ($res) { while ($row = mysqli_fetch_assoc($res)) { $contacts[] = $row; } }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Contacts Grid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 25px; }
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); background: #fff; margin-bottom: 24px; }
        .card-body { padding: 20px; }
        .contact-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }
        .avatar-xl { width: 80px; height: 80px; font-size: 24px; display: flex; align-items: center; justify-content: center; background: #e0e7ff; color: #4338ca; font-weight: bold; overflow: hidden; }
        .avatar-xl img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-sm { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 50%; color: #555; text-decoration: none; }
        .bg-pink-transparent { background: rgba(252, 96, 117, 0.1); color: #fc6075; padding: 5px 10px; border-radius: 4px; font-size: 11px; font-weight: 500; }
        .nav-underline .nav-link { color: #555; font-weight: 500; }
        .nav-underline .nav-link.active { color: #FF9B44; border-bottom-color: #FF9B44; }
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
                    <h3 class="mb-1 fw-bold">Contacts</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="#" style="text-decoration:none; color:#6c757d;">Dashboard</a></li>
                            <li class="breadcrumb-item active">Contacts</li>
                        </ol>
                    </nav>
                </div>
                <button class="btn btn-primary d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#add_contact">
                    <i class="ti ti-plus"></i> Add Contact
                </button>
            </div>

            <div class="contact-grid">
                <?php foreach($contacts as $c): 
                    $initials = strtoupper(substr($c['first_name'], 0, 1) . substr($c['last_name'], 0, 1));
                    $fullname = htmlspecialchars($c['first_name'] . ' ' . $c['last_name']);
                    // Data for JS
                    $cJson = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
                    // Check for image
                    $imgSrc = !empty($c['image']) ? $c['image'] : '';
                ?>
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="form-check"><input class="form-check-input" type="checkbox"></div>
                            <div>
                                <a href="#" class="avatar avatar-xl rounded-circle border p-1 border-primary text-decoration-none">
                                    <?php if($imgSrc): ?>
                                        <img src="<?= $imgSrc ?>" alt="<?= $initials ?>">
                                    <?php else: ?>
                                        <?= $initials ?>
                                    <?php endif; ?>
                                </a>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="#" onclick="openEditModal(<?= $cJson ?>)"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="openDeleteModal(<?= $c['id'] ?>)"><i class="ti ti-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <h6 class="mb-1 fw-bold"><?= $fullname ?></h6>
                            <span class="badge bg-pink-transparent"><?= htmlspecialchars($c['job_title'] ?: 'Unknown') ?></span>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            <small class="text-muted"><i class="ti ti-mail me-2"></i><?= htmlspecialchars($c['email']) ?></small>
                            <small class="text-muted"><i class="ti ti-phone me-2"></i><?= htmlspecialchars($c['phone_1']) ?></small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <div class="modal fade" id="add_contact" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Add Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_contact" value="1">
                    <div class="modal-body pb-0">
                        
                        <div class="col-12 mb-4">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="avatar avatar-xl me-3 bg-white border border-dashed text-dark"><i class="ti ti-photo"></i></div>
                                <div>
                                    <h6 class="mb-1">Upload Profile Image</h6>
                                    <input type="file" name="image" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-underline mb-3" role="tablist">
                            <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#add_basic" type="button">Basic Info</button></li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="add_basic">
                                <div class="row">
                                    <div class="col-md-6 mb-3"><label class="form-label">First Name *</label><input type="text" name="first_name" class="form-control" required></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Last Name *</label><input type="text" name="last_name" class="form-control" required></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Phone *</label><input type="text" name="phone_1" class="form-control" required></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Job Title</label><input type="text" name="job_title" class="form-control"></div>
                                    <div class="col-md-6 mb-3"><label class="form-label">Company</label><input type="text" name="company_name" class="form-control"></div>
                                </div>
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

    <div class="modal fade" id="edit_contact" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Edit Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_contact_submit" value="1">
                    <input type="hidden" name="contact_id" id="edit_id">
                    
                    <div class="modal-body pb-0">
                        <div class="col-12 mb-4">
                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                <div class="avatar avatar-xl me-3 bg-white border border-dashed text-dark"><i class="ti ti-photo"></i></div>
                                <div>
                                    <h6 class="mb-1">Update Profile Image</h6>
                                    <input type="file" name="image" class="form-control form-control-sm">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">First Name *</label><input type="text" name="first_name" id="edit_fname" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Last Name *</label><input type="text" name="last_name" id="edit_lname" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" id="edit_email" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone_1" id="edit_phone" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Job Title</label><input type="text" name="job_title" id="edit_job" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Company</label><input type="text" name="company_name" id="edit_comp" class="form-control"></div>
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

    <div class="modal fade" id="delete_modal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center p-4">
                    <h4 class="mb-2">Are you sure?</h4>
                    <p class="text-muted">Do you really want to delete this contact?</p>
                    <form method="POST">
                        <input type="hidden" name="delete_contact_submit" value="1">
                        <input type="hidden" name="contact_id" id="delete_id">
                        <div class="d-flex justify-content-center gap-2 mt-4">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        lucide.createIcons();

        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_fname').value = data.first_name;
            document.getElementById('edit_lname').value = data.last_name;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_phone').value = data.phone_1;
            document.getElementById('edit_job').value = data.job_title;
            document.getElementById('edit_comp').value = data.company_name;
            var modal = new bootstrap.Modal(document.getElementById('edit_contact'));
            modal.show();
        }

        function openDeleteModal(id) {
            document.getElementById('delete_id').value = id;
            var modal = new bootstrap.Modal(document.getElementById('delete_modal'));
            modal.show();
        }
    </script>

</body>
</html>