<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<?php
/**
 * ACCOUNTS & PAYROLL MANAGEMENT SYSTEM
 * ---------------------------------------------------
 * Logic for LOP (Loss of Pay) and Net Salary calculation.
 */

// --- MOCK DATA (Simulated Database) ---
$employees = [
    ['id' => "ACC-101", 'name' => "Varshith", 'base' => 85000, 'leaves' => 1, 'leaveType' => 'Casual', 'hike' => 10000, 'lpa' => 10.2],
    ['id' => "ACC-102", 'name' => "Aditi Rao", 'base' => 55000, 'leaves' => 4, 'leaveType' => 'Medical', 'hike' => 0, 'lpa' => 6.6],
    ['id' => "ACC-103", 'name' => "Sanjay Kumar", 'base' => 72000, 'leaves' => 0, 'leaveType' => 'None', 'hike' => 5000, 'lpa' => 8.4],
];

// --- PAYROLL CALCULATION LOGIC ---
function calculatePT($lpa) {
    return ($lpa > 10 ? 2000 : ($lpa > 5 ? 1000 : 0));
}

function calculateNet($emp) {
    $totalDays = 30; // Standard month calculation
    $allowedCL = 2;  // Free Casual Leaves allowed per month
    
    // Logic: If leaves > 2, the extra days are LOP
    $lopDays = ($emp['leaves'] > $allowedCL) ? ($emp['leaves'] - $allowedCL) : 0;
    $paidDays = $totalDays - $lopDays;

    $dailyRate = $emp['base'] / 30;
    $gross = ($dailyRate * $paidDays) + (float)$emp['hike'];
    
    $deductions = calculatePT($emp['lpa']) + ($gross * 0.10); // PT + 10% TDS
    return floor($gross - $deductions);
}

// Calculate total projected payroll for the header stat card
$totalProjectedPayroll = 0;
foreach ($employees as $e) {
    $totalProjectedPayroll += calculateNet($e);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts & Payroll Management</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* 1. Layout & Container Styles */
        .acc-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
        }

        /* 2. Header Section */
        .acc-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 40px; flex-wrap: wrap; gap: 20px;
        }
        .acc-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .acc-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }
        .header-actions { display: flex; gap: 12px; }
        
        .btn {
            display: flex; align-items: center; gap: 8px; padding: 10px 20px;
            border-radius: 8px; font-weight: 700; font-size: 14px; cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .btn-white { background: white; border: 1px solid #e1e1e1; color: #555; }
        .btn-primary { background: #FF9B44; color: white; border: none; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.2); }

        /* 3. Stats Grid */
        .stats-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px; margin-bottom: 40px;
        }
        .stat-card {
            background: white; padding: 25px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1;
            display: flex; align-items: center; gap: 20px; transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-icon {
            width: 55px; height: 55px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
        }
        .bg-blue { background: #eff6ff; color: #3b82f6; }
        .bg-green { background: #f0fdf4; color: #22c55e; }
        .bg-orange { background: #fff7ed; color: #f97316; }
        
        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 5px; display: block; }
        .stat-value { font-size: 24px; font-weight: 800; color: #333; margin: 0; }

        /* 4. Main Payroll Table */
        .table-card {
            background: white; border-radius: 16px; box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            border: 1px solid #e1e1e1; margin-bottom: 40px; overflow: hidden;
        }
        .table-header { padding: 20px; border-bottom: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
        .section-title { font-size: 18px; font-weight: 700; color: #333; display: flex; align-items: center; gap: 10px; }
        .live-badge { background: #dcfce7; color: #166534; font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 6px; text-transform: uppercase; }

        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 900px; }
        th {
            text-align: left; padding: 15px 20px; background: #f9fafb; color: #666;
            font-size: 12px; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid #e5e7eb;
        }
        td { padding: 15px 20px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: #333; font-size: 14px; }
        tr:hover td { background-color: #fcfcfc; }

        .salary-input {
            width: 80px; padding: 8px; border: 1px solid #ddd; border-radius: 6px; 
            font-size: 14px; outline: none; font-weight: 600; color: #555;
        }
        .leave-select { padding: 8px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; outline: none; background: white; width: 100px; }

        .lop-badge {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700;
        }
        .badge-danger { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
        .badge-success { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }

        .save-icon-btn {
            background: #FF9B44; color: white; border: none; padding: 8px; border-radius: 6px; cursor: pointer;
        }

        /* 5. Bottom Grid */
        .bottom-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; }
        .info-card { background: white; padding: 30px; border-radius: 12px; border: 1px solid #e1e1e1; }
        .audit-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f0f0f0; }
        .report-btn {
            width: 100%; padding: 12px; border: 1px solid #FF9B44; background: white; color: #FF9B44;
            font-weight: 700; border-radius: 8px; cursor: pointer; transition: all 0.2s;
        }
        .report-btn:hover { background: #FF9B44; color: white; }

        @media print { .header-actions, .save-icon-btn, .report-btn { display: none; } }
    </style>
</head>
<body>

<div class="acc-container">
    
    <div class="acc-header">
        <div>
            <h2 class="acc-title">Accounts & Financial Control</h2>
            <div class="acc-breadcrumb">
                Dashboard / <span style="color:#FF9B44; font-weight:bold;">Payroll Management</span>
            </div>
        </div>
        <div class="header-actions">
            <button class="btn btn-white" onclick="window.print()">
                <i data-lucide="printer"></i> Print Slips
            </button>
            <button class="btn btn-primary">
                <i data-lucide="file-spreadsheet"></i> Export to Auditor
            </button>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i data-lucide="trending-up"></i></div>
            <div>
                <span class="stat-label">Projected Payroll</span>
                <h3 class="stat-value">₹<?php echo number_format($totalProjectedPayroll); ?></h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-green"><i data-lucide="shield-check"></i></div>
            <div>
                <span class="stat-label">Tax Compliance</span>
                <h3 class="stat-value">TDS/GST Active</h3>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon bg-orange"><i data-lucide="landmark"></i></div>
            <div>
                <span class="stat-label">Org Balance</span>
                <h3 class="stat-value">₹45,20,000</h3>
            </div>
        </div>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h4 class="section-title">
                <i data-lucide="calculator" style="color:#FF9B44"></i> Admin Payroll Management
            </h4>
            <span class="live-badge">Live Calculation</span>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Emp ID</th>
                        <th>Employee Name</th>
                        <th>Base Salary (₹)</th>
                        <th>Leaves Taken</th>
                        <th>Leave Type</th>
                        <th>LOP Status</th>
                        <th>Net Payable</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <?php 
                        $lopDays = max(0, $emp['leaves'] - 2); 
                        $netPayable = calculateNet($emp);
                    ?>
                    <tr>
                        <td style="font-weight:bold; color:#FF9B44"><?php echo $emp['id']; ?></td>
                        <td style="font-weight:bold"><?php echo htmlspecialchars($emp['name']); ?></td>
                        <td>
                            <input type="number" class="salary-input" value="<?php echo $emp['base']; ?>">
                        </td>
                        <td>
                            <input type="number" class="salary-input" style="width:50px; text-align:center;" value="<?php echo $emp['leaves']; ?>">
                        </td>
                        <td>
                            <select class="leave-select">
                                <option value="None" <?php echo $emp['leaveType'] == 'None' ? 'selected' : ''; ?>>None</option>
                                <option value="Casual" <?php echo $emp['leaveType'] == 'Casual' ? 'selected' : ''; ?>>Casual</option>
                                <option value="Medical" <?php echo $emp['leaveType'] == 'Medical' ? 'selected' : ''; ?>>Medical</option>
                                <option value="Emergency" <?php echo $emp['leaveType'] == 'Emergency' ? 'selected' : ''; ?>>Emergency</option>
                            </select>
                        </td>
                        <td>
                            <?php if ($lopDays > 0): ?>
                                <span class="lop-badge badge-danger">
                                    <i data-lucide="alert-circle" style="width:12px"></i> -<?php echo $lopDays; ?> Days Pay
                                </span>
                            <?php else: ?>
                                <span class="lop-badge badge-success">Full Pay</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-weight:800; color:#166534; font-size:16px">
                            ₹<?php echo number_format($netPayable); ?>
                        </td>
                        <td>
                            <button class="save-icon-btn"><i data-lucide="save"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="bottom-grid">
        <div class="info-card">
            <h4 class="section-title" style="margin-bottom:20px;">
                <i data-lucide="history" style="color:#FF9B44"></i> Audit Summary
            </h4>
            <div>
                <div class="audit-row">
                    <span class="audit-label">Hard Cash Reserve</span> 
                    <span class="audit-val">₹25,000</span>
                </div>
                <div class="audit-row">
                    <span class="audit-label">Bank Transfers</span> 
                    <span class="audit-val">₹12,20,000</span>
                </div>
                <div class="audit-row">
                    <span class="audit-label">GST Liability</span> 
                    <span class="live-badge">Cleared</span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h4 class="section-title" style="margin-bottom:10px;">GST & TDS Reporting</h4>
            <p style="font-size:14px; color:#666; line-height:1.6; margin-bottom:20px;">
                Monthly GST liability and Tax Deducted at Source reports generated for the current cycle. Ensure compliance before the 20th.
            </p>
            <button class="report-btn">Generate Monthly Tax Report</button>
        </div>
    </div>

</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();
</script>

</body>
</html>