<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE PAYMENTS TABLE
// This links to the 'invoices' table via invoice_id
$sql = "CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `invoice_no` varchar(50),
  `client_name` varchar(100),
  `company_name` varchar(100),
  `payment_type` varchar(50),
  `paid_date` date,
  `paid_amount` decimal(15,2),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $sql);

// 3. SEED DUMMY DATA (Only if table is empty, for demonstration)
$check = mysqli_query($conn, "SELECT COUNT(*) as count FROM payments");
$row = mysqli_fetch_assoc($check);
if ($row['count'] == 0) {
    // Insert some sample data to match your screenshot
    $dummy_data = [
        "INSERT INTO payments (invoice_no, client_name, company_name, payment_type, paid_date, paid_amount) VALUES ('INV-001', 'Michael Walker', 'BrightWave Innovations', 'Paypal', '2024-01-15', 3000.00)",
        "INSERT INTO payments (invoice_no, client_name, company_name, payment_type, paid_date, paid_amount) VALUES ('INV-002', 'Sophie Headrick', 'Stellar Dynamics', 'Paypal', '2024-01-25', 2500.00)",
        "INSERT INTO payments (invoice_no, client_name, company_name, payment_type, paid_date, paid_amount) VALUES ('INV-003', 'Cameron Drake', 'Quantum Nexus', 'Paypal', '2024-02-22', 2800.00)"
    ];
    foreach($dummy_data as $q) mysqli_query($conn, $q);
}

// 4. FETCH DATA
$payments = [];
$res = mysqli_query($conn, "SELECT * FROM payments ORDER BY paid_date DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $payments[] = $row; }
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
                        <div class="dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                <i class="ti ti-file-export me-1"></i>Export
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a></li>
                                <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel</a></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Payment List</h5>
                        <div class="d-flex my-xl-auto right-content align-items-center flex-wrap row-gap-3">
                            <div class="me-3">
                                <div class="input-icon position-relative">
                                    <span class="input-icon-addon"><i class="ti ti-calendar text-gray-9"></i></span>
                                    <input type="text" class="form-control" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                                </div>
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    Sort By : Last 7 Days
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Recently Added</a></li>
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Ascending</a></li>
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Descending</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Client Name</th>
                                        <th>Company Name</th>
                                        <th>Payment Type</th>
                                        <th>Paid Date</th>
                                        <th>Paid Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($payments)): ?>
                                        <tr><td colspan="6" class="text-center p-4">No payments found.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($payments as $pay): ?>
                                        <tr>
                                            <td><a href="#" class="link-info"><?= htmlspecialchars($pay['invoice_no']) ?></a></td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <span class="avatar avatar-md bg-secondary rounded-circle text-white me-2">
                                                        <?= strtoupper(substr($pay['client_name'], 0, 2)) ?>
                                                    </span>
                                                    <div>
                                                        <h6 class="fw-medium mb-0"><?= htmlspecialchars($pay['client_name']) ?></h6>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?= htmlspecialchars($pay['company_name']) ?></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>