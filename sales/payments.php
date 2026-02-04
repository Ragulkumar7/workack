<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<div class="flex">
    <div class="flex-1 p-8 max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-slate-800">Payments</h1>
                <nav class="flex text-sm text-gray-500 mt-1">
                    <i data-lucide="home" class="w-4 h-4 mr-2"></i> Sales <span class="mx-2">></span> <span class="text-blue-600">Payments</span>
                </nav>
            </div>
            <div class="flex gap-2">
                <button class="flex items-center bg-white border border-gray-200 px-4 py-2 rounded-lg shadow-sm hover:bg-gray-50">
                    <i data-lucide="download" class="w-4 h-4 mr-2"></i> Export <i data-lucide="chevron-down" class="w-4 h-4 ml-2"></i>
                </button>
                <button class="bg-white border border-gray-200 p-2 rounded-lg shadow-sm hover:bg-gray-50 text-gray-600">
                    <i data-lucide="chevron-up" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            
            <div class="p-4 border-b border-gray-100 flex flex-wrap justify-between items-center gap-4">
                <h2 class="text-lg font-semibold text-gray-700">Payment List</h2>
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <span class="absolute inset-y-0 left-3 flex items-center text-gray-400">
                            <i data-lucide="calendar" class="w-4 h-4"></i>
                        </span>
                        <input type="text" value="01/29/2026 - 02/04/2026" class="pl-10 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 text-gray-600">
                    </div>
                    <select class="border border-gray-200 rounded-lg text-sm px-4 py-2 bg-white focus:outline-none text-gray-600">
                        <option>Sort By : Last 7 Days</option>
                    </select>
                </div>
            </div>

            <div class="p-4 flex justify-between items-center">
                <div class="text-sm text-gray-500">
                    Row Per Page 
                    <select class="mx-1 border border-gray-200 rounded px-2 py-1 bg-white">
                        <option>10</option>
                    </select>
                    Entries
                </div>
                <div class="relative">
                    <input type="text" placeholder="Search" class="border border-gray-200 rounded-md px-4 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 w-48 lg:w-64">
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead>
                        <tr class="bg-gray-50 border-y border-gray-100 text-gray-600 text-sm">
                            <th class="px-6 py-4 font-semibold">Invoice ID <i data-lucide="arrow-up-down" class="inline w-3 h-3 ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 font-semibold">Client Name <i data-lucide="arrow-up-down" class="inline w-3 h-3 ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 font-semibold">Company Name <i data-lucide="arrow-up-down" class="inline w-3 h-3 ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 font-semibold">Payment Type <i data-lucide="arrow-up-down" class="inline w-3 h-3 ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 font-semibold">Paid Date <i data-lucide="arrow-up-down" class="inline w-3 h-3 ml-1 text-gray-300"></i></th>
                            <th class="px-6 py-4 font-semibold">Paid Amount <i data-lucide="arrow-up-down" class="inline w-3 h-3 ml-1 text-gray-300"></i></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php
                        $payments = [
                            ["id" => "Inv-001", "name" => "Michael Walker", "role" => "CEO", "company" => "BrightWave Innovations", "type" => "Paypal", "date" => "15 Jan 2024", "amount" => "$3000", "img" => "https://i.pravatar.cc/150?u=1"],
                            ["id" => "Inv-002", "name" => "Sophie Headrick", "role" => "Manager", "company" => "Stellar Dynamics", "type" => "Paypal", "date" => "25 Jan 2024", "amount" => "$2500", "img" => "https://i.pravatar.cc/150?u=2"],
                            ["id" => "Inv-003", "name" => "Cameron Drake", "role" => "Director", "company" => "Quantum Nexus", "type" => "Paypal", "date" => "22 Feb 2024", "amount" => "$2800", "img" => "https://i.pravatar.cc/150?u=3"],
                            ["id" => "Inv-004", "name" => "Doris Crowley", "role" => "Consultant", "company" => "EcoVision Enterprises", "type" => "Paypal", "date" => "17 Mar 2024", "amount" => "$3300", "img" => "https://i.pravatar.cc/150?u=4"],
                            ["id" => "Inv-005", "name" => "Thomas Bordelon", "role" => "Manager", "company" => "Aurora Technologies", "type" => "Paypal", "date" => "16 Apr 2024", "amount" => "$3600", "img" => "https://i.pravatar.cc/150?u=5"],
                            ["id" => "Inv-006", "name" => "Kathleen Gutierrez", "role" => "Director", "company" => "BlueSky Ventures", "type" => "Paypal", "date" => "21 Apr 2024", "amount" => "$2000", "img" => "https://i.pravatar.cc/150?u=6"],
                            ["id" => "Inv-007", "name" => "Bruce Wright", "role" => "CEO", "company" => "TerraFusion Energy", "type" => "Paypal", "date" => "06 Jul 2024", "amount" => "$3400", "img" => "https://i.pravatar.cc/150?u=7"],
                            ["id" => "Inv-008", "name" => "Estelle Morgan", "role" => "Manager", "company" => "UrbanPulse Design", "type" => "Paypal", "date" => "04 Sep 2024", "amount" => "$4000", "img" => "https://i.pravatar.cc/150?u=8"],
                            ["id" => "Inv-009", "name" => "Stephen Dias", "role" => "CEO", "company" => "Nimbus Networks", "type" => "Paypal", "date" => "15 Nov 2024", "amount" => "$4500", "img" => "https://i.pravatar.cc/150?u=9"],
                            ["id" => "Inv-010", "name" => "Angela Thomas", "role" => "Consultant", "company" => "Epicurean Delights", "type" => "Paypal", "date" => "11 Dec 2024", "amount" => "$3800", "img" => "https://i.pravatar.cc/150?u=10"],
                        ];

                        foreach ($payments as $payment):
                        ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 text-blue-500 font-medium"><?= $payment['id'] ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <img src="<?= $payment['img'] ?>" class="w-10 h-10 rounded-full mr-3 border border-gray-100">
                                    <div>
                                        <div class="font-bold text-gray-800 text-sm"><?= $payment['name'] ?></div>
                                        <div class="text-xs text-gray-400"><?= $payment['role'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-500 text-sm"><?= $payment['company'] ?></td>
                            <td class="px-6 py-4 text-gray-500 text-sm"><?= $payment['type'] ?></td>
                            <td class="px-6 py-4 text-gray-500 text-sm"><?= $payment['date'] ?></td>
                            <td class="px-6 py-4 font-semibold text-gray-800 text-sm"><?= $payment['amount'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex justify-between items-center text-sm text-gray-500">
                <div>Showing 1 - 10 of 10 entries</div>
                <div class="flex items-center gap-1">
                    <button class="p-2 hover:bg-gray-100 rounded-lg text-gray-400"><i data-lucide="chevron-left" class="w-4 h-4"></i></button>
                    <button class="w-8 h-8 flex items-center justify-center bg-orange-500 text-white rounded-full font-medium">1</button>
                    <button class="p-2 hover:bg-gray-100 rounded-lg text-gray-400"><i data-lucide="chevron-right" class="w-4 h-4"></i></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="fixed right-0 top-1/2 -translate-y-1/2 bg-orange-500 p-2 rounded-l-md shadow-lg cursor-pointer hover:bg-orange-600 transition-all">
    <i data-lucide="settings" class="w-5 h-5 text-white"></i>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>