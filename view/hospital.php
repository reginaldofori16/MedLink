<?php
/**
 * Hospital Dashboard
 * Includes core functions for session management
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
    <title>Hospital Dashboard - MedLink</title>
    <link rel="stylesheet" href="../css/hospital.css">
    <link rel="stylesheet" href="../css/chatbot.css">
</head>
<body class="hos-body">
    <header class="hos-header">
        <div>
            <p class="eyebrow">Hospital console</p>
            <h1><?php echo $hospitalName; ?> Dashboard</h1>
            <p class="subtitle">Viewing patient submitted prescriptions</p>
        </div>
        <div class="header-actions">
            <a href="hospitalhistory.php" class="ghost-btn">View history log</a>
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
                <option>Submitted by patient</option>
                <option>Hospital reviewing</option>
                <option>Clarification requested</option>
                <option>Waiting for hospital</option>
                <option>Sent to pharmacies</option>
                <option>Pharmacy reviewing</option>
                <option>Awaiting patient payment</option>
                <option>Payment received</option>
                <option>Ready for pickup</option>
                <option>Ready for delivery</option>
                <option>Dispensed</option>
                <option>On hold</option>
                <option>Rejected</option>
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
            <h2>Active prescriptions</h2>
            <p>Prescriptions submitted by patients and awaiting hospital action</p>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient name</th>
                        <th>Patient ID</th>
                        <th>Date submitted</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="prescriptionTable"></tbody>
            </table>
        </div>
    </section>

    <section class="details-section glass" id="detailsPanel">
        <div class="details-header">
            <div>
                <p class="eyebrow">Prescription details</p>
                <h3 id="detailPrescriptionId">Select a prescription</h3>
            </div>
            <span class="status-badge" id="detailStatus">—</span>
        </div>
        <div class="details-columns">
            <div>
                <h4>Patient information</h4>
                <p id="detailPatient">—</p>
                <p id="detailPatientId"></p>
                <p id="detailSubmitted"></p>
            </div>
                <div>
                <h4>Notes</h4>
                <p id="detailNotes">Choose a prescription to view notes.</p>
            </div>
        </div>
        <div>
            <h4>Medicines</h4>
            <ul id="detailMedicines"></ul>
        </div>
        <div class="warning" id="detailWarnings" style="display:none;"></div>
        <div class="clarification-history" id="clarificationHistory" style="display:none; background: #FFF7ED; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin: 16px 0;">
            <h4 style="margin: 0 0 12px 0; color: #92400E;">Clarification Communication</h4>
            <div id="clarificationContent"></div>
        </div>
        <div class="action-area">
            <button class="primary" id="confirmBtn" disabled>Confirm prescription</button>
            <button class="ghost" id="clarifyBtn" disabled>Request clarification</button>
            <button class="ghost" id="rejectBtn" disabled style="background: #FEE2E2; color: #991B1B; border-color: #FCA5A5;">Reject</button>
            <button class="primary" id="transferBtn" disabled>Transfer to pharmacies</button>
        </div>
        <textarea id="clarifyNote" placeholder="Clarification note to patient" style="display:none;"></textarea>
        <p class="info-msg" id="infoMessage"></p>
    </section>

    <template id="prescriptionRowTemplate">
        <tr>
            <td class="pres-id"></td>
            <td class="pres-name"></td>
            <td class="pres-patient"></td>
            <td class="pres-date"></td>
            <td><span class="status-badge"></span></td>
            <td><button class="ghost view-btn">View details</button></td>
        </tr>
    </template>

    <script>
    // Prescription data array (will be loaded from database)
    let prescriptionData = [];
    let hospitalData = []; // For chatbot access
    
    // Load hospital prescriptions from database
    async function loadHospitalPrescriptions(preserveSelection = false) {
        try {
            // Store currently selected prescription ID if preserving selection
            const selectedPrescriptionId = preserveSelection && selectedPrescription ? selectedPrescription.prescription_id : null;
            
            const response = await fetch('../actions/get_hospital_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                prescriptionData = result.prescriptions;
                hospitalData = result.prescriptions; // Make sure hospitalData is also set
                renderTable(prescriptionData);
                
                // Re-select the prescription if it was selected before
                if (selectedPrescriptionId) {
                    const updatedPrescription = prescriptionData.find(p => p.prescription_id === selectedPrescriptionId);
                    if (updatedPrescription) {
                        selectedPrescription = updatedPrescription;
                        showDetails(updatedPrescription);
                    }
                }
            } else {
                console.error('Error loading prescriptions:', result.message);
                prescriptionData = [];
                hospitalData = [];
                renderTable(prescriptionData);
                selectedPrescription = null;
            }
            return prescriptionData;
        } catch (error) {
            console.error('Error loading prescriptions:', error);
            prescriptionData = [];
            hospitalData = [];
            renderTable(prescriptionData);
            selectedPrescription = null;
            return [];
        }
    }
    
    // Load prescriptions when page loads
    loadHospitalPrescriptions().then(() => {
        // Initialize chatbot after data is loaded
        if (typeof chatbot !== 'undefined') {
            chatbot.updateUserData({ prescriptions: hospitalData });
        }
    });
    
    // Auto-refresh prescriptions every 30 seconds to check for updates from pharmacies
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            loadHospitalPrescriptions(true); // Preserve selection while refreshing
        }
    }, 30000); // Refresh every 30 seconds
    
    // Function to update prescription status in database
    async function updatePrescriptionStatus(prescriptionId, status, clarificationMessage = null, timelineText = null) {
        try {
            const response = await fetch('../actions/update_prescription_status_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    prescription_id: prescriptionId,
                    status: status,
                    clarification_message: clarificationMessage,
                    timeline_text: timelineText || status
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                // Reload prescriptions to get updated data, preserving selection
                await loadHospitalPrescriptions(true);
                return true;
            } else {
                alert('Error: ' + (result.message || 'Failed to update prescription status. Please try again.'));
                return false;
            }
        } catch (error) {
            console.error('Error updating prescription status:', error);
            alert('An error occurred while updating prescription status. Please try again.');
            return false;
        }
    }

    const statusClasses = {
        'Submitted by patient': 'badge-blue',
        'Hospital reviewing': 'badge-slate',
        'Clarification requested': 'badge-warning',
        'Waiting for hospital': 'badge-blue',
        'Confirmed by hospital': 'badge-green',
        'Sent to pharmacies': 'badge-purple',
        'Pharmacy reviewing': 'badge-indigo',
        'Awaiting patient payment': 'badge-orange',
        'Payment received': 'badge-green',
        'Ready for pickup': 'badge-green',
        'Ready for delivery': 'badge-teal',
        'Dispensed': 'badge-neutral',
        'On hold': 'badge-amber',
        'Rejected': 'badge-warning'
    };

    const tableBody = document.getElementById('prescriptionTable');
    const rowTemplate = document.getElementById('prescriptionRowTemplate');
    let selectedPrescription = null;

    function renderTable(data) {
        tableBody.innerHTML = '';
        data.forEach((entry, index) => {
            const row = rowTemplate.content.cloneNode(true);
            row.querySelector('.pres-id').textContent = entry.id;
            row.querySelector('.pres-name').textContent = entry.patient;
            row.querySelector('.pres-patient').textContent = entry.patientId;
            row.querySelector('.pres-date').textContent = entry.date;
            const badge = row.querySelector('.status-badge');
            badge.textContent = entry.status;
            badge.className = 'status-badge ' + (statusClasses[entry.status] || '');
            row.querySelector('.view-btn').dataset.index = index;
            tableBody.appendChild(row);
        });
    }

    function bindEvents() {
        document.addEventListener('click', (evt) => {
            if (evt.target.matches('.view-btn')) {
                const index = evt.target.dataset.index;
                showDetails(prescriptionData[index]);
            }
        });

        document.getElementById('confirmBtn').addEventListener('click', async () => {
            if (!selectedPrescription) return;
            const currentPrescriptionId = selectedPrescription.prescription_id;
            const success = await updatePrescriptionStatus(currentPrescriptionId, 'Confirmed by hospital', null, 'Prescription confirmed by hospital');
            
            if (success) {
                // Find the updated prescription in the reloaded data
                const updatedPrescription = prescriptionData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
            document.getElementById('transferBtn').disabled = false;
            document.getElementById('clarifyBtn').disabled = false;
            document.getElementById('infoMessage').textContent = 'Prescription confirmed. You can transfer to pharmacies when ready.';
            }
        });

        document.getElementById('clarifyBtn').addEventListener('click', () => {
            if (!selectedPrescription) return;
            const clarifyNote = document.getElementById('clarifyNote');
            clarifyNote.style.display = 'block';
            clarifyNote.value = ''; // Clear previous message
            
            // Show submit button if not already visible
            const submitBtn = document.getElementById('submitClarificationBtn');
            if (!submitBtn) {
                const btn = document.createElement('button');
                btn.id = 'submitClarificationBtn';
                btn.type = 'button';
                btn.className = 'primary';
                btn.textContent = 'Send Clarification Request';
                btn.style.marginTop = '10px';
                btn.addEventListener('click', async () => {
                    const message = clarifyNote.value.trim();
                    if (!message) {
                        alert('Please enter a clarification message.');
                        return;
                    }
                    
                    const currentPrescriptionId = selectedPrescription.prescription_id;
                    const success = await updatePrescriptionStatus(
                        currentPrescriptionId, 
                        'Clarification requested', 
                        message,
                        'Clarification requested from patient'
                    );
                    
                    if (success) {
                        // Find the updated prescription in the reloaded data
                        const updatedPrescription = prescriptionData.find(p => p.prescription_id === currentPrescriptionId);
                        if (updatedPrescription) {
                            selectedPrescription = updatedPrescription;
                            showDetails(updatedPrescription);
                        }
            document.getElementById('infoMessage').textContent = 'Clarification requested. Patient will be notified to respond.';
                        clarifyNote.style.display = 'none';
                        btn.style.display = 'none';
                    }
                });
                clarifyNote.parentNode.insertBefore(btn, clarifyNote.nextSibling);
            } else {
                submitBtn.style.display = 'block';
            }
        });

        document.getElementById('rejectBtn').addEventListener('click', async () => {
            if (!selectedPrescription) return;
            
            // Confirm rejection
            if (!confirm('Are you sure you want to reject this prescription? This action cannot be undone.')) {
                return;
            }
            
            const currentPrescriptionId = selectedPrescription.prescription_id;
            const success = await updatePrescriptionStatus(currentPrescriptionId, 'Rejected', null, 'Prescription rejected by hospital');
            
            if (success) {
                // Find the updated prescription in the reloaded data
                const updatedPrescription = prescriptionData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
                document.getElementById('infoMessage').textContent = 'Prescription has been rejected. Patient will be notified.';
            }
        });

        document.getElementById('transferBtn').addEventListener('click', async () => {
            if (!selectedPrescription || selectedPrescription.status !== 'Confirmed by hospital') return;
            const currentPrescriptionId = selectedPrescription.prescription_id;
            const success = await updatePrescriptionStatus(currentPrescriptionId, 'Sent to pharmacies', null, 'Prescription sent to pharmacies');
            
            if (success) {
                // Find the updated prescription in the reloaded data
                const updatedPrescription = prescriptionData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
            document.getElementById('infoMessage').textContent = 'This prescription has been transferred to partner pharmacies for review and dispensing.';
            }
        });
    }

    function showDetails(prescription) {
        selectedPrescription = prescription;
        
        // Scroll to details panel
        document.getElementById('detailsPanel').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        document.getElementById('detailPrescriptionId').textContent = prescription.id;
        document.getElementById('detailPatient').textContent = prescription.patient;
        document.getElementById('detailPatientId').textContent = `Patient ID: ${prescription.patientId}`;
        document.getElementById('detailSubmitted').textContent = `Submitted: ${prescription.date}`;
        document.getElementById('detailNotes').textContent = prescription.notes || '—';
        const medList = document.getElementById('detailMedicines');
        medList.innerHTML = '';
        prescription.medicines.forEach(med => {
            const li = document.createElement('li');
            li.textContent = `${med.name} — ${med.dosage}, ${med.frequency} for ${med.duration}`;
            medList.appendChild(li);
        });
        const warnings = document.getElementById('detailWarnings');
        if (prescription.warnings) {
            warnings.style.display = 'block';
            warnings.textContent = prescription.warnings;
        } else {
            warnings.style.display = 'none';
        }
        
        // Display clarification history if exists
        const clarificationHistory = document.getElementById('clarificationHistory');
        const clarificationContent = document.getElementById('clarificationContent');
        
        if (prescription.clarification_message || prescription.clarification_response) {
            clarificationContent.innerHTML = '';
            
            // Show hospital's clarification request
            if (prescription.clarification_message) {
                const requestDiv = document.createElement('div');
                requestDiv.style.marginBottom = '12px';
                requestDiv.innerHTML = `
                    <p style="margin: 0 0 4px 0;"><strong>Your clarification request:</strong></p>
                    <p style="margin: 0; padding: 8px 12px; background: white; border-radius: 6px; color: #1F2937;">${prescription.clarification_message}</p>
                `;
                clarificationContent.appendChild(requestDiv);
            }
            
            // Show patient's response if exists
            if (prescription.clarification_response) {
                const responseDiv = document.createElement('div');
                responseDiv.innerHTML = `
                    <p style="margin: 0 0 4px 0;"><strong>Patient's response:</strong></p>
                    <p style="margin: 0; padding: 8px 12px; background: #D1FAE5; border-radius: 6px; color: #065F46;">${prescription.clarification_response}</p>
                `;
                clarificationContent.appendChild(responseDiv);
            } else if (prescription.status === 'Clarification requested') {
                const waitingDiv = document.createElement('div');
                waitingDiv.innerHTML = `
                    <p style="margin: 0; padding: 8px 12px; background: #FEF3C7; border-radius: 6px; color: #92400E; font-style: italic;">⏳ Waiting for patient's response...</p>
                `;
                clarificationContent.appendChild(waitingDiv);
            }
            
            clarificationHistory.style.display = 'block';
        } else {
            clarificationHistory.style.display = 'none';
        }
        
        document.getElementById('clarifyNote').style.display = 'none';
        document.getElementById('infoMessage').textContent = '';
        const submitClarificationBtn = document.getElementById('submitClarificationBtn');
        if (submitClarificationBtn) {
            submitClarificationBtn.style.display = 'none';
        }
        updateDetailStatus();
        document.getElementById('confirmBtn').disabled = false;
        document.getElementById('clarifyBtn').disabled = false;
        document.getElementById('rejectBtn').disabled = false;
        document.getElementById('transferBtn').disabled = prescription.status !== 'Confirmed by hospital';
        
        // Disable all action buttons if prescription is rejected
        if (prescription.status === 'Rejected') {
            document.getElementById('confirmBtn').disabled = true;
            document.getElementById('clarifyBtn').disabled = true;
            document.getElementById('rejectBtn').disabled = true;
            document.getElementById('transferBtn').disabled = true;
        }
    }

    function updateDetailStatus() {
        if (!selectedPrescription) return;
        const badge = document.getElementById('detailStatus');
        badge.textContent = selectedPrescription.status;
        badge.className = 'status-badge ' + (statusClasses[selectedPrescription.status] || '');
    }

    // Add search and filter functionality
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        const filtered = prescriptionData.filter(prescription => {
            // Search filter (patient name or ID)
            const matchesSearch = !searchTerm || 
                prescription.patient.toLowerCase().includes(searchTerm) ||
                prescription.patientId.toLowerCase().includes(searchTerm) ||
                prescription.id.toLowerCase().includes(searchTerm);
            
            // Status filter
            const matchesStatus = !statusFilter || prescription.status === statusFilter;
            
            // Date filter
            const prescriptionDate = new Date(prescription.date);
            const matchesDateFrom = !dateFrom || prescriptionDate >= new Date(dateFrom);
            const matchesDateTo = !dateTo || prescriptionDate <= new Date(dateTo);
            
            return matchesSearch && matchesStatus && matchesDateFrom && matchesDateTo;
        });
        
        renderTable(filtered);
    }
    
    // Attach event listeners for search and filters
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);
    document.getElementById('dateFrom').addEventListener('change', applyFilters);
    document.getElementById('dateTo').addEventListener('change', applyFilters);

    bindEvents();
    </script>

    <!-- MedLink AI Chatbot -->
    <script src="../js/chatbot.js"></script>
    <script>
        // Initialize chatbot with hospital context
        document.addEventListener('DOMContentLoaded', () => {
            chatbot = new MedLinkChatbot('hospital', {
                prescriptions: hospitalData
            });
        });
    </script>
</body>
</html>