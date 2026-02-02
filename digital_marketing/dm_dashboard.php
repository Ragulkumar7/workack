<?php
// -------------------------------------------------------------------------
// MOCK DATA (Ideally, this comes from your MySQL Database later)
// -------------------------------------------------------------------------

// Key Metrics
$total_leads = 6000;
$new_leads = 120;
$lost_leads = 30;
$total_customers = 9895;

// Recent Leads Data (Array)
$recent_leads = [
    ['company' => 'BrightWave', 'img' => 'assets/img/company/company-01.svg', 'stage' => 'Contacted', 'badge' => 'secondary', 'date' => '14 Jan 2024', 'owner' => 'William Parsons'],
    ['company' => 'Stellar', 'img' => 'assets/img/company/company-02.svg', 'stage' => 'Closed', 'badge' => 'success', 'date' => '21 Jan 2024', 'owner' => 'Lucille Tomberlin'],
    ['company' => 'Quantum', 'img' => 'assets/img/company/company-03.svg', 'stage' => 'Lost', 'badge' => 'danger', 'date' => '20 Feb 2024', 'owner' => 'Frederick Johnson'],
    ['company' => 'EcoVision', 'img' => 'assets/img/company/company-04.svg', 'stage' => 'Not Contacted', 'badge' => 'purple', 'date' => '15 Mar 2024', 'owner' => 'Sarah Henry'],
    ['company' => 'Aurora', 'img' => 'assets/img/company/company-05.svg', 'stage' => 'Closed', 'badge' => 'success', 'date' => '12 Apr 2024', 'owner' => 'Thomas Miller'],
];

// Top Countries Data (Array)
$top_countries = [
    ['name' => 'Singapore', 'img' => 'assets/img/payment-gateway/country-03.svg', 'leads' => 236, 'color' => 'primary'],
    ['name' => 'France', 'img' => 'assets/img/payment-gateway/country-04.svg', 'leads' => 589, 'color' => 'secondary'],
    ['name' => 'Norway', 'img' => 'assets/img/payment-gateway/country-05.svg', 'leads' => 221, 'color' => 'info'],
    ['name' => 'USA', 'img' => 'assets/img/payment-gateway/country-01.svg', 'leads' => 350, 'color' => 'danger'],
    ['name' => 'UAE', 'img' => 'assets/img/payment-gateway/country-02.svg', 'leads' => 221, 'color' => 'warning'],
];

?>

<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>
<style>
    /* --- 1. Layout & Structure --- */
    body {
        background-color: #f7f8f9;
        font-family: 'CircularStd', sans-serif;
        color: #333;
        margin: 0;
        overflow-x: hidden;
    }
    
    /* Pushes content right to not hide behind sidebar */
    .page-wrapper {
        margin-left: 260px; /* Width of your sidebar */
        padding-top: 60px; /* Height of your header */
        padding: 30px;
        transition: all 0.2s ease-in-out;
    }

    .content {
        padding: 1.5rem 0;
    }

    /* --- 2. Cards & Metrics --- */
    .card {
        background: #fff;
        border: 0;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.03);
        margin-bottom: 24px;
        position: relative;
        display: flex;
        flex-direction: column;
        min-width: 0;
        word-wrap: break-word;
    }

    .card-body {
        padding: 1.5rem;
        flex: 1 1 auto;
    }

    .card-header {
        background-color: #fff;
        border-bottom: 1px solid #f0f0f0;
        padding: 1rem 1.5rem;
        border-radius: 10px 10px 0 0;
    }

    .card-header h5 {
        margin-bottom: 0;
        font-size: 16px;
        font-weight: 600;
        color: #333;
    }

    /* Metric Icons */
    .avatar {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
    }
    
    .avatar-md {
        width: 48px;
        height: 48px;
    }

    .bg-primary { background-color: #FF902F !important; color: #fff; } /* Orange */
    .bg-secondary { background-color: #333 !important; color: #fff; }
    .bg-danger { background-color: #F62D51 !important; color: #fff; }
    .bg-purple { background-color: #7a92a3 !important; color: #fff; }
    .bg-success { background-color: #55ce63 !important; color: #fff; }
    .bg-info { background-color: #009efb !important; color: #fff; }

    /* Progress Bars */
    .progress {
        height: 6px;
        background-color: #f5f5f5;
        border-radius: 10px;
        overflow: hidden;
    }
    .progress-bar {
        height: 100%;
    }

    /* --- 3. Typography & Helpers --- */
    h5 { font-size: 20px; font-weight: 700; margin: 0; }
    p { color: #777; font-size: 14px; margin-bottom: 5px; }
    .text-success { color: #28a745 !important; }
    .text-danger { color: #dc3545 !important; }
    .fs-12 { font-size: 12px; }
    .fs-13 { font-size: 13px; }
    .fw-medium { font-weight: 500; }
    .mb-3 { margin-bottom: 1rem !important; }

    /* --- 4. Tables --- */
    .table-responsive {
        overflow-x: auto;
    }
    .table {
        width: 100%;
        margin-bottom: 1rem;
        color: #333;
        vertical-align: top;
        border-color: #dee2e6;
    }
    .table thead th {
        vertical-align: bottom;
        border-bottom: 1px solid #f0f0f0;
        background-color: #fafafa;
        color: #333;
        font-weight: 600;
        padding: 15px;
        font-size: 14px;
        text-align: left;
    }
    .table td {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #f0f0f0;
        font-size: 14px;
    }

    /* Badges */
    .badge {
        display: inline-block;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
        font-weight: 700;
        line-height: 1;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border-radius: 0.25rem;
    }
    .badge-secondary { background-color: #7460ee; }
    .badge-success { background-color: #28a745; }
    .badge-danger { background-color: #dc3545; }
    .badge-purple { background-color: #7460ee; }

    /* --- 5. Breadcrumb & Header Actions --- */
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin-bottom: 0;
        list-style: none;
        display: flex;
    }
    .breadcrumb-item {
        font-size: 14px;
        color: #6c757d;
    }
    .breadcrumb-item.active {
        color: #333;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        content: "/";
        padding: 0 0.5rem;
    }

    /* Buttons */
    .btn-white {
        background-color: #fff;
        border: 1px solid #e3e3e3;
        color: #333;
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
    }
    
    /* Footer */
    .footer {
        text-align: center;
        color: #777;
        font-size: 13px;
    }

    /* --- Mobile Responsive --- */
    @media (max-width: 991.98px) {
        .page-wrapper {
            margin-left: 0; /* Sidebar collapses on mobile */
            padding: 15px;
        }
    }
</style>
<div class="page-wrapper">
    <div class="content">

        <div class="d-md-flex d-block align-items-center justify-content-between mb-3">
            <div class="my-auto mb-2">
                <h2 class="mb-1">Leads Dashboard</h2>
                <nav>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="index.php"><i class="ti ti-smart-home"></i></a>
                        </li>
                        <li class="breadcrumb-item">Dashboard</li>
                        <li class="breadcrumb-item active" aria-current="page">Leads Dashboard</li>
                    </ol>
                </nav>
            </div>
            <div class="d-flex my-xl-auto right-content align-items-center flex-wrap ">
                <div class="me-2 mb-2">
                    <div class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle btn btn-white d-inline-flex align-items-center" data-bs-toggle="dropdown">
                            <i class="ti ti-file-export me-1"></i>Export
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end p-3">
                            <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-pdf me-1"></i>Export as PDF</a></li>
                            <li><a href="javascript:void(0);" class="dropdown-item rounded-1"><i class="ti ti-file-type-xls me-1"></i>Export as Excel </a></li>
                        </ul>
                    </div>
                </div>
                <div class="input-icon mb-2 position-relative">
                    <span class="input-icon-addon"><i class="ti ti-calendar text-gray-9"></i></span>
                    <input type="text" class="form-control date-range bookingrange" placeholder="dd/mm/yyyy - dd/mm/yyyy">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card position-relative">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md br-10 icon-rotate bg-primary flex-shrink-0">
                                <span class="d-flex align-items-center"><i class="ti ti-delta text-white fs-16"></i></span>
                            </div>
                            <div class="ms-3">
                                <p class="fw-medium text-truncate mb-1">Total No of Leads</p>
                                <h5><?= number_format($total_leads) ?></h5>
                            </div>
                        </div>
                        <div class="progress progress-xs mb-2">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 40%"></div>
                        </div>
                        <p class="fw-medium fs-13 mb-0"><span class="text-danger fs-12"><i class="ti ti-arrow-wave-right-up me-1"></i>-4.01% </span> from last week</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card position-relative">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md br-10 icon-rotate bg-secondary flex-shrink-0">
                                <span class="d-flex align-items-center"><i class="ti ti-currency text-white fs-16"></i></span>
                            </div>
                            <div class="ms-3">
                                <p class="fw-medium text-truncate mb-1">No of New Leads</p>
                                <h5><?= number_format($new_leads) ?></h5>
                            </div>
                        </div>
                        <div class="progress progress-xs mb-2">
                            <div class="progress-bar bg-secondary" role="progressbar" style="width: 40%"></div>
                        </div>
                        <p class="fw-medium fs-13 mb-0"><span class="text-success fs-12"><i class="ti ti-arrow-wave-right-up me-1"></i>+20.01% </span> from last week</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card position-relative">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md br-10 icon-rotate bg-danger flex-shrink-0">
                                <span class="d-flex align-items-center"><i class="ti ti-stairs-up text-white fs-16"></i></span>
                            </div>
                            <div class="ms-3">
                                <p class="fw-medium text-truncate mb-1">No of Lost Leads</p>
                                <h5><?= number_format($lost_leads) ?></h5>
                            </div>
                        </div>
                        <div class="progress progress-xs mb-2">
                            <div class="progress-bar bg-pink" role="progressbar" style="width: 40%"></div>
                        </div>
                        <p class="fw-medium fs-13 mb-0"><span class="text-success fs-12"><i class="ti ti-arrow-wave-right-up me-1"></i>+55% </span> from last week</p>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6">
                <div class="card position-relative">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-md br-10 icon-rotate bg-purple flex-shrink-0">
                                <span class="d-flex align-items-center"><i class="ti ti-users-group text-white fs-16"></i></span>
                            </div>
                            <div class="ms-3">
                                <p class="fw-medium text-truncate mb-1">No of Total Customers</p>
                                <h5><?= number_format($total_customers) ?></h5>
                            </div>
                        </div>
                        <div class="progress progress-xs mb-2">
                            <div class="progress-bar bg-purple" role="progressbar" style="width: 40%"></div>
                        </div>
                        <p class="fw-medium fs-13 mb-0"><span class="text-success fs-12"><i class="ti ti-arrow-wave-right-up me-1"></i>+55% </span> from last week</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-8 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <h5>Pipeline Stages</h5>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-white border btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>2023 - 2024
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">2023 - 2024</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div class="row g-2 justify-content-center mb-3">
                            <div class="col-md col-sm-4 col-6">
                                <div class="border rounded p-2">
                                    <p class="mb-1 d-flex align-items-center gap-1"><i class="ti ti-square-rounded-filled text-primary fs-13"></i>Contacted</p>
                                    <h6>50000</h6>
                                </div>
                            </div>
                            <div class="col-md col-sm-4 col-6">
                                <div class="border rounded p-2">
                                    <p class="mb-1 d-flex align-items-center gap-1"><i class="ti ti-square-rounded-filled text-secondary fs-13"></i>Oppurtunity</p>
                                    <h6>25985</h6>
                                </div>
                            </div>
                            <div class="col-md col-sm-4 col-6">
                                <div class="border rounded p-2">
                                    <p class="mb-1 d-flex align-items-center gap-1"><i class="ti ti-square-rounded-filled text-info fs-13"></i>Not Contacted</p>
                                    <h6>12566</h6>
                                </div>
                            </div>
                        </div>
                        <div id="revenue-income"></div> 
                    </div>
                </div>
            </div>

            <div class="col-xl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <h5>New Leads</h5>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-white border btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>This Week
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">This Week</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body pb-0">
                        <div id="heat_chart"></div> 
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <h5>Lost Leads </h5>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-white border-0 dropdown-toggle dropdown-sm btn-sm d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    Sales Pipeline
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">This Month</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body py-0">
                        <div id="leads_stage"></div> 
                    </div>
                </div>
            </div>

            <div class="col-xl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <h5>Leads by Source</h5>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-white border btn-md d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    <i class="ti ti-calendar me-1 fs-14"></i>This Week
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">This Month</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="donut-chart-2"></div> 
                        
                        <div>
                            <h6 class="mb-3">Status</h6>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <p class="f-13 mb-0"><i class="ti ti-circle-filled text-secondary me-1"></i>Google</p>
                                <p class="f-13 fw-medium text-gray-9">40%</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <p class="f-13 mb-0"><i class="ti ti-circle-filled text-warning me-1"></i>Paid</p>
                                <p class="f-13 fw-medium text-gray-9">35%</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <p class="f-13 mb-0"><i class="ti ti-circle-filled text-pink me-1"></i>Campaigns</p>
                                <p class="f-13 fw-medium text-gray-9">15%</p>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <p class="f-13 mb-0"><i class="ti ti-circle-filled text-purple me-1"></i>Referals</p>
                                <p class="f-13 fw-medium text-gray-9">10%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <h5>Recent Follow Up</h5>
                            <div><a href="#" class="btn btn-light btn-md">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-4">
                            <div class="d-flex align-items-center">
                                <a href="javascript:void(0);" class="avatar flex-shrink-0">
                                    <img src="assets/img/users/user-27.jpg" class="rounded-circle border border-2" alt="img">
                                </a>
                                <div class="ms-2">
                                    <h6 class="fs-14 fw-medium text-truncate mb-1"><a href="#">Alexander Jermai</a></h6>
                                    <p class="fs-13">UI/UX Designer</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <a href="#" class="btn btn-light btn-icon btn-sm d-flex justify-content-center align-items-center border-0 p-2"><i class="ti ti-mail-bolt fs-16"></i></a>
                            </div>
                        </div>
                        </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xl-5 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <div class="d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                            <h5>Top Countries</h5>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="btn btn-white border-0 dropdown-toggle dropdown-sm btn-sm d-inline-flex align-items-center" data-bs-toggle="dropdown">
                                    Referrals
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end p-3">
                                    <li><a href="javascript:void(0);" class="dropdown-item rounded-1">Referrals</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-xxl-5 col-sm-6">
                                <div class="pe-3 border-end">
                                    <?php foreach($top_countries as $country): ?>
                                    <div class="d-flex align-items-center mb-4">
                                        <span class="me-2"><i class="ti ti-point-filled text-<?= $country['color'] ?> fs-16"></i></span>
                                        <a href="countries.html" class="avatar rounded-circle flex-shrink-0 border border-2">
                                            <img src="<?= $country['img'] ?>" class="img-fluid rounded-circle" alt="img">
                                        </a>
                                        <div class="ms-2">
                                            <h6 class="fw-medium text-truncate mb-1"><a href="countries.html"><?= $country['name'] ?></a></h6>
                                            <span class="fs-13 text-truncate">Leads : <?= $country['leads'] ?></span>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-xxl-7 col-sm-6">
                                <div id="donut-chart-3"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-7 d-flex">
                <div class="card flex-fill">
                    <div class="card-header d-flex align-items-center justify-content-between flex-wrap row-gap-2">
                        <h5>Recent Leads</h5>
                        <div class="d-flex align-items-center">
                            <div><a href="leads.html" class="btn btn-light btn-md">View All</a></div>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">  
                            <table class="table table-nowrap dashboard-table mb-0">
                                <thead>
                                    <tr>
                                        <th>Company Name</th>
                                        <th>Stage</th>
                                        <th>Created Date</th>
                                        <th>Lead Owner</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_leads as $lead): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center file-name-icon">
                                                <a href="company-details.html" class="avatar border rounded-circle">
                                                    <img src="<?= $lead['img'] ?>" class="img-fluid" alt="img">
                                                </a>
                                                <div class="ms-2">
                                                    <h6 class="fw-medium"><a href="company-details.html"><?= $lead['company'] ?></a></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?= $lead['badge'] ?> d-inline-flex align-items-center">
                                                <i class="ti ti-point-filled me-1"></i>
                                                <?= $lead['stage'] ?>
                                            </span>
                                        </td>
                                        <td><?= $lead['date'] ?></td>
                                        <td><?= $lead['owner'] ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer d-sm-flex align-items-center justify-content-between border-top bg-white p-3">
        <p class="mb-0">2014 - <?= date("Y"); ?> ©Workack.</p>
        <p>Designed &amp; Developed By <a href="javascript:void(0);" class="text-primary">neoera infotech</a></p>
    </div>

</div>
