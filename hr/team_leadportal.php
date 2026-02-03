<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<?php
// --- MOCK DATA ---
$teamProgress = [
    ["id" => 1, "name" => "Arun", "task" => "Frontend Dashboard Integration", "progress" => 85, "status" => "On Track"],
    ["id" => 2, "name" => "Priya", "task" => "Database Security Patching", "progress" => 60, "status" => "In Progress"],
    ["id" => 3, "name" => "John", "task" => "API Documentation", "progress" => 100, "status" => "Completed"],
    ["id" => 4, "name" => "Sarah", "task" => "User Authentication Flow", "progress" => 35, "status" => "Delayed"],
    ["id" => 5, "name" => "Deepak", "task" => "Bug Fixing - Login Module", "progress" => 75, "status" => "On Track"],
];

// --- STATUS HELPER FUNCTION ---
function getStatusColor($status) {
    switch($status) {
        case "Completed": 
            return ["bg" => "#dcfce7", "text" => "#166534", "border" => "#bbf7d0", "icon" => "check-circle"];
        case "Delayed": 
            return ["bg" => "#fee2e2", "text" => "#991b1b", "border" => "#fecaca", "icon" => "alert-circle"];
        default: 
            return ["bg" => "#ffedd5", "text" => "#9a3412", "border" => "#fed7aa", "icon" => "clock"];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Lead Portal</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <style>
        /* 1. Main Layout */
        .tl-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
            box-sizing: border-box;
        }

        /* 2. Header Area */
        .tl-header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 20px;
        }
        .tl-title {
            font-size: 36px;
            font-weight: 800;
            color: #1a1a1a;
            margin: 0;
            letter-spacing: -0.5px;
        }

        /* 3. Team Strength Box */
        .tl-stat-box {
            background: white;
            padding: 20px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            min-width: 250px;
            border: 1px solid #e1e1e1;
        }
        .tl-icon-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #fff0e0;
            color: #FF9B44;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* 4. Breadcrumb & Section Title */
        .tl-breadcrumb {
            margin-bottom: 50px;
        }
        .tl-bread-badge {
            display: inline-block;
            background: white;
            padding: 10px 20px;
            border-radius: 8px;
            border: 1px solid #e1e1e1;
            color: #666;
            font-size: 14px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }
        .tl-section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 25px;
            padding-left: 10px;
        }

        /* 5. Immersive Table Styling */
        .tl-table-wrapper {
            overflow-x: auto;
        }
        .immersive-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 24px;
            min-width: 900px;
        }
        .immersive-row {
            background: white;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            border-radius: 16px; 
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .immersive-row:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0,0,0,0.08);
            position: relative;
            z-index: 10;
        }
        .immersive-cell {
            padding: 24px 30px;
            vertical-align: middle;
            border-top: 1px solid #f3f4f6;
            border-bottom: 1px solid #f3f4f6;
            color: #333;
        }
        .immersive-row td:first-child {
            border-top-left-radius: 16px;
            border-bottom-left-radius: 16px;
            border-left: 1px solid #f3f4f6;
        }
        .immersive-row td:last-child {
            border-top-right-radius: 16px;
            border-bottom-right-radius: 16px;
            border-right: 1px solid #f3f4f6;
        }

        /* 6. Headers */
        .th-text {
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #9ca3af;
            text-transform: uppercase;
            padding: 0 30px 10px 30px;
            letter-spacing: 0.5px;
        }

        /* 7. Components inside table */
        .user-flex { display: flex; align-items: center; gap: 20px; }
        .user-avatar {
            width: 45px; height: 45px; border-radius: 50%;
            background: #f9fafb; border: 1px solid #e5e7eb;
            display: flex; align-items: center; justify-content: center; color: #9ca3af;
        }
        .task-badge {
            display: inline-flex; align-items: center; gap: 10px;
            background: #f9fafb; padding: 10px 15px; border-radius: 8px;
            border: 1px solid #e5e7eb; font-size: 13px; font-weight: 600; color: #374151;
        }
        
        .progress-track {
            background: #f3f4f6;
            border-radius: 8px;
            height: 8px;
            width: 100%;
            overflow: hidden;
            flex-grow: 1;
        }
        .progress-fill {
            height: 100%;
            border-radius: 8px;
            transition: width 0.6s ease-in-out;
        }
        
        .status-pill {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 6px 14px; border-radius: 20px;
            font-size: 12px; font-weight: 700; border: 1px solid transparent;
        }
        
        .action-btn {
            background: none; border: none; cursor: pointer;
            color: #9ca3af; padding: 10px; border-radius: 50%;
            transition: all 0.2s;
        }
        .action-btn:hover { background: #f3f4f6; color: #FF9B44; }
    </style>
</head>
<body>

    <div class="tl-container">
        <div class="tl-header-row">
            <div> 
                <h1 class="tl-title">Team Lead Portal</h1>
            </div>
            
            <div class="tl-stat-box">
                <div class="tl-icon-circle">
                   <i data-lucide="users"></i>
                </div>
                <div>
                    <div style="font-size: 11px; font-weight: bold; text-transform: uppercase; color: #999; margin-bottom: 5px;">
                        Team Strength
                    </div>
                    <div style="font-size: 24px; font-weight: 800; color: #333;">
                        <?php echo count($teamProgress); ?> <span style="font-size: 16px; font-weight: 500; color: #666;">Members</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="tl-breadcrumb">
            <div class="tl-bread-badge">
                Dashboard / <span style="color: #FF9B44;">Team Overview</span>
            </div>
        </div>

        <div class="tl-section-header">
            <i data-lucide="activity" style="color: #FF9B44;"></i>
            <h3 style="font-size: 20px; font-weight: bold; margin: 0;">Active Sprints & Tasks</h3>
        </div>

        <div class="tl-table-wrapper">
            <table class="immersive-table">
                <thead>
                    <tr>
                        <th class="th-text">Employee</th>
                        <th class="th-text">Current Assignment</th>
                        <th class="th-text" style="width: 25%;">Progress</th>
                        <th class="th-text">Status</th>
                        <th class="th-text" style="text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teamProgress as $emp): 
                        $style = getStatusColor($emp['status']);
                    ?>
                    <tr class="immersive-row">
                        <td class="immersive-cell">
                            <div class="user-flex"> 
                                <div class="user-avatar">
                                    <i data-lucide="user"></i>
                                </div>
                                <div>
                                    <span style="display: block; font-weight: bold; font-size: 15px;"><?php echo htmlspecialchars($emp['name']); ?></span>
                                    <span style="font-size: 12px; color: #999; font-weight: 600; text-transform: uppercase;">Developer</span>
                                </div>
                            </div>
                        </td>

                        <td class="immersive-cell">
                            <div class="task-badge">
                                <i data-lucide="briefcase" style="color: #FF9B44; width:16px; height:16px;"></i>
                                <?php echo htmlspecialchars($emp['task']); ?>
                            </div>
                        </td>

                        <td class="immersive-cell">
                            <div style="display: flex; align-items: center; gap: 15px;">
                                <div class="progress-track">
                                    <div 
                                        class="progress-fill" 
                                        style="width: <?php echo $emp['progress']; ?>%; background: <?php echo $emp['progress'] == 100 ? '#10B981' : 'linear-gradient(90deg, #FF9B44 0%, #F59E0B 100%)'; ?>;"
                                    ></div>
                                </div>
                                <span style="font-size: 13px; font-weight: bold; color: #555; min-width: 35px;"><?php echo $emp['progress']; ?>%</span>
                            </div>
                        </td>

                        <td class="immersive-cell">
                            <span 
                                class="status-pill"
                                style="background-color: <?php echo $style['bg']; ?>; color: <?php echo $style['text']; ?>; border-color: <?php echo $style['border']; ?>;"
                            >
                                <i data-lucide="<?php echo $style['icon']; ?>" style="width: 14px; height: 14px;"></i>
                                <?php echo htmlspecialchars($emp['status']); ?>
                            </span>
                        </td>

                        <td class="immersive-cell" style="text-align: right;">
                            <button class="action-btn">
                                <i data-lucide="more-horizontal"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Initialize Lucide Icons
        lucide.createIcons();
    </script>
</body>
</html>