<?php
/**
 * Pharmacy History Page
 * Shows all prescriptions history for the logged-in pharmacy
 */
require_once __DIR__ . '/../settings/core.php';

// Redirect if not logged in
if (!is_logged_in() || get_user_type() !== 'pharmacy') {
    header('Location: login.php');
    exit();
}

// Get pharmacy name from session
$pharmacyName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Pharmacy';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy History - MedLink</title>
    <link rel="stylesheet" href="../css/pharmacyhistory.css">
</head>
<body class="pharmacy-history-body">
    <header class="pharmacy-history-header">
        <div>
            <p class="eyebrow">Pharmacy console</p>
            <h1><?php echo $pharmacyName; ?> History</h1>
            <p class="subtitle">Log of all past prescriptions and actions</p>
        </div>
        <div class="header-actions">
            <a href="pharmacy.php" class="ghost-btn">Back to active prescriptions</a>
            <a href="../actions/logout_action.php" class="ghost-btn">Logout</a>
        </div>
    </header>

    <section class="filters glass">
        <div class="search-group">
            <label for="searchInput">Search patient</label>
            <input type="text" id="searchInput" placeholder="Name or patient ID">
        </div>
        <div class="filter-group">
            <label for="statusFilter">Status</label>
            <select id="statusFilter">
                <option value="">All statuses</option>
                <option>Dispensed</option>
                <option>Ready for pickup</option>
                <option>Ready for delivery</option>
                <option>On hold</option>
                <option>Clarification requested</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="dateFrom">Date from</label>
            <input type="date" id="dateFrom">
        </div>
        <div class="filter-group">
            <label for="dateTo">Date to</label>
            <input type="date" id="dateTo">
        </div>
    </section>

    <section class="table-card glass">
        <div class="table-heading">
            <h2>History log</h2>
            <p>All past prescriptions processed by this pharmacy</p>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient name</th>
                        <th>Hospital</th>
                        <th>Date received</th>
                        <th>Date last action</th>
                        <th>Final status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="historyTable"></tbody>
            </table>
        </div>
    </section>

    <section class="log-panel glass" id="logPanel" style="display:none;">
        <div class="log-header">
            <div>
                <p class="eyebrow">Action log</p>
                <h3 id="logPrescriptionId">—</h3>
            </div>
            <button class="ghost" id="closeLogBtn">Close</button>
        </div>
        <ul class="log-list" id="logList"></ul>
    </section>

    <template id="historyRowTemplate">
        <tr>
            <td class="hist-id"></td>
            <td class="hist-name"></td>
            <td class="hist-hospital"></td>
            <td class="hist-received"></td>
            <td class="hist-last"></td>
            <td><span class="status-badge"></span></td>
            <td><button class="ghost view-log-btn">View log</button></td>
        </tr>
    </template>

    <script>
    let historyData = [];
    
    // Load history data from database
    async function loadHistoryData() {
        try {
            const response = await fetch('../actions/get_pharmacy_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                // Process prescriptions to get timeline data
                const processedData = await Promise.all(result.prescriptions.map(async prescription => {
                    // Fetch timeline for each prescription
                    const timelineResponse = await fetch(`../actions/get_prescription_timeline_action.php?prescription_id=${prescription.prescription_id}`);
                    const timelineResult = await timelineResponse.json();
                    
                    let actions = [];
                    let lastAction = prescription.date;
                    
                    if (timelineResult.status === 'success' && timelineResult.timeline) {
                        actions = timelineResult.timeline.map(entry => ({
                            timestamp: entry.timestamp,
                            action: entry.status_text,
                            actor: 'System' // Can be enhanced if actor info is stored
                        }));
                        
                        // Get last action timestamp
                        if (timelineResult.timeline.length > 0) {
                            lastAction = timelineResult.timeline[timelineResult.timeline.length - 1].timestamp;
                        }
                    }
                    
                    return {
                        id: prescription.id,
                        patient: prescription.patient,
                        hospital: prescription.hospital,
                        dateReceived: prescription.date,
                        dateLastAction: lastAction,
                        status: prescription.status,
                        actions: actions
                    };
                }));
                
                historyData = processedData;
                renderHistoryTable(historyData);
            } else {
                console.error('Error loading history:', result.message);
                historyData = [];
                renderHistoryTable(historyData);
            }
        } catch (error) {
            console.error('Error fetching history:', error);
            historyData = [];
            renderHistoryTable(historyData);
        }
    }
    
    // Load data when page loads
    loadHistoryData();

    const statusClasses = {
        'Dispensed': 'badge-neutral',
        'Ready for pickup': 'badge-green',
        'Ready for delivery': 'badge-teal',
        'On hold': 'badge-amber',
        'Clarification requested': 'badge-warning'
    };

    const historyTableBody = document.getElementById('historyTable');
    const historyRowTemplate = document.getElementById('historyRowTemplate');

    function renderHistoryTable(data) {
        historyTableBody.innerHTML = '';
        data.forEach((entry, index) => {
            const row = historyRowTemplate.content.cloneNode(true);
            row.querySelector('.hist-id').textContent = entry.id;
            row.querySelector('.hist-name').textContent = entry.patient;
            row.querySelector('.hist-hospital').textContent = entry.hospital;
            row.querySelector('.hist-received').textContent = entry.dateReceived;
            row.querySelector('.hist-last').textContent = entry.dateLastAction;
            const badge = row.querySelector('.status-badge');
            badge.textContent = entry.status;
            badge.className = 'status-badge ' + (statusClasses[entry.status] || '');
            row.querySelector('.view-log-btn').dataset.index = index;
            historyTableBody.appendChild(row);
        });
    }

    function showLog(index) {
        const entry = historyData[index];
        document.getElementById('logPrescriptionId').textContent = entry.id;
        const logList = document.getElementById('logList');
        logList.innerHTML = '';
        entry.actions.forEach(action => {
            const li = document.createElement('li');
            li.innerHTML = `<strong>${action.timestamp}</strong> — ${action.action}<br><span class="actor">${action.actor}</span>`;
            logList.appendChild(li);
        });
        document.getElementById('logPanel').style.display = 'block';
    }

    document.addEventListener('click', (evt) => {
        if (evt.target.matches('.view-log-btn')) {
            const index = evt.target.dataset.index;
            showLog(index);
        }
        if (evt.target.matches('#closeLogBtn')) {
            document.getElementById('logPanel').style.display = 'none';
        }
    });

    // Add search and filter functionality
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        const filtered = historyData.filter(entry => {
            // Search filter
            const matchesSearch = !searchTerm || 
                entry.patient.toLowerCase().includes(searchTerm) ||
                entry.id.toLowerCase().includes(searchTerm);
            
            // Status filter
            const matchesStatus = !statusFilter || entry.status === statusFilter;
            
            // Date filter
            const prescriptionDate = new Date(entry.dateReceived);
            const matchesDateFrom = !dateFrom || prescriptionDate >= new Date(dateFrom);
            const matchesDateTo = !dateTo || prescriptionDate <= new Date(dateTo);
            
            return matchesSearch && matchesStatus && matchesDateFrom && matchesDateTo;
        });
        
        renderHistoryTable(filtered);
    }
    
    // Attach event listeners
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFrom').addEventListener('change', applyFilters);
    document.getElementById('dateTo').addEventListener('change', applyFilters);
    </script>
</body>
</html>

