<?php
// Production Database Connection
require_once('../include/db_connect.php'); // Host: 82.197.82.27

// --- 1. HANDLE FORM SUBMISSION ---
if (isset($_POST['save_invoice'])) {
    $invoice_no   = mysqli_real_escape_string($conn, $_POST['invoice_no']);
    $client_id    = (int)$_POST['client_id'];
    $bank_name    = mysqli_real_escape_string($conn, $_POST['bank_name']);
    $invoice_date = mysqli_real_escape_string($conn, $_POST['invoice_date']);
    $sub_total    = (float)$_POST['sub_total'];
    $discount     = (float)$_POST['discount']; 
    $tax_amount   = (float)$_POST['tax_amount'];
    $grand_total  = (float)$_POST['grand_total'];
    $notes        = mysqli_real_escape_string($conn, $_POST['notes']);

    if ($grand_total <= 0 || empty($client_id)) {
        $error_msg = "Error: Invoice must have a selected client and items.";
    } else {
        mysqli_begin_transaction($conn);
        try {
            $sql = "INSERT INTO invoices (invoice_no, client_id, invoice_date, sub_total, discount, tax_amount, grand_total, notes, status, bank_name) 
                    VALUES ('$invoice_no', '$client_id', '$invoice_date', '$sub_total', '$discount', '$tax_amount', '$grand_total', '$notes', 'Unpaid', '$bank_name')";
            
            if (!mysqli_query($conn, $sql)) throw new Exception(mysqli_error($conn));
            $invoice_id = mysqli_insert_id($conn);

            foreach ($_POST['item_desc'] as $key => $desc) {
                if (!empty($desc)) {
                    $qty      = (int)$_POST['item_qty'][$key];
                    $rate     = (float)$_POST['item_rate'][$key];
                    $gst_rate = (float)$_POST['item_gst'][$key];
                    $gst_amt  = (float)$_POST['item_gst_amt'][$key];
                    $total    = (float)$_POST['item_total'][$key];
                    $d_text   = mysqli_real_escape_string($conn, $desc);

                    $item_sql = "INSERT INTO invoice_items (invoice_id, description, qty, rate, gst_rate, gst_amount, total) 
                                 VALUES ('$invoice_id', '$d_text', '$qty', '$rate', '$gst_rate', '$gst_amt', '$total')";
                    if (!mysqli_query($conn, $item_sql)) throw new Exception(mysqli_error($conn));
                }
            }
            mysqli_commit($conn);
            header("Location: invoice_management.php?status=success");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error_msg = "Database Error: " . $e->getMessage();
        }
    }
}

// --- 2. DATA RETRIEVAL ---
$clients = mysqli_query($conn, "SELECT id, company_name FROM contacts ORDER BY company_name ASC");
$history = mysqli_query($conn, "SELECT i.*, c.company_name FROM invoices i 
                                LEFT JOIN contacts c ON i.client_id = c.id 
                                ORDER BY i.created_at DESC LIMIT 10");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice Management | Workack</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary-orange: #f97316; --bg-gray: #f8fafc; --border-color: #e2e8f0; --sidebar-width: 110px; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-gray); margin: 0; display: flex; font-size: 13px; color: #1e293b; }
        .main-content { margin-left: var(--sidebar-width); width: calc(100% - var(--sidebar-width)); padding: 40px; box-sizing: border-box; }
        
        /* THEME CARD STYLING */
        .theme-card { background: white; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); overflow: hidden; }
        .theme-card-header { background: #fff7ed; border-bottom: 2px solid var(--primary-orange); padding: 12px 24px; color: #7c2d12; font-weight: 700; font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; justify-content: space-between; }
        
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
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 10px; font-weight: 700; background: #fee2e2; color: #dc2626; text-transform: uppercase; }
    </style>
</head>
<body>

    <?php include_once('../include/sidebar.php'); ?>

    <main class="main-content">
        <div style="margin-bottom: 25px;">
            <h2 style="color: #1e1b4b; margin: 0;">Invoice Management</h2>
            <?php if(isset($_GET['status']) && $_GET['status'] == 'success'): ?>
                <p id="success-msg" style="color: green; font-size: 13px; font-weight: 600; margin-top: 5px;">Invoice saved successfully!</p>
                <script>setTimeout(() => { document.getElementById('success-msg').style.display='none'; }, 4000);</script>
            <?php endif; ?>
            <?php if(isset($error_msg)) echo "<p style='color:red; font-size:13px;'>$error_msg</p>"; ?>
        </div>

        <form method="POST" id="invoiceForm">
            <div class="theme-card">
                <div class="theme-card-header">1. Invoice Basic Details</div>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Invoice Number</label>
                        <input type="text" name="invoice_no" value="INV-<?= date('Ymd-His') ?>" readonly>
                    </div>
                    <div class="form-group">
                        <label>Client Name</label>
                        <select name="client_id" required>
                            <option value="">Select Client</option>
                            <?php while($c = mysqli_fetch_assoc($clients)) echo "<option value='{$c['id']}'>{$c['company_name']}</option>"; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Receiving Bank</label>
                        <input type="text" name="bank_name" list="bank_list" required placeholder="Select Bank">
                        <datalist id="bank_list">
                            <option value="HDFC"><option value="ICICI"><option value="SBI"><option value="Canara"><option value="KVB">
                        </datalist>
                    </div>
                    <div class="form-group">
                        <label>Invoice Date</label>
                        <input type="date" name="invoice_date" value="<?= date('Y-m-d') ?>">
                    </div>
                </div>
            </div>

            <div class="theme-card">
                <div class="theme-card-header">2. Itemized Particulars</div>
                <table class="items-table">
                    <thead>
                        <tr><th width="40px">#</th><th>Description</th><th width="100px">Qty</th><th width="120px">Rate</th><th width="100px">GST %</th><th width="120px">Total</th><th width="40px"></th></tr>
                    </thead>
                    <tbody id="itemsTableBody">
                        <tr class="item-row">
                            <td>1</td>
                            <td><input type="text" name="item_desc[]" placeholder="Particulars" required style="width:100%"></td>
                            <td><input type="number" name="item_qty[]" class="item-qty" value="1" onchange="calc()"></td>
                            <td><input type="number" name="item_rate[]" class="item-rate" step="0.01" onchange="calc()"></td>
                            <td><input type="number" name="item_gst[]" class="item-gst" value="18" onchange="calc()"></td>
                            <td><input type="number" name="item_total[]" class="item-total" readonly style="border:none; background:transparent; font-weight:700"></td>
                            <input type="hidden" name="item_gst_amt[]" class="item-gst-amt">
                            <td><button type="button" onclick="this.closest('tr').remove(); calc();" style="border:none; background:none; color:#ef4444; cursor:pointer;"><i data-lucide="trash-2"></i></button></td>
                        </tr>
                    </tbody>
                </table>
                <button type="button" class="btn-add-item" onclick="addRow()"><i data-lucide="plus-circle"></i> Add New Particular Row</button>
            </div>

            <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px;">
                <div class="theme-card">
                    <div class="theme-card-header">3. Internal Notes & Remarks</div>
                    <div style="padding:24px;">
                        <textarea name="notes" rows="6" placeholder="Additional instructions or payment terms..." style="width:100%"></textarea>
                    </div>
                </div>
                <div class="theme-card">
                    <div class="theme-card-header">4. Financial Summary</div>
                    <div class="summary-box" style="margin:24px;">
                        <div class="summary-row"><span>Subtotal</span><span id="displaySubtotal">₹0.00</span></div>
                        <div class="summary-row"><span>Total GST</span><span id="displayTax">₹0.00</span></div>
                        <div class="summary-row"><span>Discount (-)</span><input type="number" name="discount" id="discount" value="0" step="0.01" style="width:100px; text-align:right" onchange="calc()"></div>
                        <div class="summary-row total-highlight"><span>Grand Total</span><span id="displayGrandTotal">₹0.00</span></div>
                    </div>
                </div>
            </div>

            <input type="hidden" name="sub_total" id="sub_total">
            <input type="hidden" name="tax_amount" id="tax_amount">
            <input type="hidden" name="grand_total" id="grand_total">

            <div style="text-align: right; margin-bottom: 50px;">
                <button type="submit" name="save_invoice" class="btn-submit">Save & Finalize Invoice</button>
            </div>
        </form>

        <div class="theme-card">
            <div class="theme-card-header" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; color:#334155;">
                Recent Invoice History
            </div>
            <table class="history-table">
                <thead>
                    <tr><th>Inv No</th><th>Client</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($history)): ?>
                    <tr>
                        <td style="color:#2563eb; font-weight:700;"><?= $row['invoice_no'] ?></td>
                        <td><?= $row['company_name'] ?? 'N/A' ?></td>
                        <td><?= date('d-M-Y', strtotime($row['invoice_date'])) ?></td>
                        <td style="font-weight:600;">₹ <?= number_format($row['grand_total'], 2) ?></td>
                        <td><span class="status-badge"><?= $row['status'] ?></span></td>
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
            const tbody = document.getElementById('itemsTableBody');
            const idx = tbody.rows.length + 1;
            const row = `<tr class="item-row">
                <td>${idx}</td>
                <td><input type="text" name="item_desc[]" required style="width:100%"></td>
                <td><input type="number" name="item_qty[]" class="item-qty" value="1" onchange="calc()"></td>
                <td><input type="number" name="item_rate[]" class="item-rate" step="0.01" onchange="calc()"></td>
                <td><input type="number" name="item_gst[]" class="item-gst" value="18" onchange="calc()"></td>
                <td><input type="number" name="item_total[]" class="item-total" readonly style="border:none; background:transparent; font-weight:700"></td>
                <input type="hidden" name="item_gst_amt[]" class="item-gst-amt">
                <td><button type="button" onclick="this.closest('tr').remove(); calc();" style="border:none; background:none; color:#ef4444; cursor:pointer;">&times;</button></td>
            </tr>`;
            tbody.insertAdjacentHTML('beforeend', row);
            lucide.createIcons();
        }

        function calc() {
            let subtotal = 0, tax = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const q = parseFloat(row.querySelector('.item-qty').value) || 0;
                const r = parseFloat(row.querySelector('.item-rate').value) || 0;
                const g = parseFloat(row.querySelector('.item-gst').value) || 0;
                const lineSub = q * r;
                const lineGst = (lineSub * g) / 100;
                row.querySelector('.item-gst-amt').value = lineGst.toFixed(2);
                row.querySelector('.item-total').value = (lineSub + lineGst).toFixed(2);
                subtotal += lineSub; tax += lineGst;
            });
            const disc = parseFloat(document.getElementById('discount').value) || 0;
            const grand = (subtotal + tax) - disc;
            document.getElementById('displaySubtotal').textContent = '₹' + subtotal.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('displayTax').textContent = '₹' + tax.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('displayGrandTotal').textContent = '₹' + grand.toLocaleString('en-IN', {minimumFractionDigits: 2});
            document.getElementById('sub_total').value = subtotal.toFixed(2);
            document.getElementById('tax_amount').value = tax.toFixed(2);
            document.getElementById('grand_total').value = grand.toFixed(2);
        }
        window.onload = calc;
    </script>
</body>
</html>