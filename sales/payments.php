<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE PAYMENTS TABLE
$sql = "CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `payment_type` varchar(50),
  `paid_date` date,
  `paid_amount` decimal(15,2),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $sql);

// 3. HANDLE ADD PAYMENT
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_payment'])) {
    $inv_id = intval($_POST['invoice_id']);
    $pay_type = mysqli_real_escape_string($conn, $_POST['payment_type']);
    $date = $_POST['paid_date'];
    $amount = floatval($_POST['paid_amount']);

    // Insert Payment
    $ins = "INSERT INTO payments (invoice_id, payment_type, paid_date, paid_amount) VALUES ('$inv_id', '$pay_type', '$date', '$amount')";
    if(mysqli_query($conn, $ins)) {
        // Optional: Auto-update Invoice status to 'Paid'
        mysqli_query($conn, "UPDATE invoices SET status='Paid' WHERE id=$inv_id");
        
        header("Location: payments.php?msg=added");
        exit();
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

// 4. FETCH PAYMENTS (Linked to Actual Invoices)
// We JOIN with the 'invoices' table to get the real Client Name and Invoice No you created earlier.
$payments = [];
$query = "SELECT p.*, i.invoice_no, i.client_name, i.grand_total 
          FROM payments p 
          JOIN invoices i ON p.invoice_id = i.id 
          ORDER BY p.paid_date DESC";
$res = mysqli_query($conn, $query);
if($res) { while($row = mysqli_fetch_assoc($res)) $payments[] = $row; }

// 5. FETCH INVOICES FOR DROPDOWN (Only Pending Invoices)
$invoices_list = [];
$inv_res = mysqli_query($conn, "SELECT id, invoice_no, client_name, grand_total FROM invoices WHERE status != 'Paid' ORDER BY id DESC");
if($inv_res) { while($row = mysqli_fetch_assoc($inv_res)) $invoices_list[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payments - Sales</title>
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
        .link-info { color: #0d6efd; text-decoration: none; font-weight: 500; }
        
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
                
                <?php if(isset($_GET['msg']) && $_GET['msg'] == 'added'): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Payment recorded successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Payments</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item">Sales</li>
                                <li class="breadcrumb-item active">Payments</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add_payment_modal">
                            <i class="ti ti-plus me-2"></i>Record Payment
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Payment List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Client Name</th>
                                        <th>Payment Type</th>
                                        <th>Paid Date</th>
                                        <th>Paid Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($payments)): ?>
                                        <tr><td colspan="5" class="text-center p-4">No payments recorded yet. Click "Record Payment" to add one.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($payments as $pay): ?>
                                        <tr>
                                            <td><a href="#" class="link-info"><?= htmlspecialchars($pay['invoice_no']) ?></a></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-secondary rounded-circle text-white me-2">
                                                        <?= strtoupper(substr($pay['client_name'], 0, 2)) ?>
                                                    </span>
                                                    <h6 class="fw-medium mb-0"><?= htmlspecialchars($pay['client_name']) ?></h6>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($pay['payment_type']) ?></td>
                                            <td><?= date('d M Y', strtotime($pay['paid_date'])) ?></td>
                                            <td>₹<?= number_format($pay['paid_amount'], 2) ?></td>
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
                <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">Dreams</a></p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_payment_modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Record New Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="payments.php" method="POST">
                    <input type="hidden" name="add_payment" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Select Invoice <span class="text-danger">*</span></label>
                            <select name="invoice_id" class="form-select" required onchange="updateAmount(this)">
                                <option value="">Select an Invoice</option>
                                <?php foreach($invoices_list as $inv): ?>
                                    <option value="<?= $inv['id'] ?>" data-amount="<?= $inv['grand_total'] ?>">
                                        <?= $inv['invoice_no'] ?> - <?= $inv['client_name'] ?> (₹<?= $inv['grand_total'] ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if(empty($invoices_list)): ?>
                                <small class="text-danger">No pending invoices found.</small>
                            <?php endif; ?>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" name="paid_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount Received (₹)</label>
                            <input type="number" name="paid_amount" id="pay_amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_type" class="form-select">
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Paypal">Paypal</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto-fill amount when invoice is selected
        function updateAmount(select) {
            let option = select.options[select.selectedIndex];
            let amount = option.getAttribute('data-amount');
            if(amount) {
                document.getElementById('pay_amount').value = amount;
            }
        }
    </script>
</body>
</html>