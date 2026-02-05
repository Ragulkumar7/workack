<?php
// Production Database Connection
require_once('../include/db_connect.php'); // Host: 82.197.82.27

// --- 1. DATA AGGREGATION FOR DASHBOARD ---
// Calculate Inflow, Outflow, and Total Transactions
$stats_query = "SELECT 
    SUM(credit_in) as total_inflow, 
    SUM(debit_out) as total_outflow, 
    COUNT(id) as total_tx 
    FROM ledger_entries";
$stats_res = mysqli_query($conn, $stats_query);
$stats = mysqli_fetch_assoc($stats_res);

$inflow = $stats['total_inflow'] ?? 0;
$outflow = $stats['total_outflow'] ?? 0;
$net_balance = $inflow - $outflow;
$tx_count = $stats['total_tx'] ?? 0;

// Fetch Recent Activity (Last 5 Entries)
$recent_query = "SELECT entry_date, description, type, credit_in, debit_out 
                 FROM ledger_entries 
                 ORDER BY id DESC LIMIT 5";
$recent_res = mysqli_query($conn, $recent_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Accounts Overview | Workack</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary-orange: #f97316; --bg-gray: #f8fafc; --border-color: #e2e8f0; --sidebar-width: 110px; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gray); margin: 0; display: flex; font-size: 13px; color: #1e293b; }
        
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; box-sizing: border-box; }
        
        /* HEADER AREA */
        .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: flex-end; }
        .page-header h1 { font-size: 26px; font-weight: 800; margin: 0; color: #0f172a; letter-spacing: -0.02em; }
        .page-header p { color: #64748b; margin: 5px 0 0; font-size: 14px; }

        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 24px; border-radius: 12px; border: 1px solid var(--border-color); position: relative; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .stat-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: flex; justify-content: space-between; align-items: center; }
        .stat-val { font-size: 24px; font-weight: 800; margin-top: 10px; display: block; }
        
        /* SHORTCUT CARDS */
        .shortcut-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .shortcut-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); display: flex; align-items: center; gap: 15px; text-decoration: none; color: inherit; transition: transform 0.2s, border-color 0.2s; }
        .shortcut-card:hover { transform: translateY(-2px); border-color: var(--primary-orange); }
        .icon-wrap { width: 45px; height: 45px; background: #f8fafc; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #64748b; }

        /* ACTIVITY SECTION */
        .dashboard-row { display: grid; grid-template-columns: 1.8fr 1fr; gap: 24px; }
        .theme-card { background: white; border-radius: 12px; border: 1px solid var(--border-color); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .theme-card-header { background: #fff7ed; border-bottom: 2px solid var(--primary-orange); padding: 12px 24px; color: #7c2d12; font-weight: 700; font-size: 11px; text-transform: uppercase; display: flex; justify-content: space-between; }
        
        .activity-table { width: 100%; border-collapse: collapse; }
        .activity-table td { padding: 16px 24px; border-bottom: 1px solid #f1f5f9; }
        .badge-income { background: #d1fae5; color: #065f46; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; }
        .badge-expense { background: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 6px; font-size: 10px; font-weight: 700; }

        .trend-box { padding: 24px; }
        .trend-item { margin-bottom: 15px; }
        .trend-bar-bg { height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden; margin-top: 8px; }
        .trend-bar-fill { height: 100%; background: var(--primary-orange); border-radius: 4px; }
    </style>
</head>
<body>

    <?php include_once('../include/sidebar.php'); ?>

    <main class="main-content">
        <div class="page-header">
            <div>
                <h1>Accounts Overview</h1>
                <p>Summary of your organization's financial health</p>
            </div>
            <div style="text-align: right; color: #64748b; font-size: 11px; font-weight: 600;">
                LOGGED IN AS: <span style="color: #0f172a;"><?= strtoupper($_SESSION['username'] ?? 'USER') ?></span><br>
                <?= date('d M, H:i') ?>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Total Inflow <i data-lucide="trending-up" style="width:14px; color:#10b981;"></i></div>
                <span class="stat-val" style="color: #10b981;">₹ <?= number_format($inflow, 2) ?></span>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Outflow <i data-lucide="trending-down" style="width:14px; color:#ef4444;"></i></div>
                <span class="stat-val" style="color: #ef4444;">₹ <?= number_format($outflow, 2) ?></span>
            </div>
            <div class="stat-card">
                <div class="stat-label">Net Balance <i data-lucide="landmark" style="width:14px; color:#2563eb;"></i></div>
                <span class="stat-val" style="color: #2563eb;">₹ <?= number_format($net_balance, 2) ?></span>
            </div>
            <div class="stat-card">
                <div class="stat-label">Transactions <i data-lucide="refresh-cw" style="width:14px; color:#64748b;"></i></div>
                <span class="stat-val"><?= $tx_count ?></span>
            </div>
        </div>

        <div class="shortcut-grid">
            <a href="invoice_management.php" class="shortcut-card">
                <div class="icon-wrap"><i data-lucide="file-plus"></i></div>
                <div>
                    <div style="font-weight:700;">Create Invoice</div>
                    <div style="font-size:11px; color:#64748b;">Generate client billing</div>
                </div>
            </a>
            <a href="purchase_order.php" class="shortcut-card">
                <div class="icon-wrap"><i data-lucide="shopping-cart"></i></div>
                <div>
                    <div style="font-weight:700;">Add Purchase</div>
                    <div style="font-size:11px; color:#64748b;">Record vendor procurement</div>
                </div>
            </a>
            <a href="ledger.php" class="shortcut-card">
                <div class="icon-wrap"><i data-lucide="book-open"></i></div>
                <div>
                    <div style="font-weight:700;">View Ledger</div>
                    <div style="font-size:11px; color:#64748b;">Manual entries & history</div>
                </div>
            </a>
        </div>

        <div class="dashboard-row">
            <div class="theme-card">
                <div class="theme-card-header">
                    <span>Recent Activity</span>
                    <a href="ledger.php" style="color: #7c2d12; text-decoration: none;">View All</a>
                </div>
                <table class="activity-table">
                    <tbody>
                        <?php while($row = mysqli_fetch_assoc($recent_res)): ?>
                        <tr>
                            <td width="80px" style="color:#64748b; font-weight:600;"><?= date('d M', strtotime($row['entry_date'])) ?></td>
                            <td>
                                <div style="font-weight:700; color:#0f172a;"><?= htmlspecialchars($row['description']) ?></div>
                                <div style="font-size:11px; color:#94a3b8;"><?= $row['type'] ?> Transaction</div>
                            </td>
                            <td><span class="<?= $row['type'] == 'Income' ? 'badge-income' : 'badge-expense' ?>"><?= strtoupper($row['type']) ?></span></td>
                            <td style="text-align:right; font-weight:700; color: <?= $row['type'] == 'Income' ? '#10b981' : '#ef4444' ?>;">
                                ₹ <?= number_format(($row['credit_in'] > 0 ? $row['credit_in'] : $row['debit_out']), 2) ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <div class="theme-card">
                <div class="theme-card-header">Monthly Trend</div>
                <div class="trend-box">
                    <div class="trend-item">
                        <div style="display:flex; justify-content:space-between; font-weight:600;">
                            <span><?= date('F Y') ?></span>
                            <span style="color:#10b981;">+ ₹<?= number_format($net_balance, 0) ?></span>
                        </div>
                        <div class="trend-bar-bg">
                            <div class="trend-bar-fill" style="width: 75%;"></div>
                        </div>
                    </div>
                    <p style="font-size:11px; color:#64748b; line-height:1.5;">This trend represents the net financial movement for the current month based on ledger entries.</p>
                </div>
            </div>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>