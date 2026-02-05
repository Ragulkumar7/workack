<?php
session_start();

// 1. DATABASE CONNECTION
$paths = ['../../include/db_connect.php', '../include/db_connect.php', 'include/db_connect.php'];
$conn = null;
foreach ($paths as $path) { if (file_exists($path)) { include $path; break; } }

if (!isset($conn)) die("Error: DB connection not found.");

// 2. CREATE EXPENSES TABLE
$sql = "CREATE TABLE IF NOT EXISTS `expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `expense_name` varchar(100),
  `expense_date` date,
  `amount` decimal(15,2),
  `payment_method` varchar(50),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
)";
mysqli_query($conn, $sql);

// 3. HANDLE FORM SUBMISSIONS

// ADD EXPENSE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_expense'])) {
    $name = mysqli_real_escape_string($conn, $_POST['expense_name']);
    $date = $_POST['expense_date'];
    $amount = floatval($_POST['amount']);
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    $ins = "INSERT INTO expenses (expense_name, expense_date, amount, payment_method) VALUES ('$name', '$date', '$amount', '$method')";
    if(mysqli_query($conn, $ins)) {
        header("Location: expenses.php?msg=added");
        exit();
    }
}

// EDIT EXPENSE
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['edit_expense'])) {
    $id = intval($_POST['expense_id']);
    $name = mysqli_real_escape_string($conn, $_POST['expense_name']);
    $date = $_POST['expense_date'];
    $amount = floatval($_POST['amount']);
    $method = mysqli_real_escape_string($conn, $_POST['payment_method']);

    $upd = "UPDATE expenses SET expense_name='$name', expense_date='$date', amount='$amount', payment_method='$method' WHERE id=$id";
    if(mysqli_query($conn, $upd)) {
        header("Location: expenses.php?msg=updated");
        exit();
    }
}

// DELETE EXPENSE
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    mysqli_query($conn, "DELETE FROM expenses WHERE id=$id");
    header("Location: expenses.php?msg=deleted");
    exit();
}

// 4. FETCH DATA
$expenses = [];
$res = mysqli_query($conn, "SELECT * FROM expenses ORDER BY expense_date DESC");
if($res) { while($row = mysqli_fetch_assoc($res)) $expenses[] = $row; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Expenses - Sales</title>
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
                
                <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    Action completed successfully!
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>

                <div class="d-md-flex d-block align-items-center justify-content-between page-breadcrumb mb-3">
                    <div class="my-auto mb-2">
                        <h2 class="mb-1">Expenses</h2>
                        <nav>
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../../index.php">Dashboard</a></li>
                                <li class="breadcrumb-item">Sales</li>
                                <li class="breadcrumb-item active">Expenses</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="d-flex my-xl-auto right-content align-items-center flex-wrap gap-2">
                        <button class="btn btn-primary d-flex align-items-center" data-bs-toggle="modal" data-bs-target="#add_expenses">
                            <i class="ti ti-plus me-2"></i>Add New Expense
                        </button>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-3">
                        <h5>Expenses List</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Expense Name</th>
                                        <th>Date</th>
                                        <th>Payment Method</th>
                                        <th>Amount</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(empty($expenses)): ?>
                                        <tr><td colspan="5" class="text-center p-4">No expenses recorded.</td></tr>
                                    <?php else: ?>
                                        <?php foreach($expenses as $exp): 
                                            $json = htmlspecialchars(json_encode($exp), ENT_QUOTES, 'UTF-8');
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($exp['expense_name']) ?></td>
                                            <td><?= date('d M Y', strtotime($exp['expense_date'])) ?></td>
                                            <td><?= htmlspecialchars($exp['payment_method']) ?></td>
                                            <td>₹<?= number_format($exp['amount'], 2) ?></td>
                                            <td class="text-end">
                                                <div class="action-icon d-inline-flex">
                                                    <a href="#" class="me-2" onclick="editExpense(<?= $json ?>)"><i class="ti ti-edit"></i></a>
                                                    <a href="expenses.php?delete_id=<?= $exp['id'] ?>" onclick="return confirm('Delete this expense?')" class="text-danger"><i class="ti ti-trash"></i></a>
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
            </div>
        </div>
    </div>

    <div class="modal fade" id="add_expenses">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Expenses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="expenses.php" method="POST">
                    <input type="hidden" name="add_expense" value="1">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Expense Name</label>
                            <input type="text" name="expense_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="expense_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" name="amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Online Transfer">Online Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="edit_expenses">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Expenses</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="expenses.php" method="POST">
                    <input type="hidden" name="edit_expense" value="1">
                    <input type="hidden" name="expense_id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Expense Name</label>
                            <input type="text" name="expense_name" id="edit_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date</label>
                            <input type="date" name="expense_date" id="edit_date" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount (₹)</label>
                            <input type="number" name="amount" id="edit_amount" class="form-control" step="0.01" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select name="payment_method" id="edit_method" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Online Transfer">Online Transfer</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editExpense(data) {
            document.getElementById('edit_id').value = data.id;
            document.getElementById('edit_name').value = data.expense_name;
            document.getElementById('edit_date').value = data.expense_date;
            document.getElementById('edit_amount').value = data.amount;
            document.getElementById('edit_method').value = data.payment_method;
            
            var myModal = new bootstrap.Modal(document.getElementById('edit_expenses'));
            myModal.show();
        }
    </script>
</body>
</html>