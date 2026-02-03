<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<?php
// --- SIMULATED CONTEXT & DATA ---
// In a real app, these would come from a Database or Session
$user = ['role' => 'Manager']; // Change to 'TL' to see role-based view

$unassigned = [
    ['id' => 'EMP-NEW-1', 'name' => 'Rohan (New)', 'role' => 'Jr. Dev', 'joined' => 'Yesterday']
];

$teamData = [
    [
        'id' => 'TL-01', 'name' => 'Karthik (TL)',
        'members' => [
            ['id' => 'E-101', 'name' => 'Varshith', 'role' => 'Dev', 'efficiency' => 92, 'status' => 'Active'],
            ['id' => 'E-102', 'name' => 'Aditi Rao', 'role' => 'Designer', 'efficiency' => 88, 'status' => 'Active']
        ]
    ],
    [
        'id' => 'TL-02', 'name' => 'Sarah (TL)',
        'members' => [
            ['id' => 'E-103', 'name' => 'Sanjay', 'role' => 'DevOps', 'efficiency' => 45, 'status' => 'Active']
        ]
    ]
];

$events = [
    ['id' => 1, 'name' => 'Varshith', 'type' => 'Birthday', 'role' => 'Software Developer', 'date' => '30-Jan-2026'],
    ['id' => 2, 'name' => 'Sarah (TL)', 'type' => 'Work Anniversary', 'role' => 'Team Lead', 'date' => '31-Jan-2026']
];

// Logic for Role-based visibility
$visibleTeams = ($user['role'] === 'TL') ? array_filter($teamData, fn($t) => $t['id'] === 'TL-01') : $teamData;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Dashboard</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .mgr-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: #f4f7fc;
            min-height: 100vh;
            padding: 40px;
            color: #333;
        }
        .mgr-header { margin-bottom: 40px; }
        .mgr-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .mgr-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }
        .mgr-card { background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); padding: 25px; margin-bottom: 30px; border: 1px solid #e1e1e1; }
        .mgr-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px; }
        .mgr-section-title { font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 10px; color: #333; }
        .wishes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
        .wish-card { background: #f9fafb; border: 1px solid #eee; border-radius: 10px; padding: 15px; display: flex; align-items: center; gap: 15px; transition: transform 0.2s; }
        .wish-card:hover { transform: translateY(-2px); background: white; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .wish-avatar { width: 45px; height: 45px; border-radius: 50%; background: #fff0e0; color: #FF9B44; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; }
        .wish-tag { font-size: 10px; text-transform: uppercase; padding: 2px 6px; background: #fff0e0; color: #c2410c; border-radius: 4px; font-weight: bold; }
        .hire-card { display: flex; justify-content: space-between; align-items: center; background: #eff6ff; border: 1px solid #dbeafe; padding: 15px; border-radius: 10px; margin-bottom: 10px; }
        .hire-select { padding: 8px; border-radius: 6px; border: 1px solid #bfdbfe; background: white; font-size: 12px; outline: none; }
        .team-accordion { border: 1px solid #eee; border-radius: 10px; overflow: hidden; margin-bottom: 15px; }
        .accordion-header { padding: 15px 20px; background: #f8fafc; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-weight: 700; color: #333; transition: background 0.2s; }
        .accordion-header:hover { background: #f1f5f9; }
        .inner-table-wrapper { overflow-x: auto; }
        .inner-table { width: 100%; border-collapse: collapse; min-width: 700px; }
        .inner-table th { text-align: left; padding: 12px 20px; background: white; color: #94a3b8; font-size: 12px; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid #eee; }
        .inner-table td { padding: 15px 20px; border-bottom: 1px solid #f8f9fa; font-size: 14px; color: #333; vertical-align: middle; }
        .efficiency-bar-bg { width: 100px; height: 6px; background: #e2e8f0; border-radius: 10px; overflow: hidden; }
        .efficiency-fill { height: 100%; border-radius: 10px; }
        .status-badge { font-size: 11px; font-weight: bold; padding: 4px 10px; border-radius: 20px; background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .mgr-input { padding: 10px; border: 1px solid #ddd; border-radius: 8px; width: 100%; outline: none; font-size: 14px; }
        .mgr-input:focus { border-color: #FF9B44; }
        .hidden { display: none; }
    </style>
</head>
<body>

<div class="mgr-container">
    <div class="mgr-header">
        <h2 class="mgr-title">Global Manager Portal</h2>
        <div class="mgr-breadcrumb">
            Dashboard / <span style="color: #FF9B44; font-weight: bold;">Performance & Allocations</span>
        </div>
    </div>

    <div class="mgr-card">
        <div class="mgr-card-header">
            <h4 class="mgr-section-title">
                <i data-lucide="gift" style="color:#FF9B44; width:20px;"></i> Automated Wishes & Events
            </h4>
            <div style="position:relative; width: 250px;">
                <i data-lucide="search" style="position:absolute; left: 10px; top: 12px; color: #999; width:16px;"></i>
                <input type="text" placeholder="Search..." class="mgr-input" style="padding-left: 35px;">
            </div>
        </div>
        
        <div class="wishes-grid">
            <?php foreach ($events as $event): ?>
            <div class="wish-card">
                <div class="wish-avatar"><?php echo htmlspecialchars($event['name'][0]); ?></div>
                <div style="flex: 1">
                    <div style="display:flex; justify-content:space-between; margin-bottom:5px">
                        <strong style="font-size:14px"><?php echo htmlspecialchars($event['name']); ?></strong>
                        <span class="wish-tag"><?php echo htmlspecialchars($event['type']); ?></span>
                    </div>
                    <div style="font-size:12px; color:#666">
                        <div style="display:flex; align-items:center; gap:5px; margin-bottom:2px">
                            <i data-lucide="briefcase" style="width:12px;"></i> <?php echo htmlspecialchars($event['role']); ?>
                        </div>
                        <div style="display:flex; align-items:center; gap:5px; font-weight:bold; color:#333">
                            <i data-lucide="calendar" style="width:12px;"></i> <?php echo htmlspecialchars($event['date']); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if ($user['role'] === 'Manager' && !empty($unassigned)): ?>
    <div class="mgr-card">
        <div class="mgr-section-title" style="margin-bottom: 20px;">
            <i data-lucide="user-plus" style="color:#FF9B44; width:20px;"></i> New Hires (From HR)
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
            <?php foreach ($unassigned as $emp): ?>
            <div class="hire-card">
                <div>
                    <div style="font-weight:bold; font-size:14px"><?php echo htmlspecialchars($emp['name']); ?></div>
                    <div style="font-size:12px; color:#2563eb"><?php echo htmlspecialchars($emp['role']); ?></div>
                </div>
                <select class="hire-select">
                    <option disabled selected>Assign TL...</option>
                    <?php foreach ($teamData as $tl): ?>
                        <option value="<?php echo $tl['id']; ?>"><?php echo htmlspecialchars($tl['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="mgr-card">
        <div class="mgr-section-title" style="margin-bottom: 25px;">
            <i data-lucide="users" style="color:#FF9B44; width:20px;"></i> Team Structure & Efficiency
        </div>
        
        <?php foreach ($visibleTeams as $tl): ?>
        <div class="team-accordion">
            <div class="accordion-header" onclick="toggleAccordion('<?php echo $tl['id']; ?>')">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="chevron-right" id="icon-<?php echo $tl['id']; ?>" style="width:18px; color:#666;"></i>
                    <?php echo htmlspecialchars($tl['name']); ?> 
                </div>
                <span style="font-size:12px; background:white; border:1px solid #ddd; padding:2px 8px; border-radius:4px; color:#666">
                    <?php echo count($tl['members']); ?> Members
                </span>
            </div>
            
            <div id="content-<?php echo $tl['id']; ?>" class="hidden inner-table-wrapper">
                <table class="inner-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Efficiency %</th>
                            <th>Status</th>
                            <th>Update</th>
                            <?php if ($user['role'] === 'Manager'): ?><th>Swap Team</th><?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tl['members'] as $emp): ?>
                        <tr>
                            <td>
                                <div style="font-weight:bold; color:#333"><?php echo htmlspecialchars($emp['name']); ?></div>
                                <div style="font-size:12px; color:#888"><?php echo htmlspecialchars($emp['role']); ?></div>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px">
                                    <div class="efficiency-bar-bg">
                                        <?php 
                                            $eff = $emp['efficiency'];
                                            $color = $eff > 80 ? '#22c55e' : ($eff > 50 ? '#f59e0b' : '#ef4444');
                                        ?>
                                        <div class="efficiency-fill" style="width: <?php echo $eff; ?>%; background: <?php echo $color; ?>;"></div>
                                    </div>
                                    <span style="font-size:12px; font-weight:bold; color:#666"><?php echo $eff; ?>%</span>
                                </div>
                            </td>
                            <td><span class="status-badge"><?php echo htmlspecialchars($emp['status']); ?></span></td>
                            <td>
                                <button style="border:none; background:none; cursor:pointer; color:#999">
                                    <i data-lucide="edit-2" style="width:18px;"></i>
                                </button>
                            </td>
                            <?php if ($user['role'] === 'Manager'): ?>
                            <td>
                                <div style="position:relative; display:inline-block;">
                                    <select style="padding:6px 25px 6px 10px; font-size:12px; border:1px solid #ddd; border-radius:4px; appearance:none; background:white;">
                                        <option disabled selected>Switch to...</option>
                                        <?php foreach ($teamData as $target): ?>
                                            <?php if ($target['id'] !== $tl['id']): ?>
                                                <option><?php echo htmlspecialchars($target['name']); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <i data-lucide="arrow-right-left" style="position:absolute; right:8px; top:8px; width:12px; color:#999; pointer-events:none;"></i>
                                </div>
                            </td>
                            <?php endif; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Initialize Lucide Icons
    lucide.createIcons();

    // Accordion Logic
    function toggleAccordion(id) {
        const content = document.getElementById('content-' + id);
        const icon = document.getElementById('icon-' + id);
        
        const isHidden = content.classList.contains('hidden');
        
        if (isHidden) {
            content.classList.remove('hidden');
            icon.setAttribute('data-lucide', 'chevron-down');
        } else {
            content.classList.add('hidden');
            icon.setAttribute('data-lucide', 'chevron-right');
        }
        
        // Refresh icons for the specific element
        lucide.createIcons();
    }
</script>
</body>
</html>