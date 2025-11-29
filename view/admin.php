<?php
/**
 * Admin Dashboard
 * Includes core functions for session management
 */
require_once __DIR__ . '/../settings/core.php';

// Redirect if not logged in or not admin
if (!is_logged_in() || !is_admin()) {
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MedLink</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>
<body class="admin-body">
    <header class="admin-header">
        <div>
            <p class="eyebrow">Admin console</p>
            <h1>MedLink Admin Dashboard</h1>
            <p class="subtitle">System overview and management</p>
        </div>
        <div class="header-actions">
            <a href="../actions/logout_action.php" class="ghost-btn">Logout</a>
        </div>
    </header>

    <!-- Statistics Overview -->
    <section class="stats-grid">
        <div class="stat-card glass">
            <div class="stat-icon blue">📋</div>
            <div class="stat-content">
                <h3 id="totalPrescriptions">0</h3>
                <p>Total Prescriptions</p>
            </div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon green">✅</div>
            <div class="stat-content">
                <h3 id="activePrescriptions">0</h3>
                <p>Active Prescriptions</p>
            </div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon purple">🏥</div>
            <div class="stat-content">
                <h3 id="totalHospitals">0</h3>
                <p>Registered Hospitals</p>
            </div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon orange">💊</div>
            <div class="stat-content">
                <h3 id="totalPharmacies">0</h3>
                <p>Registered Pharmacies</p>
            </div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon teal">👥</div>
            <div class="stat-content">
                <h3 id="totalPatients">0</h3>
                <p>Registered Patients</p>
            </div>
        </div>
        <div class="stat-card glass">
            <div class="stat-icon indigo">💰</div>
            <div class="stat-content">
                <h3 id="totalRevenue">GHS 0.00</h3>
                <p>Total Revenue</p>
            </div>
        </div>
    </section>

    <!-- Tabs Navigation -->
    <div class="tabs glass">
        <button class="tab-btn active" data-tab="overview">Overview</button>
        <button class="tab-btn" data-tab="prescriptions">Prescriptions</button>
        <button class="tab-btn" data-tab="users">Users</button>
        <button class="tab-btn" data-tab="payments">Payments</button>
        <button class="tab-btn" data-tab="analytics">Analytics</button>
    </div>

    <!-- Overview Tab -->
    <section class="tab-content active" id="overviewTab">
        <div class="overview-grid">
            <div class="overview-card glass">
                <h2>Recent Activity</h2>
                <ul class="activity-list" id="recentActivity"></ul>
            </div>
            <div class="overview-card glass">
                <h2>Prescription Status Distribution</h2>
                <div class="status-chart" id="statusChart"></div>
            </div>
            <div class="overview-card glass">
                <h2>Pending Approvals</h2>
                <div class="pending-list" id="pendingApprovals"></div>
            </div>
            <div class="overview-card glass">
                <h2>System Health</h2>
                <div class="health-metrics" id="systemHealth"></div>
            </div>
        </div>
    </section>

    <!-- Prescriptions Tab -->
    <section class="tab-content" id="prescriptionsTab">
        <section class="filters glass">
            <div class="search-group">
                <label for="prescriptionSearch">Search</label>
                <input type="text" id="prescriptionSearch" placeholder="Prescription ID, Patient, Hospital">
            </div>
            <div class="filter-group">
                <label for="prescriptionStatus">Status</label>
                <select id="prescriptionStatus">
                    <option value="">All statuses</option>
                    <option>Submitted by patient</option>
                    <option>Hospital reviewing</option>
                    <option>Pharmacy reviewing</option>
                    <option>Awaiting patient payment</option>
                    <option>Payment received</option>
                    <option>Ready for pickup</option>
                    <option>Dispensed</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="prescriptionDateFrom">Date from</label>
                <input type="date" id="prescriptionDateFrom">
            </div>
            <div class="filter-group">
                <label for="prescriptionDateTo">Date to</label>
                <input type="date" id="prescriptionDateTo">
            </div>
        </section>
        <section class="table-card glass">
            <div class="table-heading">
                <h2>All Prescriptions</h2>
                <p>Complete view of all prescriptions in the system</p>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Hospital</th>
                            <th>Pharmacy</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="allPrescriptionsTable"></tbody>
                </table>
            </div>
        </section>
    </section>

    <!-- Users Tab -->
    <section class="tab-content" id="usersTab">
        <div class="users-tabs">
            <button class="user-tab-btn active" data-user-type="hospitals">Hospitals</button>
            <button class="user-tab-btn" data-user-type="pharmacies">Pharmacies</button>
            <button class="user-tab-btn" data-user-type="patients">Patients</button>
        </div>
        <section class="filters glass">
            <div class="search-group">
                <label for="userSearch">Search</label>
                <input type="text" id="userSearch" placeholder="Name, ID, Email">
            </div>
            <div class="filter-group">
                <label for="userStatus">Status</label>
                <select id="userStatus">
                    <option value="">All</option>
                    <option>Active</option>
                    <option>Pending</option>
                    <option>Suspended</option>
                </select>
            </div>
        </section>
        <section class="table-card glass">
            <div class="table-heading">
                <h2 id="usersTableTitle">Hospitals</h2>
                <p id="usersTableSubtitle">Manage registered hospitals</p>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead id="usersTableHead"></thead>
                    <tbody id="usersTableBody"></tbody>
                </table>
            </div>
        </section>
    </section>

    <!-- Payments Tab -->
    <section class="tab-content" id="paymentsTab">
        <section class="filters glass">
            <div class="search-group">
                <label for="paymentSearch">Search</label>
                <input type="text" id="paymentSearch" placeholder="Transaction ID, Prescription ID">
            </div>
            <div class="filter-group">
                <label for="paymentMethod">Payment Method</label>
                <select id="paymentMethod">
                    <option value="">All methods</option>
                    <option>Mobile Money</option>
                    <option>Card</option>
                    <option>Bank Transfer</option>
                    <option>Cash on Delivery</option>
                </select>
            </div>
            <div class="filter-group">
                <label for="paymentDateFrom">Date from</label>
                <input type="date" id="paymentDateFrom">
            </div>
            <div class="filter-group">
                <label for="paymentDateTo">Date to</label>
                <input type="date" id="paymentDateTo">
            </div>
        </section>
        <section class="table-card glass">
            <div class="table-heading">
                <h2>Payment Transactions</h2>
                <p>All payment transactions in the system</p>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Transaction ID</th>
                            <th>Prescription ID</th>
                            <th>Patient</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="paymentsTable"></tbody>
                </table>
            </div>
        </section>
    </section>

    <!-- Analytics Tab -->
    <section class="tab-content" id="analyticsTab">
        <div class="analytics-grid">
            <div class="analytics-card glass">
                <h2>Prescription Trends</h2>
                <div class="chart-placeholder" id="prescriptionTrends">
                    <p>📈 Chart: Prescriptions over time</p>
                    <p class="chart-note">Last 30 days: 245 prescriptions</p>
                </div>
            </div>
            <div class="analytics-card glass">
                <h2>Revenue Trends</h2>
                <div class="chart-placeholder" id="revenueTrends">
                    <p>💰 Chart: Revenue over time</p>
                    <p class="chart-note">Last 30 days: GHS 12,450.00</p>
                </div>
            </div>
            <div class="analytics-card glass">
                <h2>Top Hospitals</h2>
                <ul class="top-list" id="topHospitals"></ul>
            </div>
            <div class="analytics-card glass">
                <h2>Top Pharmacies</h2>
                <ul class="top-list" id="topPharmacies"></ul>
            </div>
            <div class="analytics-card glass">
                <h2>Popular Medicines</h2>
                <ul class="top-list" id="popularMedicines"></ul>
            </div>
            <div class="analytics-card glass">
                <h2>User Growth</h2>
                <div class="chart-placeholder" id="userGrowth">
                    <p>👥 Chart: User growth over time</p>
                    <p class="chart-note">Total users: 1,234</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Detail Modal -->
    <div class="modal" id="detailModal" style="display:none;">
        <div class="modal-content glass">
            <div class="modal-header">
                <h2 id="modalTitle">Details</h2>
                <button class="close-modal" id="closeModal">&times;</button>
            </div>
            <div class="modal-body" id="modalBody"></div>
        </div>
    </div>

    <script>
    // Data storage (populated from database)
    const adminData = {
        hospitals: [],
        pharmacies: [],
        patients: [],
        prescriptions: [],
        payments: [],
        statistics: null,
        analytics: null,
        activities: []
    };

    const statusClasses = {
        'Submitted by patient': 'badge-blue',
        'Hospital reviewing': 'badge-slate',
        'Pharmacy reviewing': 'badge-indigo',
        'Awaiting patient payment': 'badge-orange',
        'Payment received': 'badge-green',
        'Ready for pickup': 'badge-green',
        'Dispensed': 'badge-neutral',
        'Active': 'badge-green',
        'Pending': 'badge-amber',
        'Suspended': 'badge-warning',
        'Completed': 'badge-green'
    };

    async function initializeDashboard() {
        await updateStatistics();
        await renderOverview();
        await renderPrescriptions();
        await renderUsers('hospitals');
        await renderPayments();
        await renderAnalytics();
        bindEvents();
    }

    async function updateStatistics() {
        try {
            const response = await fetch('../actions/get_admin_statistics_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.statistics) {
                const stats = result.statistics;
                document.getElementById('totalPrescriptions').textContent = stats.totalPrescriptions;
                document.getElementById('activePrescriptions').textContent = stats.activePrescriptions;
                document.getElementById('totalHospitals').textContent = stats.totalHospitals;
                document.getElementById('totalPharmacies').textContent = stats.totalPharmacies;
                document.getElementById('totalPatients').textContent = stats.totalPatients;
                document.getElementById('totalRevenue').textContent = `GHS ${stats.totalRevenue.toFixed(2)}`;
                adminData.statistics = stats;
            } else {
                // Fallback to 0 if fetch fails
                document.getElementById('totalPrescriptions').textContent = '0';
                document.getElementById('activePrescriptions').textContent = '0';
                document.getElementById('totalHospitals').textContent = '0';
                document.getElementById('totalPharmacies').textContent = '0';
                document.getElementById('totalPatients').textContent = '0';
                document.getElementById('totalRevenue').textContent = 'GHS 0.00';
            }
        } catch (error) {
            console.error('Error fetching statistics:', error);
            // Fallback to 0 if fetch fails
            document.getElementById('totalPrescriptions').textContent = '0';
            document.getElementById('activePrescriptions').textContent = '0';
            document.getElementById('totalHospitals').textContent = '0';
            document.getElementById('totalPharmacies').textContent = '0';
            document.getElementById('totalPatients').textContent = '0';
            document.getElementById('totalRevenue').textContent = 'GHS 0.00';
        }
    }

    async function renderOverview() {
        // Recent Activity
        const activityList = document.getElementById('recentActivity');
        activityList.innerHTML = '<li>Loading...</li>';
        
        try {
            const response = await fetch('../actions/get_recent_activity_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.activities) {
                adminData.activities = result.activities;
                activityList.innerHTML = '';
                if (result.activities.length === 0) {
                    activityList.innerHTML = '<li>No recent activity</li>';
                } else {
                    result.activities.forEach(activity => {
                        const li = document.createElement('li');
                        li.innerHTML = `<span class="activity-time">${activity.time}</span><span class="activity-text">${activity.text}</span>`;
                        activityList.appendChild(li);
                    });
                }
            } else {
                activityList.innerHTML = '<li>No recent activity</li>';
            }
        } catch (error) {
            console.error('Error fetching recent activity:', error);
            activityList.innerHTML = '<li>Error loading activity</li>';
        }

        // Status Distribution
        const statusChart = document.getElementById('statusChart');
        const statusCounts = {};
        adminData.prescriptions.forEach(p => {
            statusCounts[p.status] = (statusCounts[p.status] || 0) + 1;
        });
        
        if (Object.keys(statusCounts).length === 0) {
            statusChart.innerHTML = '<p>No prescriptions yet</p>';
        } else {
            statusChart.innerHTML = Object.entries(statusCounts).map(([status, count]) => 
                `<div class="status-bar"><span>${status}</span><span>${count}</span></div>`
            ).join('');
        }

        // Pending Approvals
        const pendingList = document.getElementById('pendingApprovals');
        const pending = [
            ...adminData.hospitals.filter(h => h.status && h.status.toLowerCase() === 'pending'),
            ...adminData.pharmacies.filter(p => p.status && p.status.toLowerCase() === 'pending')
        ];
        if (pending.length === 0) {
            pendingList.innerHTML = '<p>No pending approvals</p>';
        } else {
            pendingList.innerHTML = pending.map(item => 
                `<div class="pending-item"><strong>${item.name}</strong><button class="ghost approve-btn">Approve</button></div>`
            ).join('');
        }

        // System Health
        document.getElementById('systemHealth').innerHTML = `
            <div class="health-item"><span>System Status</span><span class="health-good">Operational</span></div>
            <div class="health-item"><span>API Response</span><span class="health-good">Active</span></div>
            <div class="health-item"><span>Database</span><span class="health-good">Connected</span></div>
            <div class="health-item"><span>Uptime</span><span>99.9%</span></div>
        `;
    }

    async function renderPrescriptions() {
        const tbody = document.getElementById('allPrescriptionsTable');
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">Loading...</td></tr>';
        
        try {
            const response = await fetch('../actions/get_all_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                adminData.prescriptions = result.prescriptions;
                tbody.innerHTML = '';
                
                if (result.prescriptions.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No prescriptions found</td></tr>';
                } else {
                    result.prescriptions.forEach(pres => {
                        const row = document.createElement('tr');
                        const badge = statusClasses[pres.status] || '';
                        row.innerHTML = `
                            <td>${pres.id}</td>
                            <td>${pres.patient}<br><small>${pres.patientId}</small></td>
                            <td>${pres.hospital}</td>
                            <td>${pres.pharmacy || '—'}</td>
                            <td><span class="status-badge ${badge}">${pres.status}</span></td>
                            <td>${pres.date}</td>
                            <td>GHS ${pres.amount.toFixed(2)}</td>
                            <td><button class="view-detail-btn" data-type="prescription" data-id="${pres.id}">View</button></td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">Error loading prescriptions</td></tr>';
            }
        } catch (error) {
            console.error('Error fetching prescriptions:', error);
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">Error loading prescriptions</td></tr>';
        }
    }

    async function renderUsers(type) {
        const tbody = document.getElementById('usersTableBody');
        const thead = document.getElementById('usersTableHead');
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">Loading...</td></tr>';
        
        let data, columns;
        if (type === 'hospitals') {
            // Fetch hospitals from database
            try {
                const response = await fetch('../actions/get_hospitals_action.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    data = result.hospitals;
                } else {
                    console.error('Error fetching hospitals:', result.message);
                    data = []; // Fallback to empty array
                }
            } catch (error) {
                console.error('Error fetching hospitals:', error);
                data = []; // Fallback to empty array
            }
            
            columns = ['ID', 'Name', 'Code', 'Contact', 'Status', 'Registered', 'Prescriptions', ''];
            thead.innerHTML = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No hospitals found</td></tr>';
                return;
            }
            
            // Store fetched hospitals in adminData for modal access
            adminData.hospitals = data;
            
            data.forEach(item => {
                const row = document.createElement('tr');
                const badge = statusClasses[item.status] || '';
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td>${item.code}</td>
                    <td>${item.contact}</td>
                    <td><span class="status-badge ${badge}">${item.status}</span></td>
                    <td>${item.registered}</td>
                    <td>${item.prescriptions}</td>
                    <td><button class="view-detail-btn" data-type="hospital" data-id="${item.id}" data-hospital-id="${item.hospital_id}">View</button></td>
                `;
                tbody.appendChild(row);
            });
        } else if (type === 'pharmacies') {
            // Fetch pharmacies from database
            try {
                const response = await fetch('../actions/get_pharmacies_action.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    data = result.pharmacies;
                } else {
                    console.error('Error fetching pharmacies:', result.message);
                    data = []; // Fallback to empty array
                }
            } catch (error) {
                console.error('Error fetching pharmacies:', error);
                data = []; // Fallback to empty array
            }
            
            columns = ['ID', 'Name', 'Code', 'Location', 'Contact', 'Status', 'Registered', 'Orders', ''];
            thead.innerHTML = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;">No pharmacies found</td></tr>';
                return;
            }
            
            // Store fetched pharmacies in adminData for modal access
            adminData.pharmacies = data;
            
            data.forEach(item => {
                const row = document.createElement('tr');
                const badge = statusClasses[item.status] || '';
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td>${item.code}</td>
                    <td>${item.location}</td>
                    <td>${item.contact}</td>
                    <td><span class="status-badge ${badge}">${item.status}</span></td>
                    <td>${item.registered}</td>
                    <td>${item.orders}</td>
                    <td><button class="view-detail-btn" data-type="pharmacy" data-id="${item.id}" data-pharmacy-id="${item.pharmacy_id}">View</button></td>
                `;
                tbody.appendChild(row);
            });
        } else {
            // Fetch patients from database
            try {
                const response = await fetch('../actions/get_patients_action.php');
                const result = await response.json();
                
                if (result.status === 'success') {
                    data = result.patients;
                } else {
                    console.error('Error fetching patients:', result.message);
                    data = []; // Fallback to empty array
                }
            } catch (error) {
                console.error('Error fetching patients:', error);
                data = []; // Fallback to empty array
            }
            
            columns = ['ID', 'Name', 'Email', 'Phone', 'Status', 'Registered', 'Prescriptions', ''];
            thead.innerHTML = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;
            tbody.innerHTML = '';
            
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No patients found</td></tr>';
                return;
            }
            
            // Store fetched patients in adminData for modal access
            adminData.patients = data;
            
            data.forEach(item => {
                const row = document.createElement('tr');
                const badge = statusClasses[item.status] || '';
                row.innerHTML = `
                    <td>${item.id}</td>
                    <td>${item.name}</td>
                    <td>${item.email}</td>
                    <td>${item.phone}</td>
                    <td><span class="status-badge ${badge}">${item.status}</span></td>
                    <td>${item.registered}</td>
                    <td>${item.prescriptions}</td>
                    <td><button class="view-detail-btn" data-type="patient" data-id="${item.id}">View</button></td>
                `;
                tbody.appendChild(row);
            });
        }
    }

    async function renderPayments() {
        const tbody = document.getElementById('paymentsTable');
        tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">Loading...</td></tr>';
        
        // Generate payments from prescriptions with payment status
        try {
            const response = await fetch('../actions/get_all_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                // Filter prescriptions that have payment status and amount
                const payments = result.prescriptions
                    .filter(p => p.amount > 0 && ['Payment received', 'Ready for pickup', 'Ready for delivery', 'Dispensed', 'Awaiting patient payment'].includes(p.status))
                    .map((pres, index) => ({
                        id: 'TXN-' + String(index + 1).padStart(3, '0'),
                        prescriptionId: pres.id,
                        patient: pres.patient,
                        amount: pres.amount,
                        method: 'Mobile Money', // Default - can be enhanced with actual payment method if stored
                        channel: 'MTN MoMo', // Default - can be enhanced with actual channel if stored
                        status: pres.status === 'Awaiting patient payment' ? 'Pending' : 'Completed',
                        date: pres.date
                    }));
                
                adminData.payments = payments;
                tbody.innerHTML = '';
                
                if (payments.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No payment transactions found</td></tr>';
                } else {
                    payments.forEach(payment => {
                        const row = document.createElement('tr');
                        const badge = statusClasses[payment.status] || '';
                        row.innerHTML = `
                            <td>${payment.id}</td>
                            <td>${payment.prescriptionId}</td>
                            <td>${payment.patient}</td>
                            <td>GHS ${payment.amount.toFixed(2)}</td>
                            <td>${payment.method}<br><small>${payment.channel}</small></td>
                            <td><span class="status-badge ${badge}">${payment.status}</span></td>
                            <td>${payment.date}</td>
                            <td><button class="view-detail-btn" data-type="payment" data-id="${payment.id}">View</button></td>
                        `;
                        tbody.appendChild(row);
                    });
                }
            } else {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">Error loading payments</td></tr>';
            }
        } catch (error) {
            console.error('Error fetching payments:', error);
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">Error loading payments</td></tr>';
        }
    }

    async function renderAnalytics() {
        try {
            const response = await fetch('../actions/get_admin_analytics_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.analytics) {
                adminData.analytics = result.analytics;
                
                // Top Hospitals
                const topHospitalsList = document.getElementById('topHospitals');
                if (result.analytics.topHospitals && result.analytics.topHospitals.length > 0) {
                    topHospitalsList.innerHTML = result.analytics.topHospitals
                        .map(h => `<li><strong>${h.name}</strong><span>${h.prescriptions} prescriptions</span></li>`)
                        .join('');
                } else {
                    topHospitalsList.innerHTML = '<li>No data available</li>';
                }
                
                // Top Pharmacies
                const topPharmaciesList = document.getElementById('topPharmacies');
                if (result.analytics.topPharmacies && result.analytics.topPharmacies.length > 0) {
                    topPharmaciesList.innerHTML = result.analytics.topPharmacies
                        .map(p => `<li><strong>${p.name}</strong><span>${p.orders} orders</span></li>`)
                        .join('');
                } else {
                    topPharmaciesList.innerHTML = '<li>No data available</li>';
                }
                
                // Popular Medicines
                const popularMedicinesList = document.getElementById('popularMedicines');
                if (result.analytics.popularMedicines && result.analytics.popularMedicines.length > 0) {
                    popularMedicinesList.innerHTML = result.analytics.popularMedicines
                        .map(m => `<li><strong>${m.name}</strong><span>${m.count} prescriptions</span></li>`)
                        .join('');
                } else {
                    popularMedicinesList.innerHTML = '<li>No data available</li>';
                }
                
                // Update chart placeholders with real data
                const prescriptionTrendsEl = document.getElementById('prescriptionTrends');
                const prescriptionCount = result.analytics.prescriptionTrends 
                    ? result.analytics.prescriptionTrends.reduce((sum, t) => sum + t.count, 0)
                    : 0;
                prescriptionTrendsEl.innerHTML = `
                    <p>📈 Chart: Prescriptions over time</p>
                    <p class="chart-note">Last 30 days: ${prescriptionCount} prescriptions</p>
                `;
                
                const revenueTrendsEl = document.getElementById('revenueTrends');
                const revenue30Days = result.analytics.totalRevenue30Days || 0;
                revenueTrendsEl.innerHTML = `
                    <p>💰 Chart: Revenue over time</p>
                    <p class="chart-note">Last 30 days: GHS ${revenue30Days.toFixed(2)}</p>
                `;
                
                const userGrowthEl = document.getElementById('userGrowth');
                const totalUsers = result.analytics.totalUsers || 0;
                userGrowthEl.innerHTML = `
                    <p>👥 Chart: User growth over time</p>
                    <p class="chart-note">Total users: ${totalUsers}</p>
                `;
            } else {
                // Fallback if fetch fails
                document.getElementById('topHospitals').innerHTML = '<li>No data available</li>';
                document.getElementById('topPharmacies').innerHTML = '<li>No data available</li>';
                document.getElementById('popularMedicines').innerHTML = '<li>No data available</li>';
            }
        } catch (error) {
            console.error('Error fetching analytics:', error);
            document.getElementById('topHospitals').innerHTML = '<li>Error loading data</li>';
            document.getElementById('topPharmacies').innerHTML = '<li>Error loading data</li>';
            document.getElementById('popularMedicines').innerHTML = '<li>Error loading data</li>';
        }
    }

    function bindEvents() {
        // Tab switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
                btn.classList.add('active');
                const tabId = btn.dataset.tab + 'Tab';
                document.getElementById(tabId).classList.add('active');
                
                // Reload data when switching to specific tabs
                if (btn.dataset.tab === 'prescriptions') {
                    await renderPrescriptions();
                } else if (btn.dataset.tab === 'payments') {
                    await renderPayments();
                } else if (btn.dataset.tab === 'analytics') {
                    await renderAnalytics();
                } else if (btn.dataset.tab === 'overview') {
                    await renderOverview();
                }
            });
        });

        // User type switching
        document.querySelectorAll('.user-tab-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                document.querySelectorAll('.user-tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                const type = btn.dataset.userType;
                await renderUsers(type);
                document.getElementById('usersTableTitle').textContent = type.charAt(0).toUpperCase() + type.slice(1);
                document.getElementById('usersTableSubtitle').textContent = `Manage registered ${type}`;
            });
        });

        // View detail buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('.view-detail-btn')) {
                const type = e.target.dataset.type;
                const id = e.target.dataset.id;
                const hospitalId = e.target.dataset.hospitalId || null;
                const pharmacyId = e.target.dataset.pharmacyId || null;
                showDetailModal(type, id, hospitalId, pharmacyId);
            }
        });
        
        // Status update button
        document.addEventListener('click', (e) => {
            if (e.target.matches('.update-status-btn')) {
                const hospitalId = e.target.dataset.hospitalId;
                const pharmacyId = e.target.dataset.pharmacyId;
                const statusSelect = document.getElementById('hospitalStatusSelect') || document.getElementById('pharmacyStatusSelect');
                if (statusSelect && hospitalId) {
                    const newStatus = statusSelect.value;
                    updateHospitalStatus(hospitalId, newStatus);
                } else if (statusSelect && pharmacyId) {
                    const newStatus = statusSelect.value;
                    updatePharmacyStatus(pharmacyId, newStatus);
                }
            }
        });

        // Close modal
        document.getElementById('closeModal').addEventListener('click', () => {
            document.getElementById('detailModal').style.display = 'none';
        });
    }

    function showDetailModal(type, id, hospitalId = null, pharmacyId = null) {
        const modal = document.getElementById('detailModal');
        const modalTitle = document.getElementById('modalTitle');
        const modalBody = document.getElementById('modalBody');
        
        let data, title, content;
        if (type === 'prescription') {
            data = adminData.prescriptions.find(p => p.id === id);
            title = `Prescription ${id}`;
            content = `
                <p><strong>Patient:</strong> ${data.patient} (${data.patientId})</p>
                <p><strong>Hospital:</strong> ${data.hospital}</p>
                <p><strong>Pharmacy:</strong> ${data.pharmacy || 'Not assigned'}</p>
                <p><strong>Status:</strong> ${data.status}</p>
                <p><strong>Date:</strong> ${data.date}</p>
                <p><strong>Amount:</strong> GHS ${data.amount.toFixed(2)}</p>
            `;
        } else if (type === 'hospital') {
            data = adminData.hospitals.find(h => h.id === id);
            if (!data) {
                title = `Hospital ${id}`;
                content = `<p>Hospital data not found.</p>`;
            } else {
                const currentStatus = data.status.toLowerCase();
                title = data.name;
                content = `
                    <p><strong>ID:</strong> ${data.id}</p>
                    <p><strong>Code:</strong> ${data.code}</p>
                    <p><strong>Contact:</strong> ${data.contact}</p>
                    <div style="margin: 20px 0;">
                        <label for="hospitalStatusSelect" style="display: block; margin-bottom: 8px; font-weight: 600;"><strong>Status:</strong></label>
                        <select id="hospitalStatusSelect" style="width: 100%; padding: 10px; border-radius: 8px; border: 1.4px solid #e2e8f4; font-size: 1rem;">
                            <option value="active" ${currentStatus === 'active' ? 'selected' : ''}>Active</option>
                            <option value="suspended" ${currentStatus === 'suspended' ? 'selected' : ''}>Suspended</option>
                        </select>
                    </div>
                    <p><strong>Registered:</strong> ${data.registered}</p>
                    <p><strong>Total Prescriptions:</strong> ${data.prescriptions}</p>
                    <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" class="update-status-btn" data-hospital-id="${data.hospital_id}" style="padding: 10px 24px; cursor: pointer; background: #0066FF; color: white; border: none; border-radius: 8px; font-weight: 600; transition: background 0.2s;">Update Status</button>
                    </div>
                `;
            }
        } else if (type === 'pharmacy') {
            data = adminData.pharmacies.find(p => p.id === id);
            
            // If not found, show a message
            if (!data) {
                title = `Pharmacy ${id}`;
                content = `<p>Pharmacy data not found.</p>`;
            } else {
                const currentStatus = data.status.toLowerCase();
                title = data.name;
                content = `
                    <p><strong>ID:</strong> ${data.id}</p>
                    <p><strong>Code:</strong> ${data.code}</p>
                    <p><strong>Location:</strong> ${data.location}</p>
                    <p><strong>Contact:</strong> ${data.contact}</p>
                    <div style="margin: 20px 0;">
                        <label for="pharmacyStatusSelect" style="display: block; margin-bottom: 8px; font-weight: 600;"><strong>Status:</strong></label>
                        <select id="pharmacyStatusSelect" style="width: 100%; padding: 10px; border-radius: 8px; border: 1.4px solid #e2e8f4; font-size: 1rem;">
                            <option value="active" ${currentStatus === 'active' ? 'selected' : ''}>Active</option>
                            <option value="suspended" ${currentStatus === 'suspended' ? 'selected' : ''}>Suspended</option>
                        </select>
                    </div>
                    <p><strong>Registered:</strong> ${data.registered}</p>
                    <p><strong>Total Orders:</strong> ${data.orders}</p>
                    <div style="margin-top: 24px; display: flex; gap: 12px; justify-content: flex-end;">
                        <button type="button" class="update-status-btn" data-pharmacy-id="${data.pharmacy_id}" style="padding: 10px 24px; cursor: pointer; background: #0066FF; color: white; border: none; border-radius: 8px; font-weight: 600; transition: background 0.2s;">Update Status</button>
                    </div>
                `;
            }
        } else if (type === 'patient') {
            data = adminData.patients.find(p => p.id === id);
            
            // If not found, show a message
            if (!data) {
                title = `Patient ${id}`;
                content = `<p>Full details for this patient will be displayed here.</p>`;
            } else {
                title = data.name;
                content = `
                    <p><strong>ID:</strong> ${data.id}</p>
                    <p><strong>Email:</strong> ${data.email}</p>
                    <p><strong>Phone:</strong> ${data.phone}</p>
                    <p><strong>Country:</strong> ${data.country || 'N/A'}</p>
                    <p><strong>City:</strong> ${data.city || 'N/A'}</p>
                    <p><strong>Status:</strong> ${data.status}</p>
                    <p><strong>Registered:</strong> ${data.registered}</p>
                    <p><strong>Last Login:</strong> ${data.last_login || 'Never'}</p>
                    <p><strong>Total Prescriptions:</strong> ${data.prescriptions}</p>
                `;
            }
        } else if (type === 'payment') {
            data = adminData.payments.find(p => p.id === id);
            title = `Transaction ${id}`;
            content = `
                <p><strong>Prescription ID:</strong> ${data.prescriptionId}</p>
                <p><strong>Patient:</strong> ${data.patient}</p>
                <p><strong>Amount:</strong> GHS ${data.amount.toFixed(2)}</p>
                <p><strong>Method:</strong> ${data.method}</p>
                <p><strong>Channel:</strong> ${data.channel}</p>
                <p><strong>Status:</strong> ${data.status}</p>
                <p><strong>Date:</strong> ${data.date}</p>
            `;
        }
        
        modalTitle.textContent = title;
        modalBody.innerHTML = content;
        modal.style.display = 'flex';
    }

    async function updateHospitalStatus(hospitalId, newStatus) {
        try {
            const response = await fetch('../actions/update_hospital_status_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    hospital_id: parseInt(hospitalId),
                    status: newStatus
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                // Close modal
                document.getElementById('detailModal').style.display = 'none';
                
                // Refresh hospitals table
                await renderUsers('hospitals');
                
                // Show success message (optional - you can customize this)
                alert('Hospital status updated successfully to ' + result.new_status);
            } else {
                alert('Error updating status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating hospital status:', error);
            alert('An error occurred while updating the status. Please try again.');
        }
    }

    async function updatePharmacyStatus(pharmacyId, newStatus) {
        try {
            const response = await fetch('../actions/update_pharmacy_status_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    pharmacy_id: parseInt(pharmacyId),
                    status: newStatus
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                // Close modal
                document.getElementById('detailModal').style.display = 'none';
                
                // Refresh pharmacies table
                await renderUsers('pharmacies');
                
                // Show success message (optional - you can customize this)
                alert('Pharmacy status updated successfully to ' + result.new_status);
            } else {
                alert('Error updating status: ' + result.message);
            }
        } catch (error) {
            console.error('Error updating pharmacy status:', error);
            alert('An error occurred while updating the status. Please try again.');
        }
    }

    // Add search and filter functionality for Prescriptions tab
    function applyPrescriptionFilters() {
        const searchTerm = document.getElementById('prescriptionSearch').value.toLowerCase();
        const statusFilter = document.getElementById('prescriptionStatus').value;
        const dateFrom = document.getElementById('prescriptionDateFrom').value;
        const dateTo = document.getElementById('prescriptionDateTo').value;
        
        const filtered = adminData.prescriptions.filter(prescription => {
            // Search filter
            const matchesSearch = !searchTerm || 
                prescription.id.toLowerCase().includes(searchTerm) ||
                prescription.patient.toLowerCase().includes(searchTerm) ||
                prescription.hospital.toLowerCase().includes(searchTerm);
            
            // Status filter
            const matchesStatus = !statusFilter || prescription.status === statusFilter;
            
            // Date filter
            const prescriptionDate = new Date(prescription.date);
            const matchesDateFrom = !dateFrom || prescriptionDate >= new Date(dateFrom);
            const matchesDateTo = !dateTo || prescriptionDate <= new Date(dateTo);
            
            return matchesSearch && matchesStatus && matchesDateFrom && matchesDateTo;
        });
        
        // Re-render prescription table with filtered data
        const tbody = document.getElementById('allPrescriptionsTable');
        tbody.innerHTML = '';
        
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No prescriptions found</td></tr>';
        } else {
            filtered.forEach(pres => {
                const row = document.createElement('tr');
                const badge = statusClasses[pres.status] || '';
                row.innerHTML = `
                    <td>${pres.id}</td>
                    <td>${pres.patient}<br><small>${pres.patientId}</small></td>
                    <td>${pres.hospital}</td>
                    <td>${pres.pharmacy || '—'}</td>
                    <td><span class="status-badge ${badge}">${pres.status}</span></td>
                    <td>${pres.date}</td>
                    <td>GHS ${pres.amount.toFixed(2)}</td>
                    <td><button class="view-detail-btn" data-type="prescription" data-id="${pres.id}">View</button></td>
                `;
                tbody.appendChild(row);
            });
        }
    }
    
    // Add search and filter functionality for Users tab
    function applyUserFilters() {
        const searchTerm = document.getElementById('userSearch').value.toLowerCase();
        const statusFilter = document.getElementById('userStatus').value;
        
        // Get current user type (hospitals, pharmacies, or patients)
        const activeTab = document.querySelector('.user-tab-btn.active');
        if (!activeTab) return;
        
        const userType = activeTab.dataset.userType;
        let data = [];
        
        if (userType === 'hospitals') {
            data = adminData.hospitals;
        } else if (userType === 'pharmacies') {
            data = adminData.pharmacies;
        } else if (userType === 'patients') {
            data = adminData.patients;
        }
        
        const filtered = data.filter(user => {
            // Search filter
            const matchesSearch = !searchTerm || 
                (user.name && user.name.toLowerCase().includes(searchTerm)) ||
                (user.id && user.id.toString().includes(searchTerm)) ||
                (user.email && user.email.toLowerCase().includes(searchTerm)) ||
                (user.code && user.code.toLowerCase().includes(searchTerm));
            
            // Status filter
            const matchesStatus = !statusFilter || 
                (user.status && user.status.toLowerCase() === statusFilter.toLowerCase());
            
            return matchesSearch && matchesStatus;
        });
        
        // Re-render users table based on type
        const tbody = document.getElementById('usersTableBody');
        const thead = document.getElementById('usersTableHead');
        tbody.innerHTML = '';
        
        if (userType === 'hospitals') {
            const columns = ['ID', 'Name', 'Code', 'Contact', 'Status', 'Registered', 'Prescriptions', ''];
            thead.innerHTML = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No hospitals found</td></tr>';
            } else {
                filtered.forEach(item => {
                    const row = document.createElement('tr');
                    const badge = statusClasses[item.status] || '';
                    row.innerHTML = `
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.code}</td>
                        <td>${item.contact}</td>
                        <td><span class="status-badge ${badge}">${item.status}</span></td>
                        <td>${item.registered}</td>
                        <td>${item.prescriptions}</td>
                        <td><button class="view-detail-btn" data-type="hospital" data-id="${item.id}" data-hospital-id="${item.hospital_id}">View</button></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        } else if (userType === 'pharmacies') {
            const columns = ['ID', 'Name', 'Code', 'Location', 'Contact', 'Status', 'Registered', 'Orders', ''];
            thead.innerHTML = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:20px;">No pharmacies found</td></tr>';
            } else {
                filtered.forEach(item => {
                    const row = document.createElement('tr');
                    const badge = statusClasses[item.status] || '';
                    row.innerHTML = `
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.code}</td>
                        <td>${item.location}</td>
                        <td>${item.contact}</td>
                        <td><span class="status-badge ${badge}">${item.status}</span></td>
                        <td>${item.registered}</td>
                        <td>${item.orders}</td>
                        <td><button class="view-detail-btn" data-type="pharmacy" data-id="${item.id}" data-pharmacy-id="${item.pharmacy_id}">View</button></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        } else if (userType === 'patients') {
            const columns = ['ID', 'Name', 'Email', 'Phone', 'Status', 'Registered', 'Prescriptions', ''];
            thead.innerHTML = `<tr>${columns.map(c => `<th>${c}</th>`).join('')}</tr>`;
            
            if (filtered.length === 0) {
                tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No patients found</td></tr>';
            } else {
                filtered.forEach(item => {
                    const row = document.createElement('tr');
                    const badge = statusClasses[item.status] || '';
                    row.innerHTML = `
                        <td>${item.id}</td>
                        <td>${item.name}</td>
                        <td>${item.email}</td>
                        <td>${item.phone}</td>
                        <td><span class="status-badge ${badge}">${item.status}</span></td>
                        <td>${item.registered}</td>
                        <td>${item.prescriptions}</td>
                        <td><button class="view-detail-btn" data-type="patient" data-id="${item.id}">View</button></td>
                    `;
                    tbody.appendChild(row);
                });
            }
        }
    }
    
    // Add search and filter functionality for Payments tab
    function applyPaymentFilters() {
        const searchTerm = document.getElementById('paymentSearch').value.toLowerCase();
        const methodFilter = document.getElementById('paymentMethod').value;
        const dateFrom = document.getElementById('paymentDateFrom').value;
        const dateTo = document.getElementById('paymentDateTo').value;
        
        const filtered = adminData.payments.filter(payment => {
            // Search filter
            const matchesSearch = !searchTerm || 
                payment.id.toLowerCase().includes(searchTerm) ||
                payment.prescriptionId.toLowerCase().includes(searchTerm);
            
            // Method filter
            const matchesMethod = !methodFilter || payment.method === methodFilter;
            
            // Date filter
            const paymentDate = new Date(payment.date);
            const matchesDateFrom = !dateFrom || paymentDate >= new Date(dateFrom);
            const matchesDateTo = !dateTo || paymentDate <= new Date(dateTo);
            
            return matchesSearch && matchesMethod && matchesDateFrom && matchesDateTo;
        });
        
        // Re-render payments table
        const tbody = document.getElementById('paymentsTable');
        tbody.innerHTML = '';
        
        if (filtered.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:20px;">No payment transactions found</td></tr>';
        } else {
            filtered.forEach(payment => {
                const row = document.createElement('tr');
                const badge = statusClasses[payment.status] || '';
                row.innerHTML = `
                    <td>${payment.id}</td>
                    <td>${payment.prescriptionId}</td>
                    <td>${payment.patient}</td>
                    <td>GHS ${payment.amount.toFixed(2)}</td>
                    <td>${payment.method}<br><small>${payment.channel}</small></td>
                    <td><span class="status-badge ${badge}">${payment.status}</span></td>
                    <td>${payment.date}</td>
                    <td><button class="view-detail-btn" data-type="payment" data-id="${payment.id}">View</button></td>
                `;
                tbody.appendChild(row);
            });
        }
    }
    
    // Attach event listeners for all filters
    document.getElementById('prescriptionSearch').addEventListener('input', applyPrescriptionFilters);
    document.getElementById('prescriptionStatus').addEventListener('change', applyPrescriptionFilters);
    document.getElementById('prescriptionDateFrom').addEventListener('change', applyPrescriptionFilters);
    document.getElementById('prescriptionDateTo').addEventListener('change', applyPrescriptionFilters);
    
    document.getElementById('userSearch').addEventListener('input', applyUserFilters);
    document.getElementById('userStatus').addEventListener('change', applyUserFilters);
    
    document.getElementById('paymentSearch').addEventListener('input', applyPaymentFilters);
    document.getElementById('paymentMethod').addEventListener('change', applyPaymentFilters);
    document.getElementById('paymentDateFrom').addEventListener('change', applyPaymentFilters);
    document.getElementById('paymentDateTo').addEventListener('change', applyPaymentFilters);

    initializeDashboard();
    </script>
</body>
</html>

