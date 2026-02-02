<?php
// Logic for handling Tabs and Modals via URL parameters
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'directory';
$showModal = isset($_GET['modal']) && $_GET['modal'] === 'true';

// Mock Data: Employee Directory
$employees = [
    ['id' => 'EMP001', 'name' => 'Varshith', 'role' => 'Sr. Developer', 'dept' => 'Engineering', 'status' => 'Active', 'ini' => 'VA'],
    ['id' => 'EMP002', 'name' => 'Aditi Rao', 'role' => 'UI/UX Designer', 'dept' => 'Design', 'status' => 'Active', 'ini' => 'AR'],
    ['id' => 'EMP003', 'name' => 'Sanjay Kumar', 'role' => 'DevOps', 'dept' => 'Engineering', 'status' => 'On Leave', 'ini' => 'SK'],
    ['id' => 'EMP004', 'name' => 'Priya Sharma', 'role' => 'HR Exec', 'dept' => 'People', 'status' => 'Active', 'ini' => 'PS'],
];

// Mock Data: Hiring & ATS
$candidates = [
    ['name' => 'Alex Rivers', 'skills' => 'React, Node, Tailwind', 'exp' => '4 Years', 'status' => 'Screened', 'color' => '#e0f2fe', 'text' => '#0369a1'],
    ['name' => 'Sarah Connor', 'skills' => 'UI/UX, Figma, Adobe', 'exp' => '5 Years', 'status' => 'Interview', 'color' => '#eef2ff', 'text' => '#4338ca'],
    ['name' => 'Michael Scott', 'skills' => 'Sales, Management', 'exp' => '10 Years', 'status' => 'Applied', 'color' => '#f0f9ff', 'text' => '#075985'],
];

// Mock Data: Attendance Maintenance
$attendance = [
    ['name' => 'John Doe (Employee)', 'date' => 'Oct 26, 2023', 'in' => '09:02 AM', 'out' => '06:15 PM', 'status' => 'Present', 's_bg' => '#e6fffa', 's_txt' => '#047857', 'leaves' => '2'],
    ['name' => 'Jane Smith (TL)', 'date' => 'Oct 26, 2023', 'in' => '09:45 AM', 'out' => '06:00 PM', 'status' => 'Late', 's_bg' => '#fff7ed', 's_txt' => '#c2410c', 'leaves' => '1'],
    ['name' => 'Robert Johnson (Manager)', 'date' => 'Oct 26, 2023', 'in' => '--', 'out' => '--', 'status' => 'Absent', 's_bg' => '#fef2f2', 's_txt' => '#b91c1c', 'leaves' => '5'],
];

// Mock Data: Announcements
$recent_comms = [
    ['type' => 'COMPANY UPDATE', 'title' => 'New Remote Work Policy - V2.0', 'date' => 'Oct 24, 2023', 'color' => '#f97316'],
    ['type' => 'EVENT', 'title' => 'Annual Team Retreat 2023 Registration', 'date' => 'Oct 20, 2023', 'color' => '#8b5cf6']
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Management System</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --primary-orange: #FF9B44;
            --primary-purple: #FF9B44;
            --bg-gray: #f4f6f9;
            --dark-text: #111;
        }

        body {
            font-family: 'Inter', -apple-system, sans-serif;
            background-color: var(--bg-gray);
            margin: 0; padding: 40px; color: #333;
        }

        /* Tabs Navigation */
        .hr-tabs-area {
            margin-bottom: 40px; display: flex; gap: 15px;
            background: white; padding: 10px; border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .hr-tab-btn {
            text-decoration: none; display: inline-flex; align-items: center;
            gap: 10px; padding: 12px 24px; border-radius: 8px;
            font-size: 14px; font-weight: bold; color: #666; transition: all 0.2s;
        }

        .hr-tab-btn:hover:not(.active) { color: var(--primary-orange); }
        .hr-tab-btn.active { background-color: #f3f0ff; color: var(--primary-purple); }

        .content-card {
            background: white; padding: 30px; border-radius: 16px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        /* Forms & Buttons */
        .btn-purple { background: var(--primary-purple); color: white; border: none; padding: 12px 24px; border-radius: 8px; font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        .form-input { width: 100%; padding: 12px; border: 1px solid #e5e7eb; border-radius: 8px; outline: none; box-sizing: border-box; font-size: 14px; }
        .form-input:focus { border-color: var(--primary-purple); }
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { text-align: left; padding: 15px; color: #222; text-transform: uppercase; font-size: 12px; font-weight: 700; border-bottom: 2px solid #f0f0f0; }
        td { padding: 18px 15px; border-bottom: 1px solid #f8f9fa; font-size: 14px; }
        
        .status-badge { padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .avatar-circle { width: 40px; height: 40px; background: #fff0e0; color: var(--primary-orange); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; float: left; }

        /* Modal Specific Styles */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); display: flex;
            align-items: center; justify-content: center; z-index: 999;
        }
        .modal-content { 
            background: white; width: 850px; padding: 35px; border-radius: 20px; 
            position: relative; box-shadow: 0 20px 50px rgba(0,0,0,0.25);
        }
        .modal-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .modal-header h2 { margin: 0; font-size: 26px; color: #1a202c; font-weight: 700; }
        .close-btn { background: none; border: none; cursor: pointer; color: #333; }

        .modal-form-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 30px;
        }
        .field-group { display: flex; flex-direction: column; gap: 8px; }
        .field-group label { font-size: 11px; font-weight: 800; color: #4a5568; text-transform: uppercase; letter-spacing: 0.5px; }

        .btn-save { background: var(--primary-purple); color: white; border: none; padding: 14px 45px; border-radius: 12px; font-weight: 700; cursor: pointer; float: right; }
        
        /* Stats list for hiring */
        .stat-list-item { padding: 15px 0; border-bottom: 1px solid #f0f0f0; }
    </style>
</head>
<body>

    <div class="hr-tabs-area">
        <a href="?tab=directory" class="hr-tab-btn <?php echo $activeTab == 'directory' ? 'active' : ''; ?>">
            <i data-lucide="layout-grid"></i> Employee Directory
        </a>
        <a href="?tab=hiring" class="hr-tab-btn <?php echo $activeTab == 'hiring' ? 'active' : ''; ?>">
            <i data-lucide="user-plus"></i> Hiring & ATS
        </a>
        <a href="?tab=attendance" class="hr-tab-btn <?php echo $activeTab == 'attendance' ? 'active' : ''; ?>">
            <i data-lucide="calendar"></i> Attendance Maintenance
        </a>
        <a href="?tab=announcements" class="hr-tab-btn <?php echo $activeTab == 'announcements' ? 'active' : ''; ?>">
            <i data-lucide="megaphone"></i> Announcements
        </a>
    </div>

    <?php if ($activeTab === 'directory'): ?>
        <div class="content-card">
            <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
                <div style="position:relative; width:300px">
                    <input placeholder="Search..." class="form-input" style="padding-left:35px;">
                    <i data-lucide="search" style="position:absolute; top:12px; left:10px; width:16px; color:#999"></i>
                </div>
                <a href="?tab=directory&modal=true" class="btn-purple" style="background:#111; text-decoration:none;">
                    <i data-lucide="plus"></i> Add Employee
                </a>
            </div>
            <table>
                <thead><tr><th>Employee</th><th>Role</th><th>Dept</th><th>Status</th><th style="text-align: right;">Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <div class="avatar-circle"><?php echo $emp['ini']; ?></div>
                            <div style="font-weight:700;"><?php echo $emp['name']; ?></div>
                            <div style="font-size:12px; color:#999;"><?php echo $emp['id']; ?></div>
                        </td>
                        <td><?php echo $emp['role']; ?></td>
                        <td><?php echo $emp['dept']; ?></td>
                        <td><span class="status-badge" style="background:#e6fffa; color:#047857; text-transform:uppercase;"><?php echo $emp['status']; ?></span></td>
                        <td style="text-align: right;"><i data-lucide="eye" style="color:#999; cursor:pointer;"></i></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($activeTab === 'hiring'): ?>
        <div class="content-card">
            <div class="stat-list-item">
                <div style="font-weight:800; color:#fb923c;"><i data-lucide="users" size="20"></i> Open Positions</div>
                <div style="padding-left:30px;">14</div>
            </div>
            <div class="stat-list-item">
                <div style="font-weight:800; color:#3b82f6;"><i data-lucide="file-search" size="20"></i> Screened</div>
                <div style="padding-left:30px;">482</div>
            </div>
            <div class="stat-list-item">
                <div style="font-weight:800; color:#22c55e;"><i data-lucide="calendar-check" size="20"></i> Interviews</div>
                <div style="padding-left:30px;">28</div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top:30px;">
                <div style="position:relative; width:400px">
                    <input placeholder="Search candidates..." class="form-input" style="padding-left: 40px;">
                    <i data-lucide="search" style="position:absolute; top:12px; left:12px; width:18px; color:#9ca3af"></i>
                </div>
                <button class="btn-purple"><i data-lucide="upload-cloud"></i> Bulk Upload</button>
            </div>
            <table>
                <thead><tr><th>Candidate</th><th>Matched Skills</th><th>Exp</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($candidates as $c): ?>
                    <tr>
                        <td style="font-weight:700;"><?php echo $c['name']; ?></td>
                        <td style="color:var(--primary-orange); font-weight:600;"><?php echo $c['skills']; ?></td>
                        <td><?php echo $c['exp']; ?></td>
                        <td><span class="status-badge" style="background:<?php echo $c['color']; ?>; color:<?php echo $c['text']; ?>;"><?php echo $c['status']; ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($activeTab === 'attendance'): ?>
        <div class="content-card">
            <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                <select class="form-input" style="width:150px;"><option>October 2023</option></select>
                <select class="form-input" style="width:180px;"><option>All Departments</option></select>
            </div>
            <table>
                <thead><tr><th>Employee</th><th>Date</th><th>Clock In</th><th>Clock Out</th><th>Status</th><th>Leaves</th><th>Actions</th></tr></thead>
                <tbody>
                    <?php foreach ($attendance as $row): ?>
                    <tr>
                        <td style="font-weight: 700;"><?php echo $row['name']; ?></td>
                        <td><?php echo $row['date']; ?></td>
                        <td><?php echo $row['in']; ?></td>
                        <td><?php echo $row['out']; ?></td>
                        <td><span class="status-badge" style="background:<?php echo $row['s_bg']; ?>; color:<?php echo $row['s_txt']; ?>;"><?php echo $row['status']; ?></span></td>
                        <td style="color: #059669; font-weight: 700;"><?php echo $row['leaves']; ?></td>
                        <td><a href="#" style="color:#63439C; text-decoration:none; font-weight:700;">View History</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($activeTab === 'announcements'): ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div class="content-card">
                <h2>Post Announcement</h2>
                <div style="margin-bottom:15px;"><label style="font-size:11px; font-weight:800; color:#666;">TITLE</label><input type="text" class="form-input"></div>
                <div style="margin-bottom:15px;"><label style="font-size:11px; font-weight:800; color:#666;">MESSAGE</label><textarea class="form-input" style="height:120px;"></textarea></div>
                <button class="btn-purple" style="width:100%; justify-content:center;">Publish Now</button>
            </div>
            <div class="content-card">
                <h2>Recent Communications</h2>
                <?php foreach ($recent_comms as $comm): ?>
                <div style="padding: 15px 0; border-bottom: 1px solid #f3f4f6;">
                    <div style="font-size: 10px; font-weight: 800; color: <?php echo $comm['color']; ?>;"><?php echo $comm['type']; ?></div>
                    <div style="font-size: 16px; font-weight: 700;"><?php echo $comm['title']; ?></div>
                    <div style="font-size: 13px; color: #FF9B44;"><?php echo $comm['date']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($showModal): ?>
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Add New Employee</h2>
                <a href="?tab=<?php echo $activeTab; ?>" class="close-btn"><i data-lucide="x" size="24"></i></a>
            </div>

            <form method="POST">
                <div class="modal-form-grid">
                    <div class="field-group">
                        <label>Employee ID</label>
                        <input type="text" class="form-input" placeholder="EMP001">
                    </div>
                    <div class="field-group">
                        <label>Full Name</label>
                        <input type="text" class="form-input" placeholder="Enter full name">
                    </div>

                    <div class="field-group">
                        <label>Email ID</label>
                        <input type="email" class="form-input" placeholder="employee@company.com">
                    </div>
                    <div class="field-group">
                        <label>Role</label>
                        <select class="form-input">
                            <option>Software Developer</option>
                            <option>UI/UX Designer</option>
                            <option>Human Resources</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label>Designation</label>
                        <select class="form-input">
                            <option>Employee</option>
                            <option>Senior Employee</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label>Department</label>
                        <select class="form-input">
                            <option>Engineering</option>
                            <option>Design</option>
                            <option>Management</option>
                        </select>
                    </div>

                    <div class="field-group">
                        <label>Joining Date</label>
                        <input type="date" class="form-input">
                    </div>
                    <div class="field-group">
                        <label>Salary (₹)</label>
                        <input type="text" class="form-input" placeholder="Monthly salary">
                    </div>
                </div>

                <div style="overflow:hidden;">
                    <button type="submit" class="btn-save">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script>lucide.createIcons();</script>
</body>
</html>