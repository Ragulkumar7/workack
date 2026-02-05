<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE TABLE
$est_sql = "CREATE TABLE IF NOT EXISTS `estimates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_name` varchar(100),
  `project` varchar(100),
  `email` varchar(100),
  `tax_name` varchar(50),
  `client_address` text,
  `billing_address` text,
  `estimate_date` date,
  `expiry_date` date,
  `items` LONGTEXT, 
  `sub_total` decimal(15,2),
  `tax_amount` decimal(15,2),
  `discount_percent` decimal(15,2),
  `grand_total` decimal(15,2),
  `other_info` text,
  `status` enum('Accepted','Sent','Expired','Declined') DEFAULT 'Sent',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $est_sql);

// 3. HANDLE FORM SUBMISSION
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_estimate'])) {
    
    // Data Sanitization
    $client = mysqli_real_escape_string($conn, $_POST['client_name']);
    $project = mysqli_real_escape_string($conn, $_POST['project']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $tax_name = mysqli_real_escape_string($conn, $_POST['tax']);
    $c_addr = mysqli_real_escape_string($conn, $_POST['client_address']);
    $b_addr = mysqli_real_escape_string($conn, $_POST['billing_address']);
    $est_date = $_POST['estimate_date'];
    $exp_date = $_POST['expiry_date'];
    $other = mysqli_real_escape_string($conn, $_POST['other_info']);
    
    // Process Items
    $items = [];
    $sub_total = 0;
    
    if(isset($_POST['items']) && is_array($_POST['items'])) {
        foreach($_POST['items'] as $i) {
            $qty = floatval($i['qty']);
            $cost = floatval($i['cost']);
            $amount = $qty * $cost;
            $sub_total += $amount;
            
            $items[] = [
                'desc' => $i['desc'],
                'qty' => $qty,
                'cost' => $cost,
                'amount' => $amount
            ];
        }
    }
    
    // Calculations
    $tax_percent = 0;
    if(strpos($tax_name, 'GST') !== false) $tax_percent = 18;
    if(strpos($tax_name, 'VAT') !== false) $tax_percent = 5;
    
    $tax_amount = ($sub_total * $tax_percent) / 100;
    $discount_percent = floatval($_POST['discount']);
    $discount_amount = ($sub_total * $discount_percent) / 100;
    
    $grand_total = ($sub_total + $tax_amount) - $discount_amount;
    
    // Encode Items
    $items_json = json_encode($items);
    $items_safe = mysqli_real_escape_string($conn, $items_json);

    $sql = "INSERT INTO estimates (
        client_name, project, email, tax_name, client_address, billing_address, 
        estimate_date, expiry_date, items, sub_total, tax_amount, 
        discount_percent, grand_total, other_info, status
    ) VALUES (
        '$client', '$project', '$email', '$tax_name', '$c_addr', '$b_addr', 
        '$est_date', '$exp_date', '$items_safe', '$sub_total', '$tax_amount', 
        '$discount_percent', '$grand_total', '$other', 'Sent'
    )";

    if(mysqli_query($conn, $sql)) {
        echo "<script>window.location.href='estimates.php?msg=added';</script>";
    } else {
        die("Error Saving Data: " . mysqli_error($conn)); 
    }
}

// Delete Logic
if(isset($_GET['delete_id'])){
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM estimates WHERE id=$id");
    header("Location: estimates.php?msg=deleted"); exit();
}

// 4. FETCH DATA
$estimates = [];
$res = mysqli_query($conn, "SELECT * FROM estimates ORDER BY id DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $estimates[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Estimates - Sales</title>
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
        
        /* Status Badges */
        .badge-soft-success { background-color: rgba(40, 199, 111, 0.1); color: #28c76f; }
        .badge-soft-purple { background-color: rgba(111, 66, 193, 0.1); color: #6f42c1; }
        .badge-soft-warning { background-color: rgba(255, 159, 67, 0.1); color: #ff9f43; }
        .badge-soft-danger { background-color: rgba(234, 84, 85, 0.1); color: #ea5455; }

        .table td { vertical-align: middle; }
        @media (max-width: 991px) { .main-content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

    <?php 
        $sidebar_paths = ['../include/sidebar.php', '../../include/sidebar.php', 'include/sidebar.php'];
        foreach ($sidebar_paths as $path) { if (file_exists($path)) { include $path; break; } }
    ?>

    <div class="main-content-wrapper">
        
        <?php 
            $header_paths = ['../include/header.php', '../../include/header.php', 'include/header.php'];
            foreach ($header_paths as $path) { if (file_exists($path)) { include $path; break; } }
        ?>

        <div class="page-wrapper">
            <div class="content">
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="ti ti-check"></i> Estimate created successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Estimates</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item">Sales</li>
                                <li class="breadcrumb-item active">Estimates</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <a href="#" data-bs-toggle="modal" data-bs-target="#add_estimate_modal" class="btn btn-primary d-flex align-items-center"><i class="ti ti-circle-plus me-2"></i>Add Estimate</a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Estimates List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Client Name</th>
                                        <th>Project</th>
                                        <th>Estimate Date</th>
                                        <th>Expiry Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($estimates)): ?>
                                        <tr><td colspan="7" class="text-center p-4">No estimates found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($estimates as $est): 
                                            $statusClass = 'badge-soft-purple';
                                            if($est['status'] == 'Accepted') $statusClass = 'badge-soft-success';
                                            elseif($est['status'] == 'Expired') $statusClass = 'badge-soft-warning';
                                            elseif($est['status'] == 'Declined') $statusClass = 'badge-soft-danger';
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-secondary rounded-circle text-white me-2">
                                                        <?= strtoupper(substr($est['client_name'], 0, 2)) ?>
                                                    </span>
                                                    <h6 class="fw-medium mb-0"><a href="#" class="text-dark"><?= htmlspecialchars($est['client_name']) ?></a></h6>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($est['project']) ?></td>
                                            <td><?= date('d M Y', strtotime($est['estimate_date'])) ?></td>
                                            <td><?= date('d M Y', strtotime($est['expiry_date'])) ?></td>
                                            <td>₹<?= number_format($est['grand_total'], 2) ?></td>
                                            <td><span class="badge <?= $statusClass ?>"><?= $est['status'] ?></span></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2"><i class="ti ti-edit"></i></a>
                                                    <a href="estimates.php?delete_id=<?= $est['id'] ?>" onclick="return confirm('Delete this estimate?')" class="text-danger"><i class="ti ti-trash"></i></a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            
            <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
                <p class="mb-0">2014 - 2026 © SmartHR.</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_estimate_modal">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Create New Estimate</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="estimates.php" method="POST">
                    <input type="hidden" name="save_estimate" value="1">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client Name <span class="text-danger">*</span></label>
                                <input type="text" name="client_name" class="form-control" required placeholder="Enter Client Name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Project Name</label>
                                <input type="text" name="project" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tax</label>
                                <select name="tax" class="form-select">
                                    <option value="None">None</option>
                                    <option value="GST">GST (18%)</option>
                                    <option value="VAT">VAT (5%)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client Address</label>
                                <textarea name="client_address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Billing Address</label>
                                <textarea name="billing_address" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estimate Date <span class="text-danger">*</span></label>
                                <input type="date" name="estimate_date" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Expiry Date <span class="text-danger">*</span></label>
                                <input type="date" name="expiry_date" class="form-control" required>
                            </div>
                        </div>

                        <div class="table-responsive mt-4">
                            <table class="table table-bordered" id="items_table">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Item / Description</th>
                                        <th width="150">Unit Cost (₹)</th>
                                        <th width="100">Qty</th>
                                        <th width="150">Amount (₹)</th>
                                        <th width="50"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><input type="text" name="items[0][desc]" class="form-control" placeholder="Item Name"></td>
                                        <td><input type="number" name="items[0][cost]" class="form-control cost" oninput="calcRow(this)" step="0.01"></td>
                                        <td><input type="number" name="items[0][qty]" class="form-control qty" oninput="calcRow(this)" value="1"></td>
                                        <td><input type="text" class="form-control amount" readonly value="0.00"></td>
                                        <td><button type="button" class="btn btn-sm btn-danger remove-row" onclick="removeRow(this)"><i class="ti ti-trash"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addItem()"><i class="ti ti-plus"></i> Add Item</button>
                        </div>

                        <div class="row mt-4 justify-content-end">
                            <div class="col-md-4">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Sub Total</strong></td>
                                            <td class="text-end" id="display_sub_total">₹0.00</td>
                                        </tr>
                                        <tr>
                                            <td>Discount (%)</td>
                                            <td class="text-end"><input type="number" name="discount" id="discount_input" class="form-control form-control-sm d-inline-block w-50" value="0" oninput="calcTotal()"></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Grand Total</strong></td>
                                            <td class="text-end"><h5 class="fw-bold text-primary" id="display_grand_total">₹0.00</h5></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Other Information</label>
                            <textarea name="other_info" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Estimate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let rowIdx = 1;
        function addItem() {
            let table = document.getElementById('items_table').getElementsByTagName('tbody')[0];
            let row = table.insertRow();
            row.innerHTML = `
                <td><input type="text" name="items[${rowIdx}][desc]" class="form-control" placeholder="Item Name"></td>
                <td><input type="number" name="items[${rowIdx}][cost]" class="form-control cost" oninput="calcRow(this)" step="0.01"></td>
                <td><input type="number" name="items[${rowIdx}][qty]" class="form-control qty" oninput="calcRow(this)" value="1"></td>
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
            let cost = parseFloat(row.querySelector('.cost').value) || 0;
            let qty = parseFloat(row.querySelector('.qty').value) || 0;
            row.querySelector('.amount').value = (cost * qty).toFixed(2);
            calcTotal();
        }

        function calcTotal() {
            let total = 0;
            document.querySelectorAll('.amount').forEach(el => total += parseFloat(el.value) || 0);
            
            document.getElementById('display_sub_total').innerText = '₹' + total.toFixed(2);
            
            let discPer = parseFloat(document.getElementById('discount_input').value) || 0;
            let discAmt = (total * discPer) / 100;
            
            // Tax Calculation (Simplified for UI display)
            let taxSel = document.querySelector('select[name="tax"]').value;
            let taxPer = 0;
            if(taxSel.includes('GST')) taxPer = 18;
            if(taxSel.includes('VAT')) taxPer = 5;
            
            let taxAmt = (total * taxPer) / 100;
            
            let grand = (total + taxAmt) - discAmt;
            document.getElementById('display_grand_total').innerText = '₹' + grand.toFixed(2);
        }
    </script>
</body>
</html> 