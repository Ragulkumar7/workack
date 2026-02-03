<?php
// --- 1. GLOBAL USER DATA ---
// Ensuring 'avatar_initial' is defined to fix the "Undefined array key" error
$user = [
    'name' => 'John Doe',
    'role' => 'Manager', 
    'avatar_initial' => 'J'
];

// --- 2. INITIAL SETTINGS CONTENT ---
$profile = [
    'email' => 'user@smarthr.com',
    'phone' => '+91 98765 43210',
    'bio' => 'Senior Developer with 5 years of experience.'
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
        /* --- GLOBAL LAYOUT STYLES --- */
        body { margin: 0; padding: 0; font-family: 'Poppins', sans-serif; background-color: #f4f7fc; display: flex; height: 100vh; overflow: hidden; }
        .main-content-wrapper { flex: 1; display: flex; flex-direction: column; min-width: 0; height: 100vh; }
        .dashboard-scroll-area { flex: 1; overflow-y: auto; padding: 40px; }

        /* --- SETTINGS GRID --- */
        .set-header { margin-bottom: 40px; }
        .set-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        
        .set-grid { display: grid; grid-template-columns: 280px 1fr; gap: 30px; align-items: start; }
        
        /* Sidebar Tabs */
        .set-sidebar { background: white; border-radius: 12px; border: 1px solid #e1e1e1; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.02); }
        .nav-btn { width: 100%; display: flex; align-items: center; gap: 12px; padding: 15px 20px; border: none; background: white; text-align: left; font-size: 14px; font-weight: 600; color: #666; cursor: pointer; border-left: 4px solid transparent; transition: 0.2s; }
        .nav-btn:hover { background: #f9fafb; color: #333; }
        .nav-btn.active { background: #fff7ed; color: #ea580c; border-left-color: #ea580c; }

        /* Content Area */
        .set-content { background: white; border-radius: 12px; border: 1px solid #e1e1e1; padding: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); min-height: 500px; }
        .section-tab { display: none; }
        .section-tab.active { display: block; }
        .section-title { font-size: 20px; font-weight: 700; color: #333; margin: 0 0 20px 0; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0; }

        /* Forms */
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .input-group { margin-bottom: 20px; }
        .label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 6px; }
        .input, .textarea, .select { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; }
        .input:focus { border-color: #FF9B44; }

        /* Switches */
        .toggle-row { display: flex; justify-content: space-between; align-items: center; padding: 20px; border: 1px solid #eee; border-radius: 10px; background: #f9fafb; margin-bottom: 15px; }
        .toggle-text h4 { margin: 0 0 5px 0; font-size: 14px; font-weight: 700; }
        .toggle-text p { margin: 0; font-size: 12px; color: #666; }

        .switch { position: relative; display: inline-block; width: 44px; height: 24px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #FF9B44; }
        input:checked + .slider:before { transform: translateX(20px); }

        .save-btn { background: #FF9B44; color: white; border: none; padding: 12px 30px; border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; }
        
        .set-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .set-table th { text-align: left; font-size: 11px; color: #999; padding: 10px; border-bottom: 1px solid #eee; }
        .set-table td { padding: 12px 10px; font-size: 13px; border-bottom: 1px solid #f9f9f9; }
    </style>
</head>
<body>

    <?php include './include/sidebar.php'; ?>

    <div class="main-content-wrapper">
        <?php include './include/header.php'; ?>

        <div class="dashboard-scroll-area">
            <div class="set-header">
                <h1 class="set-title">Account & System Settings</h1>
                <p style="font-size:14px; color:#666;">Dashboard / <span style="color:#FF9B44; font-weight:600;">Settings</span></p>
            </div>

            <div class="set-grid">
                <div class="set-sidebar">
                    <button class="nav-btn active" onclick="showTab('profile', this)"><i data-lucide="user"></i> My Profile</button>
                    <button class="nav-btn" onclick="showTab('security', this)"><i data-lucide="lock"></i> Security</button>
                    <button class="nav-btn" onclick="showTab('notifications', this)"><i data-lucide="bell"></i> Notifications</button>
                    
                    <?php if($user['role'] === 'Manager'): ?>
                        <button class="nav-btn" onclick="showTab('company', this)"><i data-lucide="building"></i> Company Settings</button>
                    <?php endif; ?>
                </div>

                <div class="set-content">
                    
                    <div id="profile" class="section-tab active">
                        <h3 class="section-title">Personal Information</h3>
                        <div style="display:flex; align-items:center; gap:20px; margin-bottom:30px;">
                            <div style="width:80px; height:80px; border-radius:50%; background:#FF9B44; color:white; display:flex; align-items:center; justify-content:center; font-size:32px; font-weight:800; border:4px solid #ffedd5;">
                                <?= htmlspecialchars($user['avatar_initial'] ?? substr($user['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <button style="background:white; border:1px solid #ddd; padding:8px 15px; border-radius:8px; font-weight:600; cursor:pointer;"><i data-lucide="camera" size="14"></i> Change Photo</button>
                                <p style="font-size:11px; color:#999; margin:5px 0 0 0;">Allowed JPG, GIF or PNG. Max size of 2MB</p>
                            </div>
                        </div>
                        <div class="form-grid">
                            <div class="input-group">
                                <label class="label">Full Name</label>
                                <input type="text" class="input" value="<?= htmlspecialchars($user['name']) ?>">
                            </div>
                            <div class="input-group">
                                <label class="label">Email Address</label>
                                <input type="text" class="input" value="<?= htmlspecialchars($profile['email']) ?>" disabled>
                            </div>
                            <div class="input-group">
                                <label class="label">Phone Number</label>
                                <input type="text" class="input" value="<?= htmlspecialchars($profile['phone']) ?>">
                            </div>
                            <div class="input-group">
                                <label class="label">Work Designation</label>
                                <input type="text" class="input" value="Senior Web Developer" disabled>
                            </div>
                        </div>
                        <div class="input-group">
                            <label class="label">Bio / About Me</label>
                            <textarea class="textarea"><?= htmlspecialchars($profile['bio']) ?></textarea>
                        </div>
                    </div>

                    <div id="security" class="section-tab">
                        <h3 class="section-title">Security & Credentials</h3>
                        <div class="form-grid">
                            <div class="input-group">
                                <label class="label">Current Password</label>
                                <input type="password" class="input" placeholder="••••••••">
                            </div>
                            <div class="input-group">
                                <label class="label">New Password</label>
                                <input type="password" class="input" placeholder="Min 8 characters">
                            </div>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-text">
                                <h4>Multi-Factor Authentication (MFA)</h4>
                                <p>Enable SMS or TOTP app-based secondary verification for logins.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-text">
                                <h4>End-to-End Data Encryption</h4>
                                <p>Enable full encryption for your personal files and chat history.</p>
                            </div>
                            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
                        </div>
                        <h4 style="font-size:14px; margin:20px 0 10px;">Login History Log</h4>
                        <table class="set-table">
                            <thead><tr><th>Device / Browser</th><th>IP Address</th><th>Status</th><th>Last Login</th></tr></thead>
                            <tbody>
                                <tr><td>Chrome (Windows 11)</td><td>192.168.1.45</td><td><span style="color:#10b981; font-weight:bold;">Active</span></td><td>Just Now</td></tr>
                                <tr><td>Safari (iPhone 15)</td><td>223.187.9.112</td><td><span style="color:#999;">Offline</span></td><td>Yesterday, 10:20 PM</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="notifications" class="section-tab">
                        <h3 class="section-title">Automated Alerts</h3>
                        <div class="toggle-row">
                            <div class="toggle-text"><h4>Email Summaries</h4><p>Weekly performance reports and project milestone summaries.</p></div>
                            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-text"><h4>Shift Reminders</h4><p>Mobile push alerts 15 minutes before your shift starts.</p></div>
                            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-text"><h4>Leave Approvals</h4><p>SMS notifications when your leave request is approved or rejected.</p></div>
                            <label class="switch"><input type="checkbox"><span class="slider"></span></label>
                        </div>
                    </div>

                    <div id="company" class="section-tab">
                        <h3 class="section-title">Organization Policy</h3>
                        <div style="background:#fff7ed; border-left:4px solid #f97316; padding:15px; border-radius:8px; display:flex; gap:15px; margin-bottom:30px;">
                            <i data-lucide="shield-check" color="#f97316"></i>
                            <div><strong>Global Administrator Mode</strong><p style="margin:0; font-size:13px; color:#c2410c;">System-wide changes will take effect after next employee login.</p></div>
                        </div>
                        <div class="form-grid">
                            <div class="input-group">
                                <label class="label">Organization Name</label>
                                <input type="text" class="input" value="SmartHR Solutions Pvt Ltd">
                            </div>
                            <div class="input-group">
                                <label class="label">Primary HR Contact</label>
                                <input type="text" class="input" value="admin.hr@smarthr.com">
                            </div>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-text"><h4>Geofencing Attendance</h4><p>Restrict clock-in/out to a 500m radius from office coordinates.</p></div>
                            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-text"><h4>Official Holiday Sync</h4><p>Automatically sync national holiday calendars to employee schedules.</p></div>
                            <label class="switch"><input type="checkbox" checked><span class="slider"></span></label>
                        </div>
                    </div>

                    <div style="margin-top:30px; padding-top:25px; border-top:1px solid #f0f0f0; text-align:right;">
                        <button class="save-btn" onclick="saveSettings()"><i data-lucide="save" size="18"></i> Save Changes</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function showTab(tabId, btn) {
            // Remove active classes from all sections and buttons
            document.querySelectorAll('.section-tab').forEach(tab => tab.classList.remove('active'));
            document.querySelectorAll('.nav-btn').forEach(b => b.classList.remove('active'));
            
            // Activate target
            const target = document.getElementById(tabId);
            if(target) {
                target.classList.add('active');
                btn.classList.add('active');
            }
        }

        function saveSettings() {
            alert("Settings updated successfully! Changes have been synced to the HRMS server.");
        }
    </script>
</body>
</html>