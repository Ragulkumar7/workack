<?php
// 1. MOCK USER DATA
if (!isset($user)) {
    $user = [
        'name' => 'Admin User',
        'role' => 'Manager'
    ];
}

// 2. DEFINE MENU OPTIONS
$sections = [
    [
        'label' => 'Main',
        'items' => [
            ['name' => 'Dashboard', 'path' => '/', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Team Lead', 'path' => '/teamlead', 'allowed' => ['TL', 'HR', 'Manager']],
            ['name' => 'HR Mgmt', 'path' => '/hrManagement', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Directory', 'path' => '/employees', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Manager', 'path' => '/manager', 'allowed' => ['Manager', 'HR']],
        ]
    ],
    [
        'label' => 'Operations',
        'items' => [
            ['name' => 'Attendance', 'path' => '/EmployeeAttendance', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts', 'DM']],
            ['name' => 'Tasks', 'path' => '/tasks', 'allowed' => ['Manager', 'TL', 'Employee', 'HR', 'DM']],
            ['name' => 'Chat', 'path' => '/teams', 'allowed' => ['Manager', 'TL', 'HR', 'Employee', 'Accounts']],
            ['name' => 'Payroll', 'path' => '/payroll', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Recruitment', 'path' => '/recruitment', 'allowed' => ['Manager', 'HR']],
            ['name' => 'Sales', 'path' => '/sales', 'allowed' => ['Manager', 'TL', 'HR']],
            [
                'name' => 'Digital Mkt',
                'allowed' => ['DM'],
                'subItems' => [
                    ['name' => 'Manager', 'path' => '/digital-marketing/manager', 'allowed' => ['DM']],
                    ['name' => 'Executive', 'path' => '/digital-marketing/executive', 'allowed' => ['DM']],
                ]
            ],
            ['name' => 'Accounts', 'path' => '/accountsteam', 'allowed' => ['Accounts']],
        ]
    ],
    [
        'label' => 'System',
        'items' => [
            ['name' => 'Reports', 'path' => '/reports', 'allowed' => ['Manager', 'TL', 'HR', 'Accounts', 'DM']],
            ['name' => 'Settings', 'path' => '/settings', 'allowed' => ['Manager', 'TL', 'Employee', 'Accounts', 'HR', 'DM']],
        ]
    ]
];

// 3. FILTER ITEMS BASED ON ROLE
$activeSections = [];
foreach ($sections as $section) {
    $filteredItems = array_filter($section['items'], function ($item) use ($user) {
        return !isset($item['allowed']) || in_array($user['role'], $item['allowed']);
    });

    if (!empty($filteredItems)) {
        $section['items'] = $filteredItems;
        $activeSections[] = $section;
    }
}
?>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<div class="header-wrapper">
    
    <header class="top-bar">
        <div class="search-container">
            <div class="search-box">
                <i data-lucide="search" class="search-icon"></i>
                <input type="text" placeholder="Search operations..." class="search-input">
            </div>
        </div>

        <div class="user-nav">
            <button class="icon-btn-style"><i data-lucide="layout-grid" class="nav-icon-size"></i></button>
            <button class="icon-btn-style">
                <i data-lucide="message-square" class="nav-icon-size"></i>
                <span class="dot dot-green"></span>
            </button>
            <button class="icon-btn-style"><i data-lucide="mail" class="nav-icon-size"></i></button>
            <button class="icon-btn-style">
                <i data-lucide="bell" class="nav-icon-size"></i>
                <span class="dot dot-red"></span>
            </button>

            <div class="profile-pill">
                <div class="avatar-container">
                    <img src="https://i.pravatar.cc/150?u=admin" alt="Profile" class="avatar-img">
                    <span class="status-indicator"></span>
                </div>
                <div class="profile-text">
                    <span class="user-name"><?= htmlspecialchars($user['name']) ?></span>
                </div>
                <i data-lucide="chevron-down" class="chevron-size"></i>
            </div>
        </div>
    </header>

    <nav class="horizontal-nav">
        <ul class="nav-list">
            <?php foreach ($activeSections as $section): ?>
                <?php foreach ($section['items'] as $item): ?>
                    
                    <?php if (isset($item['subItems'])): ?>
                        <li class="nav-item dropdown">
                            <span class="nav-link"><?= htmlspecialchars($item['name']) ?> ▾</span>
                            <div class="dropdown-content">
                                <?php foreach ($item['subItems'] as $sub): ?>
                                    <a href="<?= $sub['path'] ?>" class="dropdown-link">
                                        <?= htmlspecialchars($sub['name']) ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a href="<?= $item['path'] ?>" class="nav-link">
                                <?= htmlspecialchars($item['name']) ?>
                            </a>
                        </li>
                    <?php endif; ?>

                <?php endforeach; ?>
                <li class="separator"></li>
            <?php endforeach; ?>
        </ul>
    </nav>

</div>

<style>
    /* --- LAYOUT & RESETS --- */
    .header-wrapper {
        background-color: #ffffff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        font-family: 'Poppins', sans-serif;
    }

    /* --- TOP BAR STYLES --- */
    .top-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 20px;
        height: 60px;
        border-bottom: 1px solid #f0f0f0;
    }

    .search-container { flex: 1; }
    .search-box {
        display: flex;
        align-items: center;
        background-color: #f9fafb;
        padding: 8px 15px;
        border-radius: 25px;
        width: fit-content;
        min-width: 300px;
        border: 1px solid transparent;
        transition: 0.2s;
    }
    .search-box:focus-within {
        border-color: #e3e3e3;
        background-color: #fff;
    }

    .search-icon { width: 18px; height: 18px; color: #9CA3AF; }
    .search-input {
        border: none;
        background: transparent;
        margin-left: 10px;
        outline: none;
        width: 100%;
        color: #333;
        font-family: 'Poppins', sans-serif;
    }

    .user-nav { display: flex; align-items: center; gap: 15px; }
    
    .icon-btn-style {
        background: none;
        border: none;
        cursor: pointer;
        color: #4B5563;
        position: relative;
        padding: 8px;
        display: flex;
        align-items: center;
        transition: 0.2s;
        border-radius: 50%;
    }
    .icon-btn-style:hover { background-color: #f3f4f6; }
    
    .nav-icon-size { width: 20px; height: 20px; }
    
    .dot {
        position: absolute; top: 6px; right: 6px; width: 8px; height: 8px;
        border-radius: 50%; border: 2px solid white;
    }
    .dot-green { background-color: #22c55e; }
    .dot-red { background-color: #ef4444; }

    .profile-pill { display: flex; align-items: center; gap: 10px; cursor: pointer; margin-left: 10px; padding: 5px; border-radius: 20px; transition: 0.2s; }
    .profile-pill:hover { background-color: #f3f4f6; }
    
    .avatar-container { position: relative; width: 35px; height: 35px; }
    .avatar-img { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; }
    .status-indicator {
        position: absolute; bottom: 0; right: 0; width: 10px; height: 10px;
        background-color: #22c55e; border: 2px solid white; border-radius: 50%;
    }
    .user-name { font-weight: 500; font-size: 14px; color: #374151; }
    .chevron-size { width: 16px; height: 16px; color: #6b7280; }

    /* --- PROFESSIONAL NAVIGATION BAR STYLES --- */
    .horizontal-nav {
        background-color: #fff;
        border-bottom: 1px solid #e5e7eb;
        padding: 0 20px;
        overflow-x: auto; 
    }

    .nav-list {
        display: flex;
        align-items: center;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 25px; /* Spacing between items */
    }

    .nav-item {
        position: relative;
    }

    /* --- PROFESSIONAL TEXT STYLES --- */
    .nav-link {
        display: block;
        padding: 14px 0; /* Vertical padding */
        text-decoration: none;
        color: #4b5563; /* Gray-600 */
        font-size: 14px;
        font-weight: 500; /* Medium weight */
        transition: all 0.2s;
        cursor: pointer;
        white-space: nowrap;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px; /* Align border with container border */
    }

    .nav-link:hover {
        color: #FF9B44; /* Primary Orange */
        /* No background on hover, typically just color change in pro navs */
    }

    /* Separator Style */
    .separator {
        border-right: 1px solid #e5e7eb;
        height: 20px;
        margin: 0 5px;
    }
    .separator:last-child { display: none; }

    /* Dropdown CSS */
    .dropdown-content {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        background-color: #fff;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        min-width: 200px;
        z-index: 100;
        border: 1px solid #f3f4f6;
        border-radius: 6px;
        padding: 5px 0;
        margin-top: 5px;
    }

    .dropdown:hover .dropdown-content {
        display: block;
        animation: fadeIn 0.2s ease-in-out;
    }

    .dropdown-link {
        display: block;
        padding: 10px 16px;
        text-decoration: none;
        color: #4b5563;
        font-size: 14px;
        font-weight: 400;
        transition: 0.15s;
    }

    .dropdown-link:hover {
        background-color: #f9fafb;
        color: #FF9B44;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-5px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
    lucide.createIcons();
</script>