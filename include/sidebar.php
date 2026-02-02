<?php
// 1. MOCK USER DATA (Replace this with your $_SESSION logic)
$user = [
    'name' => 'John Doe',
    'role' => 'Manager' // Try changing this to 'Employee' or 'TL' to test permissions
];

// 2. GET CURRENT URL (To highlight active link)
$current_path = $_SERVER['REQUEST_URI'];

// 3. DEFINE MENU DATA
$sections = [
    [
        'label' => 'Main',
        'items' => [
            ['name' => 'Dashboard', 'path' => '/', 'icon' => 'layout-dashboard', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Team Lead Portal', 'path' => '/teamlead', 'icon' => 'users', 'allowed' => ['TL', 'HR', 'Manager']],
            ['name' => 'HR Management', 'path' => '/hrManagement', 'icon' => 'fingerprint', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Employees Directory', 'path' => '/employees', 'icon' => 'users', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Manager Portal', 'path' => '/manager', 'icon' => 'shield-check', 'allowed' => ['Manager', 'HR']],
        ]
    ],
    [
        'label' => 'Operations',
        'items' => [
            ['name' => 'Attendance', 'path' => '/EmployeeAttendance', 'icon' => 'calendar-check', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'My Attendance History', 'path' => '/attendance', 'icon' => 'history', 'allowed' => ['TL']],
            ['name' => 'Attendance History', 'path' => '/attendance-history', 'icon' => 'calculator', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Task Management', 'path' => '/tasks', 'icon' => 'clipboard-list', 'allowed' => ['Manager', 'TL', 'Employee', 'HR', 'DM']],
            ['name' => 'Self Assigned Tasks', 'path' => '/self-task', 'icon' => 'user-check', 'allowed' => ['Manager', 'TL', 'Employee']],
            ['name' => 'Teams / Chat', 'path' => '/teams', 'icon' => 'message-square', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts']],
            ['name' => 'Payroll & Accounts', 'path' => '/payroll', 'icon' => 'banknote', 'allowed' => ['Manager', 'HR']],
            ['name' => 'IT & Operations', 'path' => '/it', 'icon' => 'cpu', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Recruitment', 'path' => '/recruitment', 'icon' => 'briefcase', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Sales Manager', 'path' => '/sales', 'icon' => 'trending-up', 'allowed' => ['Manager', 'TL', 'HR']],
            [
                'name' => 'Digital Marketing',
                'icon' => 'phone',
                'allowed' => ['DM'],
                'subItems' => [
                    ['name' => 'Digital Manager', 'path' => '/digital-marketing/manager', 'allowed' => ['DM']],
                    ['name' => 'Digital Executive', 'path' => '/digital-marketing/executive', 'allowed' => ['DM']],
                ]
            ],
            ['name' => 'Accounts Team', 'path' => '/accountsteam', 'icon' => 'banknote', 'allowed' => ['Accounts']],
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

// 4. FILTER ITEMS BASED ON ROLE
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --collapsed-width: 70px;
            --primary-orange: #FF9B44;
            --text-dark: #333333;
            --text-grey: #6c757d;
            --sidebar-bg: #ffffff;
            --body-bg: #f7f7f7;
            --border-color: #e3e3e3;
            --transition-speed: 0.3s;
        }

        body {
            background-color: var(--body-bg);
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }

        .sidebar-wrapper {
            display: flex;
            height: 100vh;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        /* --- HOVER EFFECT LOGIC --- */
        .sidebar {
            width: var(--collapsed-width); /* Default Narrow */
            background: var(--sidebar-bg);
            height: 100vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 0 10px 0 rgba(0, 0, 0, 0.05);
            border-right: 1px solid var(--border-color);
            transition: width var(--transition-speed) ease;
            overflow-x: hidden;
            white-space: nowrap;
        }

        /* Expand on Hover (Desktop only) */
        @media (min-width: 969px) {
            .sidebar:hover {
                width: var(--sidebar-width);
            }
        }

        /* --- ELEMENT VISIBILITY CONTROLS --- */
        @media (min-width: 969px) {
            .sidebar:not(:hover) .brand-text-smart,
            .sidebar:not(:hover) .brand-text-hr,
            .sidebar:not(:hover) .nav-text-label,
            .sidebar:not(:hover) .nav-label,
            .sidebar:not(:hover) .chevron-icon,
            .sidebar:not(:hover) .user-details {
                opacity: 0;
                visibility: hidden;
                transition: opacity 0.1s;
            }

            .sidebar:hover .brand-text-smart,
            .sidebar:hover .brand-text-hr,
            .sidebar:hover .nav-text-label,
            .sidebar:hover .nav-label,
            .sidebar:hover .chevron-icon,
            .sidebar:hover .user-details {
                opacity: 1;
                visibility: visible;
                transition: opacity 0.4s ease-in 0.1s;
            }
        }

        /* Logo Area */
        .sidebar-header {
            height: 60px;
            min-height: 60px;
            display: flex;
            align-items: center;
            padding-left: 18px;
            background: #ffffff;
            border-bottom: 1px solid transparent;
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.05);
            z-index: 10;
            overflow: hidden;
        }

        .brand-info {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 20px;
            font-weight: 700;
        }

        .logo-icon {
            min-width: 32px;
            height: 32px;
            background: var(--primary-orange);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            font-weight: bold;
        }

        .brand-text-smart { color: #333; }
        .brand-text-hr { color: var(--primary-orange); }

        /* Navigation List */
        .nav-container {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 20px 0;
        }

        .nav-label {
            font-size: 12px;
            color: #888;
            margin: 15px 20px 5px 20px;
            font-weight: 500;
            white-space: nowrap;
        }

        .nav-item {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 12px 22px;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.2s;
            font-size: 15px;
            font-weight: 400;
            cursor: pointer;
            border-left: 3px solid transparent;
            height: 24px;
        }

        .nav-item:hover {
            color: var(--primary-orange);
        }

        .nav-item.active {
            color: var(--primary-orange);
            background: rgba(255, 155, 68, 0.1);
            border-left: 3px solid var(--primary-orange);
        }

        .nav-content { display: flex; align-items: center; gap: 15px; width: 100%; }
        .nav-icon { display: flex; align-items: center; min-width: 24px; justify-content: center; }

        /* Submenu */
        .sub-menu-container {
            background: #f9f9f9;
            padding: 5px 0;
            display: none; /* Hidden by default in JS/PHP version */
        }
        
        .sub-menu-container.show {
            display: block;
        }

        .sub-nav-item {
            display: block;
            padding: 8px 20px 8px 60px;
            color: var(--text-grey);
            text-decoration: none;
            font-size: 14px;
            transition: 0.2s;
        }
        .sub-nav-item:hover { color: var(--primary-orange); }
        .sub-nav-item.active { color: var(--primary-orange); font-weight: 500; }

        /* Profile Section */
        .profile-card {
            margin: 0;
            padding: 15px 15px;
            border-top: 1px solid var(--border-color);
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
            overflow: hidden;
        }

        .avatar-circle {
            min-width: 38px;
            height: 38px;
            background: #eee;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #555;
            font-size: 14px;
            border: 1px solid #ddd;
        }

        .user-details { display: flex; flex-direction: column; overflow: hidden; }
        .user-details h4 { margin: 0; font-size: 14px; color: var(--text-dark); font-weight: 600; white-space: nowrap; }
        .user-details p { margin: 0; font-size: 12px; color: #888; white-space: nowrap; }

        .logout-btn {
            margin-left: auto;
            color: #555;
            display: flex;
            align-items: center;
        }
        
        /* Mobile Logic */
        .mobile-toggle {
            display: none;
            position: fixed; top: 15px; left: 15px; z-index: 1100;
            background: var(--primary-orange); color: white; border: none;
            padding: 8px; border-radius: 4px; cursor: pointer;
        }

        .sidebar-overlay {
            display: none; position: fixed; top: 0; left: 0;
            width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1150;
        }

        .sidebar-overlay.visible { display: block; }

        @media (max-width: 968px) {
            .mobile-toggle { display: block; }
            .sidebar {
                position: fixed; left: 0; top: 0; height: 100vh;
                width: var(--sidebar-width);
                transform: translateX(-100%); z-index: 1200;
            }
            .sidebar.open { transform: translateX(0); }

            /* Ensure text is always visible on mobile when menu is open */
            .brand-text-smart, .brand-text-hr, .nav-text-label,
            .nav-label, .chevron-icon, .user-details {
                opacity: 1 !important;
                visibility: visible !important;
            }
        }
    </style>
</head>
<body>

    <button class="mobile-toggle" id="mobileToggleBtn">
        <i data-lucide="menu"></i>
    </button>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="sidebar-wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="brand-info">
                    <div class="logo-icon">S</div>
                    <div>
                        <span class="brand-text-smart">Smart</span>
                        <span class="brand-text-hr">HR</span>
                    </div>
                </div>
            </div>

            <div class="nav-container">
                <?php foreach ($activeSections as $section): ?>
                    <div>
                        <div class="nav-label"><?= htmlspecialchars($section['label']) ?></div>
                        <nav>
                            <?php foreach ($section['items'] as $item): 
                                $hasSubItems = isset($item['subItems']) && !empty($item['subItems']);
                                $isActive = ($current_path === $item['path']);
                            ?>

                                <?php if ($hasSubItems): ?>
                                    <div class="has-submenu-wrapper">
                                        <div class="nav-item submenu-toggle">
                                            <div class="nav-content">
                                                <span class="nav-icon"><i data-lucide="<?= $item['icon'] ?>"></i></span>
                                                <span class="nav-text-label"><?= htmlspecialchars($item['name']) ?></span>
                                            </div>
                                            <div class="chevron-icon">
                                                <i data-lucide="chevron-down" class="chevron-img"></i>
                                            </div>
                                        </div>
                                        <div class="sub-menu-container">
                                            <?php foreach ($item['subItems'] as $sub): 
                                                $isSubActive = ($current_path === $sub['path']);
                                            ?>
                                                <a href="<?= $sub['path'] ?>" class="sub-nav-item <?= $isSubActive ? 'active' : '' ?>">
                                                    <?= htmlspecialchars($sub['name']) ?>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>

                                <?php else: ?>
                                    <a href="<?= $item['path'] ?: '#' ?>" class="nav-item <?= $isActive ? 'active' : '' ?>">
                                        <div class="nav-content">
                                            <span class="nav-icon"><i data-lucide="<?= $item['icon'] ?>"></i></span>
                                            <span class="nav-text-label"><?= htmlspecialchars($item['name']) ?></span>
                                        </div>
                                    </a>
                                <?php endif; ?>

                            <?php endforeach; ?>
                        </nav>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="profile-card">
                <div class="avatar-circle"><?= substr($user['name'], 0, 1) ?></div>
                <div class="user-details">
                    <h4><?= htmlspecialchars($user['name']) ?></h4>
                    <p><?= htmlspecialchars($user['role']) ?></p>
                </div>
                <a href="/logout" class="logout-btn">
                    <i data-lucide="log-out" width="18"></i>
                </a>
            </div>
        </aside>
    </div>

    <script>
        // 1. Initialize Lucide Icons
        lucide.createIcons();

        // 2. Mobile Menu Toggle
        const mobileBtn = document.getElementById('mobileToggleBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');

        function toggleMobileMenu() {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('visible');
        }

        mobileBtn.addEventListener('click', toggleMobileMenu);
        overlay.addEventListener('click', toggleMobileMenu);

        // 3. Submenu Toggle Logic
        const submenuToggles = document.querySelectorAll('.submenu-toggle');
        
        submenuToggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                // Find the next sibling which is the container
                const container = this.nextElementSibling;
                const chevron = this.querySelector('.chevron-img');
                
                // Toggle visibility
                container.classList.toggle('show');
                
                // Optional: Rotate chevron
                // We'd need to re-render the icon or use CSS transform, 
                // keeping it simple for now as per request.
            });
        });
    </script>
</body>
</html>