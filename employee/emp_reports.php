<?php 
// 1. Connection file
require_once '../include/db_connect.php'; 
include '../include/sidebar.php'; 

/** * MYSQLI VERSION OF DATA FETCHING
 * Assumes your connection variable in db_connect.php is named $conn or $pdo
 */
$db = isset($conn) ? $conn : $pdo;

if ($db) {
    // Fetch Stats using MySQLi commands
    $resTotal = $db->query("SELECT COUNT(*) as count FROM employees");
    $totalEmpCount = $resTotal->fetch_assoc()['count'];

    $resActive = $db->query("SELECT COUNT(*) as count FROM employees WHERE status = 'Active'");
    $activeEmpCount = $resActive->fetch_assoc()['count'];

    // Fetch Employee List from salary_reports table
    $query = "SELECT e.emp_code as id, e.name, e.email, e.role, 
                     sr.base_salary as salary, sr.reduction_amount, sr.final_salary,
                     l.from_date as leave_date, l.reason
              FROM employees e
              INNER JOIN salary_reports sr ON e.id = sr.emp_id
              LEFT JOIN leaves l ON e.id = l.emp_id
              GROUP BY e.id";

    $employees = $db->query($query);
} else {
    die("Database connection failed. Please check include/db_connect.php");
}
?>

<main class="w-full lg:ml-60 bg-gray-50 min-h-screen p-4 lg:p-6 transition-all duration-300">
    
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Employee Report</h1>
            <nav class="text-sm text-gray-400 flex gap-1 items-center">
                <span class="hover:text-orange-500 cursor-pointer text-xs">🏠</span>
                <span>&rsaquo; Reports &rsaquo; Employee Report</span>
            </nav>
        </div>
        <div class="flex gap-2">
            <button class="bg-white border border-gray-200 px-4 py-2 rounded shadow-sm text-sm font-medium flex items-center gap-2 hover:bg-orange-50 hover:text-orange-600">
                📄 Export <span class="text-xs">▼</span>
            </button>
            <button class="bg-white border border-gray-200 px-3 py-2 rounded shadow-sm hover:bg-orange-50 text-gray-400 hover:text-orange-500 font-bold">⛭</button>
        </div>
    </div>

    <div class="grid grid-cols-12 gap-5 mb-6">
        <div class="col-span-12 lg:col-span-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white p-5 rounded-xl border-l-4 border-orange-500 shadow-sm">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-orange-100 rounded-lg text-orange-600">👥</div>
                    <span class="text-gray-500 text-xs font-semibold uppercase">Total Employee</span>
                </div>
                <div class="text-2xl font-bold text-slate-800"><?php echo $totalEmpCount; ?></div>
                <div class="text-orange-500 text-[10px] mt-1 font-bold">~ +20.01%</div>
            </div>
            <div class="bg-white p-5 rounded-xl border shadow-sm border-gray-100">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-500 text-lg">👤</div>
                    <span class="text-gray-500 text-xs font-semibold uppercase">Active Employee</span>
                </div>
                <div class="text-2xl font-bold text-slate-800"><?php echo $activeEmpCount; ?></div>
                <div class="text-orange-500 text-[10px] mt-1 font-bold">~ +20.01%</div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-7 bg-white p-5 rounded-xl border shadow-sm border-gray-100 text-center">
            <h3 class="font-bold text-slate-700 text-sm mb-4 text-left">📊 Employee Growth</h3>
            <div class="h-[180px]">
                <canvas id="employeeChart"></canvas>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-50 flex justify-between items-center">
            <h3 class="font-bold text-slate-800">Employees List</h3>
            <div class="flex gap-2">
                <input type="text" placeholder="Search..." class="border border-gray-200 rounded px-3 py-1 text-xs outline-none focus:ring-1 focus:ring-orange-400">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[1000px]">
                <thead class="bg-orange-50/50 text-orange-800 text-[10px] uppercase font-bold">
                    <tr>
                        <th class="px-6 py-4 border-b border-orange-100">EMP ID</th>
                        <th class="px-6 py-4 border-b border-orange-100">NAME</th>
                        <th class="px-6 py-4 border-b border-orange-100">EMAIL</th>
                        <th class="px-6 py-4 border-b border-orange-100">ROLE</th>
                        <th class="px-6 py-4 border-b border-orange-100">SALARY</th>
                        <th class="px-6 py-4 border-b border-orange-100">LEAVE DATE</th>
                        <th class="px-6 py-4 border-b border-orange-100">SALARY REDUCION</th>
                        <th class="px-6 py-4 border-b border-orange-100">REASON</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-50">
                    <?php while($emp = $employees->fetch_assoc()): ?>
                    <tr class="hover:bg-orange-50/30 transition">
                        <td class="px-6 py-4 text-gray-500 font-medium"><?php echo $emp['id']; ?></td>
                        <td class="px-6 py-4 font-semibold text-slate-700">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-orange-100 flex items-center justify-center font-bold text-[10px] text-orange-600 border border-orange-200 uppercase">
                                    <?php echo substr($emp['name'], 0, 1); ?>
                                </div>
                                <?php echo htmlspecialchars($emp['name']); ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-500 text-xs"><?php echo htmlspecialchars($emp['email']); ?></td>
                        <td class="px-6 py-4 text-gray-500 text-xs font-medium"><?php echo htmlspecialchars($emp['role']); ?></td>
                        <td class="px-6 py-4 font-bold text-slate-700">₹<?php echo number_format($emp['salary']); ?></td>
                        <td class="px-6 py-4 text-gray-500 text-xs"><?php echo $emp['leave_date'] ?? 'N/A'; ?></td>
                        <td class="px-6 py-4 text-red-500 font-bold">
                            <?php echo $emp['reduction_amount'] > 0 ? "-₹" . number_format($emp['reduction_amount'], 2) : "₹0.00"; ?>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-[10px] px-2 py-1 rounded-md <?php echo ($emp['reduction_amount'] > 0) ? 'bg-orange-100 text-orange-700 font-medium' : 'text-gray-400 italic'; ?>">
                                <?php echo htmlspecialchars($emp['reason'] ?? 'N/A'); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('employeeChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
            datasets: [{
                label: 'Active',
                data: [50, 55, 60, 58, 62, 55, 65, 63, 68],
                backgroundColor: '#f97316',
                borderRadius: 4,
                barThickness: 12,
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f8fafc' }, border: { display: false }, ticks: { font: { size: 10 } } },
                x: { grid: { display: false }, border: { display: false }, ticks: { font: { size: 10 } } }
            }
        }
    });
</script>