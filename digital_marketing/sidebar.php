<?php
// 1. MOCK USER DATA
$user = ['name' => 'John Doe', 'role' => 'Manager'];
$current_path = $_SERVER['REQUEST_URI'];

// 2. DEFINE MENU DATA
$sections = [
    [
        'label' => 'Main',
        'items' => [
            ['name' => 'Home', 'path' => '/', 'icon' => 'home', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Dashboard', 'path' => '/dashboard', 'icon' => 'layout-dashboard', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Team Lead', 'icon' => 'users', 'allowed' => ['TL', 'HR', 'Manager'], 
                'subItems' => [
                    ['name' => 'Overview', 'path' => '/teamlead/overview', 'icon' => 'layout-grid'], 
                    ['name' => 'Performance', 'path' => '/teamlead/performance', 'icon' => 'gauge']
                ]
            ],
            ['name' => 'HR Management', 'icon' => 'fingerprint', 'allowed' => ['Manager', 'HR'], 
                'subItems' => [
                    ['name' => 'Records', 'path' => '/hr/records', 'icon' => 'database']
                ]
            ],
            ['name' => 'Directory', 'path' => '/employees', 'icon' => 'book-user', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Manager', 'path' => '/manager', 'icon' => 'shield-check', 'allowed' => ['Manager', 'HR']],
        ]
    ],
    [
        'label' => 'Operations',
        'items' => [
            ['name' => 'Attendance', 'path' => '/EmployeeAttendance', 'icon' => 'calendar-check', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Tasks', 'path' => '/tasks', 'icon' => 'clipboard-list', 'allowed' => ['Manager', 'TL', 'Employee', 'HR', 'DM']],
            ['name' => 'Teams', 'path' => '/teams', 'icon' => 'message-square', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts']],
            ['name' => 'Payroll', 'path' => '/payroll', 'icon' => 'banknote', 'allowed' => ['Manager', 'Accounts']],
            ['name' => 'Recruitment', 'path' => '/recruitment', 'icon' => 'briefcase', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Digital Marketing', 'icon' => 'target', 'allowed' => ['DM', 'Manager'], 
                'subItems' => [
                    ['name' => 'Dashboard', 'path' => '/dm/dashboard', 'icon' => 'layout-dashboard'],
                    ['name' => 'Analytics', 'path' => '/dm/analytics', 'icon' => 'trending-up']
                ]
            ],
            ['name' => 'Reports', 'path' => '/reports', 'icon' => 'file-text', 'allowed' => ['Manager', 'HR', 'TL']],
            ['name' => 'Settings', 'path' => '/settings', 'icon' => 'settings', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
        ]
    ]
];

// 3. FILTER SECTIONS
$activeSections = [];
foreach ($sections as $section) {
    $filteredItems = array_filter($section['items'], function ($item) use ($user) {
        return in_array($user['role'], $item['allowed']);
    });
    if (!empty($filteredItems)) {
        $section['items'] = $filteredItems;
        $activeSections[] = $section;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-sidebar-width: 95px;
            --secondary-sidebar-width: 220px; 
            --active-bg: #f4f4f5;
            --border-color: #e4e4e7;
            --text-muted: #71717a;
        }

        body { margin: 0; font-family: 'Inter', sans-serif; display: flex; background: #fff; height: 100vh; overflow: hidden; }

        /* PRIMARY SIDEBAR */
        .sidebar-primary {
            width: var(--primary-sidebar-width); 
            height: 100vh;
            border-right: 1px solid var(--border-color);
            background: #fff; 
            position: fixed; 
            left: 0; 
            top: 0;
            z-index: 1001;
            overflow-y: auto;
            scrollbar-width: none;
        }
        .sidebar-primary::-webkit-scrollbar { display: none; }

        .nav-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            min-height: 100%;
        }

        .nav-item {
            width: 100%; 
            padding: 12px 0; 
            display: flex; 
            flex-direction: column; 
            align-items: center;
            cursor: pointer; 
            text-decoration: none; 
            color: var(--text-muted); 
            transition: 0.2s;
            flex-shrink: 0;
        }
        .nav-item:hover, .nav-item.active { color: #000; background: var(--active-bg); }
        .nav-item span { font-size: 10px; margin-top: 5px; font-weight: 500; text-align: center; padding: 0 4px; }

        /* SECONDARY SIDEBAR */
        .sidebar-secondary {
            width: var(--secondary-sidebar-width); 
            height: 100vh; 
            background: #fff;
            border-right: 1px solid var(--border-color); 
            position: fixed;
            left: var(--primary-sidebar-width); 
            top: 0;
            transform: translateX(-105%);
            transition: transform 0.3s ease; 
            z-index: 1000; 
            overflow-y: auto;
            scrollbar-width: none;
        }
        .sidebar-secondary.open { transform: translateX(0); }

        #subItemContainer {
            /* Kept the 130px padding for the lower position you liked */
            padding: 130px 15px 40px 15px; 
            display: flex;
            flex-direction: column;
        }

        .sub-item {
            display: flex; align-items: center; padding: 10px;
            text-decoration: none; color: #3f3f46; border-radius: 8px;
            font-size: 13px; margin-bottom: 4px; transition: 0.2s; font-weight: 500;
        }
        .sub-item:hover { background: var(--active-bg); color: #000; }
        .sub-item .sub-icon { margin-right: 10px; width: 16px; height: 16px; color: #71717a; }

        .back-btn {
            display: flex;
            align-items: center;
            padding: 10px;
            margin-bottom: 20px; /* Added extra space after back button since title is gone */
            cursor: pointer;
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.2s;
        }
        .back-btn:hover { color: #000; }

        .user-footer {
            margin-top: auto; padding: 20px 0; width: 100%;
            display: flex; flex-direction: column; align-items: center;
            border-top: 1px solid var(--border-color); background: #fff;
        }

        main { margin-left: var(--primary-sidebar-width); padding: 40px; width: 100%; transition: 0.3s; }
        .main-shifted { margin-left: calc(var(--primary-sidebar-width) + var(--secondary-sidebar-width)); }
    </style>
</head>
<body>

    <aside class="sidebar-primary">
        <div class="nav-inner">
            <div style="width: 45px; height: 45px; background: #eee; border-radius: 12px; margin-bottom: 20px; flex-shrink: 0;"></div>
            
            <?php foreach ($activeSections as $section): ?>
                <?php foreach ($section['items'] as $item): ?>
                    <a href="javascript:void(0)" class="nav-item" onclick='handleNavClick(<?= json_encode($item) ?>, this)'>
                        <i data-lucide="<?= $item['icon'] ?>"></i>
                        <span><?= $item['name'] ?></span>
                    </a>
                <?php endforeach; ?>
                <div style="width: 40px; height: 1px; background: var(--border-color); margin: 10px 0; flex-shrink: 0;"></div>
            <?php endforeach; ?>

            <div class="user-footer">
                <div style="width: 42px; height: 42px; background: #27272a; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 16px; margin-bottom: 8px;">J</div>
                <div style="font-size: 11px; font-weight: 600; color: #18181b;"><?= $user['name'] ?></div>
            </div>
        </div>
    </aside>

    <aside class="sidebar-secondary" id="secondaryPanel">
        <div id="subItemContainer"></div>
    </aside>

    <main id="mainContent"></main>

    <script>
        lucide.createIcons();

        function handleNavClick(item, element) {
            const panel = document.getElementById('secondaryPanel');
            const container = document.getElementById('subItemContainer');
            const main = document.getElementById('mainContent');

            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
            element.classList.add('active');

            if (item.subItems && item.subItems.length > 0) {
                panel.classList.add('open');
                main.classList.add('main-shifted');
                
                // REMOVED: The dynamic section title heading
                container.innerHTML = `
                    <div class="back-btn" onclick="closeSubMenu()">
                        <i data-lucide="chevron-left" style="width: 16px; height: 16px; margin-right: 8px;"></i>
                        Back
                    </div>
                `;

                item.subItems.forEach(sub => {
                    container.innerHTML += `
                        <a href="${sub.path}" class="sub-item">
                            <i data-lucide="${sub.icon || 'circle'}" class="sub-icon"></i>
                            <span style="flex:1">${sub.name}</span>
                            <i data-lucide="chevron-right" style="width:12px; height:12px; color:#a1a1aa"></i>
                        </a>`;
                });
                lucide.createIcons();
            } else {
                closeSubMenu();
                if(item.path && item.path !== '#') window.location.href = item.path;
            }
        }

        function closeSubMenu() {
            const panel = document.getElementById('secondaryPanel');
            const main = document.getElementById('mainContent');
            panel.classList.remove('open');
            main.classList.remove('main-shifted');
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        }
    </script>
</body>
</html>