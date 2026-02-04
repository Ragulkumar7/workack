<?php 
include '../include/header.php'; 
include '../include/sidebar.php'; 

// Check if we are in the "Add" view or "List" view
$view = isset($_GET['page']) ? $_GET['page'] : 'list';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees List | Workack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .page-wrapper { margin-left: 260px; min-height: 100vh; transition: all 0.2s ease-in-out; }
        .header-container { display: flex; flex-direction: column; width: 100%; padding: 1.5rem 1.5rem 0.5rem 1.5rem; }
        .custom-scrollbar::-webkit-scrollbar { height: 6px; width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
        @media (max-width: 1024px) { .page-wrapper { margin-left: 0; padding-top: 60px; } }
    </style>
</head>
<body>

<div class="page-wrapper">

    <?php if ($view === 'add'): ?>
        <div class="p-6 border-b bg-white flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-slate-800">Add New Employee <span class="text-sm font-normal text-gray-400 ml-2">Employee ID : EMP - 0024</span></h2>
            </div>
            <a href="employeelist.php" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="fa-solid fa-circle-xmark"></i>
            </a>
        </div>

        <div class="px-8 pt-4 bg-white">
            <div class="flex gap-8 border-b">
                <button class="border-b-2 border-orange-500 pb-3 text-orange-600 font-bold">Basic Information</button>
                <button class="pb-3 text-gray-500 font-medium">Permissions</button>
            </div>
        </div>

        <div class="p-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
                <form action="#" method="POST">
                    <div class="flex items-center gap-6 mb-10 p-4 bg-gray-50 rounded-lg border border-dashed border-gray-200">
                        <div class="w-24 h-24 bg-white border-2 border-dashed border-gray-200 rounded-full flex items-center justify-center text-gray-300">
                            <i class="fa-regular fa-image text-3xl"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800">Upload Profile Image</h4>
                            <p class="text-sm text-gray-400">Image should be below 4 mb</p>
                            <div class="mt-2 flex gap-2">
                                <button type="button" class="bg-orange-500 text-white px-4 py-1.5 rounded text-sm font-bold">Upload</button>
                                <button type="button" class="bg-white text-gray-600 px-4 py-1.5 rounded text-sm font-bold border">Cancel</button>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">First Name <span class="text-red-500">*</span></label>
                            <input type="text" name="first_name" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Last Name</label>
                            <input type="text" name="last_name" class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Employee ID <span class="text-red-500">*</span></label>
                            <input type="text" name="employee_id" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Joining Date <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="date" name="joining_date" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Username <span class="text-red-500">*</span></label>
                            <input type="text" name="username" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Email <span class="text-red-500">*</span></label>
                            <input type="email" name="email" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="password" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                                <i class="fa-regular fa-eye-slash absolute right-4 top-3.5 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Confirm Password <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <input type="password" name="confirm_password" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                                <i class="fa-regular fa-eye-slash absolute right-4 top-3.5 text-gray-400"></i>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Phone Number <span class="text-red-500">*</span></label>
                            <input type="text" name="phone" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Company <span class="text-red-500">*</span></label>
                            <input type="text" name="company" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Department</label>
                            <select name="department" class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 appearance-none cursor-pointer">
                                <option value="">Select</option>
                                <option value="Finance">Finance</option>
                                <option value="IT">IT</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Designation</label>
                            <select name="designation" class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 appearance-none cursor-pointer">
                                <option value="">Select</option>
                                <option value="Manager">Manager</option>
                                <option value="Developer">Developer</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">About <span class="text-red-500">*</span></label>
                        <textarea name="about" rows="4" required class="w-full border rounded-lg px-4 py-2.5 bg-gray-50 outline-none focus:border-orange-500 transition-all"></textarea>
                    </div>

                    <div class="flex justify-end gap-4 mt-10">
                        <a href="employeelist.php" class="px-10 py-2.5 border rounded-lg font-bold text-gray-600 hover:bg-gray-50 transition-all text-center">Cancel</a>
                        <button type="submit" class="px-10 py-2.5 bg-orange-600 text-white rounded-lg font-bold hover:bg-orange-700 transition-all shadow-lg shadow-orange-500/30">Save</button>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div class="header-container">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Employees List</h1>
                    <nav class="text-sm text-gray-500 mt-1">
                        <span class="hover:text-orange-500 cursor-pointer">Employees</span> 
                        <i class="fa-solid fa-chevron-right text-[10px] mx-1"></i> 
                        <span class="text-slate-800 font-medium">Employees List</span>
                    </nav>
                </div>
                
                <div class="flex items-center gap-3">
                    <div class="flex bg-white rounded shadow-sm border p-1">
                        <button class="bg-orange-500 text-white p-2 rounded px-3"><i class="fa-solid fa-list-ul"></i></button>
                        <button class="text-gray-400 p-2 px-3 hover:text-orange-500"><i class="fa-solid fa-grip"></i></button>
                    </div>
                    <button class="bg-white border text-gray-700 px-4 py-2 rounded shadow-sm hover:bg-gray-50 flex items-center">
                        <i class="fa-solid fa-file-export mr-2"></i> Export <i class="fa-solid fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    <a href="?page=add" class="bg-orange-600 text-white px-4 py-2 rounded shadow-sm hover:bg-orange-700 font-medium flex items-center">
                        <i class="fa-solid fa-circle-plus mr-2"></i> Add Employee
                    </a>
                    <button class="bg-white border p-2 rounded shadow-sm hover:bg-gray-50"><i class="fa-solid fa-chevron-up text-xs"></i></button>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <?php
                $stats = [
                    ['label' => 'Total...', 'value' => '1007', 'growth' => '+19.01%', 'icon' => 'fa-users', 'icon_bg' => 'bg-gray-100', 'txt' => 'text-gray-800', 'badge' => 'bg-purple-100 text-purple-600'],
                    ['label' => 'Active', 'value' => '1007', 'growth' => '+19.01%', 'icon' => 'fa-user-check', 'icon_bg' => 'bg-green-50', 'txt' => 'text-green-600', 'badge' => 'bg-orange-100 text-orange-600'],
                    ['label' => 'InActive', 'value' => '1007', 'growth' => '+19.01%', 'icon' => 'fa-user-slash', 'icon_bg' => 'bg-red-50', 'txt' => 'text-red-600', 'badge' => 'bg-gray-100 text-gray-600'],
                    ['label' => 'New...', 'value' => '67', 'growth' => '+19.01%', 'icon' => 'fa-user-plus', 'icon_bg' => 'bg-blue-50', 'txt' => 'text-blue-600', 'badge' => 'bg-cyan-100 text-cyan-600'],
                ];
                foreach ($stats as $s): ?>
                <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm flex items-center justify-between hover:shadow-md transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="<?= $s['icon_bg'] ?> <?= $s['txt'] ?> w-12 h-12 rounded-full flex items-center justify-center text-xl">
                            <i class="fa-solid <?= $s['icon'] ?>"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm font-medium"><?= $s['label'] ?></p>
                            <h2 class="text-2xl font-bold text-slate-800"><?= $s['value'] ?></h2>
                        </div>
                    </div>
                    <div class="<?= $s['badge'] ?> px-2 py-1 rounded-md text-xs font-bold flex items-center gap-1">
                        <i class="fa-solid fa-arrow-trend-up"></i> <?= $s['growth'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex flex-wrap items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-3">
                        <h3 class="font-bold text-slate-800 mr-2 text-lg">Plan List</h3>
                        <div class="flex items-center gap-2 border rounded-lg px-3 py-2 text-sm text-gray-600 bg-white cursor-pointer hover:border-orange-500">
                            <i class="fa-regular fa-calendar text-orange-500"></i>
                            <span>01/28/2026 - 02/03/2026</span>
                        </div>
                        <select class="border rounded-lg px-3 py-2 text-sm text-gray-600 outline-none focus:border-orange-500 cursor-pointer"><option>Designation</option></select>
                        <select class="border rounded-lg px-3 py-2 text-sm text-gray-600 outline-none focus:border-orange-500 cursor-pointer"><option>Select Status</option></select>
                        <select class="border rounded-lg px-3 py-2 text-sm text-gray-600 outline-none focus:border-orange-500 cursor-pointer"><option>Sort By : Last 7 Days</option></select>
                    </div>
                </div>

                <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div class="text-sm text-gray-600">
                        Row Per Page <select class="border rounded p-1 mx-1 outline-none focus:border-orange-500"><option>10</option></select> Entries
                    </div>
                    <div class="relative w-full md:w-64">
                        <input type="text" placeholder="Search" class="border rounded-lg pl-3 pr-10 py-2 text-sm outline-none focus:ring-1 focus:ring-orange-500 w-full">
                        <i class="fa-solid fa-magnifying-glass absolute right-3 top-3 text-gray-400 text-xs"></i>
                    </div>
                </div>

                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full text-left whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50 border-y border-gray-100 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                                <th class="p-4 w-4"><input type="checkbox" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"></th>
                                <th class="p-4">Emp ID <i class="fa-solid fa-sort ml-1 text-gray-300"></i></th>
                                <th class="p-4">Name <i class="fa-solid fa-sort ml-1 text-gray-300"></i></th>
                                <th class="p-4">Email <i class="fa-solid fa-sort ml-1 text-gray-300"></i></th>
                                <th class="p-4">Phone <i class="fa-solid fa-sort ml-1 text-gray-300"></i></th>
                                <th class="p-4">Designation <i class="fa-solid fa-sort ml-1 text-gray-300"></i></th>
                                <th class="p-4">Joining Date <i class="fa-solid fa-sort ml-1 text-gray-300"></i></th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-600">
                            <?php
                            $employees = [
                                ['id' => 'Emp-001', 'name' => 'Anthony Lewis', 'sub' => 'Finance', 'email' => 'anthony@example.com', 'phone' => '(123) 4567 890', 'role' => 'Finance', 'date' => '12 Sep 2024'],
                                ['id' => 'Emp-002', 'name' => 'Brian Villalobos', 'sub' => 'Developer', 'email' => 'brian@example.com', 'phone' => '(179) 7382 829', 'role' => 'Developer', 'date' => '24 Oct 2024'],
                                ['id' => 'Emp-003', 'name' => 'Harvey Smith', 'sub' => 'Developer', 'email' => 'harvey@example.com', 'phone' => '(184) 2719 738', 'role' => 'Developer', 'date' => '18 Feb 2024'],
                                ['id' => 'Emp-004', 'name' => 'Stephan Peralt', 'sub' => 'Executive Officer', 'email' => 'peral@example.com', 'phone' => '(193) 7839 748', 'role' => 'Executive', 'date' => '17 Oct 2024'],
                                ['id' => 'Emp-005', 'name' => 'Doglas Martini', 'sub' => 'Manager', 'email' => 'martniwr@example.com', 'phone' => '(183) 9302 890', 'role' => 'Manager', 'date' => '20 Jul 2024'],
                                ['id' => 'Emp-006', 'name' => 'Linda Ray', 'sub' => 'Finance', 'email' => 'ray456@example.com', 'phone' => '(120) 3728 039', 'role' => 'Finance', 'date' => '10 Apr 2024'],
                                ['id' => 'Emp-007', 'name' => 'Elliot Murray', 'sub' => 'Finance', 'email' => 'murray@example.com', 'phone' => '(102) 8480 832', 'role' => 'Developer', 'date' => '29 Aug 2024'],
                                ['id' => 'Emp-008', 'name' => 'Rebecca Smtih', 'sub' => 'Executive', 'email' => 'smtih@example.com', 'phone' => '(162) 8920 713', 'role' => 'Executive', 'date' => '22 Feb 2024'],
                                ['id' => 'Emp-009', 'name' => 'Connie Waters', 'sub' => 'Developer', 'email' => 'connie@example.com', 'phone' => '(189) 0920 723', 'role' => 'Developer', 'date' => '03 Nov 2024'],
                                ['id' => 'Emp-010', 'name' => 'Lori Broaddus', 'sub' => 'Finance', 'email' => 'broaddus@example.com', 'phone' => '(168) 8392 823', 'role' => 'Finance', 'date' => '17 Dec 2024'],
                            ];

                            foreach ($employees as $e): ?>
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td class="p-4"><input type="checkbox" class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"></td>
                                <td class="p-4 font-medium text-slate-700"><?= $e['id'] ?></td>
                                <td class="p-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-slate-200 overflow-hidden border border-gray-100 shadow-sm">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($e['name']) ?>&background=random" class="w-full h-full object-cover" alt="<?= $e['name'] ?>">
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 leading-tight"><?= $e['name'] ?></div>
                                            <div class="text-[11px] text-gray-400 font-medium"><?= $e['sub'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="p-4 text-gray-500 font-medium"><?= $e['email'] ?></td>
                                <td class="p-4 text-gray-500"><?= $e['phone'] ?></td>
                                <td class="p-4">
                                    <select class="border rounded-lg px-2 py-1.5 bg-white text-xs font-semibold outline-none focus:ring-1 focus:ring-orange-500 w-32 cursor-pointer">
                                        <option <?= $e['role'] == 'Finance' ? 'selected' : '' ?>>Finance</option>
                                        <option <?= $e['role'] == 'Developer' ? 'selected' : '' ?>>Developer</option>
                                        <option <?= $e['role'] == 'Executive' ? 'selected' : '' ?>>Executive</option>
                                        <option <?= $e['role'] == 'Manager' ? 'selected' : '' ?>>Manager</option>
                                    </select>
                                </td>
                                <td class="p-4 text-gray-400 font-medium"><?= $e['date'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<div class="fixed bottom-6 right-6 bg-orange-500 p-3 rounded-lg text-white shadow-lg cursor-pointer hover:bg-orange-600 transition-all z-50">
    <i class="fa-solid fa-gear"></i>
</div>

</body>
</html>