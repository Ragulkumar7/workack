<?php 
require_once('../include/db_connect.php'); // Production Host: 82.197.82.27

// --- 1. PO NUMBER GENERATION ---
function generatePONo($conn) {
    $year = date('Y');
    $query = "SELECT po_no FROM purchase_orders WHERE po_no LIKE 'PO-$year-%' ORDER BY id DESC LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $lastNo = intval(substr($row['po_no'], -3));
        $newNo = str_pad($lastNo + 1, 3, '0', STR_PAD_LEFT);
    } else { $newNo = '001'; }
    return "PO-$year-" . rand(100, 999) . "-" . $newNo;
}

// --- 2. SAVE LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_po'])) {
    $po_no = mysqli_real_escape_string($conn, $_POST['po_no']);
    $vendor = mysqli_real_escape_string($conn, $_POST['vendor_name']);
    $v_gst = mysqli_real_escape_string($conn, $_POST['vendor_gstin']);
    $bill_date = $_POST['po_date'];
    $exp_del = $_POST['expected_delivery'];
    $status = $_POST['po_status'];
    $pay_mode = $_POST['payment_mode'];
    $del_loc = mysqli_real_escape_string($conn, $_POST['delivery_location']);
    $terms = mysqli_real_escape_string($conn, $_POST['terms_conditions']);
    $bank = mysqli_real_escape_string($conn, $_POST['bank_name']);
    
    $net_total = floatval($_POST['net_total_hidden']);
    $tax_total = floatval($_POST['tax_total_hidden']);
    $freight = floatval($_POST['freight_charges']);
    $grand_total = floatval($_POST['grand_total_hidden']);
    $paid = floatval($_POST['paid_amount']);
    $balance = $grand_total - $paid;

    mysqli_begin_transaction($conn);
    try {
        $sql = "INSERT INTO purchase_orders (po_no, vendor_name, vendor_gstin, bill_date, expected_delivery, po_status, payment_mode, delivery_location, net_total, tax_amount, freight_charges, grand_total, paid_amount, balance_amount, terms_conditions, bank_name) 
                VALUES ('$po_no', '$vendor', '$v_gst', '$bill_date', '$exp_del', '$status', '$pay_mode', '$del_loc', $net_total, $tax_total, $freight, $grand_total, $paid, $balance, '$terms', '$bank')";
        mysqli_query($conn, $sql);

        foreach ($_POST['item_name'] as $k => $name) {
            if (!empty($name)) {
                $code = mysqli_real_escape_string($conn, $_POST['item_code'][$k]);
                $hsn = mysqli_real_escape_string($conn, $_POST['item_hsn'][$k]);
                $qty = intval($_POST['item_qty'][$k]);
                $unit = $_POST['item_unit'][$k];
                $rate = floatval($_POST['item_rate'][$k]);
                $disc = floatval($_POST['item_disc'][$k]);
                $gst = floatval($_POST['item_gst'][$k]);
                $total = floatval($_POST['item_line_total'][$k]);
                
                mysqli_query($conn, "INSERT INTO po_items (po_no, item_code, description, hsn_code, qty, unit, rate, discount_pct, gst_pct, total) 
                                     VALUES ('$po_no', '$code', '$name', '$hsn', $qty, '$unit', $rate, $disc, $gst, $total)");
            }
        }
        mysqli_commit($conn);
        header("Location: purchase_order.php?success=1");
        exit();
    } catch (Exception $e) { mysqli_rollback($conn); $error = $e->getMessage(); }
}

$next_po_no = generatePONo($conn);
$history = mysqli_query($conn, "SELECT * FROM purchase_orders ORDER BY id DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Order | Workack</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-orange: #f97316; --bg-gray: #f8fafc; --border-color: #e2e8f0; --sidebar-width: 110px; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gray); margin: 0; display: flex; font-size: 13px; color: #1e293b; }
        
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; box-sizing: border-box; }
        
        /* THEME CARD STYLING */
        .theme-card { background: white; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
        .theme-card-header { background: #fff7ed; border-bottom: 2px solid var(--primary-orange); padding: 12px 24px; color: #7c2d12; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 10px; }
        
        .form-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; padding: 24px; }
        .form-group { display: flex; flex-direction: column; }
        label { font-weight: 600; color: #64748b; margin-bottom: 6px; font-size: 11px; text-transform: uppercase; }
        input, select, textarea { padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 8px; outline: none; font-size: 13px; transition: border 0.2s; }
        input:focus { border-color: var(--primary-orange); }

        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { background: #f8fafc; text-align: left; padding: 14px; font-size: 11px; color: #64748b; border-bottom: 1px solid var(--border-color); }
        .items-table td { padding: 10px 14px; border-bottom: 1px solid #f1f5f9; }

        .summary-layout { display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; padding: 24px; }
        .summary-box { background: #fffcf9; border: 1px solid #ffedd5; border-radius: 12px; padding: 24px; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 12px; font-weight: 500; }
        .total-highlight { color: #2563eb; font-weight: 700; font-size: 18px; border-top: 1px dashed #fed7aa; padding-top: 15px; margin-top: 10px; }

        .btn-submit { background: #0f172a; color: white; border: none; padding: 14px 40px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-submit:hover { background: #1e293b; }
        .btn-add-item { color: #2563eb; background: none; border: none; font-weight: 600; cursor: pointer; padding: 20px; display: flex; align-items: center; gap: 8px; font-size: 12px; }

        .history-table { width: 100%; border-collapse: collapse; }
        .history-table th { text-align: left; padding: 16px; background: #f8fafc; font-size: 11px; color: #64748b; }
        .history-table td { padding: 16px; border-bottom: 1px solid #f1f5f9; }
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; background: #e0f2fe; color: #0369a1; text-transform: uppercase; }
    </style>
</head>
<body>

    <?php include_once('../include/sidebar.php'); ?>

    <main class="main-content">
        <form method="POST" id="poForm">
            <div class="theme-card">
                <div class="theme-card-header">1. Header & Vendor Details</div>
                <div class="form-grid">
                    <div class="form-group"><label>PO Number</label><input type="text" name="po_no" value="<?= $next_po_no ?>" readonly></div>
                    <div class="form-group"><label>PO Date</label><input type="date" name="po_date" value="<?= date('Y-m-d') ?>"></div>
                    <div class="form-group"><label>Vendor Name *</label><input type="text" name="vendor_name" placeholder="Business Name" required></div>
                    <div class="form-group"><label>Vendor GSTIN</label><input type="text" name="vendor_gstin" placeholder="GSTIN Number"></div>
                    <div class="form-group"><label>Expected Delivery</label><input type="date" name="expected_delivery"></div>
                    <div class="form-group"><label>PO Status</label><select name="po_status"><option>Draft</option><option>Ordered</option><option>Sent</option></select></div>
                    <div class="form-group"><label>Payment Mode</label><select name="payment_mode"><option>Bank Transfer</option><option>Cash</option></select></div>
                    <div class="form-group"><label>Payment Bank</label><input type="text" name="bank_name" placeholder="Receiving Bank"></div>
                </div>
            </div>

            <div class="theme-card">
                <div class="theme-card-header">2. Line Items</div>
                <table class="items-table">
                    <thead>
                        <tr><th width="40px">#</th><th>Description & Code</th><th width="100px">HSN</th><th width="160px">Qty & Unit</th><th width="100px">Rate</th><th width="80px">Disc%</th><th width="80px">GST%</th><th width="120px">Total</th><th width="40px"></th></tr>
                    </thead>
                    <tbody id="poItemsBody">
                        <tr class="item-row">
                            <td>1</td>
                            <td>
                                <input type="text" name="item_name[]" placeholder="Item Name" required style="width:90%; margin-bottom:5px">
                                <input type="text" name="item_code[]" placeholder="Item Code" style="width:90%; font-size:10px; color:#94a3b8">
                            </td>
                            <td><input type="text" name="item_hsn[]" style="width:80px"></td>
                            <td>
                                <div style="display:flex; gap:5px;">
                                    <input type="number" name="item_qty[]" class="qty" value="0" style="width:60px" oninput="calc()">
                                    <select name="item_unit[]" style="width:80px">
                                        <option value="Nos">Nos</option>
                                        <option value="Kg">Kg</option>
                                        <option value="Pcs">Pcs</option>
                                        <option value="Box">Box</option>
                                    </select>
                                </div>
                            </td>
                            <td><input type="number" name="item_rate[]" class="rate" value="0" style="width:90px" oninput="calc()"></td>
                            <td><input type="number" name="item_disc[]" class="disc" value="0" style="width:60px" oninput="calc()"></td>
                            <td>
                                <select name="item_gst[]" class="gst" onchange="calc()" style="width:70px">
                                    <option value="0">0%</option><option value="5">5%</option><option value="12">12%</option><option value="18" selected>18%</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" class="line-total-disp" value="0.00" readonly style="border:none; background:transparent; font-weight:700; color:#2563eb">
                                <input type="hidden" name="item_line_total[]" class="line-total-hidden">
                            </td>
                            <td><button type="button" onclick="this.closest('tr').remove(); calc();" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i data-lucide="trash-2"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn-add-item" onclick="addRow()"><i data-lucide="plus-circle"></i> Add New Item Row</button>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <div class="theme-card">
                    <div class="theme-card-header">3. Terms & Shipping</div>
                    <div style="padding:24px;">
                        <label>Delivery Location</label><input type="text" name="delivery_location" placeholder="Shipping Address" style="margin-bottom:20px; width:100%">
                        <label>Terms & Conditions</label><textarea name="terms_conditions" rows="4" placeholder="Standard Terms..." style="width:100%"></textarea>
                    </div>
                </div>
                <div class="theme-card">
                    <div class="theme-card-header">4. Summary</div>
                    <div class="summary-box" style="margin:24px;">
                        <div class="summary-row"><span>Net Total</span><span id="net_disp">₹ 0.00</span></div>
                        <div class="summary-row"><span>Tax (GST)</span><span id="tax_disp">₹ 0.00</span></div>
                        <div class="summary-row"><span>Freight (+)</span><input type="number" name="freight_charges" id="freight" value="0" style="width:90px; text-align:right" oninput="calc()"></div>
                        <div class="summary-row total-highlight"><span>Grand Total</span><span id="grand_disp">₹ 0.00</span></div>
                        <div class="summary-row" style="margin-top:15px"><span>Paid Amount</span><input type="number" name="paid_amount" id="paid" value="0" style="width:110px; text-align:right" oninput="calc()"></div>
                        <div class="summary-row" style="color:#ef4444; font-weight:700"><span>Due Balance</span><span id="bal_disp">₹ 0.00</span></div>
                    </div>
                </div>
            </div>
            
            <input type="hidden" name="net_total_hidden" id="net_h"><input type="hidden" name="tax_total_hidden" id="tax_h"><input type="hidden" name="grand_total_hidden" id="grand_h">
            
            <div style="text-align: right; margin-bottom: 60px;">
                <button type="submit" name="generate_po" class="btn-submit">Generate & Save Purchase Order</button>
            </div>
        </form>

        <div class="theme-card">
            <div class="theme-card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#334155;">
                Purchase Order History
            </div>
            <table class="history-table">
                <thead>
                    <tr><th>PO No</th><th>Date</th><th>Vendor</th><th>Grand Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($history)): ?>
                    <tr>
                        <td style="color:#2563eb; font-weight:700;"><?= $row['po_no'] ?></td>
                        <td><?= date('d-M-Y', strtotime($row['bill_date'])) ?></td>
                        <td style="font-weight:600;"><?= htmlspecialchars($row['vendor_name']) ?></td>
                        <td>₹ <?= number_format($row['grand_total'], 2) ?></td>
                        <td style="color:#059669;">₹ <?= number_format($row['paid_amount'], 2) ?></td>
                        <td style="color:#ef4444;">₹ <?= number_format($row['balance_amount'], 2) ?></td>
                        <td><span class="status-badge"><?= $row['po_status'] ?></span></td>
                        <td><i data-lucide="printer" style="width:18px; color:#64748b; cursor:pointer"></i></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
    lucide.createIcons();

    function addRow() {
        const tbody = document.getElementById('poItemsBody');
        const row = tbody.insertRow();
        row.className = "item-row";
        const idx = tbody.rows.length;
        row.innerHTML = `<td>${idx}</td><td><input type="text" name="item_name[]" required style="width:90%; margin-bottom:5px"><br><input type="text" name="item_code[]" style="width:90%; font-size:10px; color:#94a3b8"></td><td><input type="text" name="item_hsn[]" style="width:80px"></td><td><div style="display:flex; gap:5px;"><input type="number" name="item_qty[]" class="qty" value="0" style="width:60px" oninput="calc()"> <select name="item_unit[]" style="width:80px"><option value="Nos">Nos</option><option value="Pcs">Pcs</option></select></div></td><td><input type="number" name="item_rate[]" class="rate" value="0" style="width:90px" oninput="calc()"></td><td><input type="number" name="item_disc[]" class="disc" value="0" style="width:60px" oninput="calc()"></td><td><select name="item_gst[]" class="gst" onchange="calc()" style="width:70px"><option value="18">18%</option><option value="12">12%</option><option value="5">5%</option></select></td><td><input type="text" class="line-total-disp" readonly style="border:none; background:transparent; font-weight:700; color:#2563eb"><input type="hidden" name="item_line_total[]" class="line-total-hidden"></td><td><button type="button" onclick="this.closest('tr').remove(); calc();" style="border:none; background:none; color:#ef4444; cursor:pointer;">&times;</button></td>`;
        lucide.createIcons();
    }

    function calc() {
        let net = 0, taxTot = 0;
        document.querySelectorAll('.item-row').forEach(r => {
            const q = parseFloat(r.querySelector('.qty').value) || 0;
            const rt = parseFloat(r.querySelector('.rate').value) || 0;
            const d = parseFloat(r.querySelector('.disc').value) || 0;
            const g = parseFloat(r.querySelector('.gst').value) || 0;
            
            const baseSub = q * rt;
            const discAmt = baseSub * (d / 100);
            const afterDisc = baseSub - discAmt;
            const gstAmt = afterDisc * (g / 100);
            const total = afterDisc + gstAmt;

            r.querySelector('.line-total-disp').value = total.toFixed(2);
            r.querySelector('.line-total-hidden').value = total.toFixed(2);
            net += afterDisc; taxTot += gstAmt;
        });

        const fr = parseFloat(document.getElementById('freight').value) || 0;
        const pd = parseFloat(document.getElementById('paid').value) || 0;
        const gt = net + taxTot + fr;

        document.getElementById('net_disp').innerText = "₹ " + net.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('tax_disp').innerText = "₹ " + taxTot.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('grand_disp').innerText = "₹ " + gt.toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('bal_disp').innerText = "₹ " + (gt - pd).toLocaleString('en-IN', {minimumFractionDigits: 2});
        document.getElementById('net_h').value = net.toFixed(2);
        document.getElementById('tax_h').value = taxTot.toFixed(2);
        document.getElementById('grand_h').value = gt.toFixed(2);
    }
    </script>
</body>
</html>