<?php
// 1. MOCK USER DATA
$user = [
    'name' => 'John Doe',
    'role' => 'Manager' 
];

// 2. GET CURRENT URL
$current_path = $_SERVER['REQUEST_URI'];
?>

<?php
// 3. DEFINE MENU DATA
$sections = [
    [
        'label' => 'Main',
        'items' => [
            ['name' => 'Dashboard', 'path' => '/', 'icon' => 'layout-dashboard', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Team Lead', 'path' => '/teamlead', 'icon' => 'users', 'allowed' => ['TL', 'HR', 'Manager']],
            ['name' => 'HR Management', 'path' => '/hrManagement', 'icon' => 'fingerprint', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Directory', 'path' => '/employees', 'icon' => 'users', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Manager', 'path' => '/manager', 'icon' => 'shield-check', 'allowed' => ['Manager', 'HR']],
        ]
    ],
    [
        'label' => 'Operations',
        'items' => [
            ['name' => 'Attendance', 'path' => '/EmployeeAttendance', 'icon' => 'calendar-check', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Tasks', 'path' => '/tasks', 'icon' => 'clipboard-list', 'allowed' => ['Manager', 'TL', 'Employee', 'HR', 'DM']],
            ['name' => 'Teams', 'path' => '/teams', 'icon' => 'message-square', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts']],
            ['name' => 'Payroll', 'path' => '/payroll', 'icon' => 'banknote', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Recruitment', 'path' => '/recruitment', 'icon' => 'briefcase', 'allowed' => ['Manager', 'HR']],
            [
                'name' => 'Marketing',
                'icon' => 'phone',
                'allowed' => ['DM', 'Manager'],
                'subItems' => [
                    ['name' => 'Manager', 'path' => '/digital-marketing/manager', 'allowed' => ['DM', 'Manager']],
                    ['name' => 'Executive', 'path' => '/digital-marketing/executive', 'allowed' => ['DM', 'Manager']],
                ]
            ],
        ]
    ],
    [
        'label' => 'System',
        'items' => [
            ['name' => 'Reports', 'path' => '/reports', 'icon' => 'file-text', 'allowed' => ['Manager', 'TL', 'HR', 'Accounts', 'DM']],
            ['name' => 'Settings', 'path' => '/settings', 'icon' => 'settings', 'allowed' => ['Manager', 'TL', 'Employee', 'Accounts', 'HR', 'DM']],
        ]
    ]
];

// 4. FILTER ITEMS
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sidebar</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            /* === CHANGED: Increased sidebar width from 90px to 110px === */
            --sidebar-width: 110px; 
            
            --active-bg: #EAEAEA;
            --active-border: #D1D1D1;
            --text-color: #555555;
            --icon-color: #444444;
            --hover-bg: #F5F5F5;
            --body-bg: #ffffff;
        }

        body {
            background-color: var(--body-bg);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding-left: var(--sidebar-width); 
            min-height: 100vh;
            box-sizing: border-box;
        }

        .sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            background: #ffffff;
            border-right: 1px solid #eee;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 0;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            scrollbar-width: none; 
            -ms-overflow-style: none;
            z-index: 1000;
        }
        
        .sidebar::-webkit-scrollbar { display: none; }

        .brand-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            margin-bottom: 25px;
            text-decoration: none;
            cursor: pointer;
            width: 100%;
            /* === CHANGED: Removed padding to give logo more space === */
            padding: 0; 
            box-sizing: border-box;
        }

        .brand-image {
            /* === CHANGED: Increased size from 85px to 100px === */
            width: 100px; 
            height: auto;
            object-fit: contain;
        }

        .user-profile {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding-bottom: 20px;
            width: 100%;
        }

        .user-avatar {
            width: 36px;
            height: 36px;
            background-color: #333;
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .user-name {
            font-size: 10px;
            font-weight: 600;
            color: #333;
            text-align: center;
            /* === CHANGED: Increased max-width slightly for the wider sidebar === */
            max-width: 90px; 
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .user-role {
            font-size: 9px;
            color: #888;
            margin-top: 2px;
        }

        .nav-group {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 10px;
        }

        .nav-divider {
            width: 40px;
            height: 1px;
            background-color: #eee;
            margin: 10px 0;
        }

        .nav-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            color: var(--text-color);
            width: 100%;
            padding: 10px 0;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
        }

        .icon-box {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: transparent;
            transition: all 0.2s ease;
            margin-bottom: 6px;
        }

        .icon-box svg { 
            width: 20px; 
            height: 20px; 
            stroke-width: 1.5px; 
        }

        .nav-label {
            font-size: 11px;
            font-weight: 500;
            text-align: center;
            line-height: 1.2;
            color: #666;
        }

        .nav-item.active .icon-box {
            background-color: var(--active-bg);
            border: 1px solid var(--active-border);
            color: #000;
        }
        
        .nav-item.active .nav-label {
            color: #000;
            font-weight: 600;
        }

        .nav-item:hover .icon-box { background-color: var(--hover-bg); }

        .submenu-container {
            display: none;
            background: #f9f9f9;
            width: 100%;
            padding-bottom: 10px;
        }
        .submenu-container.show { display: block; }
        .sub-link { font-size: 10px; color: #888; text-decoration: none; padding: 5px 0; display: block; text-align: center;}
        .sub-link:hover { color: orange; }

    </style>
</head>
<body>

    <aside class="sidebar">
        
        <a href="#" class="brand-logo">
            
            <img src="/project2/workack/assets/logo.png" 
                 alt="Workack" 
                 class="brand-image"
                 id="sidebarLogo">
                 
            <div id="logoError" style="display:none; font-size: 8px; color: red; text-align: center; margin-top: 5px;">
                Image Missing<br>
                Check: assets/logo.png
            </div>

        </a>
        <a href="/" class="nav-item <?= $current_path === '/' ? 'active' : '' ?>">
            <div class="icon-box">
                <i data-lucide="home"></i>
            </div>
            <span class="nav-label">Home</span>
        </a>

        <?php foreach ($activeSections as $index => $section): ?>
            
            <?php if ($index > 0): ?><div class="nav-divider"></div><?php endif; ?>

            <div class="nav-group">
                <?php foreach ($section['items'] as $item): 
                    $itemPath = $item['path'] ?? ''; 
                    
                    if($itemPath === '/') continue; 

                    $isActive = ($itemPath !== '' && $current_path === $itemPath);
                    $hasSub = !empty($item['subItems']);
                ?>
                    
                    <div class="nav-wrapper" style="width: 100%;">
                        <a href="<?= $itemPath ?: '#' ?>" class="nav-item <?= $isActive ? 'active' : '' ?> <?= $hasSub ? 'submenu-toggle' : '' ?>">
                            <div class="icon-box">
                                <i data-lucide="<?= $item['icon'] ?>"></i>
                            </div>
                            <span class="nav-label"><?= htmlspecialchars($item['name']) ?></span>
                        </a>

                        <?php if ($hasSub): ?>
                            <div class="submenu-container">
                                <?php foreach($item['subItems'] as $sub): 
                                    $subPath = $sub['path'] ?? '#';
                                ?>
                                    <a href="<?=$subPath?>" class="sub-link"><?= $sub['name'] ?></a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div class="user-profile">
            <div class="nav-divider"></div>
            
            <a href="/profile" style="text-decoration: none; display: flex; flex-direction: column; align-items: center;">
                <div class="user-avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <div class="user-name"><?= htmlspecialchars($user['name']) ?></div>
                <div class="user-role"><?= htmlspecialchars($user['role']) ?></div>
            </a>
        </div>

    </aside>

    <script>
        lucide.createIcons();

        document.querySelectorAll('.submenu-toggle').forEach(item => {
            item.addEventListener('click', (e) => {
                const container = item.nextElementSibling;
                if(container && container.classList.contains('submenu-container')) {
                    e.preventDefault();
                    container.classList.toggle('show');
                }
            })
        });

        // === THIS SCRIPT HELPS DEBUG THE LOGO ===
        const img = document.getElementById('sidebarLogo');
        const err = document.getElementById('logoError');
        
        img.onerror = function() {
            img.style.display = 'none';
            err.style.display = 'block';
            console.error("Logo failed to load from: " + img.src);
        };
    </script>
</body>
</html>