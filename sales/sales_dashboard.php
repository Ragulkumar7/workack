<?php 
session_start();
// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Sales') {
    // In a real app, you might redirect to login
    // header("Location: ../login/login.php");
}
include '../include/header.php'; 
include '../include/sidebar.php'; 
?>

<main class="p-6 bg-[#f8fafc] min-h-screen font-sans text-slate-700">
    
    <div class="flex flex-wrap justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Deals Dashboard</h1>
            <nav class="text-sm text-slate-500 mt-1">
                <i class="fa fa-home"></i> &nbsp; > &nbsp; Dashboard &nbsp; > &nbsp; Deals Dashboard
            </nav>
        </div>
        <div class="flex gap-3 mt-4 lg:mt-0">
            <a href="invoice.php" class="bg-blue-600 text-white border border-blue-600 px-4 py-2 rounded shadow-sm hover:bg-blue-700 flex items-center gap-2 text-sm transition-colors">
                <i class="fa fa-file-invoice-dollar"></i> Create Invoice
            </a>

            <button class="bg-white border px-4 py-2 rounded shadow-sm hover:bg-gray-50 flex items-center gap-2 text-sm">
                <i class="fa fa-download text-slate-400"></i> Export <i class="fa fa-chevron-down text-xs"></i>
            </button>
            
            <div class="bg-white border px-4 py-2 rounded shadow-sm text-sm flex items-center gap-2 text-slate-600">
                <i class="fa fa-calendar-alt text-slate-400"></i> 01/29/2026 - 02/04/2026
            </div>

            <a href="../login/login.php?logout=1" class="bg-red-50 text-red-500 border border-red-100 px-4 py-2 rounded shadow-sm hover:bg-red-100 flex items-center gap-2 text-sm transition-colors">
                <i class="fa fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mb-6">
        <div class="col-span-12 lg:col-span-5 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-8">
                <h2 class="font-bold text-lg">Pipeline Stages</h2>
                <button class="text-xs border px-3 py-1 rounded flex items-center gap-2 font-medium">
                    <i class="fa fa-calendar-alt"></i> This Week
                </button>
            </div>
            <div class="flex flex-col items-center space-y-2 mb-10">
                <div class="bg-[#f28b50] text-white w-full py-3 text-center rounded-t-lg text-sm shadow-sm">Marketing : 7,898</div>
                <div class="bg-[#f4a273] text-white w-[90%] py-3 text-center text-sm shadow-sm">Sales : 4,658</div>
                <div class="bg-[#f6b896] text-white w-[80%] py-3 text-center text-sm shadow-sm">Email : 2,898</div>
                <div class="bg-[#f8cfb9] text-white w-[70%] py-3 text-center text-sm shadow-sm">Chat : 789</div>
                <div class="bg-[#fadfd0] text-[#a35e38] w-[60%] py-3 text-center text-sm font-medium shadow-sm">Operational : 655</div>
                <div class="bg-[#fdf0e8] text-[#a35e38] w-[50%] py-3 text-center rounded-b-lg text-sm font-medium shadow-sm">Calls : 454</div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-7 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 relative overflow-hidden group">
                <div class="absolute right-0 top-0 h-full w-1 bg-gradient-to-b from-blue-400 to-blue-600"></div>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Total Invoiced</p>
                        <h2 class="text-2xl font-black mt-1 text-slate-800">$45,221</h2>
                    </div>
                    <div class="bg-blue-50 text-blue-600 p-2 rounded-lg">
                        <i class="fa fa-file-invoice-dollar text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-emerald-500">
                    <span class="bg-emerald-50 px-1.5 py-0.5 rounded">↝ +12%</span> 
                    <span class="text-slate-400 font-normal">from last month</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100 relative overflow-hidden">
                <div class="absolute right-0 top-0 h-full w-1 bg-gradient-to-b from-orange-400 to-orange-600"></div>
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Pending Payments</p>
                        <h2 class="text-2xl font-black mt-1 text-slate-800">$12,545</h2>
                    </div>
                    <div class="bg-orange-50 text-orange-600 p-2 rounded-lg">
                        <i class="fa fa-clock text-xl"></i>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-xs font-bold text-orange-500">
                    <span class="bg-orange-50 px-1.5 py-0.5 rounded">3 Invoices</span> 
                    <span class="text-slate-400 font-normal">waiting action</span>
                </div>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-slate-500 text-xs font-bold">Total Customers</p>
                    <i class="fa fa-users text-purple-400"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800">9,895</h2>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                <div class="flex justify-between items-center mb-2">
                    <p class="text-slate-500 text-xs font-bold">Conversion Rate</p>
                    <i class="fa fa-chart-pie text-teal-400"></i>
                </div>
                <h2 class="text-xl font-black text-slate-800">51.96%</h2>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        
        <div class="col-span-12 lg:col-span-8 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 flex justify-between items-center border-b border-slate-50">
                <div class="flex items-center gap-3">
                    <div class="bg-blue-50 p-2 rounded-lg text-blue-600">
                        <i class="fa fa-file-invoice"></i>
                    </div>
                    <h2 class="font-bold text-slate-800">Recent Invoices</h2>
                </div>
                <a href="invoice.php" class="text-xs bg-white border border-slate-200 text-slate-600 px-3 py-1.5 rounded hover:bg-slate-50 transition-colors">View All Invoices</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                        <tr>
                            <th class="px-6 py-3">Invoice ID</th>
                            <th class="px-6 py-3">Client</th>
                            <th class="px-6 py-3">Date</th>
                            <th class="px-6 py-3">Amount</th>
                            <th class="px-6 py-3">Status</th>
                            <th class="px-6 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4 font-bold text-sm text-blue-600">#INV-2024-001</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold">A</div>
                                    <span class="text-sm font-medium text-slate-700">Acme Corp</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">Feb 04, 2026</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-800">$2,450.00</td>
                            <td class="px-6 py-4">
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">Paid</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-slate-400 hover:text-blue-600 transition-colors"><i class="fa fa-download"></i></button>
                            </td>
                        </tr>
                        
                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4 font-bold text-sm text-blue-600">#INV-2024-002</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold">G</div>
                                    <span class="text-sm font-medium text-slate-700">Global Tech</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">Feb 02, 2026</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-800">$1,200.00</td>
                            <td class="px-6 py-4">
                                <span class="bg-orange-100 text-orange-700 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">Pending</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-slate-400 hover:text-blue-600 transition-colors"><i class="fa fa-eye"></i></button>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50 transition-colors group">
                            <td class="px-6 py-4 font-bold text-sm text-blue-600">#INV-2024-003</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">S</div>
                                    <span class="text-sm font-medium text-slate-700">Stark Ind</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-500">Jan 28, 2026</td>
                            <td class="px-6 py-4 text-sm font-bold text-slate-800">$5,800.00</td>
                            <td class="px-6 py-4">
                                <span class="bg-red-100 text-red-700 px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">Overdue</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <button class="text-slate-400 hover:text-blue-600 transition-colors"><i class="fa fa-bell"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
                <a href="invoice.php" class="text-xs font-bold text-blue-600 hover:text-blue-700 hover:underline">
                    + Create New Invoice
                </a>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-bold text-slate-800">Deal Activity</h2>
            </div>
            <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-slate-300 before:to-transparent">
                
                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-50 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                        <i class="fa fa-file-invoice text-blue-500 text-sm"></i>
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <div class="flex items-center justify-between space-x-2 mb-1">
                            <div class="font-bold text-slate-900 text-sm">Invoice Sent</div>
                            <time class="font-caveat font-medium text-xs text-slate-500">Just now</time>
                        </div>
                        <div class="text-slate-500 text-xs">Sent invoice #001 to Acme Corp.</div>
                    </div>
                </div>

                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-slate-50 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2 z-10">
                        <i class="fa fa-phone text-orange-500 text-sm"></i>
                    </div>
                    <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded-xl border border-slate-100 shadow-sm">
                        <div class="flex items-center justify-between space-x-2 mb-1">
                            <div class="font-bold text-slate-900 text-sm">Call Logged</div>
                            <time class="font-caveat font-medium text-xs text-slate-500">2 hrs ago</time>
                        </div>
                        <div class="text-slate-500 text-xs">Discussed pricing with Stark Ind.</div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</main>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">