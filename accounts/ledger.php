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
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(debit_out) as d, SUM(credit_in) as c, COUNT(id) as t FROM ledger_entries"));

// Dynamic Transaction Query
$query = "SELECT le.*, 
          (SELECT SUM(credit_in) - SUM(debit_out) FROM ledger_entries le2 WHERE le2.id <= le.id) as running_balance 
          FROM ledger_entries le 
          WHERE le.entry_date BETWEEN '$from_date' AND '$to_date'";

if ($filter_bank !== 'All Banks') { $query .= " AND le.bank_name = '" . mysqli_real_escape_string($conn, $filter_bank) . "'"; }
if ($filter_type !== 'All') { $query .= " AND le.type = '" . mysqli_real_escape_string($conn, $filter_type) . "'"; }

$result = mysqli_query($conn, $query . " ORDER BY le.id DESC");

// --- 3. SIDEBAR PROTECTION BLOCK ---
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
    <title>General Ledger | Neoera</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 110px; --bg: #f8fafc; --border: #e2e8f0; --primary: #1e1b4b; --text-muted: #64748b; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; color: #1e293b; }
        .main-content { margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); padding: 30px; box-sizing: border-box; }
        
        /* SUMMARY GRID */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 25px; border-radius: 12px; border: 1.5px solid var(--border); position: relative; }
        .stat-card.green { border-left: 4px solid #10b981; }
        .stat-card.red { border-left: 4px solid #ef4444; }
        .stat-card.blue { border-left: 4px solid #3b82f6; }
        .stat-label { font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-val { font-size: 28px; font-weight: 900; margin-top: 8px; display: block; }
        .stat-icon { position: absolute; right: 20px; bottom: 20px; font-size: 24px; opacity: 0.2; }

        /* TOOLBAR */
        .toolbar-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .filter-group { display: flex; align-items: center; gap: 15px; }
        input, select { padding: 10px 14px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; font-weight: 600; outline: none; }
        .btn-filter { background: #1e1b4b; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; }
        
        .type-pills { display: flex; gap: 8px; background: #f1f5f9; padding: 4px; border-radius: 10px; }
        .pill { padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 700; cursor: pointer; text-decoration: none; color: #64748b; }
        .pill.active { background: #1e1b4b; color: white; }

        /* ENTRY BOX */
        .entry-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 30px; margin-bottom: 25px; }
        .entry-row { display: grid; grid-template-columns: 100px 1.5fr 1.5fr 1fr 1fr 140px; gap: 12px; align-items: center; padding: 15px; background: #f8fafc; border-radius: 10px; border: 1px solid var(--border); margin-top: 15px; }
        .row-tag { font-size: 10px; font-weight: 900; color: #1e1b4b; }
        .btn-save { background: #1e1b4b; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; }

        /* TABLE */
        .table-card { background: white; border-radius: 12px; border: 1px solid var(--border); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .table-header { padding: 20px 25px; font-weight: 800; border-bottom: 1px solid var(--border); font-size: 16px; }
        .ledger-table { width: 100%; border-collapse: collapse; }
        .ledger-table th { text-align: left; padding: 18px 25px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; border-bottom: 2px solid var(--border); background: #fbfcfd; }
        .ledger-table td { padding: 18px 25px; border-bottom: 1px solid #f1f5f9; font-size: 14px; font-weight: 600; }
        .val-red { color: #ef4444; }
        .val-green { color: #10b981; }
    </style>
</head>
<body>
    <main class="main-content">
        <h1 style="font-weight:900; letter-spacing:-1px; margin:0 0 5px;">General Ledger</h1>
        <p style="color:var(--text-muted); font-size:14px; margin-bottom:30px;">Track all financial transactions and account balances</p>

        <div class="stats-grid">
            <div class="stat-card green"><span class="stat-label">Total Credit (In)</span><span class="stat-val" style="color:#10b981;">₹<?= number_format($stats['c'], 2) ?></span><i class="ph ph-arrow-circle-down-right stat-icon" style="color:#10b981;"></i></div>
            <div class="stat-card red"><span class="stat-label">Total Debit (Out)</span><span class="stat-val" style="color:#ef4444;">₹<?= number_format($stats['d'], 2) ?></span><i class="ph ph-arrow-circle-up-right stat-icon" style="color:#ef4444;"></i></div>
            <div class="stat-card blue"><span class="stat-label">Net Balance</span><span class="stat-val">₹<?= number_format($stats['c'] - $stats['d'], 2) ?></span><i class="ph ph-bank stat-icon" style="color:#3b82f6;"></i></div>
            <div class="stat-card"><span class="stat-label">Total Entries</span><span class="stat-val"><?= $stats['t'] ?></span><i class="ph ph-list-bullets stat-icon"></i></div>
        </div>

        <div class="toolbar-card">
            <form method="GET" class="filter-group">
                <label class="stat-label">From:</label> <input type="date" name="from" value="<?= $from_date ?>">
                <label class="stat-label">To:</label> <input type="date" name="to" value="<?= $to_date ?>">
                <select name="bank">
                    <option>All Banks</option>
                    <?php $banks = ["Canara", "HDFC", "ICICI", "SBI"]; foreach($banks as $b) echo "<option value='$b' ".($filter_bank==$b?'selected':'').">$b</option>"; ?>
                </select>
                <button type="submit" class="btn-filter"><i class="ph ph-funnel"></i> Apply Filter</button>
            </form>
            <div class="type-pills">
                <a href="?filter_type=All" class="pill <?= $filter_type=='All'?'active':'' ?>">All</a>
                <a href="?filter_type=Expense" class="pill <?= $filter_type=='Expense'?'active':'' ?>">Expenses</a>
                <a href="?filter_type=Income" class="pill <?= $filter_type=='Income'?'active':'' ?>">Income</a>
            </div>
        </div>

        <div class="entry-card">
            <h3 class="stat-label" style="margin-bottom: 20px;">Add Manual Entry</h3>
            <form method="POST">
                <label class="stat-label">Entry Date</label><br>
                <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" style="margin-top:8px; width:220px;">
                <div class="entry-row">
                    <span class="row-tag">EXPENSE</span>
                    <input type="hidden" name="type" value="Expense">
                    <input type="text" name="description" placeholder="Description (e.g., Office Rent)" required>
                    <input type="text" name="party_name" placeholder="Paid To (e.g., Landlord)" required>
                    <select name="bank_name" required><option value="">Select Bank</option><option>Canara</option><option>HDFC</option><option>ICICI</option><option>SBI</option></select>
                    <input type="number" name="amount" placeholder="Amount (₹)" step="0.01" required>
                    <button type="submit" name="action" class="btn-save">Save Expense</button>
                </div>
            </form>
            <form method="POST" style="margin-top:10px;">
                <div class="entry-row">
                    <span class="row-tag">INCOME</span>
                    <input type="hidden" name="type" value="Income">
                    <input type="text" name="description" placeholder="Description (e.g., Project Final Payment)" required>
                    <input type="text" name="party_name" placeholder="Received From (Client Name)" required>
                    <select name="bank_name" required><option value="">Select Bank</option><option>Canara</option><option>HDFC</option><option>ICICI</option><option>SBI</option></select>
                    <input type="number" name="amount" placeholder="Amount (₹)" step="0.01" required>
                    <button type="submit" name="action" class="btn-save">Save Income</button>
                </div>
            </form>
        </div>

        <div class="table-card">
            <div class="table-header">Transaction History & Ledger Details</div>
            <table class="ledger-table">
                <thead>
                    <tr><th>Date</th><th>Type</th><th>Party Name</th><th>Bank</th><th>Description</th><th style="text-align:right;">Debit (Out)</th><th style="text-align:right;">Credit (In)</th><th style="text-align:right;">Balance</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($result) > 0): while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['entry_date'])) ?></td>
                        <td><span style="font-size:10px; font-weight:800; opacity:0.6;"><?= strtoupper($row['type']) ?></span></td>
                        <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>
                        <td><?= $row['bank_name'] ?: 'N/A' ?></td>
                        <td><small style="color:var(--text-muted);"><?= htmlspecialchars($row['description']) ?></small></td>
                        <td style="text-align:right;" class="val-red"><?= $row['debit_out'] > 0 ? "₹".number_format($row['debit_out'], 2) : "—" ?></td>
                        <td style="text-align:right;" class="val-green"><?= $row['credit_in'] > 0 ? "₹".number_format($row['credit_in'], 2) : "—" ?></td>
                        <td style="text-align:right; font-weight:800;">₹<?= number_format($row['running_balance'], 2) ?></td>
                        <td><i class="ph ph-lock-key" style="color:#cbd5e1; font-size:18px;"></i></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="9" style="text-align:center; padding:100px; color:var(--text-muted); font-weight:600;"><i class="ph ph-folder-open" style="font-size:40px; display:block; margin-bottom:15px;"></i>No verified transactions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>