<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. FILTER LOGIC & DATA FETCHING ---
$from_date = $_GET['from'] ?? date('Y-01-01');
$to_date = $_GET['to'] ?? date('Y-m-d');
$report_type = $_GET['type'] ?? 'all';
$filter_bank = $_GET['bank'] ?? 'All Banks';

// Handle Quick Range Buttons
if (isset($_GET['range'])) {
    $to_date = date('Y-m-d');
    if ($_GET['range'] == '1m') $from_date = date('Y-m-d', strtotime('-1 month'));
    if ($_GET['range'] == '3m') $from_date = date('Y-m-d', strtotime('-3 months'));
    if ($_GET['range'] == '1y') $from_date = date('Y-m-d', strtotime('-1 year'));
}

// Master Query: Aggregating Ledger Entries
$sql = "SELECT entry_date, type, name, description, debit_out, credit_in, bank_name 
        FROM ledger_entries 
        WHERE entry_date BETWEEN '$from_date' AND '$to_date'";

if($report_type !== 'all') { $sql .= " AND type = '" . $conn->real_escape_string($report_type) . "'"; }
if($filter_bank !== 'All Banks') { $sql .= " AND bank_name = '" . $conn->real_escape_string($filter_bank) . "'"; }

$result = $conn->query($sql . " ORDER BY entry_date ASC");

// Totals Calculation
$t_sql = "SELECT SUM(debit_out) as d, SUM(credit_in) as c FROM ledger_entries WHERE entry_date BETWEEN '$from_date' AND '$to_date'";
$t_res = $conn->query($t_sql)->fetch_assoc();
$total_in = $t_res['c'] ?? 0;
$total_out = $t_res['d'] ?? 0;
$net_position = $total_in - $total_out;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Audit Reports | Workack</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary-orange: #f97316; --bg-gray: #f8fafc; --border-color: #e2e8f0; --sidebar-width: 110px; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gray); margin: 0; display: flex; font-size: 13px; color: #1e293b; }
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; box-sizing: border-box; }

        /* THEME CARD STYLING */
        .theme-card { background: white; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
        .theme-card-header { background: #fff7ed; border-bottom: 2px solid var(--primary-orange); padding: 12px 24px; color: #7c2d12; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; justify-content: space-between; }
        
        /* PREMIUM STATS TILES */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid var(--border-color); border-left: 5px solid #cbd5e1; }
        .stat-card.green { border-left-color: #10b981; }
        .stat-card.red { border-left-color: #ef4444; }
        .stat-card.blue { border-left-color: #2563eb; }
        .stat-label { font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: block; }
        .stat-val { font-size: 24px; font-weight: 800; }

        /* FORM CONTROLS */
        .toolbar { background: white; border-radius: 12px; border: 1px solid var(--border-color); padding: 20px 24px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .filter-group { display: flex; align-items: center; gap: 15px; }
        input, select { padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; font-size: 13px; font-weight: 600; outline: none; }
        .btn-action { background: #0f172a; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }

        /* AUDIT TABLE */
        .audit-table { width: 100%; border-collapse: collapse; }
        .audit-table th { text-align: left; padding: 16px 24px; font-size: 11px; text-transform: uppercase; color: #64748b; background: #f8fafc; border-bottom: 2px solid var(--border-color); font-weight: 800; }
        .audit-table td { padding: 18px 24px; border-bottom: 1px solid #f1f5f9; font-size: 14px; font-weight: 500; }

        .text-in { color: #10b981; font-weight: 700; } 
        .text-out { color: #ef4444; font-weight: 700; }
        .type-badge { font-size: 10px; font-weight: 800; background: #f1f5f9; padding: 4px 10px; border-radius: 6px; color: #475569; text-transform: uppercase; }

        @media print {
            .no-print, .sidebar { display: none !important; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .theme-card { border: none; box-shadow: none; }
            .theme-card-header { background: white; border-bottom: 1px solid black; color: black; }
        }
    </style>
</head>
<body>

    <?php include_once('../include/sidebar.php'); // Production Sidebar ?>

    <main class="main-content">
        <div class="toolbar no-print">
            <div>
                <h2 style="color: #1e1b4b; margin: 0;">Financial Audit Reports</h2>
                <p style="color: #64748b; font-size: 13px;">Consolidated ledger analysis and position tracking</p>
            </div>
            <div style="display:flex; gap:12px;">
                <button onclick="window.print()" class="btn-action" style="background:#2563eb;"><i data-lucide="file-text"></i> Export PDF</button>
            </div>
        </div>

        <div class="toolbar no-print">
            <form method="GET" class="filter-group">
                <div style="display:flex; align-items:center; gap:8px;">
                    <label style="font-size:10px; font-weight:800; color:#64748b;">PERIOD:</label>
                    <input type="date" name="from" value="<?= $from_date ?>">
                    <span style="font-weight:800; color:#cbd5e1;">-</span>
                    <input type="date" name="to" value="<?= $to_date ?>">
                </div>
                
                <select name="bank">
                    <option>All Banks</option>
                    <?php $banks = ["Canara", "HDFC", "ICICI", "SBI"]; 
                    foreach($banks as $b) echo "<option value='$b' ".($filter_bank==$b?'selected':'').">$b</option>"; ?>
                </select>

                <select name="type">
                    <option value="all">All Channels</option>
                    <option value="Invoice" <?= $report_type=='Invoice'?'selected':'' ?>>Sales Inflow</option>
                    <option value="Expenses" <?= $report_type=='Expenses'?'selected':'' ?>>Operating Costs</option>
                    <option value="Purchase" <?= $report_type=='Purchase'?'selected':'' ?>>Stock Expenditure</option>
                </select>
                <button type="submit" class="btn-action"><i data-lucide="search"></i> Generate Audit</button>
            </form>
        </div>

        <div class="stats-grid no-print">
            <div class="stat-card green"><span class="stat-label">Total Credit Inflow</span><span class="stat-val text-in">₹ <?= number_format($total_in, 2) ?></span></div>
            <div class="stat-card red"><span class="stat-label">Total Debit Outflow</span><span class="stat-val text-out">₹ <?= number_format($total_out, 2) ?></span></div>
            <div class="stat-card blue"><span class="stat-label">Net Financial Position</span><span class="stat-val" style="color:#2563eb;">₹ <?= number_format($net_position, 2) ?></span></div>
        </div>

        <div class="theme-card">
            <div class="theme-card-header">
                <div>
                    <span style="font-size:14px; color:#1e1b4b;">WORKACK SOLUTIONS - FINANCIAL STATEMENT</span>
                </div>
                <div style="text-align:right; font-size:10px; color:#7c2d12;">
                    PERIOD: <?= date('d M Y', strtotime($from_date)) ?> — <?= date('d M Y', strtotime($to_date)) ?>
                </div>
            </div>

            <table class="audit-table">
                <thead>
                    <tr>
                        <th width="15%">Date</th>
                        <th width="40%">Account Entity / Description</th>
                        <th width="15%">Category</th>
                        <th style="text-align:right;">Debit (Out)</th>
                        <th style="text-align:right;">Credit (In)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d M, Y', strtotime($row['entry_date'])) ?></td>
                        <td>
                            <div style="font-weight:800; color: #0f172a;"><?= htmlspecialchars($row['name']) ?></div>
                            <div style="font-size:12px; color:#94a3b8; font-weight:500; margin-top:3px;"><?= htmlspecialchars($row['description']) ?></div>
                        </td>
                        <td><span class="type-badge"><?= $row['type'] ?></span></td>
                        <td style="text-align:right;" class="text-out"><?= $row['debit_out'] > 0 ? "₹".number_format($row['debit_out'], 2) : "—" ?></td>
                        <td style="text-align:right;" class="text-in"><?= $row['credit_in'] > 0 ? "₹".number_format($row['credit_in'], 2) : "—" ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:120px; color:#94a3b8; font-weight:600;">No audited records found for the selected period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="padding: 100px 40px 60px; display:flex; justify-content:space-between; font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:0.05em; color:#64748b;">
                <div style="text-align:center; border-top:2px solid #e2e8f0; width:250px; padding-top:15px;">Verified by CFO</div>
                <div style="text-align:center; border-top:2px solid #e2e8f0; width:250px; padding-top:15px;">Executive Approval</div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>