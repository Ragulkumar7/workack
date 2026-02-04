<?php include '../include/header.php'; ?>
<?php include '../include/sidebar.php'; ?>

<main class="p-6 bg-[#f8fafc] min-h-screen font-sans text-slate-700">
    
    <div class="flex flex-wrap justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Deals Dashboard</h1>
            <nav class="text-sm text-slate-500 mt-1">
                <i class="fa fa-home"></i> &nbsp; > &nbsp; Dashboard &nbsp; > &nbsp; Deals Dashboard
            </nav>
        </div>
        <div class="flex gap-3 mt-4 lg:mt-0">
            <button class="bg-white border px-4 py-2 rounded shadow-sm hover:bg-gray-50 flex items-center gap-2 text-sm">
                <i class="fa fa-download text-slate-400"></i> Export <i class="fa fa-chevron-down text-xs"></i>
            </button>
            <button class="bg-white border px-4 py-2 rounded shadow-sm hover:bg-gray-50 flex items-center gap-2 text-sm">
                <i class="fa fa-calendar text-slate-400"></i> 01/29/2026 - 02/04/2026
            </button>
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
                <div class="bg-[#f28b50] text-white w-full py-3 text-center rounded-t-lg text-sm">Marketing : 7,898</div>
                <div class="bg-[#f4a273] text-white w-[90%] py-3 text-center text-sm">Sales : 4,658</div>
                <div class="bg-[#f6b896] text-white w-[80%] py-3 text-center text-sm">Email : 2,898</div>
                <div class="bg-[#f8cfb9] text-white w-[70%] py-3 text-center text-sm">Chat : 789</div>
                <div class="bg-[#fadfd0] text-[#a35e38] w-[60%] py-3 text-center text-sm font-medium">Operational : 655</div>
                <div class="bg-[#fdf0e8] text-[#a35e38] w-[50%] py-3 text-center rounded-b-lg text-sm font-medium">Calls : 454</div>
            </div>
            <h3 class="font-bold mb-4 text-sm">Leads Values By Stages</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <?php
                $leads = [
                    ['l' => 'Marketing', 'v' => '$5,221,45', 'c' => 'bg-orange-500'],
                    ['l' => 'Sales', 'v' => '$30,424', 'c' => 'bg-red-400'],
                    ['l' => 'Email', 'v' => '$21,135', 'c' => 'bg-orange-300'],
                    ['l' => 'Chat', 'v' => '$15,235', 'c' => 'bg-orange-200']
                ];
                foreach ($leads as $lead): ?>
                    <div class="border rounded-lg p-3 bg-white">
                        <div class="text-[10px] uppercase text-slate-500 flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full <?php echo $lead['c']; ?>"></span> <?php echo $lead['l']; ?>
                        </div>
                        <div class="font-bold text-xs"><?php echo $lead['v']; ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-7 grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php
            $stats = [
                ['t' => 'Total Deals', 'v' => '$45,221,45', 'tr' => '-4.01%', 'up' => false, 'i' => 'fa-play rotate-[-90deg]', 'c' => 'orange'],
                ['t' => 'Total Customers', 'v' => '9895', 'tr' => '+55%', 'up' => true, 'i' => 'fa-users', 'c' => 'purple'],
                ['t' => 'Deal Value', 'v' => '$12,545,68', 'tr' => '+20.01%', 'up' => true, 'i' => 'fa-bullseye', 'c' => 'teal'],
                ['t' => 'Conversion Rate', 'v' => '51.96%', 'tr' => '-6.01%', 'up' => false, 'i' => 'fa-copy', 'c' => 'blue'],
                ['t' => 'Revenue this month', 'v' => '$46,548,48', 'tr' => '+55%', 'up' => true, 'i' => 'fa-chart-line', 'c' => 'pink'],
                ['t' => 'Active Customers', 'v' => '8987', 'tr' => '-3.22%', 'up' => false, 'i' => 'fa-star', 'c' => 'yellow'],
            ];
            foreach ($stats as $s): ?>
                <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-100">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <p class="text-slate-500 text-xs font-bold"><?php echo $s['t']; ?></p>
                            <h2 class="text-xl font-black mt-1"><?php echo $s['v']; ?></h2>
                        </div>
                        <div class="bg-<?php echo $s['c']; ?>-500 text-white p-2.5 rounded-lg">
                            <i class="fa <?php echo $s['i']; ?> text-sm"></i>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 h-1 rounded-full mb-3"><div class="bg-<?php echo $s['c']; ?>-500 h-1 rounded-full w-1/2"></div></div>
                    <p class="text-[11px] font-bold <?php echo $s['up'] ? 'text-emerald-500' : 'text-red-500'; ?>">
                        <?php echo $s['up'] ? '↝' : '⤼'; ?> <?php echo $s['tr']; ?> <span class="text-slate-400 font-normal">from last week</span>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mb-6">
        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800">Deals by Stage</h2>
                <button class="text-xs border px-3 py-1 rounded flex items-center gap-2 font-medium">
                    <i class="fa fa-calendar-alt"></i> This Week
                </button>
            </div>
            <div class="flex items-center gap-3 mb-6">
                <h2 class="text-2xl font-black">$20,245</h2>
                <span class="bg-emerald-100 text-emerald-600 text-[10px] font-bold px-2 py-0.5 rounded-full">↑ 12%</span>
                <span class="text-xs text-slate-400">vs last years</span>
            </div>
            <div class="h-64">
                <canvas id="dealsStageChart"></canvas>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-bold text-slate-800">Deals By Companies</h2>
                <button class="text-xs border px-3 py-1 rounded flex items-center gap-2 font-medium">
                    <i class="fa fa-calendar-alt"></i> This Week
                </button>
            </div>
            <div class="space-y-3">
                <?php
                $companies = [
                    ['n' => 'Pitch', 'd' => 'Closing Deal date 05...', 'v' => '$3655', 'i' => 'fa-p'],
                    ['n' => 'Initech', 'd' => 'Closing Deal date 05...', 'v' => '$2185', 'i' => 'fa-i'],
                    ['n' => 'Umbrella Corp', 'd' => 'Closing Deal date 29...', 'v' => '$1583', 'i' => 'fa-u'],
                    ['n' => 'Capital Partners', 'd' => 'Closing Deal date 23...', 'v' => '$6584', 'i' => 'fa-c'],
                    ['n' => 'Massive Dynamic', 'd' => 'Closing Deal date 23 Fe...', 'v' => '$2153', 'i' => 'fa-m']
                ];
                foreach ($companies as $comp): ?>
                <div class="flex items-center justify-between p-3 border border-dashed rounded-lg hover:bg-slate-50 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-100 rounded-lg flex items-center justify-center border">
                            <i class="fa <?php echo $comp['i']; ?> text-slate-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-slate-800"><?php echo $comp['n']; ?></p>
                            <p class="text-[10px] text-slate-400"><?php echo $comp['d']; ?></p>
                        </div>
                    </div>
                    <div class="text-sm font-black text-slate-700"><?php echo $comp['v']; ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-bold text-slate-800">Top Deals</h2>
                <button class="text-xs border px-3 py-1 rounded flex items-center gap-2 font-medium">
                    <i class="fa fa-calendar-alt"></i> This Week
                </button>
            </div>
            <div class="h-48 flex justify-center mb-8">
                <canvas id="topDealsChart"></canvas>
            </div>
            <div class="space-y-4">
                <p class="text-sm font-bold text-slate-800">Status</p>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-[#f26522]"></span> Marketing
                    </div>
                    <span class="text-sm font-bold">$5,69,877</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-[#3d6373]"></span> Chat
                    </div>
                    <span class="text-sm font-bold">$4,84,575</span>
                </div>
                <div class="flex justify-between items-center">
                    <div class="flex items-center gap-2 text-sm text-slate-500">
                        <span class="w-3 h-3 rounded-full bg-[#ffc107]"></span> Email
                    </div>
                    <span class="text-sm font-bold">$1,84,575</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6 mb-6">
        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-bold text-slate-800">Deals By Country</h2>
                <button class="text-xs bg-slate-50 border px-3 py-1 rounded font-medium">View All</button>
            </div>
            <div class="space-y-5">
                <?php
                $countries = [
                    ['n' => 'USA', 'd' => '350', 'v' => '$1065.00', 'f' => '🇺🇸', 'c' => '#10b981'],
                    ['n' => 'UAE', 'd' => '221', 'v' => '$966.00', 'f' => '🇦🇪', 'c' => '#10b981'],
                    ['n' => 'Singapore', 'd' => '236', 'v' => '$959.00', 'f' => '🇸🇬', 'c' => '#ef4444'],
                    ['n' => 'France', 'd' => '589', 'v' => '$879.00', 'f' => '🇫🇷', 'c' => '#10b981'],
                    ['n' => 'Norway', 'd' => '221', 'v' => '$632.00', 'f' => '🇳🇴', 'c' => '#ef4444']
                ];
                foreach ($countries as $index => $con): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <span class="text-3xl"><?php echo $con['f']; ?></span>
                        <div>
                            <p class="text-sm font-bold"><?php echo $con['n']; ?></p>
                            <p class="text-[10px] text-slate-400">Deals : <?php echo $con['d']; ?></p>
                        </div>
                    </div>
                    <div class="w-16 h-8">
                        <canvas id="sparkline<?php echo $index; ?>"></canvas>
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-400">Total Value</p>
                        <p class="text-sm font-black"><?php echo $con['v']; ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-bold text-slate-800">Won Deals Stage</h2>
                <button class="text-xs border px-3 py-1 rounded flex items-center gap-2 font-medium">
                    <i class="fa fa-calendar-alt"></i> This Week
                </button>
            </div>
            <div class="text-center mb-4">
                <p class="text-xs text-slate-400 font-medium">Stages Won This Year</p>
                <div class="flex items-center justify-center gap-2">
                    <h2 class="text-2xl font-black">$45,899,79</h2>
                    <span class="text-red-500 bg-red-50 text-[10px] font-bold px-2 py-0.5 rounded-full border border-red-100">↓ 12%</span>
                </div>
            </div>
            <div class="relative h-64 flex items-center justify-center overflow-hidden">
                <div class="absolute w-32 h-32 bg-[#0e4a5b] rounded-full flex flex-col items-center justify-center text-white -translate-x-12 z-0 shadow-lg">
                    <span class="text-[10px]">Conversion</span>
                    <span class="font-bold text-lg">48%</span>
                </div>
                <div class="absolute w-24 h-24 bg-[#e61e14] rounded-full flex flex-col items-center justify-center text-white translate-x-8 -translate-y-12 z-10 border-2 border-white shadow-lg">
                    <span class="text-[10px]">Calls</span>
                    <span class="font-bold text-lg">24%</span>
                </div>
                <div class="absolute w-28 h-28 bg-[#ffc107] rounded-full flex flex-col items-center justify-center text-white translate-x-16 translate-y-8 z-10 border-2 border-white shadow-lg">
                    <span class="text-[10px]">Email</span>
                    <span class="font-bold text-lg">39%</span>
                </div>
                <div class="absolute w-20 h-20 bg-[#00c853] rounded-full flex flex-col items-center justify-center text-white translate-x-2 translate-y-16 z-20 border-2 border-white shadow-lg">
                    <span class="text-[10px]">Chats</span>
                    <span class="font-bold text-lg">20%</span>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-bold text-slate-800">Recent Follow Up</h2>
                <button class="text-xs bg-slate-50 border px-3 py-1 rounded font-medium">View All</button>
            </div>
            <div class="space-y-4">
                <?php
                $follows = [
                    ['n' => 'Alexander Jermai', 'r' => 'UI/UX Designer', 'i' => 'https://i.pravatar.cc/150?u=1', 'ic' => 'fa-envelope'],
                    ['n' => 'Doglas Martini', 'r' => 'Product Designer', 'i' => 'https://i.pravatar.cc/150?u=2', 'ic' => 'fa-phone'],
                    ['n' => 'Daniel Esbella', 'r' => 'Project Manager', 'i' => 'https://i.pravatar.cc/150?u=3', 'ic' => 'fa-envelope'],
                    ['n' => 'Daniel Esbella', 'r' => 'Team Lead', 'i' => 'https://i.pravatar.cc/150?u=4', 'ic' => 'fa-comment-dots'],
                    ['n' => 'Stephan Peralt', 'r' => 'Team Lead', 'i' => 'https://i.pravatar.cc/150?u=5', 'ic' => 'fa-comment-dots']
                ];
                foreach ($follows as $f): ?>
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="<?php echo $f['i']; ?>" class="w-10 h-10 rounded-full border border-slate-200">
                        <div>
                            <p class="text-sm font-bold text-slate-800"><?php echo $f['n']; ?></p>
                            <p class="text-[10px] text-slate-400 font-medium"><?php echo $f['r']; ?></p>
                        </div>
                    </div>
                    <button class="w-8 h-8 rounded-lg border border-slate-100 flex items-center justify-center text-slate-400">
                        <i class="fa <?php echo $f['ic']; ?> text-xs"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 lg:col-span-8 bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-6 flex justify-between items-center">
                <h2 class="font-bold text-slate-800">Recent Deals</h2>
                <button class="text-xs bg-slate-50 border px-3 py-1 rounded font-medium">View All</button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left">
                    <thead class="bg-slate-50 text-slate-500 text-[11px] uppercase tracking-wider font-bold">
                        <tr>
                            <th class="px-6 py-3">Deal Name</th>
                            <th class="px-6 py-3">Stage</th>
                            <th class="px-6 py-3">Deal Value</th>
                            <th class="px-6 py-3">Owner</th>
                            <th class="px-6 py-3">Closed Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php
                        $recentDeals = [
                            ['n' => 'Collins', 's' => 'Quality To Buy', 'v' => '$4,50,000', 'o' => 'Anthony Lewis', 'd' => '14 Jan 2024', 'i' => 'https://i.pravatar.cc/150?u=11'],
                            ['n' => 'Konopelski', 's' => 'Proposal Made', 'v' => '$3,15,000', 'o' => 'Brian Villalobos', 'd' => '21 Jan 2024', 'i' => 'https://i.pravatar.cc/150?u=12'],
                            ['n' => 'Adams', 's' => 'Contact Made', 'v' => '$8,40,000', 'o' => 'Harvey Smith', 'd' => '20 Feb 2024', 'i' => 'https://i.pravatar.cc/150?u=13'],
                            ['n' => 'Schumm', 's' => 'Quality To Buy', 'v' => '$6,10,000', 'o' => 'Stephan Peralt', 'd' => '15 Mar 2024', 'i' => 'https://i.pravatar.cc/150?u=14'],
                            ['n' => 'Wisozk', 's' => 'Presentation', 'v' => '$4,70,000', 'o' => 'Doglas Martini', 'd' => '12 Apr 2024', 'i' => 'https://i.pravatar.cc/150?u=15']
                        ];
                        foreach ($recentDeals as $deal): ?>
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 font-bold text-sm"><?php echo $deal['n']; ?></td>
                            <td class="px-6 py-4 text-sm text-slate-500"><?php echo $deal['s']; ?></td>
                            <td class="px-6 py-4 text-sm font-bold"><?php echo $deal['v']; ?></td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <img src="<?php echo $deal['i']; ?>" class="w-8 h-8 rounded-full">
                                    <span class="text-sm font-medium"><?php echo $deal['o']; ?></span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-slate-400"><?php echo $deal['d']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-slate-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="font-bold text-slate-800">Recent Activities</h2>
                <button class="text-xs bg-slate-50 border px-3 py-1 rounded font-medium">View All</button>
            </div>
            <div class="relative space-y-8 before:absolute before:left-[15px] before:top-2 before:bottom-2 before:w-[1px] before:bg-slate-100 before:border-l before:border-dashed">
                <div class="relative pl-10">
                    <div class="absolute left-0 top-1 w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center z-10">
                        <i class="fa fa-phone text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Drain responded to your appointment schedule question.</p>
                        <p class="text-xs text-slate-400 mt-1">09:25 PM</p>
                    </div>
                </div>
                <div class="relative pl-10">
                    <div class="absolute left-0 top-1 w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center z-10">
                        <i class="fa fa-paper-plane text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">You sent 1 Message to the James.</p>
                        <p class="text-xs text-slate-400 mt-1">10:25 PM</p>
                    </div>
                </div>
                <div class="relative pl-10">
                    <div class="absolute left-0 top-1 w-8 h-8 bg-emerald-500 text-white rounded-full flex items-center justify-center z-10">
                        <i class="fa fa-phone text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800">Denwar responded to your appointment on 25 Jan 2025, 08:15 PM</p>
                        <p class="text-xs text-slate-400 mt-1">09:25 PM</p>
                    </div>
                </div>
                <div class="relative pl-10">
                    <div class="absolute left-0 top-1 w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center z-10">
                        <i class="fa fa-user text-xs"></i>
                    </div>
                    <div class="flex items-center gap-2">
                        <p class="text-sm font-bold text-slate-800">Meeting With</p>
                        <img src="https://i.pravatar.cc/150?u=99" class="w-6 h-6 rounded-full border">
                        <p class="text-sm font-bold text-slate-800">Abraham</p>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">09:25 PM</p>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
    // 1. Bar Chart: Deals by Stage
    const ctxBar = document.getElementById('dealsStageChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: ['Inpipeline', 'Follow Up', 'Schedule', 'Conversion'],
            datasets: [{
                data: [180, 140, 200, 120],
                backgroundColor: '#f26522',
                borderRadius: 20,
                barThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [5, 5] }, ticks: { stepSize: 50 } },
                x: { grid: { display: false }, ticks: { font: { size: 10 }, rotation: 45 } }
            }
        }
    });

    // 2. Donut Chart: Top Deals
    const ctxDonut = document.getElementById('topDealsChart').getContext('2d');
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [30, 45, 25],
                backgroundColor: ['#f26522', '#3d6373', '#ffc107'],
                borderWidth: 0,
                cutout: '80%',
                borderRadius: 10,
                spacing: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // 3. Sparklines for Country List
    const createSparkline = (id, color) => {
        new Chart(document.getElementById(id), {
            type: 'line',
            data: {
                labels: [1,2,3,4,5],
                datasets: [{
                    data: [Math.random()*10, Math.random()*10, Math.random()*10, Math.random()*10, Math.random()*10],
                    borderColor: color,
                    borderWidth: 2,
                    pointRadius: 0,
                    fill: false,
                    tension: 0.4
                }]
            },
            options: {
                events: [],
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { display: false }, y: { display: false } }
            }
        });
    };

    <?php foreach($countries as $index => $con): ?>
    createSparkline('sparkline<?php echo $index; ?>', '<?php echo $con['c']; ?>');
    <?php endforeach; ?>
</script>