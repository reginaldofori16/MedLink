<?php
/**
 * Hospital History Page
 * Shows all prescriptions history for the logged-in hospital
 */
require_once __DIR__ . '/../settings/core.php';

// Redirect if not logged in
if (!is_logged_in() || get_user_type() !== 'hospital') {
    header('Location: login.php');
    exit();
}

// Get hospital name from session
$hospitalName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Hospital';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Prescription History - MedLink</title>
    <link rel="stylesheet" href="../css/hospitalhistory.css">
</head>
<body class="hos-body">
    <header class="hos-header">
        <div>
            <p class="eyebrow">Hospital console</p>
            <h1><?php echo $hospitalName; ?> History</h1>
            <p class="subtitle">Log of all past prescriptions and actions</p>
        </div>
        <div class="header-actions">
            <a href="hospital.php" class="ghost-btn">Back to active prescriptions</a>
            <a href="../actions/logout_action.php" class="ghost-btn">Logout</a>
        </div>
    </header>

    <section class="filters glass">
        <div class="search-group">
            <label for="historySearch">Search patient</label>
            <input type="text" id="historySearch" placeholder="Name or patient ID">
        </div>
        <div class="filter-group">
            <label for="historyStatus">Status</label>
            <select id="historyStatus">
                <option value="">All</option>
                <option>Confirmed</option>
                <option>Sent to pharmacies</option>
                <option>Completed</option>
                <option>Rejected</option>
                <option>Clarification requested</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="historyFrom">Date from</label>
            <input type="date" id="historyFrom">
        </div>
        <div class="filter-group">
            <label for="historyTo">Date to</label>
            <input type="date" id="historyTo">
        </div>
    </section>

    <section class="table-card glass">
        <div class="table-heading">
            <h2>History log</h2>
            <p>All prescriptions including dispatched and completed cases</p>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient name</th>
                        <th>Date submitted</th>
                        <th>Last action</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="historyTable"></tbody>
            </table>
        </div>
    </section>

    <section class="details-section glass" id="logPanel">
        <div class="details-header">
            <div>
                <p class="eyebrow">Action log</p>
                <h3 id="logPrescriptionId">Select a record</h3>
            </div>
            <span class="status-badge" id="logStatus">—</span>
        </div>
        <ul class="log-list" id="logList">
            <li>Choose a prescription to view log.</li>
        </ul>
    </section>

    <template id="historyRowTemplate">
        <tr>
            <td class="hist-id"></td>
            <td class="hist-name"></td>
            <td class="hist-submitted"></td>
            <td class="hist-last"></td>
            <td><span class="status-badge"></span></td>
            <td><button class="ghost view-log">View log</button></td>
        </tr>
    </template>

    <script>
    let historyData = [];
    
    // Load history data from database
    async function loadHistoryData() {
        try {
            const response = await fetch('../actions/get_hospital_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                // Process prescriptions to get timeline data
                const processedData = await Promise.all(result.prescriptions.map(async prescription => {
                    // Fetch timeline for each prescription
                    const timelineResponse = await fetch(`../actions/get_prescription_timeline_action.php?prescription_id=${prescription.prescription_id}`);
                    const timelineResult = await timelineResponse.json();
                    
                    let timeline = [];
                    let lastAction = prescription.date;
                    
                    if (timelineResult.status === 'success' && timelineResult.timeline) {
                        timeline = timelineResult.timeline.map(entry => 
                            `${entry.timestamp} - ${entry.status_text}`
                        );
                        
                        // Get last action timestamp
                        if (timelineResult.timeline.length > 0) {
                            lastAction = timelineResult.timeline[timelineResult.timeline.length - 1].timestamp;
                        }
                    }
                    
                    return {
                        id: prescription.id,
                        patient: prescription.patient,
                        submitted: prescription.date,
                        lastAction: lastAction,
                        status: prescription.status,
                        logs: timeline
                    };
                }));
                
                historyData = processedData;
                renderHistory(historyData);
            } else {
                console.error('Error loading history:', result.message);
                historyData = [];
                renderHistory(historyData);
            }
        } catch (error) {
            console.error('Error fetching history:', error);
            historyData = [];
            renderHistory(historyData);
        }
    }
    
    // Load data when page loads
    loadHistoryData();

    const statusClasses = {
        'Submitted by patient': 'badge-blue',
        'Under review': 'badge-slate',
        'Awaiting pharmacy': 'badge-amber',
        'Pharmacy reviewing': 'badge-indigo',
        'Confirmed': 'badge-green',
        'Sent to pharmacies': 'badge-purple',
        'Completed': 'badge-green',
        'Rejected': 'badge-warning',
        'Clarification requested': 'badge-warning'
    };

    const historyTable = document.getElementById('historyTable');
    const rowTemplate = document.getElementById('historyRowTemplate');

    function renderHistory(data) {
        historyTable.innerHTML = '';
        data.forEach((entry, index) => {
            const row = rowTemplate.content.cloneNode(true);
            row.querySelector('.hist-id').textContent = entry.id;
            row.querySelector('.hist-name').textContent = entry.patient;
            row.querySelector('.hist-submitted').textContent = entry.submitted;
            row.querySelector('.hist-last').textContent = entry.lastAction;
            const badge = row.querySelector('.status-badge');
            badge.textContent = entry.status;
            badge.className = 'status-badge ' + (statusClasses[entry.status] || '');
            row.querySelector('.view-log').dataset.index = index;
            historyTable.appendChild(row);
        });
    }

    function bindHistoryEvents() {
        document.addEventListener('click', (evt) => {
            if (evt.target.matches('.view-log')) {
                const index = evt.target.dataset.index;
                showLog(historyData[index]);
            }
        });
    }

    function showLog(entry) {
        document.getElementById('logPrescriptionId').textContent = entry.id;
        const badge = document.getElementById('logStatus');
        badge.textContent = entry.status;
        badge.className = 'status-badge ' + (statusClasses[entry.status] || '');
        const logList = document.getElementById('logList');
        logList.innerHTML = '';
        entry.logs.forEach(item => {
            const li = document.createElement('li');
            li.textContent = item;
            logList.appendChild(li);
        });
    }

    // Add search and filter functionality
    function applyFilters() {
        const searchTerm = document.getElementById('historySearch').value.toLowerCase();
        const statusFilter = document.getElementById('historyStatus').value;
        const dateFrom = document.getElementById('historyFrom').value;
        const dateTo = document.getElementById('historyTo').value;
        
        const filtered = historyData.filter(entry => {
            // Search filter
            const matchesSearch = !searchTerm || 
                entry.patient.toLowerCase().includes(searchTerm) ||
                entry.id.toLowerCase().includes(searchTerm);
            
            // Status filter
            const matchesStatus = !statusFilter || entry.status === statusFilter;
            
            // Date filter
            const prescriptionDate = new Date(entry.submitted);
            const matchesDateFrom = !dateFrom || prescriptionDate >= new Date(dateFrom);
            const matchesDateTo = !dateTo || prescriptionDate <= new Date(dateTo);
            
            return matchesSearch && matchesStatus && matchesDateFrom && matchesDateTo;
        });
        
        renderHistory(filtered);
    }
    
    // Attach event listeners
    document.getElementById('historySearch').addEventListener('input', applyFilters);
    document.getElementById('historyStatus').addEventListener('change', applyFilters);
    document.getElementById('historyFrom').addEventListener('change', applyFilters);
    document.getElementById('historyTo').addEventListener('change', applyFilters);
    
    bindHistoryEvents();
    </script>
</body>
</html>

