<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<?php
// Static data for the Estimates List table
$estimates = [
    ["name" => "Michael Walker", "role" => "CEO", "avatar" => "https://i.pravatar.cc/150?u=michael", "company" => "BrightWave Innovations", "date" => "14 Jan 2024", "expiry" => "15 Jan 2024", "amount" => "$3000", "status" => "Accepted", "class" => "bg-green-100 text-green-700"],
    ["name" => "Sophie Headrick", "role" => "Manager", "avatar" => "https://i.pravatar.cc/150?u=sophie", "company" => "Stellar Dynamics", "date" => "21 Jan 2024", "expiry" => "25 Jan 2024", "amount" => "$2500", "status" => "Sent", "class" => "bg-purple-100 text-purple-700"]
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; margin: 0; }
        .modal-active { display: flex !important; }
        /* Ensures the table and content utilize the page width beside the sidebar */
        .main-wrapper { width: 100%; padding: 20px; }
    </style>
</head>
<body>

<div class="main-wrapper">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Estimates</h1>
            <nav class="text-sm text-slate-500">
                <i class="fa fa-home"></i> &nbsp;>&nbsp; Sales &nbsp;>&nbsp; <span class="text-slate-800">Estimates</span>
            </nav>
        </div>
        <div class="flex gap-2">
            <button onclick="toggleModal()" class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                <i class="fa fa-plus-circle"></i> Add Estimates
            </button>
            <button class="bg-white border p-2 rounded-lg text-slate-400"><i class="fa fa-chevron-up"></i></button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200">
        <div class="p-4 border-b border-slate-100 flex justify-between items-center">
            <h2 class="font-semibold text-slate-700">Estimates List</h2>
            <div class="flex gap-3">
                <select class="border border-slate-200 rounded-md px-3 py-1 text-sm text-slate-600 outline-none"><option>Select Status</option></select>
                <select class="border border-slate-200 rounded-md px-3 py-1 text-sm text-slate-600 outline-none"><option>Sort By : Last 7 Days</option></select>
            </div>
        </div>
        
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-700 text-sm font-semibold border-y border-slate-100">
                        <th class="p-4"><input type="checkbox"></th>
                        <th class="p-4">Client Name</th>
                        <th class="p-4">Company Name</th>
                        <th class="p-4">Estimate Date</th>
                        <th class="p-4">Expiry Date</th>
                        <th class="p-4">Amount</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="text-slate-600 text-sm">
                    <?php foreach ($estimates as $row): ?>
                    <tr class="border-b border-slate-50 hover:bg-slate-50">
                        <td class="p-4"><input type="checkbox"></td>
                        <td class="p-4 flex items-center gap-3">
                            <img src="<?= $row['avatar'] ?>" class="w-10 h-10 rounded-full border">
                            <div><div class="font-bold text-slate-800"><?= $row['name'] ?></div><div class="text-xs text-slate-400"><?= $row['role'] ?></div></div>
                        </td>
                        <td class="p-4"><?= $row['company'] ?></td>
                        <td class="p-4"><?= $row['date'] ?></td>
                        <td class="p-4"><?= $row['expiry'] ?></td>
                        <td class="p-4 font-medium"><?= $row['amount'] ?></td>
                        <td class="p-4"><span class="px-3 py-1 rounded-md text-[11px] font-bold <?= $row['class'] ?>"><?= $row['status'] ?></span></td>
                        <td class="p-4 text-right"><i class="fa fa-edit text-slate-400 hover:text-blue-500 mx-1 cursor-pointer"></i> <i class="fa fa-trash-can text-slate-400 hover:text-red-500 mx-1 cursor-pointer"></i></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div id="addEstimateModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm hidden items-center justify-center z-[100] p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-5xl max-h-[95vh] flex flex-col">
        <div class="flex justify-between items-center px-6 py-4 border-b">
            <h2 class="text-xl font-bold text-slate-800">Add New Estimate</h2>
            <button onclick="toggleModal()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        </div>

        <form action="#" class="flex-1 overflow-y-auto p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div><label class="block text-sm font-semibold mb-1">Client <span class="text-red-500">*</span></label><select class="w-full border rounded-md p-2 bg-slate-50"><option>Select</option></select></div>
                <div><label class="block text-sm font-semibold mb-1">Project <span class="text-red-500">*</span></label><select class="w-full border rounded-md p-2 bg-slate-50"><option>Select</option></select></div>
                <div><label class="block text-sm font-semibold mb-1">Email <span class="text-red-500">*</span></label><input type="email" class="w-full border rounded-md p-2"></div>
                <div><label class="block text-sm font-semibold mb-1">Tax <span class="text-red-500">*</span></label><select class="w-full border rounded-md p-2 bg-slate-50"><option>Select</option></select></div>
                <div><label class="block text-sm font-semibold mb-1">Client Address</label><textarea rows="2" class="w-full border rounded-md p-2"></textarea></div>
                <div><label class="block text-sm font-semibold mb-1">Billing Address</label><textarea rows="2" class="w-full border rounded-md p-2"></textarea></div>
                <div><label class="block text-sm font-semibold mb-1 text-slate-700">Estimate Date</label><input type="date" class="w-full border rounded-md p-2 text-slate-500"></div>
                <div><label class="block text-sm font-semibold mb-1 text-slate-700">Expiry Date</label><input type="date" class="w-full border rounded-md p-2 text-slate-500"></div>
            </div>

            <hr>

            <div class="space-y-4">
                <h3 class="font-bold text-slate-800">Add Items</h3>
                <div class="grid grid-cols-5 gap-2 text-xs font-bold text-slate-500">
                    <div>Item</div><div>Description</div><div>Unit Cost</div><div>Qty</div><div>Amount</div>
                </div>
                <div class="grid grid-cols-5 gap-2">
                    <input type="text" class="border rounded p-2 text-sm">
                    <input type="text" class="border rounded p-2 text-sm">
                    <input type="number" class="border rounded p-2 text-sm">
                    <input type="number" class="border rounded p-2 text-sm">
                    <input type="text" class="border rounded p-2 text-sm bg-slate-100" readonly value="0">
                </div>
                <button type="button" class="text-orange-500 text-sm font-bold flex items-center gap-1 hover:underline"><i class="fa fa-plus"></i> Add New Item</button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                <div><label class="block text-sm font-semibold mb-1">Total</label><input type="text" class="w-full border rounded-md p-2 bg-slate-50" readonly></div>
                <div><label class="block text-sm font-semibold mb-1">Tax</label><input type="text" class="w-full border rounded-md p-2 bg-slate-50" readonly></div>
                <div><label class="block text-sm font-semibold mb-1">Discount(%)</label><input type="text" class="w-full border rounded-md p-2"></div>
                <div><label class="block text-sm font-semibold mb-1 font-bold">Grand Total</label><input type="text" class="w-full border rounded-md p-2 bg-slate-50 font-bold" readonly></div>
            </div>

            <div><label class="block text-sm font-semibold mb-1">Other Information</label><textarea rows="3" class="w-full border rounded-md p-2"></textarea></div>

            <div class="flex justify-end gap-3 pt-4 border-t sticky bottom-0 bg-white">
                <button type="button" onclick="toggleModal()" class="px-6 py-2 border rounded-lg text-slate-600 hover:bg-slate-50">Cancel</button>
                <button type="submit" class="px-6 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600 font-bold">Add Estimate</button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleModal() {
        const modal = document.getElementById('addEstimateModal');
        modal.classList.toggle('hidden');
        modal.classList.toggle('modal-active');
    }
</script>

</body>
</html>