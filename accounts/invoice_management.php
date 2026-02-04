<?php 
// 1. CLEAR PREVIOUS STATE & CONNECT
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once('../include/db_connect.php'); 
unset($sections); // Force clean menu data to prevent crash

// --- 2. ACTION HANDLERS ---
function generateInvoiceNo($conn) {
    $year = date('Y');
    $result = $conn->query("SELECT invoice_no FROM invoices WHERE invoice_no LIKE 'INV-$year-%' ORDER BY id DESC LIMIT 1");
    $lastNo = ($result && $result->num_rows > 0) ? intval(substr($result->fetch_assoc()['invoice_no'], -3)) : 0;
    return "INV-$year-" . str_pad($lastNo + 1, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_invoice'])) {
    $invoice_no = $conn->real_escape_string($_POST['invoice_no']);
    $client_id = intval($_POST['client_id']);
    
    // FIX: Format date strictly for MySQL
    $raw_date = $_POST['invoice_date'];
    $inv_date = !empty($raw_date) ? date('Y-m-d', strtotime($raw_date)) : date('Y-m-d');
    
    $bank = $conn->real_escape_string($_POST['bank_name']);
    $sub_total = floatval($_POST['sub_total'] ?? 0);
    $discount = floatval($_POST['discount'] ?? 0);
    $tax_amt = floatval($_POST['tax_amount'] ?? 0);
    $grand_total = floatval($_POST['grand_total'] ?? 0);
    $terms = $conn->real_escape_string($_POST['payment_terms'] ?? '');
    $notes = $conn->real_escape_string($_POST['notes'] ?? '');

    if($client_id > 0 && $grand_total > 0) {
        $sql = "INSERT INTO invoices (invoice_no, client_id, invoice_date, sub_total, discount, tax_amount, grand_total, payment_terms, notes, bank_name, payment_status) 
                VALUES ('$invoice_no', $client_id, '$inv_date', $sub_total, $discount, $tax_amt, $grand_total, '$terms', '$notes', '$bank', 'Unpaid')";

        if ($conn->query($sql)) {
            $invoice_id = $conn->insert_id;
            if (isset($_POST['item_desc'])) {
                foreach ($_POST['item_desc'] as $key => $desc) {
                    if(!empty(trim($desc))) {
                        $qty = intval($_POST['item_qty'][$key]);
                        $rate = floatval($_POST['item_rate'][$key]);
                        $gst_p = floatval($_POST['item_gst'][$key]);
                        $gst_a = ($qty * $rate * $gst_p) / 100;
                        $total = ($qty * $rate) + $gst_a;
                        $conn->query("INSERT INTO invoice_items (invoice_id, description, qty, rate, gst_rate, gst_amount, total) 
                                     VALUES ($invoice_id, '$desc', $qty, $rate, $gst_p, $gst_a, $total)");
                    }
                }
            }
            // Sync with Ledger using strict ENUM match
            $client_res = $conn->query("SELECT client_name FROM clients WHERE id = $client_id");
            $client_name = ($client_res->fetch_assoc())['client_name'] ?? 'Client';
            $conn->query("INSERT INTO ledger_entries (entry_date, type, name, description, credit_in, bank_name) 
                          VALUES ('$inv_date', 'Invoice', '$client_name', 'Invoice generated: $invoice_no', $grand_total, '$bank')");
            
            echo "<script>alert('Invoice Saved Successfully!'); window.location.href='invoice_management.php';</script>";
            exit();
        }
    }
}

// --- 3. SIDEBAR PROTECTION ---
include_once('../include/sidebar.php'); 
if (isset($sections) && is_array($sections)) {
    foreach ($sections as &$section) {
        if (isset($section['items']) && is_array($section['items'])) {
            $section['items'] = array_filter($section['items'], function($item) { return is_array($item); });
        }
    }
}
$next_inv_no = generateInvoiceNo($conn);
?>

<div class="main-content" style="margin-left: 110px; width: calc(100% - 110px); background: #f8fafc; min-height: 100vh; padding: 40px; box-sizing: border-box; display: flow-root;">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; color: #1e293b; margin: 0; display: flex; }
        .card { background: white; border-radius: 12px; border: 1px solid #e2e8f0; padding: 35px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); width: 100%; box-sizing: border-box; margin-bottom: 30px; clear: both; }
        .header-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 30px; }
        label { font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 8px; display: block; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 14px; outline: none; box-sizing: border-box; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { text-align: left; padding: 15px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; border-bottom: 2.5px solid #e2e8f0; background: #fbfcfd; }
        .items-table td { padding: 15px; border-bottom: 1px solid #f1f5f9; }
        .summary-box { float: right; width: 350px; background: #fbfcfd; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-top: 30px; }
        .btn-add { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 700; margin-top: 15px; }
        .btn-save { background: #1e1b4b; color: white; border: none; padding: 15px 45px; border-radius: 8px; font-weight: 800; cursor: pointer; }
    </style>

    <h1 style="font-size: 26px; font-weight: 900; letter-spacing: -1px; margin: 0 0 35px;">Invoice Management</h1>

    <form method="POST">
        <div class="card">
            <div class="header-grid">
                <div><label>Invoice Number</label><input type="text" name="invoice_no" value="<?= $next_inv_no ?>" readonly style="background:#f8fafc; font-weight:700;"></div>
                <div><label>Client Name</label><select name="client_id" required><option value="">Select Client</option><?php $clients = $conn->query("SELECT id, client_name FROM clients ORDER BY client_name"); while($c = $clients->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['client_name']}</option>"; ?></select></div>
                <div><label>Receiving Bank</label><input type="text" name="bank_name" placeholder="Bank Name" required></div>
                <div><label>Invoice Date</label><input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>"></div>
            </div>

            <table class="items-table" id="itemTable">
                <thead><tr><th>S.NO</th><th>PARTICULARS</th><th width="8%">QTY</th><th width="12%">RATE</th><th width="8%">GST %</th><th>GST AMT</th><th>TOTAL</th><th></th></tr></thead>
                <tbody>
                    <tr class="item-row">
                        <td>1</td><td><input type="text" name="item_desc[]" oninput="calculate()" required></td>
                        <td><input type="number" name="item_qty[]" class="qty" value="1" oninput="calculate()"></td>
                        <td><input type="number" name="item_rate[]" class="rate" value="0.00" step="0.01" oninput="calculate()"></td>
                        <td><input type="number" name="item_gst[]" class="gstp" value="18" oninput="calculate()"></td>
                        <td><input type="number" class="gstamt" readonly value="0.00"></td>
                        <td><input type="number" class="row-total" readonly value="0.00" style="font-weight:700;"></td>
                        <td><button type="button" onclick="this.closest('tr').remove(); calculate();" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i class="ph ph-x-circle" style="font-size:20px;"></i></button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" onclick="addRow()" class="btn-add">+ Add Item</button>

            <div class="summary-box">
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Subtotal:</span> <span id="sub_disp">₹0.00</span></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Discount:</span> <input type="number" name="discount" id="disc" value="0" oninput="calculate()" style="width:120px; text-align:right;"></div>
                <div style="display:flex; justify-content:space-between; margin-bottom:10px;"><span>Total GST:</span> <span id="gst_disp">₹0.00</span></div>
                <div style="display:flex; justify-content:space-between; border-top: 2px solid #0f172a; padding-top: 15px; margin-top: 15px; font-size: 20px; font-weight: 900;"><span>Grand Total:</span> <span id="grand_disp">₹0.00</span></div>
                <input type="hidden" name="sub_total" id="h_sub"><input type="hidden" name="tax_amount" id="h_gst"><input type="hidden" name="grand_total" id="h_grand">
                <button type="submit" name="save_invoice" class="btn-save" style="width:100%; margin-top:20px;">Save Invoice</button>
            </div>
            <div style="clear:both;"></div>
        </div>
    </form>
    
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:25px 35px; border-bottom:1px solid #e2e8f0; background:#fbfcfd;"><h3 style="margin:0; font-size: 16px; font-weight:800;">Recent Invoice Records</h3></div>
        <table class="items-table">
            <thead><tr><th>NO</th><th>CLIENT</th><th>DATE</th><th>AMOUNT</th><th>STATUS</th></tr></thead>
            <tbody>
                <?php $history = $conn->query("SELECT i.*, c.client_name FROM invoices i JOIN clients c ON i.client_id = c.id ORDER BY i.id DESC LIMIT 5");
                if($history) { while($h = $history->fetch_assoc()): ?>
                <tr><td><?= $h['invoice_no'] ?></td><td><?= $h['client_name'] ?></td><td><?= date('d M Y', strtotime($h['invoice_date'])) ?></td><td>₹<?= number_format($h['grand_total'], 2) ?></td><td><span style="color:#ef4444; font-weight:700;"><?= $h['payment_status'] ?></span></td></tr>
                <?php endwhile; } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function calculate() {
    let sub = 0, tax = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        let q = parseFloat(row.querySelector('.qty').value) || 0, r = parseFloat(row.querySelector('.rate').value) || 0, g = parseFloat(row.querySelector('.gstp').value) || 0;
        let amt = q * r, gamt = (amt * g) / 100;
        row.querySelector('.gstamt').value = gamt.toFixed(2); row.querySelector('.row-total').value = (amt + gamt).toFixed(2);
        sub += amt; tax += gamt;
    });
    let d = parseFloat(document.getElementById('disc').value) || 0;
    let grand = sub + tax - d;
    document.getElementById('sub_disp').innerText = '₹' + sub.toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('gst_disp').innerText = '₹' + tax.toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('grand_disp').innerText = '₹' + grand.toLocaleString('en-IN', {minimumFractionDigits:2});
    document.getElementById('h_sub').value = sub; document.getElementById('h_gst').value = tax; document.getElementById('h_grand').value = grand;
}
function addRow() {
    let tbody = document.querySelector('#itemTable tbody');
    let r = tbody.insertRow(); r.className = "item-row";
    r.innerHTML = `<td>${tbody.rows.length}</td><td><input type="text" name="item_desc[]" oninput="calculate()" required></td><td><input type="number" name="item_qty[]" class="qty" value="1" oninput="calculate()"></td><td><input type="number" name="item_rate[]" class="rate" value="0.00" step="0.01" oninput="calculate()"></td><td><input type="number" name="item_gst[]" class="gstp" value="18" oninput="calculate()"></td><td><input type="number" class="gstamt" readonly value="0.00"></td><td><input type="number" class="row-total" readonly value="0.00" style="font-weight:700;"></td><td><button type="button" onclick="this.closest('tr').remove(); calculate();" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i class="ph ph-x-circle" style="font-size:20px;"></i></button></td>`;
}
</script>