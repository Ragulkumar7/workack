<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timesheets | Workack</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Sidebar width compensation - adjust based on your actual sidebar width */
        .main-content { margin-left: 250px; padding-top: 70px; }
        .modal-overlay { display: none; transition: opacity 0.3s ease; }
        .modal-overlay.active { display: flex; }
    </style>
</head>
<body class="bg-[#f8f9fa] font-sans">

<div class="main-content min-h-screen p-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-[#334155]">Timesheets</h1>
            <nav class="text-xs text-gray-400 mt-1">
                Attendance &nbsp;›&nbsp; <span class="text-gray-500">Timesheets</span>
            </nav>
        </div>
        <div class="flex gap-2">
            <button class="bg-white border border-gray-200 px-4 py-2 rounded text-sm flex items-center gap-2 hover:bg-gray-50 shadow-sm text-gray-600">
                <i class="fa fa-file-export text-gray-400"></i> Export <i class="fa fa-chevron-down text-[10px]"></i>
            </button>
            <button onclick="toggleModal(true)" class="bg-[#ff5a1f] text-white px-5 py-2 rounded text-sm font-semibold flex items-center gap-2 hover:bg-orange-600 shadow-md transition">
                <i class="fa fa-plus-circle"></i> Add Today's Work
            </button>
            <button class="bg-white border border-gray-200 px-3 py-2 rounded shadow-sm">
                <i class="fa fa-chevron-up text-xs text-gray-400"></i>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-6 flex justify-between items-center border-b border-gray-50">
            <h2 class="text-lg font-bold text-[#334155]">Timesheet</h2>
            <div class="flex gap-3">
                <select class="border border-gray-200 rounded-md px-3 py-2 text-xs bg-white text-gray-500 outline-none">
                    <option>Select Project</option>
                </select>
                <select class="border border-gray-200 rounded-md px-3 py-2 text-xs bg-white text-gray-500 outline-none">
                    <option>Sort By : Last 7 Days</option>
                </select>
            </div>
        </div>

        <div class="px-6 py-4 flex justify-between items-center bg-white">
            <div class="text-xs text-gray-400">
                Row Per Page 
                <select class="border-b border-gray-300 mx-1 bg-transparent text-gray-600 focus:outline-none">
                    <option>25</option>
                </select> 
                Entries
            </div>
            <div class="relative">
                <input type="text" placeholder="Search" class="border border-gray-200 rounded-md pl-3 pr-10 py-1.5 text-xs text-gray-600 focus:ring-1 focus:ring-orange-500 outline-none w-64">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="bg-gray-50 text-[11px] uppercase tracking-wider font-bold text-gray-500 border-y border-gray-100">
                        <th class="px-6 py-4">Employee <i class="fa fa-sort ml-1 opacity-30"></i></th>
                        <th class="px-6 py-4">Date <i class="fa fa-sort ml-1 opacity-30"></i></th>
                        <th class="px-6 py-4">Project <i class="fa fa-sort ml-1 opacity-30"></i></th>
                        <th class="px-6 py-4">Assigned Hours <i class="fa fa-sort ml-1 opacity-30"></i></th>
                        <th class="px-6 py-4">Worked Hours <i class="fa fa-sort ml-1 opacity-30"></i></th>
                        <th class="px-6 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 text-sm">
                    <?php
                    $data = [
                        ['name' => 'Anthony Lewis', 'role' => 'UI/UX Team', 'date' => '14 Jan 2024', 'proj' => 'Office Management', 'ah' => 32, 'wh' => 13, 'img' => 'https://i.pravatar.cc/150?u=a'],
                        ['name' => 'Brian Villalobos', 'role' => 'Development', 'date' => '21 Jan 2024', 'proj' => 'Project Management', 'ah' => 45, 'wh' => 14, 'img' => 'https://i.pravatar.cc/150?u=b'],
                        ['name' => 'Doglas Martini', 'role' => 'Development', 'date' => '12 Apr 2024', 'proj' => 'Office Management', 'ah' => 36, 'wh' => 45, 'img' => 'https://i.pravatar.cc/150?u=d'],
                        ['name' => 'Elliot Murray', 'role' => 'Developer', 'date' => '06 Jul 2024', 'proj' => 'Video Calling App', 'ah' => 57, 'wh' => 16, 'img' => 'https://i.pravatar.cc/150?u=e'],
                        ['name' => 'Harvey Smith', 'role' => 'HR', 'date' => '20 Feb 2024', 'proj' => 'Project Management', 'ah' => 45, 'wh' => 22, 'img' => 'https://i.pravatar.cc/150?u=h'],
                        ['name' => 'Linda Ray', 'role' => 'UI/UX Team', 'date' => '20 Apr 2024', 'proj' => 'Hospital Administration', 'ah' => 49, 'wh' => 14, 'img' => 'https://i.pravatar.cc/150?u=l'],
                        ['name' => 'Rebecca Smtih', 'role' => 'UI/UX Team', 'date' => '02 Sep 2024', 'proj' => 'Office Management', 'ah' => 21, 'wh' => 18, 'img' => 'https://i.pravatar.cc/150?u=r'],
                        ['name' => 'Stephan Peralt', 'role' => 'Management', 'date' => '15 Mar 2024', 'proj' => 'Hospital Administration', 'ah' => 45, 'wh' => 78, 'img' => 'https://i.pravatar.cc/150?u=s'],
                    ];

                    foreach ($data as $row): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <img src="<?= $row['img'] ?>" class="w-9 h-9 rounded-full border border-gray-200">
                            <div>
                                <div class="font-bold text-gray-700"><?= $row['name'] ?></div>
                                <div class="text-[11px] text-gray-400"><?= $row['role'] ?></div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?= $row['date'] ?></td>
                        <td class="px-6 py-4 font-bold text-gray-700">
                            <?= $row['proj'] ?> <i class="fa fa-info-circle text-blue-400 text-[10px] ml-1"></i>
                        </td>
                        <td class="px-6 py-4 text-gray-500"><?= $row['ah'] ?></td>
                        <td class="px-6 py-4 text-gray-500"><?= $row['wh'] ?></td>
                        <td class="px-6 py-4 text-right space-x-2">
                            <button class="text-gray-300 hover:text-orange-500"><i class="fa fa-edit"></i></button>
                            <button class="text-gray-300 hover:text-red-500"><i class="fa fa-trash"></i></button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="px-6 py-6 flex justify-between items-center border-t border-gray-100">
            <p class="text-sm text-gray-400">Showing 1 - 10 of 10 entries</p>
            <div class="flex gap-1 items-center">
                <button class="p-2 text-gray-300 hover:text-gray-600"><i class="fa fa-chevron-left text-[10px]"></i></button>
                <button class="w-8 h-8 rounded-full bg-[#ff5a1f] text-white text-sm font-bold shadow-md shadow-orange-200">1</button>
                <button class="p-2 text-gray-300 hover:text-gray-600"><i class="fa fa-chevron-right text-[10px]"></i></button>
            </div>
        </div>
    </div>
</div>

<div class="fixed right-0 top-1/2 -translate-y-1/2 bg-[#ff5a1f] p-2.5 rounded-l-md shadow-lg cursor-pointer">
    <i class="fa fa-cog text-white animate-spin-slow"></i>
</div>

<div id="addWorkModal" class="modal-overlay fixed inset-0 bg-black/50 z-[100] items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h3 class="text-xl font-bold text-[#1f2937]">Add Todays Work</h3>
            <button onclick="toggleModal(false)" class="text-gray-400 hover:text-gray-600">
                <i class="fa fa-times-circle text-2xl"></i>
            </button>
        </div>
        
        <form class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Project <span class="text-red-500">*</span></label>
                <select class="w-full border border-gray-200 rounded-md p-2.5 text-sm text-gray-400 focus:ring-1 focus:ring-orange-500 outline-none">
                    <option>Select</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-bold text-gray-700 mb-1">Deadline <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="text" placeholder="dd/mm/yyyy" class="w-full border border-gray-200 rounded-md p-2.5 text-sm outline-none">
                    <i class="fa fa-calendar-alt absolute right-3 top-3.5 text-gray-300"></i>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Total Hours <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full border border-gray-200 rounded-md p-2.5 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Remaining Hours <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full border border-gray-200 rounded-md p-2.5 outline-none">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" placeholder="dd/mm/yyyy" class="w-full border border-gray-200 rounded-md p-2.5 text-sm outline-none">
                        <i class="fa fa-calendar-alt absolute right-3 top-3.5 text-gray-300"></i>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">Hours <span class="text-red-500">*</span></label>
                    <input type="text" class="w-full border border-gray-200 rounded-md p-2.5 outline-none">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="toggleModal(false)" class="px-6 py-2 bg-gray-50 text-gray-600 rounded-md border border-gray-100 font-semibold hover:bg-gray-100 transition">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-[#ff5a1f] text-white rounded-md font-semibold hover:bg-orange-600 shadow-md shadow-orange-200 transition">Add Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal(show) {
        const modal = document.getElementById('addWorkModal');
        if (show) {
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        } else {
            modal.classList.remove('active');
            document.body.style.overflow = 'auto';
        }
    }
</script>

</body>
</html>