<?php include '../include/header.php'; ?>

<div style="display: flex;"> <?php include '../include/sidebar.php'; ?>

    <?php
    // 1. DATA INITIALIZATION (Simulating React State and Props)
    $activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

    $tabs = [
        ['id' => 'dashboard', 'label' => 'Overview', 'icon' => 'trending-up'],
        ['id' => 'jobs', 'label' => 'Job Board', 'icon' => 'briefcase'],
        ['id' => 'pipeline', 'label' => 'Candidate Pipeline', 'icon' => 'users'],
        ['id' => 'interviews', 'label' => 'Interviews', 'icon' => 'calendar'],
    ];

    $stats = [
        ['label' => 'Active Jobs', 'val' => '08', 'icon' => 'briefcase', 'bg' => 'bg-blue'],
        ['label' => 'Total Applicants', 'val' => '245', 'icon' => 'users', 'bg' => 'bg-purple'],
        ['label' => 'In Interview', 'val' => '14', 'icon' => 'calendar', 'bg' => 'bg-orange'],
        ['label' => 'Hired (Month)', 'val' => '06', 'icon' => 'check-circle', 'bg' => 'bg-green'],
    ];

    $activities = [
        ['text' => 'John Doe applied for Senior Developer', 'time' => '2 hours ago'],
        ['text' => 'Sarah Connor moved to Interview Stage', 'time' => '4 hours ago'],
        ['text' => 'Offer Letter sent to Mike Ross', 'time' => 'Yesterday'],
    ];

    $pending_actions = [
        ['label' => 'Review 12 new resumes', 'urgent' => true],
        ['label' => 'Schedule final round for UI Designer', 'urgent' => true],
        ['label' => 'Update job description for QA Tester', 'urgent' => false],
    ];

    $jobs = [
        ['title' => 'Senior React Developer', 'dept' => 'Engineering', 'type' => 'Full-time', 'apps' => 45, 'status' => 'Active'],
        ['title' => 'UI/UX Designer', 'dept' => 'Design', 'type' => 'Remote', 'apps' => 28, 'status' => 'Active'],
        ['title' => 'Product Manager', 'dept' => 'Product', 'type' => 'Full-time', 'apps' => 12, 'status' => 'Closed'],
        ['title' => 'Marketing Specialist', 'dept' => 'Marketing', 'type' => 'Contract', 'apps' => 34, 'status' => 'Active'],
    ];

    $pipeline_stages = [
        ['label' => 'Applied', 'count' => 12, 'class' => 'col-blue'],
        ['label' => 'Screening', 'count' => 5, 'class' => 'col-purple'],
        ['label' => 'Interview', 'count' => 3, 'class' => 'col-orange'],
        ['label' => 'Offer Sent', 'count' => 1, 'class' => 'col-green'],
        ['label' => 'Rejected', 'count' => 8, 'class' => 'col-red'],
    ];

    $interviews = [
        ['name' => 'Alice Walker', 'role' => 'UI Designer', 'time' => '10:00 AM', 'date' => '28', 'type' => 'Technical Round'],
        ['name' => 'Bob Martin', 'role' => 'Backend Dev', 'time' => '02:00 PM', 'date' => '28', 'type' => 'HR Round'],
        ['name' => 'Charlie Day', 'role' => 'Product Manager', 'time' => '11:00 AM', 'date' => '29', 'type' => 'Final Round'],
    ];
    ?>

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* Layout Reset and Sidebar Integration */
        .rec-main-wrapper {
            flex: 1; /* Automatically takes up remaining space next to sidebar */
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
            overflow-x: hidden;
        }

        .rec-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Header */
        .rec-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; flex-wrap: wrap; gap: 20px; }
        .rec-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .rec-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }
        
        .btn-primary {
            background-color: #FF9B44; color: white; border: none; padding: 12px 24px;
            border-radius: 8px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px;
            box-shadow: 0 4px 10px rgba(255, 155, 68, 0.2); transition: background 0.2s; text-decoration: none;
        }

        /* Tabs */
        .rec-tabs { display: flex; gap: 10px; margin-bottom: 30px; overflow-x: auto; padding-bottom: 5px; }
        .tab-btn {
            display: flex; align-items: center; gap: 8px; padding: 10px 20px;
            background: white; border: 1px solid #e1e1e1; border-radius: 8px;
            color: #666; font-weight: 600; font-size: 14px; cursor: pointer; white-space: nowrap;
            transition: all 0.2s; text-decoration: none;
        }
        .tab-btn.active { background: #FF9B44; color: white; border-color: #FF9B44; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.3); }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: white; padding: 20px; border-radius: 12px; border: 1px solid #e1e1e1; display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .bg-blue { background: #eff6ff; color: #2563eb; }
        .bg-purple { background: #f3e8ff; color: #9333ea; }
        .bg-orange { background: #fff7ed; color: #ea580c; }
        .bg-green { background: #f0fdf4; color: #16a34a; }
        .stat-label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #9ca3af; display: block; margin-bottom: 4px; }
        .stat-val { font-size: 24px; font-weight: 800; color: #333; margin: 0; }

        /* Split Layout */
        .split-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 30px; }
        @media (max-width: 900px) { .split-grid { grid-template-columns: 1fr; } }

        .content-card { background: white; padding: 25px; border-radius: 12px; border: 1px solid #e1e1e1; box-shadow: 0 4px 15px rgba(0,0,0,0.03); }
        .activity-item { display: flex; gap: 15px; padding-bottom: 15px; border-bottom: 1px solid #f0f0f0; margin-bottom: 15px; }
        .dot { width: 8px; height: 8px; background: #FF9B44; border-radius: 50%; margin-top: 6px; }
        .action-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border: 1px solid #e1e1e1; border-radius: 8px; margin-bottom: 10px; }
        .urgent-badge { background: #fef2f2; color: #dc2626; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; border: 1px solid #fecaca; }

        /* Job Board */
        .job-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .job-card { background: white; border: 1px solid #e1e1e1; border-radius: 12px; padding: 25px; transition: transform 0.2s; }
        .job-status { font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 4px 8px; border-radius: 4px; border: 1px solid; }
        .status-active { background: #f0fdf4; color: #16a34a; border-color: #dcfce7; }

        /* Pipeline */
        .pipeline-wrapper { display: flex; gap: 20px; overflow-x: auto; padding-bottom: 20px; }
        .pipeline-col { min-width: 280px; flex: 1; background: #f8fafc; border: 1px solid #e1e1e1; border-radius: 12px; display: flex; flex-direction: column; }
        .col-header { padding: 15px; border-bottom: 1px solid #e1e1e1; background: white; border-radius: 12px 12px 0 0; border-top: 4px solid #ccc; display: flex; justify-content: space-between; align-items: center; }
        .col-blue { border-top-color: #3b82f6; } .col-purple { border-top-color: #9333ea; } 
        .col-orange { border-top-color: #f97316; } .col-green { border-top-color: #22c55e; } .col-red { border-top-color: #ef4444; }

        /* Interviews */
        .int-item { display: flex; align-items: center; gap: 15px; padding: 15px; background: white; border: 1px solid #e1e1e1; border-radius: 8px; margin-bottom: 15px; }
        .date-box { width: 50px; height: 50px; background: #fff0e0; color: #FF9B44; border-radius: 8px; display: flex; flex-direction: column; align-items: center; justify-content: center; line-height: 1; }
        .form-select, .form-input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 14px; outline: none; background: white; box-sizing: border-box; margin-bottom: 15px; }
        .schedule-btn { width: 100%; padding: 12px; background: #FF9B44; color: white; font-weight: 700; border: none; border-radius: 6px; cursor: pointer; }
    </style>

    <div class="rec-main-wrapper">
        <div class="rec-container">
            <div class="rec-header">
                <div>
                    <h1 class="rec-title">Recruitment & ATS</h1>
                    <div class="rec-breadcrumb">
                        Dashboard / <span style="color:#FF9B44; font-weight:bold;">Recruitment</span>
                    </div>
                </div>
                <a href="#" class="btn-primary">
                    <i data-lucide="plus"></i> Post New Job
                </a>
            </div>

            <div class="rec-tabs">
                <?php foreach ($tabs as $tab): ?>
                    <a href="?tab=<?php echo $tab['id']; ?>" 
                       class="tab-btn <?php echo $activeTab === $tab['id'] ? 'active' : ''; ?>">
                        <i data-lucide="<?php echo $tab['icon']; ?>" size="18"></i> 
                        <?php echo $tab['label']; ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <?php if ($activeTab === 'dashboard'): ?>
                <div class="stats-grid">
                    <?php foreach ($stats as $s): ?>
                        <div class="stat-card">
                            <div class="stat-icon <?php echo $s['bg']; ?>"><i data-lucide="<?php echo $s['icon']; ?>"></i></div>
                            <div><span class="stat-label"><?php echo $s['label']; ?></span><h2 class="stat-val"><?php echo $s['val']; ?></h2></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="split-grid">
                    <div class="content-card">
                        <h3 class="card-title">Recent Activities</h3>
                        <?php foreach ($activities as $act): ?>
                            <div class="activity-item">
                                <div class="dot"></div>
                                <div>
                                    <p class="act-text"><?php echo $act['text']; ?></p>
                                    <p class="act-time"><?php echo $act['time']; ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="content-card">
                        <h3 class="card-title">Pending Actions</h3>
                        <?php foreach ($pending_actions as $task): ?>
                            <div class="action-item">
                                <span style="font-size:13px; color:#333; font-weight:500;"><?php echo $task['label']; ?></span>
                                <?php if ($task['urgent']): ?><span class="urgent-badge">URGENT</span><?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($activeTab === 'jobs'): ?>
                <div class="job-grid">
                    <?php foreach ($jobs as $job): ?>
                        <div class="job-card">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start">
                                <span class="job-status <?php echo $job['status'] === 'Active' ? 'status-active' : 'status-closed'; ?>">
                                    <?php echo $job['status']; ?>
                                </span>
                                <i data-lucide="more-vertical" style="color:#999; cursor:pointer"></i>
                            </div>
                            <h3 style="font-size: 18px; margin: 10px 0 5px 0;"><?php echo $job['title']; ?></h3>
                            <p style="font-size:13px; color:#666; margin-bottom: 20px;">
                                <?php echo $job['dept']; ?> • <?php echo $job['type']; ?>
                            </p>
                            <div style="border-top: 1px solid #f0f0f0; padding-top:15px; display:flex; justify-content:space-between">
                                <span style="font-size:13px; font-weight:600;"><i data-lucide="users" size="14"></i> <?php echo $job['apps']; ?> Applicants</span>
                                <span style="color:#FF9B44; font-weight:700; font-size:13px; cursor:pointer">View Details</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($activeTab === 'pipeline'): ?>
                <div class="pipeline-wrapper">
                    <?php foreach ($pipeline_stages as $stage): ?>
                        <div class="pipeline-col">
                            <div class="col-header <?php echo $stage['class']; ?>">
                                <h4 style="margin:0"><?php echo $stage['label']; ?></h4>
                                <span class="col-count"><?php echo $stage['count']; ?></span>
                            </div>
                            <div style="padding:15px">
                                <div style="background:white; padding:15px; border-radius:8px; border:1px solid #e1e1e1; margin-bottom:15px">
                                    <span style="font-weight:700; display:block">Example Candidate</span>
                                    <span style="font-size:12px; color:#888">Sample Role</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($activeTab === 'interviews'): ?>
                <div class="split-grid">
                    <div class="content-card">
                        <h3 class="card-title">Upcoming Interviews</h3>
                        <?php foreach ($interviews as $int): ?>
                            <div class="int-item">
                                <div class="date-box">
                                    <span style="font-size:18px; font-weight:800"><?php echo $int['date']; ?></span>
                                    <span style="font-size:10px; font-weight:700">OCT</span>
                                </div>
                                <div style="flex:1">
                                    <h4 style="margin:0"><?php echo $int['name']; ?></h4>
                                    <p style="font-size:12px; color:#666"><?php echo $int['role']; ?> • <span style="color:#FF9B44"><?php echo $int['type']; ?></span></p>
                                </div>
                                <div style="text-align:right">
                                    <span style="font-size:12px; font-weight:700"><?php echo $int['time']; ?></span><br>
                                    <button style="background:#FF9B44; color:white; border:none; padding:4px 8px; border-radius:4px; font-size:11px; font-weight:700">Join</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="content-card" style="height:fit-content">
                        <h3 class="card-title">Quick Schedule</h3>
                        <form action="" method="POST">
                            <label style="font-size:11px; font-weight:700; color:#666">Candidate</label>
                            <select class="form-select"><option>Select...</option></select>
                            <label style="font-size:11px; font-weight:700; color:#666">Date & Time</label>
                            <input type="datetime-local" class="form-input">
                            <button type="button" class="schedule-btn">Schedule Interview</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div> <script>
    lucide.createIcons();
</script>