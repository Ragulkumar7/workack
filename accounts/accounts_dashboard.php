<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. DATA AGGREGATION LOGIC ---

// Fetch Totals from Ledger (Master Source)
$stats_query = "SELECT 
    SUM(credit_in) as total_income, 
    SUM(debit_out) as total_expense,
    COUNT(id) as total_transactions
    FROM ledger_entries";
$stats = $conn->query($stats_query)->fetch_assoc();

$income = $stats['total_income'] ?? 0;
$expense = $stats['total_expense'] ?? 0;
$balance = $income - $expense;

// Fetch Monthly Trend (Last 6 Months)
$trend_query = "SELECT DATE_FORMAT(entry_date, '%b %Y') as month, SUM(credit_in) as inc, SUM(debit_out) as exp 
                FROM ledger_entries 
                GROUP BY month 
                ORDER BY entry_date DESC LIMIT 6";
$trend_res = $conn->query($trend_query);

// --- 2. HEADER ERROR PROTECTION ---
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
    <title>Accounts Dashboard | Neoera</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 110px; --bg: #f9fafb; --border: #e5e7eb; --primary: #111827; --text-muted: #6b7280; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; color: #111827; display: flex; }
        
        .main-content { margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); padding: 40px; box-sizing: border-box; }
        
        .page-header { margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center; }
        .page-header h1 { font-size: 24px; font-weight: 700; margin: 0; }
        
        /* FINANCIAL CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px; margin-bottom: 32px; }
        .stat-card { background: white; padding: 24px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); position: relative; }
        .stat-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-val { font-size: 24px; font-weight: 800; margin-top: 8px; display: block; }
        .stat-icon { position: absolute; right: 20px; top: 20px; font-size: 24px; opacity: 0.1; }

        /* QUICK ACTIONS */
        .action-grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-bottom: 32px; }
        .btn-action { background: white; border: 1px solid var(--border); padding: 20px; border-radius: 12px; text-decoration: none; color: var(--primary); display: flex; align-items: center; gap: 15px; transition: 0.2s; }
        .btn-action:hover { border-color: #3b82f6; background: #f0f9ff; }
        .icon-circle { width: 40px; height: 40px; border-radius: 50%; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 20px; }

        .table-card { background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; }
        .card-title { padding: 20px 24px; font-weight: 700; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; }
        
        .dash-table { width: 100%; border-collapse: collapse; }
        .dash-table th { text-align: left; padding: 16px; font-size: 11px; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border); background: #f9fafb; }
        .dash-table td { padding: 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        
        .t-income { color: #059669; font-weight: 700; }
        .t-expense { color: #dc2626; font-weight: 700; }
    </style>
</head>
<body>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Accounts Overview</h1>
                <p style="color: var(--text-muted); margin-top: 4px; font-size: 14px;">Summary of your organization's financial health</p>
            </div>
            <div style="font-size: 13px; font-weight: 600; color: var(--text-muted);">
                Last Updated: <?= date('d M, H:i') ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Total Inflow</span>
                <span class="stat-val t-income">₹ <?= number_format($income, 2) ?></span>
                <i class="ph ph-trend-up stat-icon"></i>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Outflow</span>
                <span class="stat-val t-expense">₹ <?= number_format($expense, 2) ?></span>
                <i class="ph ph-trend-down stat-icon"></i>
            </div>
            <div class="stat-card">
                <span class="stat-label">Net Balance</span>
                <span class="stat-val">₹ <?= number_format($balance, 2) ?></span>
                <i class="ph ph-bank stat-icon"></i>
            </div>
            <div class="stat-card">
                <span class="stat-label">Transactions</span>
                <span class="stat-val"><?= $stats['total_transactions'] ?></span>
                <i class="ph ph-arrows-left-right stat-icon"></i>
            </div>
        </div>

        <div class="action-grid">
            <a href="invoice_management.php" class="btn-action">
                <div class="icon-circle"><i class="ph ph-file-text"></i></div>
                <div><div style="font-weight:700;">Create Invoice</div><small style="color:var(--text-muted);">Generate client billing</small></div>
            </a>
            <a href="purchase_order.php" class="btn-action">
                <div class="icon-circle"><i class="ph ph-shopping-cart"></i></div>
                <div><div style="font-weight:700;">Add Purchase</div><small style="color:var(--text-muted);">Record vendor procurement</small></div>
            </a>
            <a href="ledger.php" class="btn-action">
                <div class="icon-circle"><i class="ph ph-notebook"></i></div>
                <div><div style="font-weight:700;">View Ledger</div><small style="color:var(--text-muted);">Manual entries & history</small></div>
            </a>
        </div>

        <div style="display:grid; grid-template-columns: 2fr 1fr; gap:24px;">
            <div class="table-card">
                <div class="card-title">Recent Activity <a href="accounts_reports.php" style="font-size:12px; color:#3b82f6; text-decoration:none;">Full Report</a></div>
                <table class="dash-table">
                    <thead>
                        <tr><th>Date</th><th>Description</th><th>Type</th><th>Amount</th></tr>
                    </thead>
                    <tbody>
                        <?php 
                        $recent = $conn->query("SELECT * FROM ledger_entries ORDER BY id DESC LIMIT 8");
                        while($r = $recent->fetch_assoc()): ?>
                        <tr>
                            <td><?= date('d M', strtotime($r['entry_date'])) ?></td>
                            <td><strong><?= $r['name'] ?></strong><br><small style="color:var(--text-muted);"><?= $r['description'] ?></small></td>
                            <td><span style="font-size:10px; font-weight:700; opacity:0.6;"><?= strtoupper($r['type']) ?></span></td>
                            <td class="<?= $r['credit_in'] > 0 ? 't-income' : 't-expense' ?>">
                                ₹ <?= number_format($r['credit_in'] + $r['debit_out'], 2) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="table-card">
                <div class="card-title">Monthly Trend</div>
                <div style="padding:20px;">
                    <?php while($t = $trend_res->fetch_assoc()): 
                        $max = max($income, 1); // Avoid division by zero
                        $perc = (($t['inc'] - $t['exp']) / $max) * 100;
                    ?>
                    <div style="margin-bottom:20px;">
                        <div style="display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px;">
                            <span style="font-weight:600;"><?= $t['month'] ?></span>
                            <span class="<?= $perc >= 0 ? 't-income' : 't-expense' ?>">₹ <?= number_format($t['inc'] - $t['exp'], 2) ?></span>
                        </div>
                        <div style="height:6px; background:#f3f4f6; border-radius:10px; overflow:hidden;">
                            <div style="width:<?= min(max($perc, 10), 100) ?>%; height:100%; background:<?= $perc >= 0 ? '#059669' : '#dc2626' ?>;"></div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </main>

</body>
</html>