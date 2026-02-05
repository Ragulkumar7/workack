<?php
// 1. MOCK USER DATA
$user = ['name' => 'John Doe', 'role' => 'Manager'];

// 2. GET CURRENT URL
$current_path = $_SERVER['REQUEST_URI'];

// 3. DEFINE MENU DATA (Structure preserved, logic added for Sub-directories)
$sections = [
    [
        'label' => 'Main',
        'items' => [
            ['name' => 'Dashboard', 'path' => '/', 'icon' => 'home', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            [
                'name' => 'Team Lead', 
                'icon' => 'users', 
                'allowed' => ['TL', 'HR', 'Manager'],
                'subItems' => [
                    ['name' => 'Overview', 'path' => '/teamlead/overview'],
                    ['name' => 'Team Performance', 'path' => '/teamlead/performance'],
                    ['name' => 'Roster Management', 'path' => '/teamlead/roster'],
                ]
            ],
            [
                'name' => 'HR Management', 
                'icon' => 'fingerprint', 
                'allowed' => ['Manager', 'HR'],
                'subItems' => [
                    ['name' => 'Employee Records', 'path' => '/hr/records'],
                    ['name' => 'Onboarding', 'path' => '/hr/onboarding'],
                    ['name' => 'Leave Policy', 'path' => '/hr/policy'],
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
            [
                'name' => 'Digital Marketing', 
                'icon' => 'target', 
                'allowed' => ['DM', 'Manager'],
                'subItems' => [
                    ['name' => 'Dashboard', 'path' => '/workack/digital_marketing/dm_dashboard.php'],
                    ['name' => 'Companies', 'path' => '/workack/digital_marketing/companies.php'],
                    ['name' => 'Leads', 'path' => '/workack/digital_marketing/leads.php'],
                    ['name' => 'Analytics', 'path' => '/workack/digital_marketing/analytics.php'],
                ]
            ],
        ]
    ]
];

// 4. FILTER ITEMS (Preserved main content logic)
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
            --primary-sidebar-width: 80px;
            --secondary-sidebar-width: 260px;
            --active-bg: #f4f4f5;
            --border-color: #e4e4e7;
        }

        body { margin: 0; font-family: 'Inter', sans-serif; display: flex; background: #fff; }

        /* PRIMARY SIDEBAR (Icon Strip) */
        .sidebar-primary {
            width: var(--primary-sidebar-width);
            height: 100vh;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-top: 20px;
            background: #fff;
            position: fixed;
            left: 0; z-index: 1001;
        }

        .nav-item {
            width: 100%;
            padding: 15px 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
            text-decoration: none;
            color: #71717a;
            transition: all 0.2s;
        }

        .nav-item:hover, .nav-item.active { color: #09090b; background: var(--active-bg); }
        .nav-item span { font-size: 10px; margin-top: 5px; font-weight: 500; text-align: center; padding: 0 4px; }

        /* SECONDARY SIDEBAR (The Drill-down Panel) */
        .sidebar-secondary {
            width: var(--secondary-sidebar-width);
            height: 100vh;
            background: #fcfcfc;
            border-right: 1px solid var(--border-color);
            position: fixed;
            left: var(--primary-sidebar-width);
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            z-index: 1000;
            padding: 20px;
            box-sizing: border-box;
        }

        .sidebar-secondary.open { transform: translateX(0); }

        .secondary-header { font-weight: 600; font-size: 16px; margin-bottom: 20px; color: #09090b; }
        
        .search-box {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .sub-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px 12px;
            text-decoration: none;
            color: #3f3f46;
            border-radius: 6px;
            font-size: 14px;
            margin-bottom: 4px;
        }

        .sub-item:hover { background: var(--active-bg); color: #000; }
        .sub-item i { width: 14px; height: 14px; }

        /* Layout Offset */
        main { margin-left: var(--primary-sidebar-width); padding: 40px; width: 100%; transition: margin-left 0.3s; }
        .main-shifted { margin-left: calc(var(--primary-sidebar-width) + var(--secondary-sidebar-width)); }
    </style>
</head>
<body>

    <aside class="sidebar-primary">
        <img src="/workack/assets/logo.png" style="width: 40px; margin-bottom: 30px;">
        
        <?php foreach ($activeSections as $section): ?>
            <?php foreach ($section['items'] as $item): ?>
                <div class="nav-item <?= !empty($item['subItems']) ? 'has-subs' : '' ?>" 
                     onclick="toggleSecondary('<?= $item['name'] ?>', <?= htmlspecialchars(json_encode($item['subItems'] ?? [])) ?>, this)">
                    <i data-lucide="<?= $item['icon'] ?>"></i>
                    <span><?= $item['name'] ?></span>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </aside>

    <aside class="sidebar-secondary" id="secondaryPanel">
        <div class="secondary-header" id="panelTitle">Directory</div>
        <input type="text" class="search-box" placeholder="Search...">
        <div id="subItemContainer">
            </div>
    </aside>

    <main id="mainContent">
        </main>

    <script>
        lucide.createIcons();

        function toggleSecondary(name, subs, element) {
            const panel = document.getElementById('secondaryPanel');
            const container = document.getElementById('subItemContainer');
            const title = document.getElementById('panelTitle');
            const main = document.getElementById('mainContent');

            // Remove active class from all
            document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));

            if (subs.length > 0) {
                element.classList.add('active');
                panel.classList.add('open');
                main.classList.add('main-shifted');
                title.innerText = name;
                
                // Clear and Populate
                container.innerHTML = '';
                subs.forEach(sub => {
                    container.innerHTML += `
                        <a href="${sub.path}" class="sub-item">
                            ${sub.name}
                            <i data-lucide="chevron-right"></i>
                        </a>
                    `;
                });
                lucide.createIcons();
            } else {
                panel.classList.remove('open');
                main.classList.remove('main-shifted');
                // If it's a direct link, redirect
                // window.location.href = path;
            }
        }
    </script>
</body>
</html>