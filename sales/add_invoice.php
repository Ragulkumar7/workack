<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// --- FORCE RESET TABLE (With Foreign Key Bypass) ---
// We disable checks to allow dropping the table even if other tables link to it.
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 0");
mysqli_query($conn, "DROP TABLE IF EXISTS `invoices`");
mysqli_query($conn, "SET FOREIGN_KEY_CHECKS = 1");
// ---------------------------------------------------

// 2. CREATE INVOICES TABLE
$sql = "CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(50) NOT NULL,
  `client_name` varchar(100),
  `invoice_date` date,
  `due_date` date,
  `payment_type` varchar(50),
  `status` enum('Paid','Pending','Overdue','Draft') DEFAULT 'Pending',
  `items` LONGTEXT,
  `sub_total` decimal(15,2),
  `tax_amount` decimal(15,2),
  `discount_amount` decimal(15,2),
  `grand_total` decimal(15,2),
  `notes` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $sql);

// 3. HANDLE SAVE INVOICE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_invoice'])) {
    
    // Basic Data
    $inv_no = mysqli_real_escape_string($conn, $_POST['invoice_no']);
    $client = mysqli_real_escape_string($conn, $_POST['client_name']);
    $date = $_POST['invoice_date'];
    $due = $_POST['due_date'];
    $pay_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $status = isset($_POST['save_as_draft']) ? 'Draft' : 'Pending';
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    // Items Logic
    $items = [];
    $sub_total = 0;
    if(isset($_POST['items']) && is_array($_POST['items'])) {
        foreach($_POST['items'] as $item) {
            $qty = floatval($item['qty']);
            $rate = floatval($item['rate']);
            $amt = $qty * $rate;
            $sub_total += $amt;
            
            $items[] = [
                'desc' => $item['desc'],
                'qty' => $qty,
                'rate' => $rate,
                'amount' => $amt
            ];
        }
    }

    // Calculations
    $discount_amt = floatval($_POST['discount_amount'] ?? 0);
    $tax_amt = ($sub_total * 5) / 100; // 5% VAT Example
    $grand_total = ($sub_total + $tax_amt) - $discount_amt;

    $items_json = mysqli_real_escape_string($conn, json_encode($items));

    // Save
    $ins = "INSERT INTO invoices (invoice_no, client_name, invoice_date, due_date, payment_type, status, items, sub_total, tax_amount, discount_amount, grand_total, notes) 
            VALUES ('$inv_no', '$client', '$date', '$due', '$pay_type', '$status', '$items_json', '$sub_total', '$tax_amt', '$discount_amt', '$grand_total', '$notes')";
    
    if(mysqli_query($conn, $ins)) {
        // Redirect to list page (we will create this next)
        header("Location: invoice_management.php?msg=added"); 
        exit();
    } else {
        die("Error: " . mysqli_error($conn));
    }
}

// Generate Invoice Number
$last_inv = mysqli_query($conn, "SELECT id FROM invoices ORDER BY id DESC LIMIT 1");
$row = mysqli_fetch_assoc($last_inv);
$next_id = ($row['id'] ?? 0) + 1;
$auto_inv_no = 'INV-' . str_pad($next_id, 4, '0', STR_PAD_LEFT);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Invoice</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    
    <style>
        body { background-color: #f4f7fc; font-family: 'Poppins', sans-serif; }
        .main-content-wrapper { display: flex; flex-direction: column; min-height: 100vh; margin-left: 110px; transition: margin-left 0.3s; }
        .page-wrapper { flex: 1; padding: 25px; }
        .card { border: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-radius: 10px; margin-bottom: 24px; background: #fff; }
        .btn-primary { background-color: #FF9B44 !important; border-color: #FF9B44 !important; }
        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php 
        $sidebar_paths = ['../include/sidebar.php', '../../include/sidebar.php', 'include/sidebar.php'];
        foreach ($sidebar_paths as $path) { if (file_exists($path)) { include $path; break; } }
        
        $header_paths = ['../include/header.php', '../../include/header.php', 'include/header.php'];
        foreach ($header_paths as $path) { if (file_exists($path)) { include $path; break; } }
    ?>

    <div class="main-content-wrapper">
        <div class="page-wrapper">
            <div class="content">
                <div class="row align-items-center">
                    <div class="col-md-10 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <form action="add_invoice.php" method="POST" id="invoiceForm">
                                    <input type="hidden" name="save_invoice" value="1">
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                        <a href="invoice_management.php" class="text-dark d-flex align-items-center fw-medium text-decoration-none">
                                            <span class="border rounded-circle p-1 me-2 d-flex"><i class="ti ti-arrow-left fs-12"></i></span> Back to List
                                        </a>
                                        <a href="#" class="text-primary text-decoration-underline" data-bs-toggle="modal" data-bs-target="#invoice_preview">Preview</a>
                                    </div>

                                    <div class="bg-light p-3 rounded mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h5>From</h5>
                                            <a href="#" class="text-dark fw-medium"><i class="ti ti-edit me-1"></i>Edit</a>
                                        </div>
                                        <div>
                                            <h4 class="mb-1">Workack Admin</h4>
                                            <p class="mb-1">123 Business Road, Chennai, India</p>
                                            <p class="mb-1">Email: admin@workack.com</p>
                                        </div>
                                    </div>

                                    <div class="border-bottom mb-3 pb-3">
                                        <h4 class="mb-3">Invoice Details</h4>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Invoice No</label>
                                                <input type="text" name="invoice_no" class="form-control" value="<?= $auto_inv_no ?>" readonly>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Invoice Date</label>
                                                <input type="date" name="invoice_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Due Date</label>
                                                <input type="date" name="due_date" class="form-control" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-bottom mb-3 pb-3">
                                        <h4 class="mb-3">Payment Details</h4>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <label class="form-label">Customer</label>
                                                    <a href="#" data-bs-toggle="modal" data-bs-target="#add_customer" class="text-primary small fw-bold">+ Add New</a>
                                                </div>
                                                <input type="text" name="client_name" class="form-control" required placeholder="Client Name">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Payment Type</label>
                                                <select name="payment_type" class="form-select">
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Cheque">Cheque</option>
                                                    <option value="Cash">Cash</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="border-bottom mb-3 pb-3">
                                        <h4 class="mb-3">Items</h4>
                                        <div class="table-responsive">
                                            <table class="table table-bordered" id="itemsTable">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Description</th>
                                                        <th width="100">Qty</th>
                                                        <th width="150">Rate (₹)</th>
                                                        <th width="150">Amount (₹)</th>
                                                        <th width="50"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr>
                                                        <td><input type="text" name="items[0][desc]" class="form-control" placeholder="Item Name"></td>
                                                        <td><input type="number" name="items[0][qty]" class="form-control qty" value="1" oninput="calcRow(this)"></td>
                                                        <td><input type="number" name="items[0][rate]" class="form-control rate" value="0" oninput="calcRow(this)"></td>
                                                        <td><input type="text" class="form-control amount" readonly value="0.00"></td>
                                                        <td><button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)"><i class="ti ti-trash"></i></button></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <button type="button" class="btn btn-sm btn-light border text-primary mt-2" onclick="addItem()"><i class="ti ti-plus"></i> Add Item</button>
                                        </div>
                                        
                                        <div class="row justify-content-end mt-3">
                                            <div class="col-md-4">
                                                <div class="d-flex justify-content-between mb-2"><span>Sub Total:</span> <span id="disp_sub">0.00</span></div>
                                                <div class="d-flex justify-content-between mb-2 align-items-center">
                                                    <span>Discount:</span> 
                                                    <input type="number" name="discount_amount" id="disc_input" class="form-control form-control-sm w-25 text-end" value="0" oninput="calcTotal()">
                                                </div>
                                                <div class="d-flex justify-content-between mb-2"><span>Tax (5%):</span> <span id="disp_tax">0.00</span></div>
                                                <div class="d-flex justify-content-between fw-bold fs-5 border-top pt-2"><span>Total:</span> <span id="disp_grand" class="text-primary">0.00</span></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label">Notes</label>
                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <button type="submit" name="save_as_draft" value="1" class="btn btn-dark"><i class="ti ti-device-floppy me-1"></i> Save Draft</button>
                                        <button type="submit" class="btn btn-primary"><i class="ti ti-send me-1"></i> Save & Send</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_customer">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Name</label><input type="text" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" class="form-control"></div>
                    <button class="btn btn-primary w-100" data-bs-dismiss="modal">Add Customer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let rowIdx = 1;
        function addItem() {
            let table = document.getElementById('itemsTable').getElementsByTagName('tbody')[0];
            let row = table.insertRow();
            row.innerHTML = `
                <td><input type="text" name="items[${rowIdx}][desc]" class="form-control" placeholder="Item Name"></td>
                <td><input type="number" name="items[${rowIdx}][qty]" class="form-control qty" value="1" oninput="calcRow(this)"></td>
                <td><input type="number" name="items[${rowIdx}][rate]" class="form-control rate" value="0" oninput="calcRow(this)"></td>
                <td><input type="text" class="form-control amount" readonly value="0.00"></td>
                <td><button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)"><i class="ti ti-trash"></i></button></td>
            `;
            rowIdx++;
        }

        function removeRow(btn) {
            btn.closest('tr').remove();
            calcTotal();
        }

        function calcRow(el) {
            let row = el.closest('tr');
            let qty = parseFloat(row.querySelector('.qty').value) || 0;
            let rate = parseFloat(row.querySelector('.rate').value) || 0;
            row.querySelector('.amount').value = (qty * rate).toFixed(2);
            calcTotal();
        }

        function calcTotal() {
            let total = 0;
            document.querySelectorAll('.amount').forEach(el => total += parseFloat(el.value) || 0);
            document.getElementById('disp_sub').innerText = total.toFixed(2);

            let disc = parseFloat(document.getElementById('disc_input').value) || 0;
            let tax = (total * 0.05); // 5% Tax Logic
            document.getElementById('disp_tax').innerText = tax.toFixed(2);

            let grand = (total + tax) - disc;
            document.getElementById('disp_grand').innerText = '₹' + grand.toFixed(2);
        }
    </script>
</body>
</html>