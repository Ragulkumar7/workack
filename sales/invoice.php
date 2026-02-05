<?php
// ==============================================================
//  PHP LOGIC: HANDLE FORM SUBMISSION
// ==============================================================
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check which button was clicked
    if (isset($_POST['save_draft'])) {
        $status = "draft_success";
    } elseif (isset($_POST['save_send'])) {
        $status = "sent_success";
    } else {
        $status = "success";
    }
    
    // Redirect back to the list with the specific status
    header("Location: invoice.php?status=" . $status);
    exit();
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>Invoices | Workack Sales</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; font-family: "Segoe UI", sans-serif; }

        /* --- FALLBACK SIDEBAR STYLES --- */
        .fallback-sidebar {
            width: 80px; /* CHANGED FROM 250px TO 80px */
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #2b3042;
            color: #fff;
            padding: 20px;
            z-index: 1000;
            overflow-y: auto;
        }
        .fallback-sidebar h4 { display: none; } /* Hide title for narrow sidebar */
        .fallback-sidebar ul { padding-left: 0; }
        .fallback-sidebar li { list-style: none; margin-bottom: 5px; text-align: center; }
        .fallback-sidebar a { 
            color: #a6b0cf; 
            text-decoration: none; 
            display: block; 
            padding: 12px; 
            font-size: 20px; /* Larger icons for narrow sidebar */
            border-radius: 5px;
            transition: 0.3s;
        }
        .fallback-sidebar a:hover, .fallback-sidebar a.active { 
            color: #fff; 
            background-color: rgba(255,255,255, 0.1);
        }

        /* --- MAIN LAYOUT (THIS FIXED YOUR GAP) --- */
        .main-content { 
            margin-left: 80px; /* CHANGED FROM 250px TO 80px */
            padding: 20px;
            transition: all 0.3s;
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .fallback-sidebar { display: none; }
        }

        /* --- PAGE STYLES --- */
        .section-title { font-weight: 700; color: #2b3042; margin-bottom: 1rem; margin-top: 2rem; border-bottom: 1px solid #eee; padding-bottom: 10px; }
        .add-new-link { color: #ff6c2f; font-weight: 600; text-decoration: none; font-size: 14px; float: right; cursor: pointer; }
        .add-new-link:hover { color: #e0551a; }
        
        /* Form Inputs */
        .form-label { font-weight: 600; color: #495057; font-size: 13px; text-transform: uppercase; }
        .form-control, .form-select { border-color: #e9ecef; padding: 0.6rem 0.9rem; font-size: 14px; border-radius: 6px; }
        .form-control:focus { border-color: #ff6c2f; box-shadow: 0 0 0 0.2rem rgba(255, 108, 47, 0.15); }

        /* Buttons */
        .btn-save-send { background-color: #ff6c2f; color: white; border: none; padding: 10px 25px; font-weight: 600; border-radius: 6px; }
        .btn-save-send:hover { background-color: #e0551a; color: white; }
        .btn-draft { background-color: #eff2f7; color: #495057; border: none; padding: 10px 25px; font-weight: 600; border-radius: 6px; margin-right: 10px; }
        .btn-draft:hover { background-color: #dce1ea; }

        /* Table & Alerts */
        .alert-success-custom { background-color: #d1e7dd; border-color: #badbcc; color: #0f5132; border-radius: 8px; }
        .badge-soft-success { background-color: rgba(52, 195, 143, 0.18); color: #34c38f; }
        .badge-soft-warning { background-color: rgba(241, 180, 76, 0.18); color: #f1b44c; }
    </style>
</head>
<body>

   <?php include '../include/sidebar.php'; ?>

    <div class="main-content">
        
        <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded shadow-sm">
            <h5 class="mb-0 text-muted">Sales / Invoices</h5>
            <div class="d-flex align-items-center">
                 <div class="me-3 text-end d-none d-sm-block">
                     <span class="d-block fw-bold text-dark" style="font-size: 14px;">Admin User</span>
                     <span class="d-block text-muted" style="font-size: 11px;">Administrator</span>
                 </div>
                 <div class="rounded-circle bg-light d-flex justify-content-center align-items-center text-primary fw-bold" style="width: 40px; height: 40px; border: 1px solid #eee;">
                     A
                 </div>
            </div>
        </div>

        <div class="container-fluid p-0">

            <?php
            // =========================================================
            // VIEW 1: ADD INVOICE FORM
            // =========================================================
            if (isset($_GET['action']) && $_GET['action'] == 'add') {
            ?>
                <div class="card shadow-sm border-0 mb-5">
                    <div class="card-body p-4">
                        
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <a href="invoice.php" class="text-secondary fw-bold text-decoration-none" style="font-size: 15px;">
                                <i class="fas fa-arrow-left me-2"></i> Back to List
                            </a>
                            <a href="#" class="text-warning fw-bold text-decoration-none" style="color: #ff6c2f !important;">
                                <i class="far fa-eye me-1"></i> Preview
                            </a>
                        </div>

                        <form action="" method="POST">
                            
                            <div class="p-4 rounded mb-4 border" style="background-color: #f8f9fa;">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-muted text-uppercase mb-3">From</h6>
                                        <h5 class="fw-bold text-dark" style="color: #2b3042;">Workack Solutions</h5>
                                        <p class="text-muted mb-1 text-sm">2077 Chicago Avenue, Orosi, CA 93647</p>
                                        <p class="text-muted mb-1 text-sm">Email: <span class="text-dark">billing@workack.com</span></p>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <h6 class="text-muted text-uppercase mb-3">Invoice Details</h6>
                                        <div class="mb-2">
                                            <span class="fw-bold me-2">Invoice #:</span> 
                                            <input type="text" name="invoice_no" class="form-control d-inline-block form-control-sm" style="width: 120px;" value="#INV-005">
                                        </div>
                                        <div class="mb-2">
                                            <span class="fw-bold me-2">Date:</span> 
                                            <input type="date" name="invoice_date" class="form-control d-inline-block form-control-sm" style="width: 150px;">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <h5 class="section-title">Bill To</h5>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Customer</label>
                                    <input type="text" name="customer" class="form-control" placeholder="Search customer...">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Due Date</label>
                                    <input type="date" name="due_date" class="form-control">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Reference No</label>
                                    <input type="text" name="ref_no" class="form-control">
                                </div>
                            </div>

                            <h5 class="section-title">Items</h5>
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%;">Description</th>
                                            <th style="width: 15%;">Qty</th>
                                            <th style="width: 20%;">Rate</th>
                                            <th style="width: 15%;">Discount</th>
                                            <th style="width: 10%;">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><input type="text" name="item_desc" class="form-control border-0" placeholder="Item Name / Description"></td>
                                            <td><input type="number" name="item_qty" class="form-control border-0" value="1"></td>
                                            <td><input type="text" name="item_rate" class="form-control border-0" placeholder="0.00"></td>
                                            <td><input type="text" name="item_discount" class="form-control border-0" placeholder="0%"></td>
                                            <td class="text-end bg-light fw-bold">$0.00</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mb-5">
                                <a href="#" class="add-new-link float-start"><i class="fas fa-plus-circle me-1"></i> Add New Line</a>
                            </div>

                            <div class="row mt-5">
                                <div class="col-md-6">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Notes for the customer..."></textarea>
                                </div>
                                <div class="col-md-6 text-end">
                                    <div class="p-3 bg-light rounded d-inline-block" style="min-width: 300px;">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Sub Total:</span>
                                            <span class="fw-bold">$0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">Tax (10%):</span>
                                            <span class="fw-bold">$0.00</span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <h5 class="mb-0">Grand Total:</h5>
                                            <h5 class="mb-0 text-success">$0.00</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-5 pt-3 border-top">
                                <button type="submit" name="save_draft" class="btn-draft"><i class="fas fa-save me-2"></i> Save Draft</button>
                                <button type="submit" name="save_send" class="btn-save-send"><i class="fas fa-paper-plane me-2"></i> Save & Send</button>
                            </div>
                        </form>
                    </div>
                </div>

            <?php
            } else {
            // =========================================================
            // VIEW 2: INVOICES LIST (Default View)
            // =========================================================
            ?>

                <?php if (isset($_GET['status'])): ?>
                    <div class="alert alert-success-custom d-flex align-items-center alert-dismissible fade show mb-4" role="alert">
                        <i class="fas fa-check-circle me-2 fs-5"></i>
                        <div>
                            <?php 
                                if($_GET['status'] == 'draft_success') echo "<strong>Success!</strong> Invoice saved as Draft.";
                                elseif($_GET['status'] == 'sent_success') echo "<strong>Success!</strong> Invoice Saved and Sent.";
                                else echo "<strong>Success!</strong> Action completed.";
                            ?>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row mb-4">
                    <div class="col-12 d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-1 fs-4 fw-bold text-dark">Invoice Management</h4>
                            <p class="text-muted mb-0">Create and manage your sales invoices</p>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="invoice.php?action=add" class="btn text-white shadow-sm" style="background-color: #ff6c2f;">
                                <i class="fas fa-plus-circle me-1"></i> Add New Invoice
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-3">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0" placeholder="Search invoices...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <input type="date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <select class="form-select">
                                    <option>Status: All</option>
                                    <option>Paid</option>
                                    <option>Pending</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-hover mb-0 align-middle">
                                <thead class="bg-light text-muted text-uppercase" style="font-size: 12px;">
                                    <tr>
                                        <th style="width: 20px;" class="ps-4"><input class="form-check-input" type="checkbox"></th>
                                        <th>Invoice ID</th>
                                        <th>Client</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th class="text-end pe-4">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="ps-4"><input class="form-check-input" type="checkbox"></td>
                                        <td><a href="#" class="text-primary fw-bold text-decoration-none">#INV-001</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex justify-content-center align-items-center me-2 fw-bold" style="width:32px; height:32px;">M</div>
                                                <div>
                                                    <h6 class="mb-0 font-size-14 text-dark">Michael Walker</h6>
                                                    <small class="text-muted">BrightWave Inc.</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>02 Feb, 2026</td>
                                        <td class="fw-bold">$3,000.00</td>
                                        <td><span class="badge badge-soft-success px-2 py-1 rounded-pill">Paid</span></td>
                                        <td class="text-end pe-4">
                                            <a href="#" class="text-muted px-1"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="text-muted px-1"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="ps-4"><input class="form-check-input" type="checkbox"></td>
                                        <td><a href="#" class="text-primary fw-bold text-decoration-none">#INV-002</a></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-warning bg-opacity-10 text-warning d-flex justify-content-center align-items-center me-2 fw-bold" style="width:32px; height:32px;">S</div>
                                                <div>
                                                    <h6 class="mb-0 font-size-14 text-dark">Sarah Jenkins</h6>
                                                    <small class="text-muted">Freelance</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>04 Feb, 2026</td>
                                        <td class="fw-bold">$1,250.00</td>
                                        <td><span class="badge badge-soft-warning px-2 py-1 rounded-pill">Pending</span></td>
                                        <td class="text-end pe-4">
                                            <a href="#" class="text-muted px-1"><i class="fas fa-edit"></i></a>
                                            <a href="#" class="text-muted px-1"><i class="fas fa-trash-alt"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            <?php } ?>

        </div>
    </div>

</body>
</html>