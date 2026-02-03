<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. ACTION HANDLERS ---

// Generate sequential Invoice No (Pattern: INV-2026-014)
function generateInvoiceNo($conn) {
    $year = date('Y');
    $result = $conn->query("SELECT invoice_no FROM invoices WHERE invoice_no LIKE 'INV-$year-%' ORDER BY id DESC LIMIT 1");
    $lastNo = ($result && $result->num_rows > 0) ? intval(substr($result->fetch_assoc()['invoice_no'], -3)) : 0;
    return "INV-$year-" . str_pad($lastNo + 1, 3, '0', STR_PAD_LEFT);
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_invoice'])) {
    $invoice_no = $conn->real_escape_string($_POST['invoice_no']);
    $client_id = intval($_POST['client_id']);
    $inv_date = $_POST['invoice_date'];
    $bank = $conn->real_escape_string($_POST['bank_name']);
    $sub_total = floatval($_POST['sub_total']);
    $discount = floatval($_POST['discount']);
    $tax_amt = floatval($_POST['tax_amount']);
    $grand_total = floatval($_POST['grand_total']);
    $terms = $conn->real_escape_string($_POST['payment_terms']);
    $notes = $conn->real_escape_string($_POST['notes']);

    if($client_id > 0) {
        $sql = "INSERT INTO invoices (invoice_no, client_id, invoice_date, sub_total, discount, tax_amount, grand_total, payment_terms, notes, bank_name, payment_status) 
                VALUES ('$invoice_no', $client_id, '$inv_date', $sub_total, $discount, $tax_amt, $grand_total, '$terms', '$notes', '$bank', 'Unpaid')";

        if ($conn->query($sql)) {
            $invoice_id = $conn->insert_id;
            foreach ($_POST['item_desc'] as $key => $desc) {
                if(!empty($desc)) {
                    $qty = intval($_POST['item_qty'][$key]);
                    $rate = floatval($_POST['item_rate'][$key]);
                    $gst_p = floatval($_POST['item_gst'][$key]);
                    $gst_a = ($qty * $rate * $gst_p) / 100;
                    $total = ($qty * $rate) + $gst_a;
                    $conn->query("INSERT INTO invoice_items (invoice_id, description, qty, rate, gst_rate, gst_amount, total) 
                                 VALUES ($invoice_id, '$desc', $qty, $rate, $gst_p, $gst_a, $total)");
                }
            }
            // AUTO-SYNC TO LEDGER
            $conn->query("INSERT INTO ledger_entries (entry_date, type, name, description, credit_in, reference_no, bank_name) 
                           VALUES ('$inv_date', 'Invoice', 'Client ID: $client_id', 'Invoice #$invoice_no', $grand_total, '$invoice_no', '$bank')");
            
            echo "<script>alert('Invoice Saved Successfully!'); window.location.href='invoice_management.php';</script>";
            exit();
        }
    }
}

// --- 2. CRITICAL FIX FOR HEADER FATAL ERROR ---
// This block must stay here to prevent line 56/58/59 errors by cleaning non-array menu items
if (isset($sections) && is_array($sections)) {
    foreach ($sections as &$section) {
        if (isset($section['items']) && is_array($section['items'])) {
            $section['items'] = array_filter($section['items'], function($item) {
                return is_array($item); 
            });
        }
    }
}

$next_inv_no = generateInvoiceNo($conn);
include_once('../include/sidebar.php'); 
?>

<div class="main-content" style="margin-left: 110px; width: calc(100% - 110px); background: #f9fafb; min-height: 100vh; padding: 40px; box-sizing: border-box;">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; margin: 0; display: flex; color: #111827; }
        .card { background: white; border-radius: 12px; border: 1px solid #e5e7eb; padding: 32px; margin-bottom: 32px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: block; }
        input, select, textarea { width: 100%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 8px; font-size: 14px; outline: none; transition: border 0.2s; box-sizing: border-box; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        .items-table th { text-align: left; padding: 12px; font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .items-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; }
        .summary-box { float: right; width: 320px; background: #f9fafb; padding: 24px; border-radius: 12px; border: 1px solid #e5e7eb; }
        .btn-add { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; margin-top: 15px; }
        .btn-save { background: #1e1b4b; color: white; border: none; padding: 14px 40px; border-radius: 8px; font-weight: 700; cursor: pointer; }
        .hist-table { width: 100%; border-collapse: collapse; }
        .hist-table th { text-align: left; padding: 16px 24px; font-size: 11px; font-weight: 700; color: #64748b; border-bottom: 1px solid #e5e7eb; background: #f9fafb; }
        .hist-table td { padding: 16px 24px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
    </style>

    <h1 style="font-size: 24px; font-weight: 700; margin: 0 0 5px;">Invoice Management</h1>
    <p style="font-size: 13px; color: #64748b; margin: 0 0 30px;">Create and manage client invoices with bank selection</p>

    <form method="POST">
        <div class="card">
            <div class="form-grid">
                <div><label>Invoice Number</label><input type="text" name="invoice_no" value="<?= $next_inv_no ?>" readonly></div>
                <div><label>Client Name</label><select name="client_id" required><option value="">Select Client</option><?php $cls = $conn->query("SELECT id, client_name FROM clients"); while($c = $cls->fetch_assoc()) echo "<option value='{$c['id']}'>{$c['client_name']}</option>"; ?></select></div>
                <div><label>Receiving Bank</label><input type="text" name="bank_name" placeholder="Select or Type Bank"></div>
                <div><label>Invoice Date</label><input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>"></div>
            </div>

            <label style="margin-top: 25px;">Invoice Items</label>
            <table class="items-table" id="itemTable">
                <thead>
                    <tr><th width="5%">S.NO</th><th>DESCRIPTION / PARTICULARS</th><th width="8%">QTY</th><th width="12%">RATE</th><th width="8%">GST %</th><th width="10%">GST AMT</th><th width="12%">TOTAL</th><th width="5%"></th></tr>
                </thead>
                <tbody>
                    <tr class="item-row">
                        <td>1</td>
                        <td><input type="text" name="item_desc[]" placeholder="Item description" oninput="calculate()"></td>
                        <td><input type="number" name="item_qty[]" class="qty" value="1" oninput="calculate()"></td>
                        <td><input type="number" name="item_rate[]" class="rate" value="0.00" step="0.01" oninput="calculate()"></td>
                        <td><input type="number" name="item_gst[]" class="gstp" value="18" oninput="calculate()"></td>
                        <td><input type="number" class="gstamt" readonly value="0.00"></td>
                        <td><input type="number" class="row-total" readonly value="0.00" style="font-weight:700;"></td>
                        <td><button type="button" onclick="this.closest('tr').remove(); calculate();" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i class="ph ph-trash"></i></button></td>
                    </tr>
                </tbody>
            </table>
            <button type="button" onclick="addRow()" class="btn-add">+ Add Item</button>

            <div style="overflow: auto; margin-top: 30px;">
                <div class="summary-box">
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px;"><span>Subtotal:</span> <span id="sub_disp">₹0.00</span></div>
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;"><span>Discount:</span> <input type="number" name="discount" id="disc" value="0" oninput="calculate()" style="width:100px;"></div>
                    <div style="display:flex; justify-content:space-between; margin-bottom:12px;"><span>Total GST:</span> <span id="gst_disp">₹0.00</span></div>
                    <div style="display:flex; justify-content:space-between; font-weight:800; font-size:18px; border-top:1px solid #ddd; padding-top:15px;"><span>Grand Total:</span> <span id="grand_disp">₹0.00</span></div>
                    <input type="hidden" name="sub_total" id="h_sub"><input type="hidden" name="tax_amount" id="h_gst"><input type="hidden" name="grand_total" id="h_grand">
                </div>
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:30px;">
                <div><label>Payment Terms</label><textarea name="payment_terms" rows="3" placeholder="Payment terms..."></textarea></div>
                <div><label>Notes</label><textarea name="notes" rows="3" placeholder="Additional notes..."></textarea></div>
            </div>

            <div style="text-align:right; margin-top:40px;"><button type="submit" name="save_invoice" class="btn-save">Save Invoice</button></div>
        </div>
    </form>
    
    <div class="card" style="padding:0; overflow:hidden;">
        <div style="padding:24px 32px; border-bottom:1px solid #e5e7eb;"><h3 style="margin:0; font-size: 16px;">Recent Invoice History</h3></div>
        <table class="hist-table">
            <thead>
                <tr><th>INVOICE NO</th><th>CLIENT NAME</th><th>DATE</th><th>BANK / SOURCE</th><th>AMOUNT</th><th>STATUS</th><th>ACTIONS</th></tr>
            </thead>
            <tbody>
                <?php $history = $conn->query("SELECT i.*, c.client_name FROM invoices i LEFT JOIN clients c ON i.client_id = c.id ORDER BY i.id DESC LIMIT 10");
                if($history): while($h = $history->fetch_assoc()): ?>
                <tr>
                    <td><strong><?= $h['invoice_no'] ?></strong></td><td><?= $h['client_name'] ?></td><td><?= date('d-M-Y', strtotime($h['invoice_date'])) ?></td><td><?= $h['bank_name'] ?: 'N/A' ?></td><td style="font-weight:700;">₹<?= number_format($h['grand_total'], 2) ?></td><td><span style="padding:4px 12px; border-radius:20px; background:#fee2e2; color:#dc2626; font-size:10px; font-weight:700;">Unpaid</span></td>
                    <td style="display:flex; gap:12px;"><i class="ph ph-eye" style="color:#10b981; cursor:pointer;"></i><i class="ph ph-trash" style="color:#ef4444; cursor:pointer;"></i></td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function calculate() {
    let subtotal = 0, totalGst = 0;
    document.querySelectorAll('.item-row').forEach(row => {
        let q = parseFloat(row.querySelector('.qty').value) || 0, r = parseFloat(row.querySelector('.rate').value) || 0, g = parseFloat(row.querySelector('.gstp').value) || 0;
        let amt = q * r, gamt = (amt * g) / 100;
        row.querySelector('.gstamt').value = gamt.toFixed(2); row.querySelector('.row-total').value = (amt + gamt).toFixed(2);
        subtotal += amt; totalGst += gamt;
    });
    let grand = subtotal + totalGst - (parseFloat(document.getElementById('disc').value) || 0);
    document.getElementById('sub_disp').innerText = '₹' + subtotal.toFixed(2); document.getElementById('gst_disp').innerText = '₹' + totalGst.toFixed(2); document.getElementById('grand_disp').innerText = '₹' + grand.toFixed(2);
    document.getElementById('h_sub').value = subtotal; document.getElementById('h_gst').value = totalGst; document.getElementById('h_grand').value = grand;
}
function addRow() {
    let tbody = document.querySelector('#itemTable tbody');
    let rowCount = tbody.rows.length + 1;
    let newRow = tbody.insertRow();
    newRow.className = "item-row";
    newRow.innerHTML = `<td>${rowCount}</td><td><input type="text" name="item_desc[]" oninput="calculate()"></td><td><input type="number" name="item_qty[]" class="qty" value="1" oninput="calculate()"></td><td><input type="number" name="item_rate[]" class="rate" value="0.00" oninput="calculate()"></td><td><input type="number" name="item_gst[]" class="gstp" value="18" oninput="calculate()"></td><td><input type="number" class="gstamt" readonly value="0.00"></td><td><input type="number" class="row-total" readonly value="0.00" style="font-weight:700;"></td><td><button type="button" onclick="this.closest('tr').remove(); calculate();" style="border:none; background:none; color:#ef4444;"><i class="ph ph-trash"></i></button></td>`;
}
</script>