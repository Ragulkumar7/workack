<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Provident Fund Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border: none; border-radius: 10px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .btn-orange { background-color: #fd7e14; color: white; border: none; }
        .btn-orange:hover { background-color: #e36a07; color: white; }
        
        /* Status Dropdown Styling */
        .status-dropdown { 
            padding: 5px 15px; border-radius: 20px; font-size: 0.85rem; border: 1px solid #dee2e6;
            background: white; cursor: pointer; width: 130px; display: flex; align-items: center; justify-content: space-between;
        }
        .dot { height: 8px; width: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; }
        .dot-approved { background-color: #28a745; }
        .dot-pending { background-color: #0dcaf0; }
        .profile-img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right: 10px; }

        /* Modal Custom Styles */
        .modal-header { border-bottom: 1px solid #eee; padding: 20px 30px; }
        .modal-title { color: #2d3748; font-weight: 700; }
        .modal-footer { border-top: 1px solid #eee; padding: 20px 30px; }
        .form-label { font-weight: 500; color: #4a5568; margin-bottom: 8px; }
        .form-control, .form-select { border: 1px solid #e2e8f0; padding: 10px; border-radius: 6px; }
    </style>
</head>
<body>

<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Provident Fund</h2>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><i class="fa fa-home"></i></li>
                    <li class="breadcrumb-item">Sales</li>
                    <li class="breadcrumb-item active">Provident Fund</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-outline-secondary me-2"><i class="fa fa-download"></i> Export <i class="fa fa-chevron-down ms-1" style="font-size: 0.7rem;"></i></button>
            <button class="btn btn-orange" data-bs-toggle="modal" data-bs-target="#addFundModal">
                <i class="fa fa-plus-circle"></i> Add New Provident Fund
            </button>
        </div>
    </div>

    <div class="card p-4">
        <div class="row mb-4 align-items-center">
            <div class="col-md-3"><h5 class="mb-0">Expenses List</h5></div>
            <div class="col-md-9 d-flex justify-content-end gap-2">
                <input type="text" class="form-control w-auto" value="01/29/2026 - 02/04/2026" readonly>
                <select class="form-select w-auto"><option>Select status</option></select>
                <select class="form-select w-auto"><option>Sort By : Last 7 Days</option></select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead class="table-light">
                    <tr>
                        <th><input type="checkbox" class="form-check-input"></th>
                        <th>Employee Name <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th>Provident Fund Type <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th>Employee Share <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th>Organization Share <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th>Status <i class="fa fa-sort ms-1 text-muted"></i></th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $employees = [
                        ['id' => 1, 'name' => 'Anthony Lewis', 'dept' => 'Finance', 'type' => 'Employee Provident Fund', 'emp_s' => '2%', 'org_s' => '2%', 'status' => 'Approved', 'img' => 'https://i.pravatar.cc/150?u=1'],
                        ['id' => 2, 'name' => 'Brian Villalobos', 'dept' => 'Developer', 'type' => 'Employee Provident Fund', 'emp_s' => '2%', 'org_s' => '2%', 'status' => 'Pending', 'img' => 'https://i.pravatar.cc/150?u=2'],
                        ['id' => 3, 'name' => 'Harvey Smith', 'dept' => 'Developer', 'type' => 'Voluntary Provident Fund', 'emp_s' => '5%', 'org_s' => '2%', 'status' => 'Approved', 'img' => 'https://i.pravatar.cc/150?u=3'],
                        ['id' => 4, 'name' => 'Stephan Peralt', 'dept' => 'Executive Officer', 'type' => 'Voluntary Provident Fund', 'emp_s' => '3%', 'org_s' => '2%', 'status' => 'Pending', 'img' => 'https://i.pravatar.cc/150?u=4'],
                        ['id' => 5, 'name' => 'Sarah Connor', 'dept' => 'Operations', 'type' => 'Employee Provident Fund', 'emp_s' => '2%', 'org_s' => '2%', 'status' => 'Approved', 'img' => 'https://i.pravatar.cc/150?u=5'],
                        ['id' => 6, 'name' => 'Michael Scott', 'dept' => 'Management', 'type' => 'Voluntary Provident Fund', 'emp_s' => '4%', 'org_s' => '2%', 'status' => 'Pending', 'img' => 'https://i.pravatar.cc/150?u=6'],
                    ];

                    foreach ($employees as $emp): 
                        $dotClass = ($emp['status'] == 'Approved') ? 'dot-approved' : 'dot-pending';
                    ?>
                    <tr>
                        <td><input type="checkbox" class="form-check-input"></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="<?= $emp['img'] ?>" class="profile-img">
                                <div><div class="fw-bold"><?= $emp['name'] ?></div><small class="text-muted"><?= $emp['dept'] ?></small></div>
                            </div>
                        </td>
                        <td class="text-muted"><?= $emp['type'] ?></td>
                        <td class="text-muted"><?= $emp['emp_s'] ?></td>
                        <td class="text-muted"><?= $emp['org_s'] ?></td>
                        <td>
                            <div class="dropdown">
                                <div class="status-dropdown dropdown-toggle" data-bs-toggle="dropdown">
                                    <span><span class="dot <?= $dotClass ?>"></span> <?= $emp['status'] ?></span>
                                    <i class="fa fa-chevron-down text-muted" style="font-size: 0.7rem;"></i>
                                </div>
                                <ul class="dropdown-menu">
                                    <li><a class="dropdown-item" href="#"><span class="dot dot-approved"></span> Approved</a></li>
                                    <li><a class="dropdown-item" href="#"><span class="dot dot-pending"></span> Pending</a></li>
                                </ul>
                            </div>
                        </td>
                        <td><i class="fa fa-edit text-muted" style="cursor: pointer;"></i></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addFundModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Add Provident Fund</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="#" method="POST">
          <div class="modal-body p-4">
            <div class="row g-4">
                <div class="col-md-6">
                    <label class="form-label">Employee Name</label>
                    <select class="form-select" name="employee_name">
                        <option selected>Select</option>
                        <?php foreach($employees as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= $e['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Provident Fund Type</label>
                    <select class="form-select" name="fund_type">
                        <option selected>Select</option>
                        <option>Employee Provident Fund</option>
                        <option>Voluntary Provident Fund</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Employee Share(%)</label>
                    <input type="text" class="form-control" placeholder="Enter percentage">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Organization Share(%)</label>
                    <input type="text" class="form-control" placeholder="Enter percentage">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Employee Share(Amount)</label>
                    <input type="text" class="form-control" placeholder="Enter amount">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Organization Share(Amount)</label>
                    <input type="text" class="form-control" placeholder="Enter amount">
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" rows="3"></textarea>
                </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-orange px-4">Add Provident Fund</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>