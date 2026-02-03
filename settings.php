<?php
// --- 1. TARGETED DATABASE CONNECTION ---
$db_path = './login/db_connect.php'; 
if (file_exists($db_path)) {
    include_once($db_path);
} else {
    die("Critical Error: Cannot find db_connect.php");
}

// --- 2. GLOBAL USER SAVE LOGIC ---
$update_status = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Sanitize Profile Data
    $fname = mysqli_real_escape_string($conn, $_POST['first_name']);
    $lname = mysqli_real_escape_string($conn, $_POST['last_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $addr  = mysqli_real_escape_string($conn, $_POST['address']);
    $city  = mysqli_real_escape_string($conn, $_POST['city']);
    
    // Notification Toggles
    $nh = isset($_POST['notif_new_hire']) ? 1 : 0;
    $nl = isset($_POST['notif_leave']) ? 1 : 0;
    $np = isset($_POST['notif_performance']) ? 1 : 0;
    $pr = isset($_POST['notif_payroll']) ? 1 : 0;

    // Company Settings
    $org_name  = mysqli_real_escape_string($conn, $_POST['org_name']);
    $org_email = mysqli_real_escape_string($conn, $_POST['org_email']);
    $geo       = isset($_POST['geo']) ? 1 : 0;

    $sqlProfile = "UPDATE user_settings SET 
                   first_name='$fname', last_name='$lname', phone='$phone', 
                   address='$addr', city='$city' WHERE user_id=1";
    
    $sqlPrefs = "UPDATE user_preferences SET 
                 notif_new_hire=$nh, notif_leave=$nl, notif_performance=$np, 
                 notif_payroll=$pr, org_name='$org_name', org_email='$org_email', 
                 geofencing=$geo WHERE user_id=1";

    if (mysqli_query($conn, $sqlProfile) && mysqli_query($conn, $sqlPrefs)) {
        $update_status = "success";
    }
}

// --- 3. DYNAMIC DATA FETCHING ---
$sqlData = "SELECT s.*, p.* FROM user_settings s JOIN user_preferences p ON s.user_id = p.user_id WHERE s.user_id = 1";
$res = mysqli_query($conn, $sqlData);
$data = mysqli_fetch_assoc($res);

$user = [
    'name' => ($data['first_name'] ?? 'John') . ' ' . ($data['last_name'] ?? 'Doe'),
    'role' => 'Manager', 
    'avatar_initial' => substr($data['first_name'] ?? 'J', 0, 1)
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Settings | Workack HRMS</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 40px; }
        .set-grid { display: grid; grid-template-columns: 280px 1fr; gap: 30px; align-items: start; }
        .set-sidebar { background: white; border-radius: 12px; border: 1px solid #e1e1e1; overflow: hidden; }
        .nav-btn { width: 100%; display: flex; align-items: center; gap: 12px; padding: 15px 20px; border: none; background: white; text-align: left; font-size: 14px; font-weight: 600; color: #666; cursor: pointer; border-left: 4px solid transparent; transition: 0.2s; }
        .nav-btn.active { background: #fff7ed; color: #ea580c; border-left-color: #ea580c; }
        .set-content { background: white; border-radius: 12px; border: 1px solid #e1e1e1; padding: 0; box-shadow: 0 4px 15px rgba(0,0,0,0.03); overflow: hidden; }
        .section-tab { display: none; padding: 40px; }
        .section-tab.active { display: block; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .input-group { margin-bottom: 20px; }
        .label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 6px; }
        .input, .textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; }
        
        .sec-row { padding: 25px 0; border-bottom: 1px solid #f0f0f0; }
        .sec-flex { display: flex; justify-content: space-between; align-items: center; }
        .sec-info h4 { margin: 0; font-size: 15px; color: #333; }
        .sec-info p { margin: 5px 0 0 0; font-size: 13px; color: #666; }
        .sec-btn { padding: 8px 20px; border-radius: 6px; border: 1px solid #ddd; background: white; cursor: pointer; font-size: 13px; font-weight: 600; }
        .sec-btn-orange { background: #FF9B44; color: white; border: none; }
        
        .sec-action-box { display: none; background: #f9fafb; padding: 20px; border-radius: 8px; margin-top: 15px; border: 1px solid #eee; }
        .sec-action-box.active { display: block; }

        .notif-table { width: 100%; border-collapse: collapse; }
        .notif-table th { text-align: left; padding: 15px; background: #f9fafb; font-size: 12px; color: #888; border-bottom: 1px solid #eee; }
        .notif-table td { padding: 20px 15px; border-bottom: 1px solid #f0f0f0; }
        
        .switch { position: relative; display: inline-block; width: 36px; height: 18px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 20px; }
        .slider:before { position: absolute; content: ""; height: 12px; width: 12px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #FF9B44; }
        input:checked + .slider:before { transform: translateX(18px); }

        .save-btn { background: #FF9B44; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 700; cursor: pointer; margin: 20px; display: inline-flex; align-items: center; gap: 8px; }
    </style>
</head>
<body>
    <?php include './include/sidebar.php'; ?>
    <div class="main-content-wrapper">
        <?php include './include/header.php'; ?>
        <div class="dashboard-scroll-area">
            <div class="set-grid">
                <div class="set-sidebar">
                    <button class="nav-btn active" onclick="showTab('profile', this)"><i data-lucide="user"></i> Profile Settings</button>
                    <button class="nav-btn" onclick="showTab('security', this)"><i data-lucide="lock"></i> Security Settings</button>
                    <button class="nav-btn" onclick="showTab('notifications', this)"><i data-lucide="bell"></i> Notifications</button>
                    <button class="nav-btn" onclick="showTab('company', this)"><i data-lucide="building"></i> Company Settings</button>
                </div>

                <form method="POST" action="" class="set-content">
                    <div id="profile" class="section-tab active">
                        <h3 style="margin-top:0;">Basic Information</h3>
                        <div class="form-grid">
                            <div class="input-group"><label class="label">First Name</label><input type="text" name="first_name" class="input" value="<?= htmlspecialchars($data['first_name'] ?? '') ?>"></div>
                            <div class="input-group"><label class="label">Last Name</label><input type="text" name="last_name" class="input" value="<?= htmlspecialchars($data['last_name'] ?? '') ?>"></div>
                            <div class="input-group"><label class="label">Email</label><input type="text" class="input" value="<?= htmlspecialchars($data['email'] ?? '') ?>" disabled></div>
                            <div class="input-group"><label class="label">Phone</label><input type="text" name="phone" class="input" value="<?= htmlspecialchars($data['phone'] ?? '') ?>"></div>
                        </div>
                        <h3>Address Information</h3>
                        <div class="input-group"><label class="label">Address</label><input type="text" name="address" class="input" value="<?= htmlspecialchars($data['address'] ?? '') ?>"></div>
                        <div class="form-grid">
                            <div class="input-group"><label class="label">City</label><input type="text" name="city" class="input" value="<?= htmlspecialchars($data['city'] ?? '') ?>"></div>
                            <div class="input-group"><label class="label">Country</label><input type="text" class="input" value="<?= htmlspecialchars($data['country'] ?? 'India') ?>" disabled></div>
                        </div>
                    </div>

                    <div id="security" class="section-tab">
                        <div class="sec-row">
                            <div class="sec-flex">
                                <div class="sec-info"><h4>Password</h4><p>Last Changed <?= htmlspecialchars($data['last_pw_change'] ?? '03 Jan 2024') ?></p></div>
                                <button type="button" class="sec-btn sec-btn-orange" onclick="toggleSecAction('pwBox')">Change Password</button>
                            </div>
                            <div id="pwBox" class="sec-action-box">
                                <div class="input-group"><label class="label">Current Password</label><input type="password" class="input" placeholder="Enter old password"></div>
                                <div class="form-grid">
                                    <div class="input-group"><label class="label">New Password</label><input type="password" class="input"></div>
                                    <div class="input-group"><label class="label">Confirm Password</label><input type="password" class="input"></div>
                                </div>
                                <button type="button" class="sec-btn sec-btn-orange" onclick="this.parentElement.classList.remove('active')">Update Password</button>
                            </div>
                        </div>

                        <div class="sec-row">
                            <div class="sec-flex">
                                <div class="sec-info"><h4>Two Factor Authentication</h4><p>Receive codes via SMS or email every time you login</p></div>
                                <button type="button" class="sec-btn" onclick="toggleSecAction('twoFactorBox')">Enable</button>
                            </div>
                            <div id="twoFactorBox" class="sec-action-box">
                                <p style="font-size:13px;">Choose your primary method:</p>
                                <div style="display:flex; gap:15px; margin-bottom:15px;">
                                    <label><input type="radio" name="2fa" checked> Authenticator App</label>
                                    <label><input type="radio" name="2fa"> SMS Text Message</label>
                                </div>
                                <button type="button" class="sec-btn sec-btn-orange" onclick="this.parentElement.classList.remove('active'); alert('2FA Setup Confirmed!');">Confirm Setup</button>
                            </div>
                        </div>

                        <div class="sec-row">
                            <div class="sec-flex">
                                <div class="sec-info"><h4>Device Management</h4><p>Manage the devices associated with this account</p></div>
                                <button type="button" class="sec-btn" onclick="toggleSecAction('deviceBox')">Manage</button>
                            </div>
                            <div id="deviceBox" class="sec-action-box">
                                <div style="font-size:13px; color:#333; padding-bottom:10px; border-bottom:1px solid #ddd;"><strong>Windows 11 PC</strong> - Coimbatore, India</div>
                                <div style="font-size:13px; color:#333; padding-top:10px;"><strong>iPhone 15</strong> - Coimbatore, India</div>
                            </div>
                        </div>

                        <div class="sec-row" style="border:none;">
                            <div class="sec-flex">
                                <div class="sec-info"><h4>Deactivate Account</h4><p>This will shutdown your account temporarily</p></div>
                                <button type="button" class="sec-btn" style="color:red;" onclick="toggleSecAction('deactivateBox')">Deactivate</button>
                            </div>
                            <div id="deactivateBox" class="sec-action-box" style="background:#fff5f5; border-color:#feb2b2;">
                                <p style="font-size:13px; color:#c53030;"><strong>Warning:</strong> You will be logged out of all devices immediately.</p>
                                <button type="button" class="sec-btn" style="background:#e53e3e; color:white; border:none;">Confirm Deactivation</button>
                            </div>
                        </div>
                    </div>

                    <div id="notifications" class="section-tab" style="padding:0;">
                        <table class="notif-table">
                            <thead><tr><th>Modules</th><th>Push</th><th>SMS</th><th>Email</th></tr></thead>
                            <tbody>
                                <tr>
                                    <td><strong>New Hire and Onboarding</strong><p style="font-size:12px;color:#888;margin:0;">Alerts when a new hire is added</p></td>
                                    <td><label class="switch"><input type="checkbox" name="notif_new_hire" <?= ($data['notif_new_hire'] ?? 0) ? 'checked' : '' ?>><span class="slider"></span></label></td>
                                    <td><label class="switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                                    <td><label class="switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                                </tr>
                                <tr>
                                    <td><strong>Time Off and Leave Requests</strong><p style="font-size:12px;color:#888;margin:0;">Notifications for leave approvals</p></td>
                                    <td><label class="switch"><input type="checkbox" name="notif_leave" <?= ($data['notif_leave'] ?? 0) ? 'checked' : '' ?>><span class="slider"></span></label></td>
                                    <td><label class="switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                                    <td><label class="switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                                </tr>
                                <tr>
                                    <td><strong>Employee Performance Updates</strong><p style="font-size:12px;color:#888;margin:0;">Performance and Review Updates</p></td>
                                    <td><label class="switch"><input type="checkbox" name="notif_performance" <?= ($data['notif_performance'] ?? 0) ? 'checked' : '' ?>><span class="slider"></span></label></td>
                                    <td><label class="switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                                    <td><label class="switch"><input type="checkbox" checked><span class="slider"></span></label></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="company" class="section-tab">
                        <h3 style="margin-top:0;">Company Profile</h3>
                        <div class="form-grid">
                            <div class="input-group"><label class="label">Organization Name</label><input type="text" name="org_name" class="input" value="<?= htmlspecialchars($data['org_name'] ?? 'Workack Solutions') ?>"></div>
                            <div class="input-group"><label class="label">Corporate Email</label><input type="text" name="org_email" class="input" value="<?= htmlspecialchars($data['org_email'] ?? 'admin@workack.com') ?>"></div>
                            <div class="input-group"><label class="label">Website URL</label><input type="text" class="input" value="www.workack.com"></div>
                            <div class="input-group"><label class="label">Contact Number</label><input type="text" class="input" value="+91 422 1234567"></div>
                        </div>
                        <h3>Operational Controls</h3>
                        <div class="toggle-row">
                            <div><strong>Geofencing Attendance</strong><p style="font-size:12px;color:#666;margin:0;">Restrict clock-in/out to 500m of office perimeter.</p></div>
                            <label class="switch"><input type="checkbox" name="geo" <?= ($data['geofencing'] ?? 0) ? 'checked' : '' ?>><span class="slider"></span></label>
                        </div>
                        <div class="toggle-row">
                            <div><strong>Auto-Approval for Overtime</strong><p style="font-size:12px;color:#666;margin:0;">Automatically approve extra hours up to 2hrs/day.</p></div>
                            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
                        </div>
                    </div>

                    <div style="text-align:right;">
                        <button type="submit" name="save_settings" class="save-btn"><i data-lucide="save" size="18"></i> Save All Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
        function showTab(tabId, btn) {
            document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            document.getElementById(tabId).classList.add('active');
            btn.classList.add('active');
        }

        function toggleSecAction(boxId) {
            const box = document.getElementById(boxId);
            const isActive = box.classList.contains('active');
            document.querySelectorAll('.sec-action-box').forEach(b => b.classList.remove('active'));
            if (!isActive) box.classList.add('active');
        }

        <?php if($update_status === "success"): ?>
            alert("Settings updated and saved to database!");
            window.location.href = 'settings.php';
        <?php endif; ?>
    </script>
</body>
</html>