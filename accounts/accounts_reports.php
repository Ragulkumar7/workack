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

// --- 2. SIDEBAR PROTECTION BLOCK ---
if (isset($sections) && is_array($sections)) {
    foreach ($sections as &$section) {
        if (isset($section['items']) && is_array($section['items'])) {
            $section['items'] = array_filter($section['items'], function($item) { return is_array($item); });
        }
    }
}
include_once('../include/sidebar.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Audit | Neoera</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 110px; --bg: #f8fafc; --primary: #1e1b4b; --border: #e2e8f0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; color: #1e293b; }
        
        .main-content { margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); padding: 40px; box-sizing: border-box; display: flex; flex-direction: column; gap: 30px; }

        /* PREMIUM STATS TILES */
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        .stat-card { background: white; padding: 30px; border-radius: 12px; border: 1.5px solid var(--border); border-left: 5px solid #cbd5e1; }
        .stat-card.green { border-left-color: #10b981; }
        .stat-card.red { border-left-color: #ef4444; }
        .stat-card.blue { border-left-color: #3b82f6; }
        .stat-label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 10px; display: block; }
        .stat-val { font-size: 32px; font-weight: 900; }

        /* DARK TOOLBAR */
        .toolbar { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 20px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .filter-group { display: flex; align-items: center; gap: 15px; }
        input, select { padding: 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; font-weight: 600; outline: none; }
        .btn-audit { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }

        /* AUDIT STATEMENT CANVAS */
        .report-canvas { background: white; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); overflow: hidden; }
        .canvas-header { padding: 50px 60px 30px; display: flex; justify-content: space-between; border-bottom: 2.5px solid var(--primary); margin: 0 60px 40px; }
        
        .audit-table { width: 100%; border-collapse: collapse; }
        .audit-table th { text-align: left; padding: 22px 60px; font-size: 11px; text-transform: uppercase; color: #64748b; background: #fbfcfd; border-bottom: 2px solid var(--border); font-weight: 800; }
        .audit-table td { padding: 22px 60px; border-bottom: 1px solid #f1f5f9; font-size: 14px; font-weight: 600; vertical-align: middle; }

        .text-in { color: #10b981; } .text-out { color: #ef4444; }
        .type-badge { font-size: 10px; font-weight: 900; background: #f1f5f9; padding: 5px 12px; border-radius: 5px; color: #475569; }

        @media print {
            .no-print, .sidebar { display: none !important; }
            .main-content { margin-left: 0; width: 100%; padding: 0; }
            .report-canvas { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>

    <main class="main-content">
        
        <div class="toolbar no-print">
            <h1 style="font-weight:900; font-size:26px; margin:0; letter-spacing:-1px;">Audit Reports</h1>
            <div style="display:flex; gap:10px;">
                <a href="?range=1m" style="text-decoration:none; color:#64748b; font-size:12px; font-weight:700; padding:10px;">30 Days</a>
                <a href="?range=3m" style="text-decoration:none; color:#64748b; font-size:12px; font-weight:700; padding:10px;">Quarterly</a>
                <button onclick="window.print()" style="background:#3b82f6; color:white; border:none; padding:12px 25px; border-radius:8px; font-weight:700; cursor:pointer;">Export PDF</button>
            </div>
        </div>

        <div class="toolbar no-print">
            <form method="GET" class="filter-group">
                <input type="date" name="from" value="<?= $from_date ?>">
                <span style="font-weight:700; color:#cbd5e1;">to</span>
                <input type="date" name="to" value="<?= $to_date ?>">
                
                <select name="bank">
                    <option>All Banks</option>
                    <?php $banks = ["Canara", "HDFC", "ICICI", "SBI"]; 
                    foreach($banks as $b) echo "<option value='$b' ".($filter_bank==$b?'selected':'').">$b</option>"; ?>
                </select>

                <select name="type">
                    <option value="all">All Channels</option>
                    <option value="Invoice">Sales Inflow</option>
                    <option value="Expenses">Operating Costs</option>
                    <option value="Purchase">Stock Expenditure</option>
                </select>
                <button type="submit" class="btn-audit"><i class="ph ph-magnifying-glass"></i> Filter Audit</button>
            </form>
        </div>

        <div class="stats-grid no-print">
            <div class="stat-card green"><span class="stat-label">Total Credit</span><span class="stat-val text-in">₹ <?= number_format($total_in, 2) ?></span></div>
            <div class="stat-card red"><span class="stat-label">Total Debit</span><span class="stat-val text-out">₹ <?= number_format($total_out, 2) ?></span></div>
            <div class="stat-card blue"><span class="stat-label">Net Position</span><span class="stat-val">₹ <?= number_format($net_position, 2) ?></span></div>
        </div>

        <div class="report-canvas">
            <div class="canvas-header">
                <div>
                    <h2 style="margin:0; font-size:36px; font-weight:900; letter-spacing:-1.5px;">NEOERA INFOTECH</h2>
                    <p style="margin:5px 0; color:#64748b; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.1em;">Consolidated Financial Audit</p>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:900; font-size: 20px; color: var(--primary);"><?= date('M Y', strtotime($from_date)) ?> - <?= date('M Y', strtotime($to_date)) ?></div>
                    <div style="font-size:11px; color:#94a3b8; margin-top:8px; font-family: monospace;">AUTH-SECURE: NE-<?= strtoupper(substr(md5(time()), 0, 8)) ?></div>
                </div>
            </div>

            <table class="audit-table">
                <thead>
                    <tr>
                        <th width="15%">Date</th>
                        <th width="40%">Account / Entity Description</th>
                        <th width="15%">Type</th>
                        <th style="text-align:right;">Debit (Out)</th>
                        <th style="text-align:right;">Credit (In)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d M, Y', strtotime($row['entry_date'])) ?></td>
                        <td>
                            <div style="font-weight:800; color: #0f172a;"><?= htmlspecialchars($row['name']) ?></div>
                            <div style="font-size:12px; color:#94a3b8; font-weight:500; margin-top:2px;"><?= htmlspecialchars($row['description']) ?></div>
                        </td>
                        <td><span class="type-badge"><?= strtoupper($row['type']) ?></span></td>
                        <td style="text-align:right;" class="text-out"><?= $row['debit_out'] > 0 ? "₹".number_format($row['debit_out'], 2) : "—" ?></td>
                        <td style="text-align:right;" class="text-in"><?= $row['credit_in'] > 0 ? "₹".number_format($row['credit_in'], 2) : "—" ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($result->num_rows == 0): ?>
                    <tr><td colspan="5" style="text-align:center; padding:150px; color:#94a3b8; font-weight:600;"><i class="ph ph-folder-open" style="font-size:40px; display:block; margin-bottom:10px;"></i>No audited records for this period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="padding: 120px 60px 80px; display:flex; justify-content:space-between; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.05em;">
                <div style="text-align:center; border-top:2.5px solid #000; width:300px; padding-top:15px;">Chief Financial Officer</div>
                <div style="text-align:center; border-top:2.5px solid #000; width:300px; padding-top:15px;">Executive Approval</div>
            </div>
        </div>
    </main>

</body>
</html>