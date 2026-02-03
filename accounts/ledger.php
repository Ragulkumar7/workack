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

    if ($amount > 0 && !empty($party) && $bank != "Select Bank") {
        $debit = ($type == 'Expense') ? $amount : 0;
        $credit = ($type == 'Income') ? $amount : 0;
        $sql = "INSERT INTO ledger_entries (entry_date, type, name, description, debit_out, credit_in, bank_name) 
                VALUES ('$entry_date', '$type', '$party', '$desc', $debit, $credit, '$bank')";
        mysqli_query($conn, $sql);
        header("Location: ledger.php?success=1");
        exit();
    }
}

// --- 2. DATA FETCHING ---
$stats = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(debit_out) as d, SUM(credit_in) as c, COUNT(id) as t FROM ledger_entries"));
$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date = $_GET['to'] ?? date('Y-m-d');
$filter_bank = $_GET['bank'] ?? 'All Banks';

$query = "SELECT * FROM ledger_entries WHERE entry_date BETWEEN '$from_date' AND '$to_date'";
if ($filter_bank !== 'All Banks') $query .= " AND bank_name = '" . mysqli_real_escape_string($conn, $filter_bank) . "'";
$result = mysqli_query($conn, $query . " ORDER BY entry_date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>General Ledger | Neoera</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 110px; --bg: #f9fafb; --border: #e5e7eb; --primary: #111827; --text-muted: #6b7280; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; color: #111827; }
        
        /* ADJUST CONTENT FOR NARROW SIDEBAR */
        .main-content { margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); padding: 40px; box-sizing: border-box; }
        
        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 24px; font-weight: 700; margin: 0; }
        .page-header p { font-size: 14px; color: var(--text-muted); margin: 4px 0 0; }

        /* SUMMARY CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        .stat-card { background: white; padding: 24px; border-radius: 12px; border: 1px solid var(--border); box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .stat-label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        .stat-val { font-size: 22px; font-weight: 800; margin-top: 8px; display: block; }

        /* CLEAN FORMS & TOOLBAR */
        .white-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 24px; margin-bottom: 24px; }
        .toolbar { display: flex; align-items: center; gap: 16px; margin-bottom: 32px; }
        .toolbar-group { display: flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 600; color: var(--text-muted); }
        
        input, select { padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; outline: none; transition: border 0.2s; }
        input:focus { border-color: #3b82f6; }
        
        .entry-row { display: grid; grid-template-columns: 100px 1.5fr 1.5fr 1fr 150px 140px; gap: 12px; align-items: center; padding: 12px; background: #f9fafb; border-radius: 10px; border: 1px solid var(--border); margin-top: 12px; }
        .entry-tag { font-size: 10px; font-weight: 800; color: var(--primary); letter-spacing: 0.1em; }
        .btn-black { background: var(--primary); color: white; border: none; padding: 10px; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; }

        /* TABLE STYLES */
        .ledger-table { width: 100%; border-collapse: collapse; }
        .ledger-table th { text-align: left; padding: 16px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); background: #f9fafb; }
        .ledger-table td { padding: 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
        .text-red { color: #dc2626; font-weight: 600; }
        .text-green { color: #059669; font-weight: 600; }
    </style>
</head>
<body>

    <?php include '../include/sidebar.php'; ?>

    <main class="main-content">
        <div class="page-header">
            <h1>General Ledger</h1>
            <p>Real-time oversight of all business transactions</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card"><span class="stat-label">Total Credit (In)</span><span class="stat-val text-green">₹<?= number_format($stats['c'], 2) ?></span></div>
            <div class="stat-card"><span class="stat-label">Total Debit (Out)</span><span class="stat-val text-red">₹<?= number_format($stats['d'], 2) ?></span></div>
            <div class="stat-card"><span class="stat-label">Net Balance</span><span class="stat-val">₹<?= number_format($stats['c'] - $stats['d'], 2) ?></span></div>
            <div class="stat-card"><span class="stat-label">Total Entries</span><span class="stat-val"><?= $stats['t'] ?></span></div>
        </div>

        <form class="toolbar" method="GET">
            <div class="toolbar-group">FROM <input type="date" name="from" value="<?= $from_date ?>"></div>
            <div class="toolbar-group">TO <input type="date" name="to" value="<?= $to_date ?>"></div>
            <div class="toolbar-group">BANK 
                <select name="bank">
                    <option>All Banks</option>
                    <option value="Canara" <?=($filter_bank=='Canara'?'selected':'')?>>Canara</option>
                    <option value="HDFC" <?=($filter_bank=='HDFC'?'selected':'')?>>HDFC</option>
                    <option value="ICICI" <?=($filter_bank=='ICICI'?'selected':'')?>>ICICI</option>
                    <option value="SBI" <?=($filter_bank=='SBI'?'selected':'')?>>SBI</option>
                </select>
            </div>
            <button type="submit" class="btn-black" style="padding: 10px 20px;">Apply Filters</button>
        </form>

        <div class="white-card">
            <div class="stat-label" style="margin-bottom: 16px;">Quick Manual Entry</div>
            <form method="POST">
                <input type="date" name="entry_date" value="<?= date('Y-m-d') ?>" style="margin-bottom: 12px; width: 180px;">
                <div class="entry-row">
                    <span class="entry-tag">EXPENSE</span>
                    <input type="hidden" name="type" value="Expense">
                    <input type="text" name="description" placeholder="Description (e.g. Rent)">
                    <input type="text" name="party_name" placeholder="Paid To (e.g. Landlord)">
                    <select name="bank_name">
                        <option>Select Bank</option><option>Canara</option><option>HDFC</option><option>ICICI</option><option>SBI</option>
                    </select>
                    <input type="number" name="amount" placeholder="Amount (₹)" step="0.01">
                    <button type="submit" name="action" class="btn-black">Save Expense</button>
                </div>
            </form>
            <form method="POST">
                <div class="entry-row">
                    <span class="entry-tag">INCOME</span>
                    <input type="hidden" name="type" value="Income">
                    <input type="text" name="description" placeholder="Description (e.g. Project)">
                    <input type="text" name="party_name" placeholder="Received From (Client)">
                    <select name="bank_name">
                        <option>Select Bank</option><option>Canara</option><option>HDFC</option><option>ICICI</option><option>SBI</option>
                    </select>
                    <input type="number" name="amount" placeholder="Amount (₹)" step="0.01">
                    <button type="submit" name="action" class="btn-black">Save Income</button>
                </div>
            </form>
        </div>

        <div class="white-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 20px 24px; font-weight: 700; border-bottom: 1px solid var(--border);">Transaction History</div>
            <table class="ledger-table">
                <thead>
                    <tr>
                        <th>Date</th><th>Type</th><th>Party Name</th><th>Bank</th><th>Description</th><th>Debit (Out)</th><th>Credit (In)</th><th>Balance</th><th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?= date('d M Y', strtotime($row['entry_date'])) ?></td>
                        <td><span style="font-weight:700; font-size:10px;"><?= strtoupper($row['type']) ?></span></td>
                        <td><strong><?= $row['name'] ?></strong></td>
                        <td><?= $row['bank_name'] ?: 'N/A' ?></td>
                        <td><?= $row['description'] ?></td>
                        <td class="text-red"><?= $row['debit_out'] > 0 ? "₹".number_format($row['debit_out'], 2) : "-" ?></td>
                        <td class="text-green"><?= $row['credit_in'] > 0 ? "₹".number_format($row['credit_in'], 2) : "-" ?></td>
                        <td style="font-weight:700;">₹<?= number_format($row['balance'] ?? 0, 2) ?></td>
                        <td><i class="ph ph-lock" style="color:var(--text-muted);"></i></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

</body>
</html>