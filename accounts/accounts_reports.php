<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. FILTER & QUICK RANGE LOGIC ---
$from_date = $_GET['from'] ?? date('Y-01-01');
$to_date = $_GET['to'] ?? date('Y-m-d');
$report_type = $_GET['type'] ?? 'all';

if (isset($_GET['range'])) {
    $to_date = date('Y-m-d');
    if ($_GET['range'] == '1m') $from_date = date('Y-m-d', strtotime('-1 month'));
    if ($_GET['range'] == '3m') $from_date = date('Y-m-d', strtotime('-3 months'));
    if ($_GET['range'] == '1y') $from_date = date('Y-m-d', strtotime('-1 year'));
}

// --- 2. MASTER DATA AGGREGATION ---
$sql = "SELECT * FROM ledger_entries WHERE entry_date BETWEEN '$from_date' AND '$to_date'";
if($report_type !== 'all') {
    $sql .= " AND type = '" . $conn->real_escape_string($report_type) . "'";
}
$result = $conn->query($sql . " ORDER BY entry_date ASC");

$totals_sql = "SELECT SUM(debit_out) as total_debit, SUM(credit_in) as total_credit 
               FROM ledger_entries WHERE entry_date BETWEEN '$from_date' AND '$to_date'";
$totals_res = $conn->query($totals_sql)->fetch_assoc();
$total_in = $totals_res['total_credit'] ?? 0;
$total_out = $totals_res['total_debit'] ?? 0;
$net_profit = $total_in - $total_out;

// --- 3. HEADER PROTECTION BLOCK ---
if (isset($sections) && is_array($sections)) {
    foreach ($sections as &$section) {
        if (isset($section['items']) && is_array($section['items'])) {
            $section['items'] = array_filter($section['items'], function($item) {
                return is_array($item);
            });
        }
    }
}

include_once('../include/sidebar.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Financial Intelligence Report | Neoera</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 110px; --bg: #f8fafc; --border: #e2e8f0; --primary: #0f172a; }
        
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; color: #1e293b; min-height: 100vh; overflow-x: hidden; }
        
        /* MAXIMUM WIDTH ADJUSTMENT */
        .main-content { 
            margin-left: var(--sidebar-w); 
            width: calc(100% - var(--sidebar-w)); 
            padding: 20px; /* Reduced padding to move content closer to edges */
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* TOOLBAR - FULL WIDTH */
        .toolbar-full { background: white; border-radius: 12px; padding: 20px 30px; border: 1px solid var(--border); box-shadow: 0 1px 3px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; width: 100%; box-sizing: border-box; }
        .btn-pill { background: #f1f5f9; border: 1px solid var(--border); padding: 8px 18px; border-radius: 20px; font-size: 11px; font-weight: 700; text-decoration: none; color: #64748b; transition: 0.2s; }
        .btn-pill:hover { background: var(--primary); color: white; border-color: var(--primary); }

        .form-input { padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; font-weight: 500; outline: none; }
        .btn-action { background: var(--primary); color: white; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 13px; }

        /* SUMMARY GRID - STRETCHED */
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; width: 100%; }
        .summary-tile { background: white; padding: 35px 30px; border-radius: 16px; border: 1px solid var(--border); display: flex; flex-direction: column; }
        .tile-label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; }
        .tile-val { font-size: 34px; font-weight: 900; color: #0f172a; }

        /* REPORT PAPER - MAXIMUM WIDTH */
        .report-canvas { background: white; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden; width: 100%; }
        .report-header { padding: 45px 50px 25px; display: flex; justify-content: space-between; border-bottom: 2px solid var(--primary); margin: 0 50px 30px; }

        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th { text-align: left; padding: 20px 50px; font-size: 11px; text-transform: uppercase; color: #64748b; background: #fbfcfd; border-bottom: 2px solid var(--border); }
        .report-table td { padding: 20px 50px; border-bottom: 1px solid #f8fafc; font-size: 14px; font-weight: 500; }

        .text-in { color: #10b981; font-weight: 700; }
        .text-out { color: #ef4444; font-weight: 700; }

        @media print {
            body { background: white; }
            .main-content { margin-left: 0; width: 100%; padding: 0; }
            .no-print, .sidebar { display: none !important; }
            .report-canvas { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>

    <main class="main-content">
        
        <div class="toolbar-full no-print">
            <div style="display:flex; gap:10px;">
                <a href="?range=1m" class="btn-pill">30 Days</a>
                <a href="?range=3m" class="btn-pill">Quarterly</a>
                <a href="?range=1y" class="btn-pill">Yearly</a>
            </div>
            <h1 style="font-size: 26px; font-weight: 900; letter-spacing: -0.5px; margin: 0;">Financial Intelligence</h1>
            <button class="btn-action" style="background:#3b82f6;" onclick="window.print()"><i class="ph ph-printer"></i> Export Statement</button>
        </div>

        <div class="toolbar-full no-print" style="padding: 15px 30px;">
            <form method="GET" style="display:flex; gap:20px; align-items:center;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <label style="font-size:11px; font-weight:800; color: #94a3b8;">AUDIT PERIOD</label>
                    <input type="date" name="from" value="<?= $from_date ?>" class="form-input">
                    <span style="color:#cbd5e1;">to</span>
                    <input type="date" name="to" value="<?= $to_date ?>" class="form-input">
                </div>
                <div style="display:flex; align-items:center; gap:12px;">
                    <label style="font-size:11px; font-weight:800; color: #94a3b8;">SOURCE</label>
                    <select name="type" class="form-input" style="min-width: 160px;">
                        <option value="all">All Channels</option>
                        <option value="Invoice" <?= ($report_type == 'Invoice') ? 'selected' : '' ?>>Sales Inflow</option>
                        <option value="Purchase" <?= ($report_type == 'Purchase') ? 'selected' : '' ?>>Expenditure</option>
                    </select>
                </div>
                <button type="submit" class="btn-action">Run Audit</button>
            </form>
        </div>

        <div class="summary-grid">
            <div class="summary-tile"><span class="tile-label">Gross Revenue</span><span class="tile-val text-in">₹ <?= number_format($total_in, 2) ?></span></div>
            <div class="summary-tile"><span class="tile-label">Total Expenditure</span><span class="tile-val text-out">₹ <?= number_format($total_out, 2) ?></span></div>
            <div class="summary-tile" style="background: var(--primary);"><span class="tile-label" style="color:rgba(255,255,255,0.5);">Net Capital Position</span><span class="tile-val" style="color:white;">₹ <?= number_format($net_profit, 2) ?></span></div>
        </div>

        <div class="report-canvas">
            <div class="report-header">
                <div>
                    <h2 style="margin:0; font-size:32px; font-weight:900; letter-spacing: -1px;">NEOERA INFOTECH</h2>
                    <p style="margin:5px 0; color:#64748b; font-weight:700; font-size:12px; text-transform:uppercase; letter-spacing:0.1em;">Consolidated Financial Statement</p>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:900; font-size: 18px; color: var(--primary);"><?= date('d M Y', strtotime($from_date)) ?> - <?= date('d M Y', strtotime($to_date)) ?></div>
                    <div style="font-size:11px; color:#94a3b8; margin-top:5px; font-family: monospace;">AUTH-ID: NE-<?= strtoupper(substr(md5(time()), 0, 8)) ?></div>
                </div>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th width="15%">Date</th>
                        <th width="18%">Reference ID</th>
                        <th width="32%">Account / Entity Name</th>
                        <th width="10%">Type</th>
                        <th style="text-align:right;">Debit (Out)</th>
                        <th style="text-align:right;">Credit (In)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d M, Y', strtotime($row['entry_date'])) ?></td>
                        <td><code style="background:#f1f5f9; padding:4px 10px; border-radius:4px; font-weight:700; color:#475569; font-size:12px;"><?= $row['reference_no'] ?: 'INTERNAL' ?></code></td>
                        <td style="font-weight:700; color: #0f172a;"><?= $row['name'] ?></td>
                        <td><span style="font-size:10px; font-weight:900; background:#f1f5f9; padding:5px 12px; border-radius:5px;"><?= strtoupper($row['type']) ?></span></td>
                        <td style="text-align:right;" class="text-out"><?= ($row['debit_out'] > 0) ? number_format($row['debit_out'], 2) : '—' ?></td>
                        <td style="text-align:right;" class="text-in"><?= ($row['credit_in'] > 0) ? number_format($row['credit_in'], 2) : '—' ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($result->num_rows == 0): ?>
                    <tr><td colspan="6" style="text-align:center; padding:120px; color:#94a3b8; font-weight:600;">No audited records found for this criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="padding: 100px 50px 60px; display:flex; justify-content:space-between; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.05em;">
                <div style="text-align:center; border-top:2.5px solid #000; width:300px; padding-top:15px;">Executive Approval</div>
                <div style="text-align:center; border-top:2.5px solid #000; width:300px; padding-top:15px;">Financial Controller</div>
            </div>
        </div>
    </main>

</body>
</html>