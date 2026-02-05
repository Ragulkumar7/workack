<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. MANUAL ENTRY SAVE LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $type = $_POST['type']; 
    $entry_date = $_POST['entry_date'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);
    $party = mysqli_real_escape_string($conn, $_POST['party_name']);
    $bank = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $amount = floatval($_POST['amount']);

    if ($amount > 0 && !empty($party) && $bank != "") {
        $debit = ($type == 'Expense') ? $amount : 0;
        $credit = ($type == 'Income') ? $amount : 0;
        
        $sql = "INSERT INTO ledger_entries (entry_date, type, name, description, debit_out, credit_in, bank_name) 
                VALUES ('$entry_date', '$type', '$party', '$desc', $debit, $credit, '$bank')";
        
        if(mysqli_query($conn, $sql)) {
            header("Location: ledger.php?success=1");
            exit();
        }
    }
}

// --- 2. DATA FETCHING & FILTERING ---
$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');
$filter_bank = $_GET['bank'] ?? 'All Banks';
$filter_type = $_GET['filter_type'] ?? 'All';

// Stats Query
$stats_query = mysqli_query($conn, "SELECT SUM(debit_out) as d, SUM(credit_in) as c, COUNT(id) as t FROM ledger_entries");
$stats = mysqli_fetch_assoc($stats_query);

// Dynamic Transaction Query with Running Balance
$query = "SELECT le.*, 
          (SELECT SUM(credit_in) - SUM(debit_out) FROM ledger_entries le2 WHERE le2.id <= le.id) as running_balance 
          FROM ledger_entries le 
          WHERE le.entry_date BETWEEN '$from_date' AND '$to_date'";

if ($filter_bank !== 'All Banks') { 
    $query .= " AND le.bank_name = '" . mysqli_real_escape_string($conn, $filter_bank) . "'"; 
}
if ($filter_type !== 'All') { 
    $query .= " AND le.type = '" . mysqli_real_escape_string($conn, $filter_type) . "'"; 
}

$result = mysqli_query($conn, $query . " ORDER BY le.id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>General Ledger | Workack</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary-orange: #f97316; --bg-gray: #f8fafc; --border-color: #e2e8f0; --sidebar-width: 110px; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gray); margin: 0; display: flex; font-size: 13px; color: #1e293b; }
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; box-sizing: border-box; }
        
        /* THEME CARD STYLING */
        .theme-card { background: white; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
        .theme-card-header { background: #fff7ed; border-bottom: 2px solid var(--primary-orange); padding: 12px 24px; color: #7c2d12; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; justify-content: space-between; }
        
        /* STATS GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 25px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid var(--border-color); }
        .stat-label { font-size: 10px; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .stat-val { font-size: 22px; font-weight: 800; margin-top: 5px; display: block; }
        
        /* FORM CONTROLS */
        .form-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; padding: 20px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; color: #64748b; margin-bottom: 6px; font-size: 11px; text-transform: uppercase; }
        input, select { padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 13px; }
        input:focus { border-color: var(--primary-orange); }

        /* LEDGER TABLE */
        .ledger-table { width: 100%; border-collapse: collapse; }
        .ledger-table th { background: #f8fafc; text-align: left; padding: 14px; font-size: 11px; color: #64748b; border-bottom: 1px solid var(--border-color); text-transform: uppercase; }
        .ledger-table td { padding: 14px; border-bottom: 1px solid #f1f5f9; font-weight: 500; }
        
        .val-red { color: #ef4444; font-weight: 700; }
        .val-green { color: #10b981; font-weight: 700; }
        .badge-type { padding: 3px 8px; border-radius: 12px; font-size: 10px; font-weight: 700; background: #f1f5f9; }

        .btn-submit { background: #0f172a; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: #1e293b; }
        .pill-group { display: flex; gap: 10px; padding: 20px; background: #f8fafc; border-bottom: 1px solid #e2e8f0; }
        .pill { padding: 6px 15px; border-radius: 20px; text-decoration: none; color: #64748b; font-weight: 600; font-size: 12px; border: 1px solid #e2e8f0; background: white; }
        .pill.active { background: var(--primary-orange); color: white; border-color: var(--primary-orange); }
    </style>
</head>
<body>

    <?php include_once('../include/sidebar.php'); // ?>

    <main class="main-content">
        <div style="margin-bottom: 25px;">
            <h2 style="color: #1e1b4b; margin: 0;">General Ledger</h2>
            <p style="color: #64748b; font-size: 13px;">Detailed financial tracking and balance management</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card" style="border-left: 4px solid #10b981;">
                <span class="stat-label">Total Credit (In)</span>
                <span class="stat-val" style="color:#10b981;">₹<?= number_format($stats['c'], 2) ?></span>
            </div>
            <div class="stat-card" style="border-left: 4px solid #ef4444;">
                <span class="stat-label">Total Debit (Out)</span>
                <span class="stat-val" style="color:#ef4444;">₹<?= number_format($stats['d'], 2) ?></span>
            </div>
            <div class="stat-card" style="border-left: 4px solid #2563eb;">
                <span class="stat-label">Net Account Balance</span>
                <span class="stat-val" style="color:#2563eb;">₹<?= number_format($stats['c'] - $stats['d'], 2) ?></span>
            </div>
            <div class="stat-card">
                <span class="stat-label">Total Transactions</span>
                <span class="stat-val"><?= $stats['t'] ?></span>
            </div>
        </div>

        <div class="theme-card">
            <div class="theme-card-header">1. Record New Transaction</div>
            <div style="padding:20px; border-bottom: 1px solid #f1f5f9;">
                <form method="POST" style="display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap;">
                    <div class="form-group"><label>Date</label><input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" required></div>
                    <div class="form-group"><label>Type</label><select name="type" required><option value="Income">Income (+)</option><option value="Expense">Expense (-)</option></select></div>
                    <div class="form-group" style="flex:1;"><label>Party Name</label><input type="text" name="party_name" placeholder="Client/Vendor Name" required></div>
                    <div class="form-group" style="flex:1;"><label>Description</label><input type="text" name="description" placeholder="Payment Details" required></div>
                    <div class="form-group"><label>Bank</label><select name="bank_name" required><option value="">Select Bank</option><option>Canara</option><option>HDFC</option><option>ICICI</option><option>SBI</option></select></div>
                    <div class="form-group"><label>Amount (₹)</label><input type="number" name="amount" step="0.01" placeholder="0.00" required></div>
                    <button type="submit" name="action" class="btn-submit">Add Entry</button>
                </form>
            </div>
        </div>

        <div class="theme-card">
            <div class="theme-card-header">
                2. Transaction History & Audit Trail
                <form method="GET" style="display: flex; gap: 10px; align-items: center;">
                    <input type="date" name="from" value="<?= $from_date ?>" style="padding:5px 10px; font-size:11px;">
                    <span style="font-size:11px; color:#7c2d12;">TO</span>
                    <input type="date" name="to" value="<?= $to_date ?>" style="padding:5px 10px; font-size:11px;">
                    <button type="submit" style="background:var(--primary-orange); color:white; border:none; padding:6px 12px; border-radius:6px; font-size:10px; font-weight:700; cursor:pointer;">FILTER</button>
                </form>
            </div>
            <div class="pill-group">
                <a href="?filter_type=All" class="pill <?= $filter_type=='All'?'active':'' ?>">All Entries</a>
                <a href="?filter_type=Income" class="pill <?= $filter_type=='Income'?'active':'' ?>">Incomes</a>
                <a href="?filter_type=Expense" class="pill <?= $filter_type=='Expense'?'active':'' ?>">Expenses</a>
            </div>
            <table class="ledger-table">
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Party / Contact</th><th>Description</th><th>Bank</th><th style="text-align:right;">Debit (Out)</th><th style="text-align:right;">Credit (In)</th><th style="text-align:right;">Balance</th></tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= date('d-M-Y', strtotime($row['entry_date'])) ?></td>
                        <td><span class="badge-type"><?= strtoupper($row['type']) ?></span></td>
                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                        <td><small style="color:#64748b;"><?= htmlspecialchars($row['description']) ?></small></td>
                        <td><?= $row['bank_name'] ?: 'N/A' ?></td>
                        <td style="text-align:right;" class="val-red"><?= $row['debit_out'] > 0 ? "₹".number_format($row['debit_out'], 2) : "—" ?></td>
                        <td style="text-align:right;" class="val-green"><?= $row['credit_in'] > 0 ? "₹".number_format($row['credit_in'], 2) : "—" ?></td>
                        <td style="text-align:right; font-weight:700; color:#1e1b4b;">₹<?= number_format($row['running_balance'], 2) ?></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="8" style="text-align:center; padding:60px; color:#94a3b8;">No transactions found for the selected criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>