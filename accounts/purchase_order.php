<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. SEQUENTIAL PO NUMBER GENERATION ---
function generatePONo($conn) {
    $year = date('Y');
    $prefix = "PO-IT-$year-";
    $query = "SELECT po_no FROM purchase_orders WHERE po_no LIKE '$prefix%' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastNo = intval(substr($row['po_no'], -3));
        $newNo = str_pad($lastNo + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $newNo = '001';
    }
    return $prefix . $newNo;
}

// --- 2. SAVE LOGIC WITH FULL VALIDATIONS ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_po'])) {
    if (empty($_POST['vendor_name']) || empty($_POST['grand_total_hidden']) || floatval($_POST['grand_total_hidden']) <= 0) {
        echo "<script>alert('Error: Vendor Name and a valid Grand Total are required.'); window.history.back();</script>";
        exit();
    }

    $po_no = mysqli_real_escape_string($conn, $_POST['po_no']);
    $vendor = mysqli_real_escape_string($conn, $_POST['vendor_name']);
    $contact = mysqli_real_escape_string($conn, $_POST['contact_number']);
    $email = mysqli_real_escape_string($conn, $_POST['email_address']);
    $v_gst = mysqli_real_escape_string($conn, $_POST['vendor_gst']);
    $v_addr = mysqli_real_escape_string($conn, $_POST['address']);
    $bill_date = $_POST['bill_date'];
    $acc_type = $_POST['accounting_type'];
    $bank = mysqli_real_escape_string($conn, $_POST['bank_name'] ?? 'N/A');
    
    $net_total = floatval($_POST['net_total_hidden']);
    $freight = floatval($_POST['freight_charges']);
    $grand_total = floatval($_POST['grand_total_hidden']);
    $paid_amount = floatval($_POST['paid_amount']);
    $balance = $grand_total - $paid_amount;
    $remarks = mysqli_real_escape_string($conn, $_POST['remarks']);

    if ($paid_amount > $grand_total) {
        echo "<script>alert('Error: Paid Amount cannot exceed Grand Total.'); window.history.back();</script>";
        exit();
    }

    // A. Insert into purchase_orders master table
    $sql = "INSERT INTO purchase_orders (po_no, vendor_name, vendor_phone, vendor_email, vendor_gst, vendor_address, bill_date, accounting_type, net_total, freight_charges, grand_total, paid_amount, balance_amount, remarks, bank_name) 
            VALUES ('$po_no', '$vendor', '$contact', '$email', '$v_gst', '$v_addr', '$bill_date', '$acc_type', $net_total, $freight, $grand_total, $paid_amount, $balance, '$remarks', '$bank')";
    
    if (mysqli_query($conn, $sql)) {
        // B. Insert itemized line items
        if (isset($_POST['item_desc'])) {
            foreach ($_POST['item_desc'] as $key => $desc) {
                if (!empty($desc)) {
                    $item_desc = mysqli_real_escape_string($conn, $desc);
                    $hsn = mysqli_real_escape_string($conn, $_POST['item_hsn'][$key]);
                    $qty = intval($_POST['item_qty'][$key]);
                    $unit = mysqli_real_escape_string($conn, $_POST['item_unit'][$key]);
                    $rate = floatval($_POST['item_rate'][$key]);
                    $total = $qty * $rate;
                    mysqli_query($conn, "INSERT INTO po_items (po_no, description, hsn_code, qty, unit, rate, total) VALUES ('$po_no', '$item_desc', '$hsn', $qty, '$unit', $rate, $total)");
                }
            }
        }

        // C. LEDGER INTEGRATION
        if ($paid_amount > 0) {
            $ledger_desc = "Payment for Purchase Order #$po_no";
            mysqli_query($conn, "INSERT INTO ledger_entries (entry_date, type, name, description, debit_out, reference_no, bank_name) VALUES ('$bill_date', 'Purchase', '$vendor', '$ledger_desc', $paid_amount, '$po_no', '$bank')");
        }
        echo "<script>alert('Purchase Order Saved Successfully!'); window.location.href='purchase_order.php';</script>";
    }
}

// --- 3. ERROR PREVENTION FOR HEADER ---
if (isset($sections) && is_array($sections)) {
    foreach ($sections as &$section) {
        if (isset($section['items']) && is_array($section['items'])) {
            $section['items'] = array_filter($section['items'], function($item) {
                return is_array($item);
            });
        }
    }
}

$next_po_no = generatePONo($conn);
$history = mysqli_query($conn, "SELECT * FROM purchase_orders ORDER BY id DESC LIMIT 10");

include_once('../include/sidebar.php'); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order Management | Neoera</title>
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --sidebar-w: 110px; --bg: #f9fafb; --border: #e5e7eb; --primary: #111827; --text-muted: #6b7280; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); margin: 0; display: flex; color: #111827; }
        
        .main-content { margin-left: var(--sidebar-w); width: calc(100% - var(--sidebar-w)); padding: 40px; box-sizing: border-box; }
        
        .page-header { margin-bottom: 32px; }
        .page-header h1 { font-size: 24px; font-weight: 700; margin: 0; }
        .page-header p { font-size: 14px; color: var(--text-muted); margin: 4px 0 0; }

        .white-card { background: white; border-radius: 12px; border: 1px solid var(--border); padding: 32px; margin-bottom: 24px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .section-title { font-size: 13px; font-weight: 700; text-transform: uppercase; color: var(--primary); letter-spacing: 0.05em; margin-bottom: 20px; border-bottom: 1px solid #f3f4f6; padding-bottom: 8px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 24px; }
        label { font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; margin-bottom: 6px; display: block; }
        input, select, textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 8px; font-size: 13px; outline: none; transition: border 0.2s; box-sizing: border-box; }
        input:focus { border-color: #3b82f6; }
        input[readonly] { background: #f9fafb; font-weight: 700; color: var(--primary); }

        .items-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items-table th { text-align: left; padding: 12px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); background: #f9fafb; }
        .items-table td { padding: 10px; border-bottom: 1px solid #f3f4f6; }
        
        .calc-container { display: grid; grid-template-columns: 1.5fr 1fr; gap: 40px; margin-top: 32px; }
        .calc-box { background: #f9fafb; padding: 24px; border-radius: 12px; border: 1px solid var(--border); }
        .calc-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-size: 14px; font-weight: 500; }
        .grand-total-row { font-size: 18px; font-weight: 800; border-top: 1px solid var(--border); padding-top: 16px; margin-top: 16px; color: var(--primary); }

        .btn-add { background: #f3f4f6; border: 1px solid var(--border); padding: 8px 16px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer; margin-top: 12px; }
        .btn-save { background: var(--primary); color: white; width: 100%; padding: 14px; border: none; border-radius: 8px; font-weight: 700; font-size: 15px; cursor: pointer; margin-top: 20px; transition: opacity 0.2s; }
        .btn-save:hover { opacity: 0.9; }

        .hist-table { width: 100%; border-collapse: collapse; }
        .hist-table th { text-align: left; padding: 16px; font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); background: #f9fafb; }
        .hist-table td { padding: 16px; border-bottom: 1px solid #f3f4f6; font-size: 13px; }
    </style>
</head>
<body>

    <main class="main-content">
        <div class="page-header">
            <h1>Purchase Order Management</h1>
            <p>Generate procurement requests and track vendor settlements</p>
        </div>

        <form method="POST" onsubmit="return validateForm()">
            <div class="white-card">
                <div class="section-title">PO Basic Information</div>
                <div class="form-grid">
                    <div><label>PO Number</label><input type="text" name="po_no" value="<?= $next_po_no ?>" readonly></div>
                    <div><label>PO Date</label><input type="date" name="bill_date" value="<?= date('Y-m-d') ?>" required></div>
                    <div><label>Accounting Type</label><select name="accounting_type" required><option>Accounting</option><option>Inventory</option><option>Services</option></select></div>
                    <div><label>Payment Bank</label><input type="text" name="bank_name" placeholder="e.g. HDFC Bank" required></div>
                </div>

                <div class="section-title">Vendor / Supplier Details</div>
                <div class="form-grid">
                    <div><label>Vendor Name</label><input type="text" name="vendor_name" placeholder="Full Registered Name" required></div>
                    <div><label>Contact Number</label><input type="text" name="contact_number" placeholder="+91"></div>
                    <div><label>Email Address</label><input type="email" name="email_address" placeholder="vendor@email.com"></div>
                    <div><label>GST Number</label><input type="text" name="vendor_gst" placeholder="GSTIN"></div>
                </div>
                <div><label>Vendor Address</label><input type="text" name="address" placeholder="Complete Billing Address"></div>

                <div class="section-title" style="margin-top: 32px;">Purchase Items</div>
                <table class="items-table" id="itemsTable">
                    <thead>
                        <tr><th width="5%">S.NO</th><th>DESCRIPTION</th><th width="12%">HSN</th><th width="8%">QTY</th><th width="12%">UNIT</th><th width="15%">RATE (₹)</th><th width="15%">TOTAL (₹)</th><th width="5%"></th></tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td>1</td>
                            <td><input type="text" name="item_desc[]" required placeholder="Item Details" oninput="calculate()"></td>
                            <td><input type="text" name="item_hsn[]" placeholder="HSN"></td>
                            <td><input type="number" name="item_qty[]" class="qty" value="1" min="1" oninput="calculate()"></td>
                            <td><select name="item_unit[]"><option>Nos</option><option>PCS</option><option>Service</option></select></td>
                            <td><input type="number" name="item_rate[]" class="rate" value="0.00" step="0.01" oninput="calculate()"></td>
                            <td><input type="number" class="row-total" value="0.00" readonly></td>
                            <td><button type="button" onclick="this.closest('tr').remove(); calculate();" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i class="ph ph-trash"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" onclick="addRow()" class="btn-add">+ Add Item</button>

                <div class="calc-container">
                    <div><label>Internal Remarks / Notes</label><textarea name="remarks" rows="8" placeholder="Special instructions..."></textarea></div>
                    <div class="calc-box">
                        <div class="calc-row"><span>Net Total:</span> <span id="net_total_disp">₹ 0.00</span></div>
                        <div class="calc-row"><span>Freight Charges (+):</span> <input type="number" name="freight_charges" id="freight" value="0" step="0.01" oninput="calculate()" style="width:100px; text-align:right;"></div>
                        <div class="calc-row grand-total-row"><span>Grand Total:</span> <span id="grand_total_disp">₹ 0.00</span></div>
                        <div class="calc-row" style="margin-top:20px;"><span>Paid Amount:</span> <input type="number" name="paid_amount" id="paid_amount" value="0" step="0.01" oninput="calculate()" style="width:120px; text-align:right;"></div>
                        <div class="calc-row" style="color:#dc2626; margin-top:10px;"><span>Balance Due:</span> <span id="balance_disp" style="font-weight:700;">₹ 0.00</span></div>
                        <input type="hidden" name="net_total_hidden" id="net_total_hidden"><input type="hidden" name="grand_total_hidden" id="grand_total_hidden">
                        <button type="submit" name="save_po" class="btn-save">Finalize & Save Purchase Order</button>
                    </div>
                </div>
            </div>
        </form>

        <div class="white-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 24px 32px; border-bottom: 1px solid var(--border); font-weight:700;">Recent Purchase Orders</div>
            <table class="hist-table">
                <thead>
                    <tr><th>PO NUMBER</th><th>VENDOR</th><th>CONTACT</th><th>DATE</th><th>GRAND TOTAL</th><th>PAID</th><th>BALANCE</th><th>BANK</th><th>ACTIONS</th></tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($history)): ?>
                    <tr>
                        <td><strong><?= $row['po_no'] ?></strong></td>
                        <td><?= $row['vendor_name'] ?></td>
                        <td><?= $row['vendor_phone'] ?: '-' ?></td>
                        <td><?= date('d M Y', strtotime($row['bill_date'])) ?></td>
                        <td>₹<?= number_format($row['grand_total'], 2) ?></td>
                        <td style="color:#059669; font-weight:600;">₹<?= number_format($row['paid_amount'], 2) ?></td>
                        <td style="color:#dc2626; font-weight:600;">₹<?= number_format($row['balance_amount'], 2) ?></td>
                        <td><?= htmlspecialchars($row['bank_name'] ?? 'N/A') ?></td>
                        <td><i class="ph ph-eye" style="color:#3b82f6; cursor:pointer;"></i></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
    function addRow() {
        let tbody = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
        let newRow = tbody.insertRow();
        newRow.className = "item-row";
        newRow.innerHTML = `<td>${tbody.rows.length}</td><td><input type="text" name="item_desc[]" required oninput="calculate()"></td><td><input type="text" name="item_hsn[]"></td><td><input type="number" name="item_qty[]" class="qty" value="1" min="1" oninput="calculate()"></td><td><select name="item_unit[]"><option>Nos</option><option>PCS</option></select></td><td><input type="number" name="item_rate[]" class="rate" value="0.00" step="0.01" oninput="calculate()"></td><td><input type="number" class="row-total" value="0.00" readonly></td><td><button type="button" onclick="this.closest('tr').remove(); calculate();" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i class="ph ph-trash"></i></button></td>`;
    }
    function calculate() {
        let nt = 0;
        document.querySelectorAll('.item-row').forEach(r => {
            let q = r.querySelector('.qty').value, rt = r.querySelector('.rate').value;
            let tot = q * rt; r.querySelector('.row-total').value = tot.toFixed(2); nt += tot;
        });
        let f = parseFloat(document.getElementById('freight').value) || 0, p = parseFloat(document.getElementById('paid_amount').value) || 0;
        let gt = nt + f, bal = gt - p;
        document.getElementById('net_total_disp').innerText = "₹ " + nt.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('grand_total_disp').innerText = "₹ " + gt.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('balance_disp').innerText = "₹ " + bal.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('net_total_hidden').value = nt; document.getElementById('grand_total_hidden').value = gt;
    }
    function validateForm() {
        let gt = parseFloat(document.getElementById('grand_total_hidden').value) || 0;
        if (gt <= 0) { alert("Invalid Order: Grand Total must be greater than zero."); return false; }
        return true;
    }
    </script>
</body>
</html>