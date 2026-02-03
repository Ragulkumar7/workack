<?php include '../include/header.php'; ?>

<div style="display: flex;"> <?php include '../include/sidebar.php'; ?>

    <?php
    // --- SIMULATED USER CONTEXT (Replacing useUser) ---
    // In a real app, this would come from your session or database
    $user = [
        'role' => 'Manager' 
    ];

    // --- MOCK DATA: RECENT REPORTS ---
    $recentReports = [
        ['id' => 1, 'name' => 'Monthly Payroll_Jan2026.pdf', 'type' => 'Financial', 'date' => '2026-02-01', 'size' => '1.2 MB', 'generatedBy' => 'Admin'],
        ['id' => 2, 'name' => 'Team_Attendance_Feb_W1.xlsx', 'type' => 'Attendance', 'date' => '2026-02-07', 'size' => '450 KB', 'generatedBy' => 'Karthik (TL)'],
        ['id' => 3, 'name' => 'Q1_Performance_Review.pdf', 'type' => 'Performance', 'date' => '2026-01-15', 'size' => '2.8 MB', 'generatedBy' => 'Admin'],
    ];

    // --- REPORT CATEGORIES CONFIG ---
    $reportCategories = [
        [
            'title' => 'Attendance & Leave',
            'description' => 'Monthly logs, LOP summary, and absentee reports.',
            'icon' => 'calendar',
            'styleClass' => 'icon-blue',
            'allowed' => ['Manager', 'TL']
        ],
        [
            'title' => 'Payroll & Finance',
            'description' => 'Salary slips, Tax deductions (TDS), and bank transfer sheets.',
            'icon' => 'file-spreadsheet',
            'styleClass' => 'icon-green',
            'allowed' => ['Manager'] 
        ],
        [
            'title' => 'Performance Analytics',
            'description' => 'Efficiency trends, project completion rates, and appraisals.',
            'icon' => 'trending-up',
            'styleClass' => 'icon-purple',
            'allowed' => ['Manager', 'TL']
        ],
        [
            'title' => 'Audit & Compliance',
            'description' => 'System logs, role changes, and access history.',
            'icon' => 'shield',
            'styleClass' => 'icon-orange',
            'allowed' => ['Manager']
        ]
    ];
    ?>

    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* Layout Adjustment: Removes the gap next to the sidebar */
        .rep-main-wrapper {
            flex: 1; /* Automatically fills the remaining width */
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .rep-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* 2. Header */
        .rep-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        .rep-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .rep-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }
        
        .print-btn {
            background: white; border: 1px solid #e1e1e1; color: #555;
            padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; gap: 8px; font-size: 13px;
            transition: all 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        .print-btn:hover { border-color: #FF9B44; color: #FF9B44; }

        /* 3. Quick Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 25px; margin-bottom: 40px; }
        .stat-card {
            background: white; padding: 25px; border-radius: 12px; border: 1px solid #e1e1e1;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02); display: flex; align-items: center; gap: 20px;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        
        .stat-icon-circle { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .icon-blue { background: #eff6ff; color: #2563eb; }
        .icon-green { background: #f0fdf4; color: #16a34a; }
        .icon-purple { background: #f3e8ff; color: #9333ea; }
        .icon-orange { background: #fff7ed; color: #ea580c; }

        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; display: block; margin-bottom: 5px; }
        .stat-value { font-size: 26px; font-weight: 800; color: #333; margin: 0; line-height: 1; }

        /* 4. Generate Reports Grid */
        .section-box { background: white; border-radius: 16px; border: 1px solid #e1e1e1; padding: 30px; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .section-title { font-size: 18px; font-weight: 700; color: #333; margin: 0 0 25px 0; display: flex; align-items: center; gap: 10px; }
        
        .gen-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; }
        .gen-card { background: white; border: 1px solid #eee; border-radius: 12px; padding: 25px; display: flex; flex-direction: column; height: 100%; transition: all 0.2s; box-sizing: border-box; }
        .gen-card:hover { border-color: #FF9B44; box-shadow: 0 10px 20px rgba(0,0,0,0.05); }
        
        .gen-title { font-weight: 700; color: #333; margin: 0 0 10px 0; font-size: 16px; }
        .gen-desc { font-size: 13px; color: #666; flex: 1; line-height: 1.5; margin-bottom: 20px; }
        
        .download-btn { width: 100%; padding: 12px; background: #FF9B44; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; transition: background 0.2s; }
        .download-btn:hover { background: #e88b3a; }
        
        .view-link { text-align: center; display: block; margin-top: 10px; font-size: 12px; color: #FF9B44; font-weight: 700; cursor: pointer; text-decoration: none; }
        .view-link:hover { text-decoration: underline; }

        /* 5. History Table */
        .table-header-row { display: flex; align-items: center; gap: 10px; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0; margin-bottom: 0; }
        .table-responsive { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th { text-align: left; padding: 15px 20px; background: #f9fafb; color: #666; font-size: 12px; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid #e5e7eb; }
        td { padding: 15px 20px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: #333; font-size: 14px; }
        
        .type-badge { padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; border: 1px solid; }
        .badge-finance { background: #f0fdf4; color: #16a34a; border-color: #dcfce7; }
        .badge-other { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
        
        .icon-btn { background: none; border: none; cursor: pointer; color: #9ca3af; padding: 5px; transition: color 0.2s; }
        .icon-btn:hover { color: #FF9B44; }

        @media print {
            .print-btn, .download-btn, .icon-btn, sidebar { display: none; }
            .rep-main-wrapper { padding: 0; margin: 0; background: white; }
        }
    </style>

    <div class="rep-main-wrapper">
        <div class="rep-container">
            
            <div class="rep-header">
                <div>
                    <h2 class="rep-title">Reports & Analytics</h2>
                    <div class="rep-breadcrumb">
                        Dashboard / <span style="color: #FF9B44; font-weight: bold;">Reports</span>
                    </div>
                </div>
                <button class="print-btn" onclick="window.print()">
                    <i data-lucide="printer" size="16"></i> Print View
                </button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-circle icon-blue">
                        <i data-lucide="file-text"></i>
                    </div>
                    <div>
                        <span class="stat-label">Reports Generated</span>
                        <h3 class="stat-value">124</h3>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-circle icon-green">
                        <i data-lucide="pie-chart"></i>
                    </div>
                    <div>
                        <span class="stat-label">Data Usage</span>
                        <h3 class="stat-value">450 MB</h3>
                    </div>
                </div>
            </div>

            <div class="section-box">
                <h4 class="section-title">
                    <i data-lucide="file-text" style="color: #FF9B44"></i> Generate New Report
                </h4>
                
                <div class="gen-grid">
                    <?php foreach ($reportCategories as $index => $cat): ?>
                        <?php if (in_array($user['role'], $cat['allowed'])): ?>
                            <div class="gen-card">
                                <div class="stat-icon-circle <?php echo $cat['styleClass']; ?>" style="width:40px; height:40px; marginBottom:15px">
                                    <i data-lucide="<?php echo $cat['icon']; ?>"></i>
                                </div>
                                <h5 class="gen-title"><?php echo $cat['title']; ?></h5>
                                <p class="gen-desc"><?php echo $cat['description']; ?></p>
                                
                                <button class="download-btn" onclick="handleDownload('cat-<?php echo $index; ?>', this)">
                                    Download CSV
                                </button>
                                
                                <?php if ($cat['title'] === 'Payroll & Finance'): ?>
                                    <span class="view-link">View PDF Summary</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="section-box" style="padding: 0; overflow: hidden;">
                <div class="table-header-row" style="padding: 20px">
                    <i data-lucide="download" style="color: #FF9B44; margin-left: 10px;"></i>
                    <h4 style="margin:0; font-weight:700; font-size:18px">Download History</h4>
                </div>
                
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Report Name</th>
                                <th>Category</th>
                                <th>Generated Date</th>
                                <th>Size</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentReports as $rep): ?>
                                <?php if ($user['role'] !== 'Manager' && $rep['type'] === 'Financial') continue; ?>
                                <tr>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px; font-weight:500">
                                            <i data-lucide="file-text" style="color: #9ca3af"></i> <?php echo $rep['name']; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="type-badge <?php echo ($rep['type'] === 'Financial') ? 'badge-finance' : 'badge-other'; ?>">
                                            <?php echo $rep['type']; ?>
                                        </span>
                                    </td>
                                    <td style="color:#666"><?php echo $rep['date']; ?></td>
                                    <td style="font-family:monospace; color:#666; font-size:12px"><?php echo $rep['size']; ?></td>
                                    <td>
                                        <button class="icon-btn" onclick="handleDownload(<?php echo $rep['id']; ?>, this)">
                                            <i data-lucide="download"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div> <script>
    // Initialize Lucide Icons
    lucide.createIcons();

    function handleDownload(reportId, btn) {
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = btn.classList.contains('icon-btn') ? '...' : 'Generating...';
        
        setTimeout(() => {
            btn.disabled = false;
            btn.innerHTML = originalText;
            alert("Report " + reportId + " downloaded successfully!");
        }, 1500);
    }
</script>

</body>
</html>