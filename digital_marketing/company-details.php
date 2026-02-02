<?php
session_start();
$paths = ['../include/db_connect.php', '../../include/db_connect.php', 'db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

// Validate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid Company ID");
}
$company_id = intval($_GET['id']);

// Fetch Company Details
$sql = "SELECT * FROM companies WHERE id = $company_id";
$result = mysqli_query($conn, $sql);
$company = mysqli_fetch_assoc($result);

if (!$company) { die("Company not found."); }

// Handle Add Note
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_note'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $note = mysqli_real_escape_string($conn, $_POST['note']);
    // (File upload logic can be added here)
    mysqli_query($conn, "INSERT INTO company_notes (company_id, title, note) VALUES ($company_id, '$title', '$note')");
    
    // Also add to Activity Timeline
    mysqli_query($conn, "INSERT INTO company_activities (company_id, type, title, description) VALUES ($company_id, 'Note', 'Note added by Admin', '$title')");
    
    header("Location: company-details.php?id=$company_id"); exit();
}

// Fetch Activities
$activities = [];
$act_res = mysqli_query($conn, "SELECT * FROM company_activities WHERE company_id = $company_id ORDER BY created_at DESC");
while($row = mysqli_fetch_assoc($act_res)) { $activities[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($company['company_name']) ?> - Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; }
        .page-content { padding: 25px; }
        
        /* Profile Header */
        .profile-cover { height: 120px; background: linear-gradient(90deg, #FF9B44, #fc6075); border-radius: 10px 10px 0 0; position: relative; }
        .profile-img { width: 100px; height: 100px; border-radius: 50%; border: 4px solid white; position: absolute; bottom: -50px; left: 30px; background: white; object-fit: cover; }
        .profile-content { margin-top: 60px; padding: 0 30px 20px; }
        
        /* Cards */
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 20px; }
        .card-header { background: white; border-bottom: 1px solid #eee; padding: 15px 20px; font-weight: 600; }
        
        /* Activity Timeline */
        .activity-item { padding-left: 20px; border-left: 2px solid #eee; position: relative; padding-bottom: 25px; }
        .activity-icon { width: 30px; height: 30px; border-radius: 50%; position: absolute; left: -16px; top: 0; display: flex; align-items: center; justify-content: center; color: white; }
        .bg-info { background: #009efb; } .bg-success { background: #28a745; } .bg-warning { background: #ffc107; }
        
        /* Tabs */
        .nav-tabs .nav-link { color: #555; border: 0; border-bottom: 2px solid transparent; }
        .nav-tabs .nav-link.active { color: #FF9B44; border-bottom-color: #FF9B44; font-weight: 600; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php include '../include/header.php'; ?>

        <div class="page-content">
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="mb-1">Companies / <?= htmlspecialchars($company['company_name']) ?></h5>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#add_notes">Add Note</button>
                    <button class="btn btn-dark btn-sm">Send Email</button>
                </div>
            </div>

            <div class="row">
                <div class="col-xl-4">
                    <div class="card">
                        <div class="profile-cover">
                            <?php 
                                $imgSrc = !empty($company['image']) ? $company['image'] : 'https://ui-avatars.com/api/?name='.urlencode($company['company_name']);
                            ?>
                            <img src="<?= $imgSrc ?>" class="profile-img">
                        </div>
                        <div class="profile-content">
                            <h4><?= htmlspecialchars($company['company_name']) ?> <i class="ti ti-circle-check-filled text-success fs-16"></i></h4>
                            <p class="text-muted mb-3"><i class="ti ti-map-pin me-1"></i> <?= htmlspecialchars($company['address'] ?? 'No Address') ?></p>
                            
                            <div class="card mb-3 border">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3">Basic Information</h6>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Phone</span>
                                        <span class="text-dark"><?= htmlspecialchars($company['phone']) ?></span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Email</span>
                                        <span class="text-dark"><?= htmlspecialchars($company['email']) ?></span>
                                    </div>
                                    <div class="mb-0 d-flex justify-content-between">
                                        <span class="text-muted">Owner</span>
                                        <span class="text-dark"><?= htmlspecialchars($company['owner']) ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="card border">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-3">Other Information</h6>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Industry</span>
                                        <span><?= htmlspecialchars($company['industry']) ?></span>
                                    </div>
                                    <div class="mb-2 d-flex justify-content-between">
                                        <span class="text-muted">Source</span>
                                        <span><?= htmlspecialchars($company['source']) ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card border">
                                <div class="card-body p-3">
                                    <h6 class="fw-bold mb-2">Tags</h6>
                                    <span class="badge bg-light text-dark border">Collab</span>
                                    <span class="badge bg-light text-dark border">Rated</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header bg-white">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#activities">Activities</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#notes">Notes</a></li>
                                <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#calls">Calls</a></li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane fade show active" id="activities">
                                    <h5 class="mb-3">Recent Activity</h5>
                                    
                                    <?php if(empty($activities)): ?>
                                        <p class="text-muted">No recent activities.</p>
                                    <?php else: ?>
                                        <?php foreach($activities as $act): ?>
                                        <div class="activity-item">
                                            <?php 
                                                $icon = 'ti-message'; $color = 'bg-info';
                                                if($act['type'] == 'Call') { $icon='ti-phone'; $color='bg-success'; }
                                                if($act['type'] == 'Note') { $icon='ti-file-text'; $color='bg-warning'; }
                                            ?>
                                            <div class="activity-icon <?= $color ?>"><i class="<?= $icon ?>"></i></div>
                                            <div>
                                                <h6 class="mb-1"><?= htmlspecialchars($act['title']) ?></h6>
                                                <p class="text-muted mb-1 fs-13"><?= htmlspecialchars($act['description']) ?></p>
                                                <small class="text-muted"><?= date('d M Y, h:i A', strtotime($act['created_at'])) ?></small>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>

                                <div class="tab-pane fade" id="notes">
                                    <p class="text-muted">Notes content goes here...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="add_notes" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Note</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="add_note" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Title *</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note *</label>
                            <textarea name="note" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Note</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>lucide.createIcons();</script>
</body>
</html>