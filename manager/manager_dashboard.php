<?php
include_once('../login/db_connect.php'); 

// Fetching Department-wide Data
// This sums up stats that a Team Lead would only see for one team
$total_dept_tasks = 0; 
$pending_approvals = 5; // Example: HR/Payroll approvals needed

if (isset($conn) && $conn) {
    // Example Query: Get total tasks across ALL teams for this manager's department
    $res = mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks WHERE status != 'Completed'");
    $total_dept_tasks = mysqli_fetch_assoc($res)['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Using the 'Styler' variables we established */
        :root {
            --primary: #6366f1;
            --surface: #ffffff;
            --bg: #f8fafc;
        }

        .manager-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-top: 30px;
        }

        /* Action Card Styling */
        .action-card {
            background: var(--surface);
            padding: 24px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 24px rgba(99, 102, 241, 0.1);
            border-color: var(--primary);
        }

        .icon-box {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        .dept-table {
            width: 100%;
            background: white;
            border-radius: 20px;
            margin-top: 30px;
            padding: 25px;
            border: 1px solid #e2e8f0;
        }

        .status-pill {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
    </style>
</head>
<body style="background: var(--bg); padding: 40px;">

    <header>
        <h1 style="font-weight: 800; font-size: 28px; margin: 0;">Managerial Overview</h1>
        <p style="color: #64748b;">Departmental performance and administrative actions.</p>
    </header>

    <div class="manager-grid">
        <div class="action-card">
            <div class="icon-box" style="background: #eef2ff; color: #6366f1;">
                <i data-lucide="layers"></i>
            </div>
            <div style="font-size: 24px; font-weight: 800;"><?= $total_dept_tasks ?></div>
            <div style="color: #64748b; font-size: 14px;">Pending Dept. Tasks</div>
        </div>

        <div class="action-card">
            <div class="icon-box" style="background: #fff7ed; color: #f59e0b;">
                <i data-lucide="bell"></i>
            </div>
            <div style="font-size: 24px; font-weight: 800;"><?= $pending_approvals ?></div>
            <div style="color: #64748b; font-size: 14px;">Awaiting Approval</div>
        </div>

        <div class="action-card">
            <div class="icon-box" style="background: #ecfdf5; color: #10b981;">
                <i data-lucide="trending-up"></i>
            </div>
            <div style="font-size: 24px; font-weight: 800;">92%</div>
            <div style="color: #64748b; font-size: 14px;">Dept. Efficiency</div>
        </div>

        <div class="action-card">
            <div class="icon-box" style="background: #fef2f2; color: #ef4444;">
                <i data-lucide="alert-circle"></i>
            </div>
            <div style="font-size: 24px; font-weight: 800;">2</div>
            <div style="color: #64748b; font-size: 14px;">Critical Delays</div>
        </div>
    </div>

    <div class="dept-table">
        <h2 style="font-size: 18px; margin-bottom: 20px;">Team Lead Performance</h2>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="color: #94a3b8; font-size: 13px; border-bottom: 1px solid #f1f5f9;">
                    <th style="padding: 15px;">TEAM LEAD</th>
                    <th>DEPARTMENT</th>
                    <th>TASK PROGRESS</th>
                    <th>EFFICIENCY</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                <tr style="border-bottom: 1px solid #f8fafc;">
                    <td style="padding: 15px; font-weight: 600;">Rahul Kumar</td>
                    <td style="color: #64748b;">Digital Marketing</td>
                    <td>85%</td>
                    <td style="font-weight: 700; color: #10b981;">High</td>
                    <td><span class="status-pill" style="background: #dcfce7; color: #15803d;">Active</span></td>
                </tr>
                <tr>
                    <td style="padding: 15px; font-weight: 600;">Sara Khan</td>
                    <td style="color: #64748b;">Sales</td>
                    <td>42%</td>
                    <td style="font-weight: 700; color: #f59e0b;">Average</td>
                    <td><span class="status-pill" style="background: #fef3c7; color: #92400e;">Warning</span></td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>