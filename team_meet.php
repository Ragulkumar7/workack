<?php
// team_meet.php - Teams Pre-Join Replica with Camera Access (Mobile Responsive)

session_start();
include 'include/db_connect.php';

//$current_user_id = $_SESSION['emp_id'] ?? 1;
$current_user_id = 1;
// Initialize alert variables
$alert_msg = '';
$alert_type = 'info';

// Handle new meeting creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_meeting'])) {
    $title = trim($_POST['title'] ?? '');
    $start_time = $_POST['start_time'] ?? '';
    $end_time = $_POST['end_time'] ?? '';
    $description = trim($_POST['description'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $repeat = $_POST['repeat'] ?? 'none';
    $attendees = $_POST['attendees'] ?? [];

    if (!empty($title) && !empty($start_time) && !empty($end_time)) {
        // Verify organizer exists
        $check_sql = "SELECT id FROM employees WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $current_user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            $alert_msg = "Error: Your user account (ID $current_user_id) not found in employees table. Please log in again.";
            $alert_type = 'danger';
        } else {
            // Organizer exists → proceed
            $meeting_link = 'meet/' . bin2hex(random_bytes(16));
            $passcode = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 6);

            $sql = "INSERT INTO meetings (title, organizer_id, start_time, end_time, description, location, meeting_link, passcode, repeat_enum) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sisssssss", $title, $current_user_id, $start_time, $end_time, $description, $location, $meeting_link, $passcode, $repeat);

            if ($stmt->execute()) {
                $meeting_id = $stmt->insert_id;

                // Add attendees
                foreach ($attendees as $emp_id) {
                    $sql_att = "INSERT INTO meeting_attendees (meeting_id, emp_id) VALUES (?, ?)";
                    $stmt_att = $conn->prepare($sql_att);
                    $stmt_att->bind_param("ii", $meeting_id, $emp_id);
                    $stmt_att->execute();
                    $stmt_att->close();
                }

                $alert_msg = "Meeting created successfully!<br>Link: <strong>$meeting_link</strong><br>Passcode: <strong>$passcode</strong>";
                $alert_type = 'success';
            } else {
                $alert_msg = "Database error: " . $conn->error;
                $alert_type = 'danger';
            }
            $stmt->close();
        }
        $check_stmt->close();
    } else {
        $alert_msg = "Please fill in all required fields (title, start time, end time).";
        $alert_type = 'warning';
    }
}
// Fetch employees for attendees
$sql_employees = "SELECT id, name FROM employees WHERE id != ?";
$stmt_employees = $conn->prepare($sql_employees);
$stmt_employees->bind_param("i", $current_user_id);
$stmt_employees->execute();
$employees_list = $stmt_employees->get_result();
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meet | Workack</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #6264a7; /* Teams purple */
            --light-bg: #f8f9fc;
            --text-muted: #6c757d;
        }
        body { background: var(--light-bg); font-family: 'Segoe UI', sans-serif; padding: 1rem 0; }
        .container { max-width: 900px; }
        .card { border: none; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
        .btn-primary { background: var(--primary); border: none; }
        .btn-primary:hover { background: #4f5299; }
        .form-control { border-radius: 4px; }
        .form-label { font-weight: 500; }
        .modal-content { border-radius: 8px; }
        .toggle-switch { position: relative; display: inline-block; width: 40px; height: 20px; }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .toggle-slider:before { position: absolute; content: ""; height: 16px; width: 16px; left: 2px; bottom: 2px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .toggle-slider { background-color: #6264a7; }
        input:checked + .toggle-slider:before { transform: translateX(20px); }
        #videoPreview { width: 100%; height: auto; background: black; border-radius: 8px; transform: scaleX(-1); } /* Mirror camera like Teams */
        .preview-container { background: #fff; border-radius: 8px; padding: 1rem; }
        .audio-option { border: 1px solid #ddd; border-radius: 8px; padding: 1rem; margin-bottom: 1rem; }
        .join-footer { justify-content: center; gap: 1rem; }
        @media (max-width: 576px) {
            .row { flex-direction: column; }
            .modal-dialog { max-width: 95%; }
            .preview-container { padding: 0.5rem; }
            .audio-option { padding: 0.5rem; }
            .form-select { font-size: 0.85rem; }
        }
    </style>
</head>
<body>

<?php include 'include/sidebar.php'; ?>

<div class="container">
    <h3 class="mb-4">Meet</h3>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <button class="btn btn-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#newMeetingModal">
                <i class="fas fa-plus me-2"></i> New meeting
            </button>
        </div>
        <div class="col-md-4">
            <button class="btn btn-outline-primary w-100 py-2" data-bs-toggle="modal" data-bs-target="#joinMeetingModal">
                <i class="fas fa-sign-in-alt me-2"></i> Join with a meeting ID
            </button>
        </div>
    </div>

    <h5 class="mb-3">Meeting links</h5>
    <div class="card p-3 mb-4">
        <p class="text-muted">You don't have any scheduled meetings yet.</p>
    </div>

    <h5 class="mb-3">Scheduled meetings</h5>
    <div class="card p-3">
        <p class="text-muted">You don't have anything scheduled.</p>
    </div>
</div>

<!-- New Meeting Modal -->
<div class="modal fade" id="newMeetingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Add title</label>
                        <input type="text" name="title" class="form-control" placeholder="Add title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Enter name or e-mail</label>
                        <select name="attendees[]" class="form-control" multiple>
                            <?php while ($emp = $employees_list->fetch_assoc()): ?>
                                <option value="<?= $emp['id'] ?>"><?= htmlspecialchars($emp['name']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date and time</label>
                        <div class="d-flex gap-2">
                            <input type="datetime-local" name="start_time" class="form-control" required>
                            <input type="datetime-local" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Does not repeat</label>
                        <select name="repeat" class="form-control">
                            <option value="none">Does not repeat</option>
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Add location</label>
                        <input type="text" name="location" class="form-control" placeholder="Add location">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type details for this new meeting</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Type details for this new meeting"></textarea>
                    </div>
                    <button type="submit" name="create_meeting" class="btn btn-primary">Save</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Join Meeting Modal -->
<div class="modal fade" id="joinMeetingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-users me-2"></i>Microsoft Teams meeting</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7 preview-container">
                        <video id="videoPreview" autoplay playsinline muted></video>
                        <small id="cameraStatus" class="text-muted d-block text-center">Your camera is turned off</small>
                    </div>
                    <div class="col-md-5">
                        <div class="audio-option">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="audioOption" id="computerAudio" checked>
                                <label class="form-check-label" for="computerAudio">Computer audio</label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-microphone me-2 text-muted"></i>
                            <select class="form-select me-2">
                                <option>Microphone (Realtek(R) Audio)</option>
                            </select>
                            <div class="toggle-switch">
                                <input type="checkbox" id="micToggle" checked>
                                <label class="toggle-slider" for="micToggle"></label>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-3">
                            <i class="fas fa-volume-up me-2 text-muted"></i>
                            <select class="form-select">
                                <option>Speakers (Realtek(R) Audio)</option>
                            </select>
                        </div>
                        <div class="audio-option">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="audioOption" id="phoneAudio">
                                <label class="form-check-label" for="phoneAudio">Phone audio</label>
                            </div>
                        </div>
                        <div class="audio-option">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="audioOption" id="noAudio">
                                <label class="form-check-label" for="noAudio">Don't use audio</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-3 mt-3">
                    <i class="fas fa-camera me-2 text-muted"></i>
                    <div class="toggle-switch me-2">
                        <input type="checkbox" id="cameraToggle" onchange="toggleCamera()">
                        <label class="toggle-slider" for="cameraToggle"></label>
                    </div>
                    <span>Background filters</span>
                </div>
            </div>
            <div class="modal-footer join-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="joinMeeting()">Join now</button>
            </div>
            <div class="text-center pb-3">
                <small class="text-muted">Need help?</small>
            </div>
        </div>
    </div>
</div>

<!-- Floating Alert -->
<?php if ($alert_msg): ?>
    <div class="alert alert-<?= $alert_type ?> alert-dismissible fade show alert-floating" role="alert">
        <?= htmlspecialchars($alert_msg) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let videoStream = null;

    function toggleCamera() {
        const cameraToggle = document.getElementById('cameraToggle');
        const videoPreview = document.getElementById('videoPreview');
        const cameraStatus = document.getElementById('cameraStatus');

        if (cameraToggle.checked) {
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(stream => {
                    videoStream = stream;
                    videoPreview.srcObject = stream;
                    cameraStatus.textContent = 'Your camera is turned on';
                })
                .catch(err => {
                    console.error('Error accessing camera: ', err);
                    cameraStatus.textContent = 'Camera access denied';
                    cameraToggle.checked = false;
                });
        } else {
            if (videoStream) {
                videoStream.getTracks().forEach(track => track.stop());
            }
            videoPreview.srcObject = null;
            cameraStatus.textContent = 'Your camera is turned off';
        }
    }

    function joinMeeting() {
        // Simulate joining (replace with actual video call logic, e.g., WebRTC room)
        alert('Joining the meeting... (Implement WebRTC for real call)');
        // e.g., window.location.href = 'meeting_room.php?link=' + someLink;
    }
</script>
</body>
</html>

<?php $conn->close(); ?>