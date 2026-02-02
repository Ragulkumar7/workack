<?php
session_start();

// 1. DB CONNECTION
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }
if (!isset($conn)) die("Error: DB connection not found.");

// --- HELPER: IMAGE UPLOAD ---
function uploadCompanyImage($file) {
    if(isset($file) && $file['error'] == 0) {
        $target_dir = "uploads/companies/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $filename = time() . "_" . basename($file["name"]);
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($file["tmp_name"], $target_file)) return $target_file;
    }
    return null;
}

// --- ACTIONS ---

// A. ADD COMPANY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_company'])) {
    $name  = mysqli_real_escape_string($conn, $_POST['company_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $web   = mysqli_real_escape_string($conn, $_POST['website']);
    $ind   = mysqli_real_escape_string($conn, $_POST['industry']);
    $own   = mysqli_real_escape_string($conn, $_POST['owner']);
    $src   = mysqli_real_escape_string($conn, $_POST['source']);
    $about = mysqli_real_escape_string($conn, $_POST['about']);
    $cntry = mysqli_real_escape_string($conn, $_POST['country']);
    
    // Image
    $img = uploadCompanyImage($_FILES['image']);

    $sql = "INSERT INTO companies (company_name, email, phone, website, industry, owner, source, about, country, image, created_at) 
            VALUES ('$name', '$email', '$phone', '$web', '$ind', '$own', '$src', '$about', '$cntry', '$img', NOW())";
    
    mysqli_query($conn, $sql);
    header("Location: companies.php?msg=added"); exit();
}

// B. EDIT COMPANY
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_company_submit'])) {
    $id = intval($_POST['company_id']);
    $name  = mysqli_real_escape_string($conn, $_POST['company_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $web   = mysqli_real_escape_string($conn, $_POST['website']);
    $ind   = mysqli_real_escape_string($conn, $_POST['industry']);
    
    // Check for new image
    $img = uploadCompanyImage($_FILES['image']);
    $imgSql = $img ? ", image='$img'" : "";

    $sql = "UPDATE companies SET company_name='$name', email='$email', phone='$phone', website='$web', industry='$ind' $imgSql WHERE id=$id";
    mysqli_query($conn, $sql);
    header("Location: companies.php?msg=updated"); exit();
}

// C. DELETE COMPANY
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM companies WHERE id=$id");
    header("Location: companies.php?msg=deleted"); exit();
}

// FETCH COMPANIES
$companies = [];
$res = mysqli_query($conn, "SELECT * FROM companies ORDER BY created_at DESC");
if ($res) { while($row = mysqli_fetch_assoc($res)) { $companies[] = $row; } }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Companies Grid</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { margin: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 25px; }

        /* Card */
        .card { border: none; border-radius: 10px; box-shadow: 0 2px 6px rgba(0,0,0,0.02); background: #fff; margin-bottom: 24px; }
        .card-body { padding: 20px; }
        .company-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px; }

        /* Avatar */
        .avatar-xl { width: 80px; height: 80px; display: flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 50%; overflow: hidden; }
        .avatar-xl img { width: 100%; height: 100%; object-fit: cover; }
        .avatar-sm { width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center; background: #f0f0f0; border-radius: 50%; color: #555; text-decoration: none; }
        
        /* Utils */
        .nav-underline .nav-link.active { color: #FF9B44; border-bottom-color: #FF9B44; }
        .nav-underline .nav-link { color: #555; }
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
                    <h3 class="mb-1 fw-bold">Companies</h3>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="dm_dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Companies</li>
                        </ol>
                    </nav>
                </div>
                <button class="btn btn-primary btn-sm d-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#add_company">
                    <i class="ti ti-plus"></i> Add Company
                </button>
            </div>

            <div class="company-grid">
                <?php foreach($companies as $c): 
                    $initial = strtoupper(substr($c['company_name'], 0, 1));
                    $imgSrc = !empty($c['image']) ? $c['image'] : '';
                    $cJson = htmlspecialchars(json_encode($c), ENT_QUOTES, 'UTF-8');
                ?>
                <div class="card mb-0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="form-check"><input class="form-check-input" type="checkbox"></div>
                            <a href="company-details.php?id=<?= $c['id'] ?>" class="avatar-xl border p-1 border-primary rounded-circle text-decoration-none">
    <?php if($imgSrc): ?>
        <img src="<?= $imgSrc ?>" alt="<?= $initial ?>">
    <?php else: ?>
        <span style="font-size:24px; font-weight:bold; color:#555;"><?= $initial ?></span>
    <?php endif; ?>
</a>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-icon" data-bs-toggle="dropdown"><i class="ti ti-dots-vertical"></i></button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><a class="dropdown-item" href="javascript:void(0);" onclick="openEditModal(<?= $cJson ?>)"><i class="ti ti-edit me-2"></i>Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="companies.php?delete_id=<?= $c['id'] ?>" onclick="return confirm('Delete company?')"><i class="ti ti-trash me-2"></i>Delete</a></li>
                                </ul>
                            </div>
                        </div>

                        <div class="text-center mb-3">
                            <h6 class="mb-1 fw-bold">
    <a href="company-details.php?id=<?= $c['id'] ?>" class="text-dark text-decoration-none">
        <?= htmlspecialchars($c['company_name']) ?>
    </a>
</h6>
                            <span class="badge bg-light text-dark border"><?= htmlspecialchars($c['industry'] ?: 'Business') ?></span>
                        </div>

                        <div class="d-flex flex-column gap-2 mb-3">
                            <small class="text-muted"><i class="ti ti-mail me-2"></i><?= htmlspecialchars($c['email']) ?></small>
                            <small class="text-muted"><i class="ti ti-phone me-2"></i><?= htmlspecialchars($c['phone']) ?></small>
                            <small class="text-muted"><i class="ti ti-map-pin me-2"></i><?= htmlspecialchars($c['country'] ?: 'Global') ?></small>
                        </div>

                        <div class="d-flex align-items-center justify-content-between border-top pt-3">
                            <div class="d-flex align-items-center gap-1">
                                <a href="#" class="avatar avatar-sm"><i class="ti ti-mail"></i></a>
                                <a href="#" class="avatar avatar-sm"><i class="ti ti-phone"></i></a>
                                <a href="<?= htmlspecialchars($c['website']) ?>" target="_blank" class="avatar avatar-sm"><i class="ti ti-world"></i></a>
                            </div>
                            <span class="d-inline-flex align-items-center fw-bold fs-12">
                                <i class="ti ti-star-filled text-warning me-1"></i>4.5
                            </span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        </div>
    </div>

    <div class="modal fade" id="add_company" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Company</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="add_company" value="1">
                    <div class="modal-body">
                        <div class="mb-3 p-3 bg-light rounded d-flex align-items-center">
                            <div class="avatar avatar-lg me-3 bg-white border"><i class="ti ti-photo"></i></div>
                            <div>
                                <h6>Logo</h6>
                                <input type="file" name="image" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Company Name *</label><input type="text" name="company_name" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email *</label><input type="email" name="email" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Phone *</label><input type="text" name="phone" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Website</label><input type="text" name="website" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Industry</label><select name="industry" class="form-select"><option>IT</option><option>Retail</option><option>Finance</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Owner</label><input type="text" name="owner" class="form-control" value="Admin"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Source</label><select name="source" class="form-select"><option>Google</option><option>Social</option></select></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Country</label><input type="text" name="country" class="form-control"></div>
                            <div class="col-12 mb-3"><label class="form-label">About</label><textarea name="about" class="form-control" rows="3"></textarea></div>
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

    <div class="modal fade" id="edit_company" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Edit Company</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="edit_company_submit" value="1">
                    <input type="hidden" name="company_id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3 p-3 bg-light rounded d-flex align-items-center">
                            <div class="avatar avatar-lg me-3 bg-white border"><i class="ti ti-photo"></i></div>
                            <input type="file" name="image" class="form-control form-control-sm">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Company Name</label><input type="text" name="company_name" id="edit_name" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" name="email" id="edit_email" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Phone</label><input type="text" name="phone" id="edit_phone" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Website</label><input type="text" name="website" id="edit_web" class="form-control"></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Industry</label><select name="industry" id="edit_ind" class="form-select"><option>IT</option><option>Retail</option></select></div>
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
        lucide.createIcons();
        function openEditModal(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.company_name;
            document.getElementById('edit_email').value = data.email;
            document.getElementById('edit_phone').value = data.phone;
            document.getElementById('edit_web').value = data.website;
            document.getElementById('edit_ind').value = data.industry;
            var modal = new bootstrap.Modal(document.getElementById('edit_company'));
            modal.show();
        }
    </script>
</body>
</html>