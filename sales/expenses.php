<?php include '../include/header.php'; ?>
<div class="flex" style="min-height: 100vh;">
    <?php include '../include/sidebar.php'; ?>

    <div class="flex-1 bg-[#f8f9fa] p-6" style="width: 100%;">
        
        <?php
        // Hardcoded data for the table
        $expenses = [
            ['name' => 'Online Course', 'date' => '14 Jan 2024', 'method' => 'Cash', 'amount' => 3000],
            ['name' => 'Employee Benefits', 'date' => '21 Jan 2024', 'method' => 'Cash', 'amount' => 2500],
            ['name' => 'Travel', 'date' => '20 Feb 2024', 'method' => 'Cheque', 'amount' => 2800],
            ['name' => 'Office Supplies', 'date' => '15 Mar 2024', 'method' => 'Cash', 'amount' => 3300],
            ['name' => 'Welcome Kit', 'date' => '12 Apr 2024', 'method' => 'Cheque', 'amount' => 3600],
            ['name' => 'Equipment', 'date' => '20 Apr 2024', 'method' => 'Cheque', 'amount' => 2000],
        ];
        ?>

        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Expenses</h1>
                <nav class="text-sm text-gray-500 flex items-center gap-2">
                    <i class="fa fa-home"></i> <span>/</span> <span>Sales</span> <span>/</span> <span class="text-gray-400">Expenses</span>
                </nav>
            </div>
            <div class="flex gap-2">
                <button class="bg-white border border-gray-200 px-4 py-2 rounded-md flex items-center gap-2 text-sm">
                    <i class="fa fa-file-export text-gray-400"></i> Export <i class="fa fa-chevron-down text-[10px]"></i>
                </button>
                <button onclick="openModal()" class="bg-[#ff5b21] text-white px-4 py-2 rounded-md flex items-center gap-2 text-sm font-semibold hover:bg-orange-600">
                    <i class="fa fa-plus-circle"></i> Add New Expenses
                </button>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-700">Expenses List</h2>
                <div class="flex gap-2">
                    <input type="text" value="01/29/2026 - 02/04/2026" class="border rounded px-3 py-1 text-xs text-gray-500">
                    <select class="border rounded px-3 py-1 text-xs text-gray-500"><option>$0.00 - $00</option></select>
                    <select class="border rounded px-3 py-1 text-xs text-gray-500"><option>Sort By : Last 7 Days</option></select>
                </div>
            </div>

            <div class="p-4 flex justify-between items-center bg-white">
                <div class="text-xs text-gray-500">
                    Row Per Page <select class="border rounded px-1 py-0.5 mx-1"><option>10</option></select> Entries
                </div>
                <input type="text" placeholder="Search" class="border rounded px-3 py-1.5 text-xs w-48 outline-none focus:border-orange-400">
            </div>

            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 border-y border-gray-100">
                    <tr>
                        <th class="p-4 w-10 text-center"><input type="checkbox"></th>
                        <th class="p-4 font-semibold text-gray-700">Expense Name <i class="fa fa-sort text-gray-300 ml-1"></i></th>
                        <th class="p-4 font-semibold text-gray-700">Date <i class="fa fa-sort text-gray-300 ml-1"></i></th>
                        <th class="p-4 font-semibold text-gray-700">Payment Method <i class="fa fa-sort text-gray-300 ml-1"></i></th>
                        <th class="p-4 font-semibold text-gray-700">Amount <i class="fa fa-sort text-gray-300 ml-1"></i></th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <?php foreach ($expenses as $row): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="p-4 text-center"><input type="checkbox"></td>
                        <td class="p-4 font-medium text-gray-800"><?php echo $row['name']; ?></td>
                        <td class="p-4 text-gray-600"><?php echo $row['date']; ?></td>
                        <td class="p-4 text-gray-600"><?php echo $row['method']; ?></td>
                        <td class="p-4 font-semibold text-gray-700">$<?php echo $row['amount']; ?></td>
                        <td class="p-4 text-right text-gray-400">
                            <i class="fa-regular fa-pen-to-square mx-2 cursor-pointer hover:text-blue-500"></i>
                            <i class="fa-regular fa-trash-can cursor-pointer hover:text-red-500"></i>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="addModal" class="hidden fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg w-full max-w-md overflow-hidden shadow-2xl">
        <div class="flex justify-between items-center p-5 border-b">
            <h3 class="text-xl font-bold text-[#1f2937]">Add Expenses</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fa-solid fa-circle-xmark text-xl"></i>
            </button>
        </div>
        <form class="p-6 space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Expenses</label>
                <input type="text" class="w-full border border-gray-200 rounded-lg p-2.5 outline-none focus:border-orange-400">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Date</label>
                <div class="relative">
                    <input type="text" placeholder="dd/mm/yyyy" class="w-full border border-gray-200 rounded-lg p-2.5 outline-none focus:border-orange-400">
                    <i class="fa fa-calendar absolute right-3 top-3.5 text-gray-400"></i>
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Amount</label>
                <input type="text" class="w-full border border-gray-200 rounded-lg p-2.5 outline-none focus:border-orange-400">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Payment Method</label>
                <select class="w-full border border-gray-200 rounded-lg p-2.5 outline-none bg-white">
                    <option>Select</option>
                    <option>Cash</option>
                    <option>Cheque</option>
                </select>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" onclick="closeModal()" class="px-6 py-2 border rounded-md font-semibold text-gray-600 hover:bg-gray-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-[#ff5b21] text-white rounded-md font-semibold hover:bg-orange-600">Add Expenses</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openModal() {
        document.getElementById('addModal').classList.remove('hidden');
    }
    function closeModal() {
        document.getElementById('addModal').classList.add('hidden');
    }
</script>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">