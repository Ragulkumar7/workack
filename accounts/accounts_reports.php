<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. FILTER & QUICK RANGE LOGIC ---
$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');
$report_type = $_GET['type'] ?? 'all';

if (isset($_GET['range'])) {
    $to_date = date('Y-m-d');
    if ($_GET['range'] == '1m') $from_date = date('Y-m-d', strtotime('-1 month'));
    if ($_GET['range'] == '3m') $from_date = date('Y-m-d', strtotime('-3 months'));
    if ($_GET['range'] == '6m') $from_date = date('Y-m-d', strtotime('-6 months'));
    if ($_GET['range'] == '1y') $from_date = date('Y-m-d', strtotime('-1 year'));
}

// --- 2. MASTER DATA AGGREGATION ---
// This query automatically pulls from Invoice and Purchase Order syncs
$sql = "SELECT * FROM ledger_entries WHERE entry_date BETWEEN '$from_date' AND '$to_date'";
if($report_type !== 'all') {
    $sql .= " AND type = '" . $conn->real_escape_string($report_type) . "'";
}
$result = $conn->query($sql . " ORDER BY entry_date ASC");

// Summary Totals Calculation
$totals_sql = "SELECT SUM(debit_out) as total_debit, SUM(credit_in) as total_credit 
               FROM ledger_entries WHERE entry_date BETWEEN '$from_date' AND '$to_date'";
$totals_res = $conn->query($totals_sql)->fetch_assoc();
$total_in = $totals_res['total_credit'] ?? 0;
$total_out = $totals_res['total_debit'] ?? 0;
$net_profit = $total_in - $total_out;

// --- 3. HEADER ERROR PROTECTION ---
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
    <title>Financial Reports | Neoera</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 110px; --bg: #f9fafb; --border: #e5e7eb; --primary: #111827; --text-muted: #6b7280; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; color: #111827; display: flex; }
        
        /* SIDEBAR DIMENSION ADJUSTMENT */
        .main-content { margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); padding: 40px; box-sizing: border-box; }
        
        /* WHITE THEME CARD DESIGN */
        .white-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 32px; margin-bottom: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        
        .toolbar { display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; }
        .range-group { display: flex; gap: 10px; margin-bottom: 15px; }
        .btn-range { background: #fff; border: 1px solid var(--border); padding: 8px 16px; border-radius: 8px; font-size: 11px; font-weight: 600; text-decoration: none; color: var(--primary); transition: 0.2s; }
        .btn-range:hover { background: #f3f4f6; }

        input, select { padding: 10px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; outline: none; }
        .btn-print { background: var(--primary); color: white; border: none; padding: 10px 24px; border-radius: 8px; cursor: pointer; font-weight: 700; display: flex; align-items: center; gap: 8px; }

        /* REPORT SUMMARY GRID */
        .sum-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; margin-bottom: 32px; }
        .sum-card { padding: 24px; border-radius: 12px; border: 1px solid var(--border); text-align: center; }
        .sum-card .label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.05em; }
        .sum-card .val { font-size: 24px; font-weight: 800; }

        /* PROFESSIONAL TABLE STYLES */
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table th { text-align: left; padding: 16px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border); background: #f9fafb; }
        .report-table td { padding: 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        
        .t-income { color: #059669; font-weight: 600; }
        .t-expense { color: #dc2626; font-weight: 600; }

        /* PRINT OPTIMIZATION */
        @media print {
            body { background: white; }
            .main-content { margin-left: 0; width: 100%; padding: 0; }
            .no-print { display: none; }
            .sidebar { display: none; }
            .white-card { border: none; box-shadow: none; }
        }
    </style>
</head>
<body>

    <main class="main-content">
        
        <div class="no-print">
            <div class="range-group">
                <a href="?range=1m" class="btn-range">Last 30 Days</a>
                <a href="?range=3m" class="btn-range">Quarterly (3M)</a>
                <a href="?range=6m" class="btn-range">Semi-Annual (6M)</a>
                <a href="?range=1y" class="btn-range">Yearly</a>
            </div>
            
            <div class="toolbar">
                <form method="GET" style="display:flex; gap:16px; align-items:flex-end;">
                    <div><label style="font-size:10px; font-weight:700; color:var(--text-muted);">FROM</label><br><input type="date" name="from" value="<?= $from_date ?>"></div>
                    <div><label style="font-size:10px; font-weight:700; color:var(--text-muted);">TO</label><br><input type="date" name="to" value="<?= $to_date ?>"></div>
                    <div><label style="font-size:10px; font-weight:700; color:var(--text-muted);">CATEGORY</label><br>
                        <select name="type">
                            <option value="all">All Records</option>
                            <option value="Invoice" <?= ($report_type == 'Invoice') ? 'selected' : '' ?>>Sales Invoices</option>
                            <option value="Purchase" <?= ($report_type == 'Purchase') ? 'selected' : '' ?>>Purchase Orders</option>
                            <option value="Expense" <?= ($report_type == 'Expense') ? 'selected' : '' ?>>Manual Expenses</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-print" style="background:#3b82f6;">Filter Report</button>
                </form>
                <button class="btn-print" onclick="window.print()"><i class="ph ph-printer"></i> Print Report</button>
            </div>
        </div>

        <div class="white-card">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: 32px;">
                <div>
                    <h2 style="margin:0; font-size:24px; font-weight:800;">NEOERA INFOTECH</h2>
                    <p style="margin:4px 0; font-size:12px; color:var(--text-muted);">Consolidated Financial Transaction Statement</p>
                </div>
                <div style="text-align:right;">
                    <div style="font-weight:700;"><?= date('d M Y', strtotime($from_date)) ?> - <?= date('d M Y', strtotime($to_date)) ?></div>
                    <div style="font-size:11px; color:var(--text-muted);">Report ID: <?= time() ?></div>
                </div>
            </div>

            <div class="sum-grid">
                <div class="sum-card"><div class="label">Total Inflow</div><div class="val t-income">₹ <?= number_format($total_in, 2) ?></div></div>
                <div class="sum-card"><div class="label">Total Outflow</div><div class="val t-expense">₹ <?= number_format($total_out, 2) ?></div></div>
                <div class="sum-card" style="background:#f8fafc;"><div class="label">Net Cashflow</div><div class="val" style="color:var(--primary);">₹ <?= number_format($net_profit, 2) ?></div></div>
            </div>

            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference No.</th>
                        <th>Party / Account</th>
                        <th>Bank Source</th>
                        <th>Type</th>
                        <th style="text-align:right;">Debit (Out)</th>
                        <th style="text-align:right;">Credit (In)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= date('d-M-Y', strtotime($row['entry_date'])) ?></td>
                        <td><strong><?= $row['reference_no'] ?: 'Direct' ?></strong></td>
                        <td><?= $row['name'] ?></td>
                        <td><?= $row['bank_name'] ?: '-' ?></td>
                        <td><span style="font-size:10px; font-weight:800; opacity:0.6;"><?= strtoupper($row['type']) ?></span></td>
                        <td style="text-align:right;" class="t-expense"><?= ($row['debit_out'] > 0) ? number_format($row['debit_out'], 2) : '-' ?></td>
                        <td style="text-align:right;" class="t-income"><?= ($row['credit_in'] > 0) ? number_format($row['credit_in'], 2) : '-' ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if($result->num_rows == 0): ?>
                    <tr><td colspan="7" style="text-align:center; padding:50px; color:var(--text-muted);">No financial records found for the selected criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div style="margin-top:100px; display:flex; justify-content:space-between; font-size:12px;">
                <div style="text-align:center; border-top:1px solid #000; width:220px; padding-top:10px;">Authorized Signatory</div>
                <div style="text-align:center; border-top:1px solid #000; width:220px; padding-top:10px;">Accounts Department</div>
            </div>
        </div>
    </main>

</body>
</html>