<?php include '../include/header.php'; ?>
<div style="display: flex;"> <?php include '../include/sidebar.php'; ?>

    <?php
    // 1. DATA INITIALIZATION
    $registry = [
        "Internal" => [
            "Housekeeping" => "Rahul Kumar",
            "Electrician" => "Suresh Raina",
            "Stationaries" => "Vikas Khanna"
        ],
        "External" => [
            "Security Guard" => "Amit Singh",
            "Cab Service" => "Rajesh Driver"
        ]
    ];

    $tasks = [
        [
            "id" => 1,
            "sector" => "Internal",
            "item" => "Housekeeping",
            "name" => "Rahul Kumar",
            "area" => "Floor 2 - Pantry",
            "time" => "09:00 AM",
            "expense" => 1200
        ]
    ];
    ?>

    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        /* This removes the dead space and makes the view responsive */
        .ops-main-wrapper {
            flex: 1; /* Takes up all remaining space next to sidebar */
            padding: 40px;
            background-color: #f4f7fc;
            min-height: 100vh;
            overflow-x: hidden;
        }

        .ops-container {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            color: #333;
            box-sizing: border-box;
            width: 100%;
        }

        /* Rest of your content styling remains exactly the same */
        .ops-header { margin-bottom: 30px; }
        .ops-title { font-size: 28px; font-weight: 800; color: #1a1a1a; margin: 0; }
        .ops-breadcrumb { font-size: 14px; color: #666; margin-top: 5px; }

        .ops-tabs { display: flex; gap: 10px; margin-bottom: 30px; }
        .ops-tab-btn {
            display: flex; align-items: center; gap: 8px;
            padding: 12px 24px; border-radius: 8px; border: 1px solid #e1e1e1;
            background: white; color: #666; font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .ops-tab-btn:hover { border-color: #FF9B44; color: #FF9B44; }
        .ops-tab-btn.active { background: #FF9B44; color: white; border-color: #FF9B44; box-shadow: 0 4px 10px rgba(255, 155, 68, 0.3); }

        .ops-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
            gap: 30px;
        }

        .ops-card {
            background: white; padding: 30px; border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e1e1e1;
        }
        .ops-card-title {
            font-size: 18px; font-weight: 700; color: #333; margin: 0 0 20px 0;
            display: flex; align-items: center; gap: 10px;
        }
        .ops-card-header {
            padding: 20px; border-bottom: 1px solid #f0f0f0; 
            display: flex; justify-content: space-between; align-items: center;
        }

        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-size: 11px; font-weight: 700; text-transform: uppercase; color: #666; margin-bottom: 6px; }
        
        .ops-input, .ops-select {
            width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; 
            font-size: 14px; outline: none; box-sizing: border-box; background: white;
        }
        .ops-input:focus, .ops-select:focus { border-color: #FF9B44; }

        .ops-btn {
            width: 100%; padding: 12px; background: #FF9B44; color: white; border: none;
            border-radius: 6px; font-weight: 700; cursor: pointer; margin-top: 10px;
            transition: background 0.2s;
        }
        .ops-btn:hover { background: #e88b3a; }
        .btn-dark { background: #1f2937; }
        .btn-dark:hover { background: #374151; }

        .flex-row { display: flex; gap: 15px; }
        .col-half { flex: 1; }
        .col-third { flex: 1; }

        .table-wrapper { overflow-x: auto; }
        .ops-table { width: 100%; border-collapse: collapse; min-width: 600px; }
        .ops-table th {
            text-align: left; padding: 15px 20px; background: #f9fafb; color: #666;
            font-size: 12px; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid #e5e7eb;
        }
        .ops-table td {
            padding: 15px 20px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; color: #333; font-size: 14px;
        }

        .toast {
            position: fixed; top: 20px; right: 20px; padding: 12px 24px;
            border-radius: 8px; color: white; font-weight: bold; font-size: 14px;
            display: none; align-items: center; gap: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            z-index: 9999;
        }
    </style>

    <div class="ops-main-wrapper">
        <div class="ops-container">
            <div id="toast" class="toast"></div>

            <div class="ops-header">
                <h1 class="ops-title">IT & Operations Control Center</h1>
                <div class="ops-breadcrumb">
                    Dashboard / <span style="color:#FF9B44; font-weight:bold;">Operations</span>
                </div>
            </div>

            <div class="ops-tabs">
                <button id="btn-internal" class="ops-tab-btn active" onclick="switchTab('Internal')">
                    <i data-lucide="home"></i> Internal Ledger
                </button>
                <button id="btn-external" class="ops-tab-btn" onclick="switchTab('External')">
                    <i data-lucide="shield"></i> External Ledger
                </button>
            </div>

            <div class="ops-grid">
                <div class="ops-card">
                    <h4 class="ops-card-title">
                        <i data-lucide="plus-circle" color="#FF9B44"></i> Register New Member
                    </h4>
                    <div class="form-group">
                        <label class="form-label">Sector</label>
                        <select id="reg-sector" class="ops-select">
                            <option value="Internal">Internal Team</option>
                            <option value="External">External Team</option>
                        </select>
                    </div>
                    <div class="flex-row">
                        <div class="col-half form-group">
                            <label class="form-label">Role</label>
                            <input type="text" id="reg-role" placeholder="e.g. Electrician" class="ops-input" />
                        </div>
                        <div class="col-half form-group">
                            <label class="form-label">Name</label>
                            <input type="text" id="reg-name" placeholder="Worker Name" class="ops-input" />
                        </div>
                    </div>
                    <button class="ops-btn" onclick="handleRegister()">Register</button>
                </div>

                <div class="ops-card">
                    <h4 class="ops-card-title" id="assign-title">Assign Internal Task</h4>
                    <div class="flex-row">
                        <div class="col-half form-group">
                            <label class="form-label">Work Type</label>
                            <select id="task-type" class="ops-select"></select>
                        </div>
                        <div class="col-half form-group">
                            <label class="form-label">Worker Name</label>
                            <input type="text" id="task-worker" placeholder="Worker Name" class="ops-input" />
                        </div>
                    </div>
                    <div class="flex-row">
                        <div class="col-third form-group">
                            <label class="form-label">Area</label>
                            <input type="text" id="task-area" placeholder="Floor/Room" class="ops-input" />
                        </div>
                        <div class="col-third form-group">
                            <label class="form-label">Time</label>
                            <input type="text" id="task-time" placeholder="00:00 AM" class="ops-input" />
                        </div>
                        <div class="col-third form-group">
                            <label class="form-label">Cost</label>
                            <input type="number" id="task-cost" placeholder="₹" class="ops-input" />
                        </div>
                    </div>
                    <button class="ops-btn btn-dark" onclick="handleAssign()">Assign Task</button>
                </div>
            </div>

            <div class="ops-card" style="margin-top: 30px; padding: 0; overflow: hidden;">
                <div class="ops-card-header">
                    <h4 class="ops-card-title">
                        <i id="ledger-icon" data-lucide="home" color="#FF9B44"></i> 
                        <span id="ledger-title-text">Internal</span> Ledger
                    </h4>
                    <select id="ledger-filter" class="ops-select" style="width: auto;" onchange="renderTable()"></select>
                </div>
                <div class="table-wrapper">
                    <table class="ops-table">
                        <thead>
                            <tr>
                                <th>Worker & Area</th>
                                <th>Work Type</th>
                                <th>Cost</th>
                            </tr>
                        </thead>
                        <tbody id="ledger-body"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> <script>
    let registry = <?php echo json_encode($registry); ?>;
    let tasks = <?php echo json_encode($tasks); ?>;
    let activeTab = 'Internal';

    function showNotification(msg, type) {
        const toast = document.getElementById('toast');
        toast.style.display = 'flex';
        toast.style.backgroundColor = type === 'success' ? '#22c55e' : '#ef4444';
        toast.innerText = msg;
        setTimeout(() => { toast.style.display = 'none'; }, 3000);
    }

    function switchTab(sector) {
        activeTab = sector;
        document.getElementById('btn-internal').classList.toggle('active', sector === 'Internal');
        document.getElementById('btn-external').classList.toggle('active', sector === 'External');
        document.getElementById('assign-title').innerText = `Assign ${sector} Task`;
        document.getElementById('ledger-title-text').innerText = sector;
        
        const icon = document.getElementById('ledger-icon');
        icon.setAttribute('data-lucide', sector === 'Internal' ? 'home' : 'shield');
        
        populateSelects();
        renderTable();
        lucide.createIcons();
    }

    function populateSelects() {
        const roles = Object.keys(registry[activeTab]);
        const options = roles.map(role => `<option value="${role}">${role}</option>`).join('');
        document.getElementById('task-type').innerHTML = options;
        document.getElementById('ledger-filter').innerHTML = options;
    }

    function renderTable() {
        const filter = document.getElementById('ledger-filter').value;
        const tbody = document.getElementById('ledger-body');
        const registeredName = registry[activeTab][filter];
        const filteredTasks = tasks.filter(t => t.sector === activeTab && t.item === filter);

        let html = '';
        if (registeredName) {
            html += `<tr style="background-color: #fff7ed;">
                <td><strong>${registeredName}</strong> <small style="background:#fed7aa; padding:2px 5px; border-radius:10px;">REGISTERED</small></td>
                <td>${filter}</td>
                <td>--</td>
            </tr>`;
        }
        filteredTasks.forEach(t => {
            html += `<tr>
                <td><strong>${t.name}</strong><br><small><i data-lucide="map-pin" size="12"></i> ${t.area}</small></td>
                <td>${t.item}<br><small><i data-lucide="clock" size="12"></i> ${t.time}</small></td>
                <td><strong>₹${t.expense}</strong></td>
            </tr>`;
        });
        tbody.innerHTML = html || '<tr><td colspan="3" style="text-align:center; padding:20px;">No records found.</td></tr>';
        lucide.createIcons();
    }

    function handleRegister() {
        const s = document.getElementById('reg-sector').value;
        const r = document.getElementById('reg-role').value;
        const n = document.getElementById('reg-name').value;
        if (!r || !n) return showNotification("Fields required", "error");
        registry[s][r] = n;
        showNotification("Member Registered", "success");
        switchTab(activeTab);
    }

    function handleAssign() {
        const name = document.getElementById('task-worker').value;
        if (!name) return showNotification("Worker name required", "error");
        tasks.unshift({
            id: Date.now(),
            sector: activeTab,
            item: document.getElementById('task-type').value,
            name: name,
            area: document.getElementById('task-area').value,
            time: document.getElementById('task-time').value,
            expense: document.getElementById('task-cost').value
        });
        showNotification("Task Assigned", "success");
        renderTable();
    }

    populateSelects();
    renderTable();
    lucide.createIcons();
</script>