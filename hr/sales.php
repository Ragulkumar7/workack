<?php include '../include/header.php'; ?>

<div style="display: flex;"> <?php include '../include/sidebar.php'; ?>

    <?php
    // 1. DATA INITIALIZATION (Simulating React State and Props)
    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'pipeline';

    $tabs = [
        ['id' => 'pipeline', 'label' => 'Pipeline & Billing', 'icon' => 'dollar-sign'],
        ['id' => 'executive', 'label' => 'Sales Executive', 'icon' => 'users'],
        ['id' => 'lead', 'label' => 'Lead & Expenses', 'icon' => 'pie-chart'],
    ];

    $savedInvoices = [
        ['invoiceNo' => 'INV-2026-013', 'client' => 'Tech Solutions', 'date' => '2026-01-28', 'amount' => 11800.00, 'status' => 'Paid']
    ];

    $clientList = [
        ['id' => 1, 'name' => "Global Corp", 'phone' => "9876543210", 'email' => "contact@global.com"],
        ['id' => 2, 'name' => "Shruthi", 'phone' => "5677445576", 'email' => "shruthi90@gmail.com"],
    ];

    $assignments = [
        ['id' => 101, 'client' => "Global Corp", 'employee' => "Arun", 'target' => 50000, 'deadline' => "2026-02-15", 'status' => "Yet to start process"],
        ['id' => 102, 'client' => "Shruthi", 'employee' => "Varshith", 'target' => 35000, 'deadline' => "2026-02-05", 'status' => "Yet to start process"],
    ];

    $pipelineStages = [
        ['label' => "Contacted", 'count' => 50000, 'color' => "p-orange", 'text' => "#ea580c"],
        ['label' => "Opportunity", 'count' => 25985, 'color' => "p-blue", 'text' => "#2563eb"],
        ['label' => "Not Contacted", 'count' => 12566, 'color' => "p-gray", 'text' => "#6b7280"],
        ['label' => "Closed", 'count' => 8965, 'color' => "p-green", 'text' => "#16a34a"],
        ['label' => "Lost", 'count' => 2452, 'color' => "p-red", 'text' => "#dc2626"],
    ];
    ?>

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Layout Adjustment: Removes the "gap" from the sidebar */
        .sales-main-wrapper {
            flex: 1; /* Automatically takes all remaining horizontal space */
            background-color: #fdfbf7;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .sales-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* 2. Header */
        .sales-header { margin-bottom: 30px; }
        .sales-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .sales-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }

        /* 3. Tabs */
        .sales-tabs { display: flex; gap: 10px; margin-bottom: 30px; overflow-x: auto; padding-bottom: 5px; }
        .tab-btn {
            display: flex; align-items: center; gap: 8px; padding: 10px 20px;
            background: white; border: 1px solid #e1e1e1; border-radius: 8px;
            color: #666; font-weight: 600; font-size: 14px; cursor: pointer; white-space: nowrap;
            transition: all 0.2s; text-decoration: none;
        }
        .tab-btn:hover { color: #FF9B44; border-color: #FF9B44; }
        .tab-btn.active { background: #FF9B44; color: white; border-color: #FF9B44; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.3); }

        /* 4. Common Card & Grid Styles */
        .card {
            background: white; border-radius: 12px; padding: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1; margin-bottom: 30px;
        }
        .card-header { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 30px; }
        .grid-4 { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; }

        /* 5. Form Styles */
        .form-group { margin-bottom: 15px; }
        .label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; margin-bottom: 5px; }
        .input, .select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; box-sizing: border-box; background: white; }
        .input:focus, .select:focus { border-color: #FF9B44; }
        
        .btn-primary { width: 100%; padding: 12px; background: #FF9B44; color: white; border: none; border-radius: 6px; font-weight: 700; cursor: pointer; transition: background 0.2s; }
        .btn-outline { background: white; border: 1px solid #FF9B44; color: #FF9B44; }

        /* 6. Invoice Table */
        .invoice-grid { display: grid; grid-template-columns: 40px 3fr 1fr 1.5fr 1fr 1.5fr 1.5fr 40px; gap: 10px; align-items: center; padding: 10px 0; border-bottom: 1px solid #f0f0f0; }
        .invoice-header-row { background: #f9fafb; border-bottom: 1px solid #e1e1e1; font-weight: 700; font-size: 11px; color: #999; text-transform: uppercase; padding: 12px 10px; }
        .item-input { padding: 8px; border: 1px solid #eee; border-radius: 4px; width: 100%; box-sizing: border-box; font-size: 13px; }
        
        /* 7. Totals Section */
        .totals-section { display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-top: 30px; }
        .notes-area { width: 100%; height: 80px; padding: 10px; border: 1px solid #ddd; border-radius: 6px; resize: none; outline: none; font-size: 13px; }
        .totals-card { background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e1e1e1; }

        /* 8. Table/Directory */
        .directory-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .directory-table th { text-align: left; padding: 12px 15px; background: white; border-bottom: 2px solid #f0f0f0; color: #999; font-size: 11px; text-transform: uppercase; }
        .directory-table td { padding: 12px 15px; border-bottom: 1px solid #f0f0f0; color: #333; }
        
        /* 9. Charts & Pipeline */
        .pipeline-card { background: #f8fafc; border: 1px solid #e1e1e1; padding: 20px; border-radius: 8px; text-align: center; border-top: 4px solid #ccc; }
        .p-blue { border-top-color: #3b82f6; } .p-orange { border-top-color: #f97316; } .p-green { border-top-color: #22c55e; } .p-red { border-top-color: #ef4444; } .p-gray { border-top-color: #9ca3af; }
        .chart-container { display: flex; align-items: flex-end; height: 250px; gap: 15px; padding-top: 30px; }
        .bar-col { flex: 1; display: flex; flex-direction: column; justify-content: flex-end; height: 100%; cursor: pointer; position: relative; }
        .bar { width: 100%; background: #fed7aa; border-radius: 4px 4px 0 0; transition: all 0.2s; position: relative; }
        .bar:hover { background: #FF9B44; }
        .bar-label { text-align: center; font-size: 10px; color: #999; margin-top: 5px; font-weight: 600; text-transform: uppercase; }
        .pie-chart { width: 200px; height: 200px; border-radius: 50%; position: relative; margin-bottom: 25px; background: conic-gradient(#FF9B44 0% 40%, #10b981 40% 70%, #3b82f6 70% 90%, #f43f5e 90% 100%); }
        .pie-hole { position: absolute; top: 15px; left: 15px; right: 15px; bottom: 15px; background: white; border-radius: 50%; display: flex; flex-direction: column; align-items: center; justify-content: center; }
    </style>

    <div class="sales-main-wrapper">
        <div class="sales-container">
            <div class="sales-header">
                <h1 class="sales-title">Sales Manager</h1>
                <div class="sales-breadcrumb">
                    Dashboard / <span style="color: #FF9B44; font-weight: bold;">Sales</span>
                </div>
            </div>

            <div class="sales-tabs">
                <?php foreach ($tabs as $tab): ?>
                    <a href="?tab=<?php echo $tab['id']; ?>" class="tab-btn <?php echo $activeTab === $tab['id'] ? 'active' : ''; ?>">
                        <i data-lucide="<?php echo $tab['icon']; ?>" size="18"></i> 
                        <?php echo $tab['label']; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="fade-in">
                <?php if ($activeTab === 'pipeline'): ?>
                    <div class="card">
                        <div class="grid-4" style="margin-bottom: 30px;">
                            <div><label class="label">Invoice Number</label><input class="input" style="font-weight: bold; color: #555;" value="INV-2026-014"></div>
                            <div><label class="label">Client Name</label>
                                <select class="select">
                                    <option>Select Client</option>
                                    <option>Global Corp</option>
                                    <option>Tech Solutions</option>
                                </select>
                            </div>
                            <div><label class="label">Receiving Bank</label><input class="input" placeholder="Bank Name"></div>
                            <div><label class="label">Invoice Date</label><input type="date" class="input" value="<?php echo date('Y-m-d'); ?>"></div>
                        </div>

                        <h3 class="card-header"><i data-lucide="file-text" color="#FF9B44"></i> Invoice Items</h3>
                        <div style="border: 1px solid #eee; border-radius: 8px; overflow: hidden;">
                            <div class="invoice-grid invoice-header-row">
                                <span style="text-align: center;">#</span><span>DESCRIPTION</span><span>QTY</span><span>RATE</span><span>GST %</span><span>GST AMT</span><span>TOTAL</span><span></span>
                            </div>
                            <div class="invoice-grid" style="padding: 10px;">
                                <span style="text-align: center; font-weight: bold; color: #ccc;">1</span>
                                <input class="item-input" placeholder="Item description">
                                <input class="item-input" type="number" value="1">
                                <input class="item-input" type="number" placeholder="0.00">
                                <input class="item-input" type="number" value="18">
                                <input class="item-input" style="background: #f9fafb;" disabled value="0.00">
                                <input class="item-input" style="background: #f9fafb;" disabled value="0.00">
                                <button style="border: none; background: none; cursor: pointer; color: #ef4444;"><i data-lucide="x" size="18"></i></button>
                            </div>
                        </div>
                        
                        <div class="totals-section">
                            <div><label class="label">Payment Terms</label><textarea class="notes-area">Payment due within 15 days</textarea></div>
                            <div class="totals-card">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 10px;"><span>Subtotal:</span> <strong>₹0.00</strong></div>
                                <div style="display: flex; justify-content: space-between; border-top: 1px solid #ddd; padding-top: 10px; font-size: 18px; font-weight: 800;"><span>Grand Total:</span> <span>₹0.00</span></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'executive'): ?>
                    <div class="card">
                        <div class="grid-2">
                            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e1e1e1;">
                                <h3 class="card-header"><i data-lucide="plus-circle" color="#FF9B44"></i> Add New Client</h3>
                                <div class="form-group"><input class="input" placeholder="Client Name"></div>
                                <div class="form-group"><input class="input" placeholder="Phone Number"></div>
                                <button class="btn-primary btn-outline">Save Permanently</button>
                            </div>
                            <div style="background: #f8fafc; padding: 20px; border-radius: 8px; border: 1px solid #e1e1e1;">
                                <h3 class="card-header"><i data-lucide="users" color="#FF9B44"></i> Set Target & Allot</h3>
                                <div class="form-group"><select class="select"><option>Select Saved Client</option><?php foreach($clientList as $c) echo "<option>{$c['name']}</option>"; ?></select></div>
                                <button class="btn-primary">Assign Project</button>
                            </div>
                        </div>
                        <div class="grid-2" style="margin-top:30px;">
                            <div style="border:1px solid #e1e1e1; border-radius:8px; overflow:hidden;">
                                <table class="directory-table">
                                    <thead><tr><th>Name</th><th>Phone</th><th>Email</th></tr></thead>
                                    <tbody><?php foreach($clientList as $c): ?><tr><td><?php echo $c['name']; ?></td><td><?php echo $c['phone']; ?></td><td><?php echo $c['email']; ?></td></tr><?php endforeach; ?></tbody>
                                </table>
                            </div>
                            <div style="padding:15px; background:#f8fafc; border:1px solid #e1e1e1; border-radius:8px;">
                                <strong>Current Allotments</strong>
                                <?php foreach($assignments as $a): ?>
                                <div style="background:white; padding:15px; border-radius:6px; border-left:4px solid #FF9B44; margin-top:10px;">
                                    <div style="display:flex; justify-content:space-between;"><strong><?php echo $a['client']; ?></strong><span><?php echo $a['employee']; ?></span></div>
                                    <div style="font-size:12px; color:#666;">Target: ₹<?php echo $a['target']; ?></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($activeTab === 'lead'): ?>
                    <div class="card">
                        <div class="grid-4" style="margin-bottom: 40px;">
                            <?php foreach ($pipelineStages as $stage): ?>
                            <div class="pipeline-card <?php echo $stage['color']; ?>">
                                <p class="label"><?php echo $stage['label']; ?></p>
                                <h3 style="color: <?php echo $stage['text']; ?>"><?php echo number_format($stage['count']); ?></h3>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="grid-2">
                            <div style="border:1px solid #e1e1e1; border-radius:8px; padding:20px;">
                                <h3 class="card-header"><i data-lucide="bar-chart-3" color="#FF9B44"></i> Monthly Lead Analysis</h3>
                                <div class="chart-container">
                                    <?php $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                                    foreach($months as $i => $m): $h = rand(30, 95); ?>
                                    <div class="bar-col">
                                        <div class="bar" style="height: <?php echo $h; ?>%;"></div>
                                        <div class="bar-label"><?php echo $m; ?></div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div style="border:1px solid #e1e1e1; border-radius:8px; padding:20px; display:flex; flex-direction:column; align-items:center;">
                                <h3 class="card-header" style="align-self: flex-start;"><i data-lucide="mouse-pointer-2" color="#FF9B44"></i> Leads by Source</h3>
                                <div class="pie-chart"><div class="pie-hole"><span style="font-size:28px; font-weight:800;">8.2k</span></div></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div> <script>
    lucide.createIcons();
</script>

</body>
</html>