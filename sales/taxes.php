<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taxes - Neoera Infotech</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50">

<div class="flex">
    <main class="flex-1 p-6">
        <div class="max-w-7xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="flex items-center justify-between p-6 border-b border-gray-100">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Taxes</h1>
                    <nav class="text-sm text-gray-500 mt-1">
                        <span>Home</span> &gt; <span>Sales</span> &gt; <span class="text-gray-800">Taxes</span>
                    </nav>
                </div>
                <div class="flex gap-3">
                    <button class="flex items-center gap-2 px-4 py-2 border rounded-md hover:bg-gray-50 text-gray-700">
                        <i data-lucide="download" class="w-4 h-4"></i> Export <i data-lucide="chevron-down" class="w-4 h-4"></i>
                    </button>
                    <button onclick="toggleModal('addTaxModal')" class="flex items-center gap-2 px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Tax
                    </button>
                </div>
            </div>

            <div class="p-6 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <h2 class="font-semibold text-gray-700">Tax List</h2>
                    <div class="relative">
                        <input type="text" value="01/29/2026 - 02/04/2026" class="border rounded-md px-3 py-1.5 text-sm text-gray-600 focus:outline-none focus:ring-1 focus:ring-orange-500">
                    </div>
                    <select class="border rounded-md px-3 py-1.5 text-sm text-gray-600">
                        <option>Taxes List</option>
                    </select>
                    <select class="border rounded-md px-3 py-1.5 text-sm text-gray-600">
                        <option>Sort By : Last 7 Days</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-500">Row Per Page</span>
                    <select class="border rounded-md px-2 py-1 text-sm">
                        <option>10</option>
                    </select>
                    <input type="text" placeholder="Search" class="border rounded-md px-3 py-1.5 text-sm ml-4 focus:outline-none focus:ring-1 focus:ring-orange-500">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-600 uppercase text-xs font-semibold">
                        <tr>
                            <th class="p-4"><input type="checkbox" class="rounded"></th>
                            <th class="p-4 border-b">Tax Name <span class="inline-block ml-1">⇅</span></th>
                            <th class="p-4 border-b">Tax Percentage(%) <span class="inline-block ml-1">⇅</span></th>
                            <th class="p-4 border-b">Status <span class="inline-block ml-1">⇅</span></th>
                            <th class="p-4 border-b text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm">
                        <?php
                        $taxes = [
                            ['name' => 'VAT', 'rate' => '20%', 'status' => 'Active'],
                            ['name' => 'GST', 'rate' => '18%', 'status' => 'Active'],
                            ['name' => 'Income Tax', 'rate' => '30%', 'status' => 'Inactive'],
                            ['name' => 'Corporate Tax', 'rate' => '25%', 'status' => 'Inactive'],
                        ];

                        foreach ($taxes as $index => $tax):
                            $isActive = $tax['status'] === 'Active';
                        ?>
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                            <td class="p-4"><input type="checkbox" class="rounded"></td>
                            <td class="p-4 font-medium">
                                <?php echo $tax['name']; ?> 
                                <span class="text-blue-400 cursor-help" title="Info">ⓘ</span>
                            </td>
                            <td class="p-4"><?php echo $tax['rate']; ?></td>
                            <td class="p-4">
                                <div class="relative inline-block text-left">
                                    <button onclick="toggleStatusDropdown(<?php echo $index; ?>)" 
                                            id="status-btn-<?php echo $index; ?>"
                                            class="flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium <?php echo $isActive ? 'bg-green-50 text-green-600 border border-green-200' : 'bg-red-50 text-red-600 border border-red-200'; ?>">
                                        <span class="w-2 h-2 rounded-full <?php echo $isActive ? 'bg-green-500' : 'bg-red-500'; ?>"></span>
                                        <?php echo $tax['status']; ?>
                                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                                    </button>

                                    <div id="dropdown-<?php echo $index; ?>" 
                                         class="hidden absolute left-0 mt-2 w-32 origin-top-left rounded-md bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-20">
                                        <div class="py-1">
                                            <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Active</button>
                                            <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Inactive</button>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex justify-end gap-3 text-gray-400">
                                    <button class="hover:text-blue-500"><i data-lucide="edit-3" class="w-4 h-4"></i></button>
                                    <button class="hover:text-red-500"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="addTaxModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
        <div class="flex items-center justify-between p-4 border-b">
            <h3 class="text-xl font-bold text-slate-800">Add Tax</h3>
            <button onclick="toggleModal('addTaxModal')" class="text-gray-400 hover:text-gray-600">
                <i data-lucide="x-circle" class="w-6 h-6"></i>
            </button>
        </div>
        
        <form class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tax Name</label>
                <input type="text" placeholder="Enter Tax Name" class="w-full border border-gray-200 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Tax Percentage(%)</label>
                <input type="text" placeholder="Enter Tax Percentage" class="w-full border border-gray-200 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Description</label>
                <textarea rows="4" placeholder="Enter Description" class="w-full border border-gray-200 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500"></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4">
                <button type="button" onclick="toggleModal('addTaxModal')" class="px-6 py-2 border border-gray-200 text-gray-700 rounded-md font-semibold hover:bg-gray-50">
                    Cancel
                </button>
                <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-md font-semibold hover:bg-orange-600">
                    Add Tax
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    lucide.createIcons();

    function toggleStatusDropdown(index) {
        const dropdown = document.getElementById(`dropdown-${index}`);
        document.querySelectorAll('[id^="dropdown-"]').forEach(el => {
            if (el.id !== `dropdown-${index}`) el.classList.add('hidden');
        });
        dropdown.classList.toggle('hidden');
    }

    function toggleModal(modalId) {
        const modal = document.getElementById(modalId);
        modal.classList.toggle('hidden');
    }

    window.addEventListener('click', function(e) {
        if (!e.target.closest('button[id^="status-btn-"]')) {
            document.querySelectorAll('[id^="dropdown-"]').forEach(el => el.classList.add('hidden'));
        }
        const modal = document.getElementById('addTaxModal');
        if (e.target === modal) {
            modal.classList.add('hidden');
        }
    });
</script>

</body>
</html>