<?php
/**
 * Pharmacy Dashboard
 * Includes core functions for session management
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
    <title>Pharmacy Dashboard - MedLink</title>
    <link rel="stylesheet" href="../css/pharmacy.css">
    <link rel="stylesheet" href="../css/chatbot.css">
</head>
<body class="pharmacy-body">
    <header class="pharmacy-header">
        <div>
            <p class="eyebrow">Pharmacy console</p>
            <h1><?php echo $pharmacyName; ?> Dashboard</h1>
            <p class="subtitle">Viewing prescriptions sent from hospitals</p>
        </div>
        <div class="header-actions">
            <a href="pharmacyhistory.php" class="ghost-btn">View history log</a>
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
                <option>Sent to pharmacies</option>
                <option>Pharmacy reviewing</option>
                <option>Awaiting patient payment</option>
                <option>Payment received</option>
                <option>Ready for pickup</option>
                <option>Ready for delivery</option>
                <option>Dispensed</option>
                <option>On hold</option>
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
            <h2>Incoming prescriptions</h2>
            <p>Prescriptions sent by hospitals and awaiting pharmacy processing</p>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Patient name</th>
                        <th>Patient ID</th>
                        <th>Hospital</th>
                        <th>Date received</th>
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
                <p id="detailDateReceived"></p>
            </div>
            <div>
                <h4>Origin</h4>
                <p id="detailHospital">—</p>
                <p id="detailHospitalNotes"></p>
            </div>
        </div>
        <div class="medicines-pricing-container">
            <h4>Medicines</h4>
            <div id="detailMedicines"></div>
            <div class="total-amount-section">
                <div class="total-amount-label">
                    <span class="total-text">Total</span>
                    <span class="total-amount-value" id="totalAmount">GHS 0.00</span>
                </div>
            </div>
        </div>
        <div class="warning" id="detailWarnings" style="display:none;"></div>
        <div>
            <h4>Pharmacy notes</h4>
            <textarea id="pharmacyNotes" placeholder="Add internal notes here..." rows="3"></textarea>
        </div>
        <div class="action-area">
            <button class="primary" id="startReviewBtn" disabled>Start review</button>
            <button class="primary" id="awaitPaymentBtn" disabled>Awaiting patient payment</button>
            <button class="primary" id="readyPickupBtn" disabled>Mark as ready for pickup</button>
            <button class="primary" id="readyDeliveryBtn" disabled>Mark as out for delivery</button>
            <button class="primary" id="dispensedBtn" disabled>Mark as dispensed</button>
            <button class="ghost" id="onHoldBtn" disabled>Put on hold</button>
        </div>
        <textarea id="holdReason" placeholder="Reason for hold or clarification needed..." style="display:none;" rows="3"></textarea>
        <p class="info-msg" id="infoMessage"></p>
    </section>

    <template id="prescriptionRowTemplate">
        <tr>
            <td class="pres-id"></td>
            <td class="pres-name"></td>
            <td class="pres-patient"></td>
            <td class="pres-hospital"></td>
            <td class="pres-date"></td>
            <td><span class="status-badge"></span></td>
            <td><button class="ghost view-btn">View details</button></td>
        </tr>
    </template>

    <script>
    // Prescription data array (will be loaded from database)
    let pharmacyData = [];
    
    // Load pharmacy prescriptions from database
    async function loadPharmacyPrescriptions(preserveSelection = false) {
        try {
            // Store currently selected prescription ID if preserving selection
            const selectedPrescriptionId = preserveSelection && selectedPrescription ? selectedPrescription.id : null;
            
            const response = await fetch('../actions/get_pharmacy_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                pharmacyData = result.prescriptions;
                renderTable(pharmacyData);
                
                // Re-select the prescription if it was selected before
                if (selectedPrescriptionId) {
                    const updatedPrescription = pharmacyData.find(p => p.id === selectedPrescriptionId);
                    if (updatedPrescription) {
                        selectedPrescription = updatedPrescription;
                        showDetails(updatedPrescription);
                    }
                }
            } else {
                console.error('Error loading prescriptions:', result.message);
                pharmacyData = [];
                renderTable(pharmacyData);
                if (!preserveSelection) {
                    selectedPrescription = null;
                }
            }
            return pharmacyData;
        } catch (error) {
            console.error('Error loading prescriptions:', error);
            pharmacyData = [];
            renderTable(pharmacyData);
            if (!preserveSelection) {
                selectedPrescription = null;
            }
            return [];
        }
    }
    
    // Load prescriptions when page loads
    loadPharmacyPrescriptions().then(() => {
        // Initialize chatbot after data is loaded
        if (typeof chatbot !== 'undefined') {
            chatbot.updateUserData({ prescriptions: pharmacyData });
        }
    });
    
    // Function to update prescription status in database
    async function updatePrescriptionStatus(prescriptionId, status, timelineText = null) {
        try {
            const response = await fetch('../actions/update_prescription_status_pharmacy_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    prescription_id: prescriptionId,
                    status: status,
                    timeline_text: timelineText || status
                })
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                // Reload prescriptions to get updated data, preserving selection
                await loadPharmacyPrescriptions(true);
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
        'Sent to pharmacies': 'badge-purple',
        'Pharmacy reviewing': 'badge-indigo',
        'Awaiting patient payment': 'badge-orange',
        'Payment received': 'badge-green',
        'Ready for pickup': 'badge-green',
        'Ready for delivery': 'badge-teal',
        'Dispensed': 'badge-neutral',
        'On hold': 'badge-amber',
        'Clarification requested': 'badge-warning'
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
            row.querySelector('.pres-hospital').textContent = entry.hospital;
            row.querySelector('.pres-date').textContent = entry.dateReceived;
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
                showDetails(pharmacyData[index]);
            }
        });

        document.getElementById('startReviewBtn').addEventListener('click', async () => {
            if (!selectedPrescription) return;
            const currentPrescriptionId = selectedPrescription.prescription_id;
            const success = await updatePrescriptionStatus(currentPrescriptionId, 'Pharmacy reviewing', 'Pharmacy started reviewing prescription');
            
            if (success) {
                // Find the updated prescription in the reloaded data
                const updatedPrescription = pharmacyData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
                document.getElementById('infoMessage').textContent = 'Review started. Complete review and then move to awaiting payment.';
            }
        });

        document.getElementById('awaitPaymentBtn').addEventListener('click', async () => {
            if (!selectedPrescription) return;
            
            // Collect all medicine prices
            const priceInputs = document.querySelectorAll('.medicine-price-input');
            const medicinePrices = [];
            let allPricesSet = true;
            
            priceInputs.forEach(input => {
                const price = parseFloat(input.value);
                if (!price || price <= 0) {
                    allPricesSet = false;
                }
                medicinePrices.push({
                    medicine_id: input.dataset.medicineId,
                    medicine_name: input.dataset.medicineName,
                    price: price || 0
                });
            });
            
            // Validate that all prices are set
            if (!allPricesSet) {
                alert('Please set prices for all medicines before proceeding to payment.');
                return;
            }
            
            const total = calculateTotal();
            
            if (total <= 0) {
                alert('Total amount must be greater than zero.');
                return;
            }
            
            // Disable button and show loading
            const btn = document.getElementById('awaitPaymentBtn');
            const originalText = btn.textContent;
            btn.disabled = true;
            btn.textContent = 'Processing...';
            
            try {
                const currentPrescriptionId = selectedPrescription.prescription_id;
                
                // Save prices and update status
                const response = await fetch('../actions/update_prescription_status_pharmacy_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        prescription_id: currentPrescriptionId,
                        status: 'Awaiting patient payment',
                        timeline_text: 'Prescription ready for payment',
                        medicine_prices: medicinePrices,
                        total_amount: total
                    })
                });
                
                const result = await response.json();
            
                if (result.status === 'success') {
                    // Reload prescriptions
                    await loadPharmacyPrescriptions(true);
                    
                    // Find the updated prescription
                const updatedPrescription = pharmacyData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
                    
                    document.getElementById('infoMessage').textContent = `Prices saved! Total: GHS ${total.toFixed(2)}. Prescription moved to awaiting payment. Patient will be notified.`;
                } else {
                    alert('Error: ' + (result.message || 'Failed to update prescription.'));
                }
            } catch (error) {
                console.error('Error updating prescription:', error);
                alert('An error occurred while updating the prescription.');
            } finally {
                btn.disabled = false;
                btn.textContent = originalText;
            }
        });

        document.getElementById('readyPickupBtn').addEventListener('click', async () => {
            if (!selectedPrescription) return;
            const currentPrescriptionId = selectedPrescription.prescription_id;
            const success = await updatePrescriptionStatus(currentPrescriptionId, 'Ready for pickup', 'Prescription ready for pickup');
            
            if (success) {
                // Find the updated prescription in the reloaded data
                const updatedPrescription = pharmacyData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
                document.getElementById('infoMessage').textContent = 'Prescription marked as ready for pickup. Patient will be notified.';
            }
        });

        document.getElementById('readyDeliveryBtn').addEventListener('click', async () => {
            if (!selectedPrescription) return;
            const currentPrescriptionId = selectedPrescription.prescription_id;
            const success = await updatePrescriptionStatus(currentPrescriptionId, 'Ready for delivery', 'Prescription out for delivery');
            
            if (success) {
                // Find the updated prescription in the reloaded data
                const updatedPrescription = pharmacyData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
                document.getElementById('infoMessage').textContent = 'Prescription marked as out for delivery.';
            }
        });

        document.getElementById('dispensedBtn').addEventListener('click', async () => {
            if (!selectedPrescription) return;
            const currentPrescriptionId = selectedPrescription.prescription_id;
            const success = await updatePrescriptionStatus(currentPrescriptionId, 'Dispensed', 'Prescription dispensed - order completed');
            
            if (success) {
                // Find the updated prescription in the reloaded data
                const updatedPrescription = pharmacyData.find(p => p.prescription_id === currentPrescriptionId);
                if (updatedPrescription) {
                    selectedPrescription = updatedPrescription;
                    showDetails(updatedPrescription);
                }
                document.getElementById('infoMessage').textContent = 'Prescription marked as dispensed. Order completed.';
            }
        });

        document.getElementById('onHoldBtn').addEventListener('click', () => {
            if (!selectedPrescription) return;
            document.getElementById('holdReason').style.display = 'block';
            
            // Add submit button for hold reason
            const submitHoldBtn = document.getElementById('submitHoldBtn');
            if (!submitHoldBtn) {
                const btn = document.createElement('button');
                btn.id = 'submitHoldBtn';
                btn.type = 'button';
                btn.className = 'primary';
                btn.textContent = 'Put on Hold';
                btn.style.marginTop = '10px';
                btn.addEventListener('click', async () => {
                    const holdReason = document.getElementById('holdReason').value.trim();
                    const currentPrescriptionId = selectedPrescription.prescription_id;
                    const timelineText = holdReason ? `Prescription put on hold: ${holdReason}` : 'Prescription put on hold';
                    
                    const success = await updatePrescriptionStatus(currentPrescriptionId, 'On hold', timelineText);
                    
                    if (success) {
                        // Find the updated prescription in the reloaded data
                        const updatedPrescription = pharmacyData.find(p => p.prescription_id === currentPrescriptionId);
                        if (updatedPrescription) {
                            selectedPrescription = updatedPrescription;
                            showDetails(updatedPrescription);
                        }
                        document.getElementById('infoMessage').textContent = 'Prescription put on hold. Hospital will be notified if clarification is needed.';
                        document.getElementById('holdReason').style.display = 'none';
                        btn.style.display = 'none';
                    }
                });
                document.getElementById('holdReason').parentNode.insertBefore(btn, document.getElementById('holdReason').nextSibling);
            } else {
                submitHoldBtn.style.display = 'block';
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
        document.getElementById('detailDateReceived').textContent = `Received: ${prescription.dateReceived}`;
        document.getElementById('detailHospital').textContent = `${prescription.hospital} (${prescription.hospitalCode})`;
        document.getElementById('detailHospitalNotes').textContent = prescription.hospitalNotes || '—';
        const medList = document.getElementById('detailMedicines');
        medList.innerHTML = '';
        
        // Check if prescription is in review status (can edit prices)
        const canEditPrices = ['Sent to pharmacies', 'Pharmacy reviewing'].includes(prescription.status);
        
        prescription.medicines.forEach((med, index) => {
            // Create medicine card
            const medCard = document.createElement('div');
            medCard.className = `medicine-card ${!canEditPrices && med.price ? 'readonly' : ''}`;
            
            // Medicine header with name and current price
            const medHeader = document.createElement('div');
            medHeader.className = 'medicine-header';
            medHeader.innerHTML = `
                <div class="medicine-name-badge">
                    <h5 class="medicine-name">${med.name}</h5>
                </div>
                ${med.price ? `<div class="medicine-price-display">GHS ${parseFloat(med.price).toFixed(2)}</div>` : ''}
            `;
            medCard.appendChild(medHeader);
            
            // Medicine details (dosage, frequency, duration)
            const medDetails = document.createElement('div');
            medDetails.className = 'medicine-details';
            medDetails.innerHTML = `
                <div class="medicine-detail-item">
                    <div class="label">Dosage</div>
                    <div class="value">${med.dosage}</div>
                </div>
                <div class="medicine-detail-item">
                    <div class="label">Frequency</div>
                    <div class="value">${med.frequency}</div>
                </div>
                <div class="medicine-detail-item">
                    <div class="label">Duration</div>
                    <div class="value">${med.duration}</div>
                </div>
            `;
            medCard.appendChild(medDetails);
            
            // Add price input if in review status
            if (canEditPrices) {
                const priceSection = document.createElement('div');
                priceSection.className = 'price-input-section';
                
                priceSection.innerHTML = `
                    <label class="price-label">Price</label>
                    <div class="price-input-wrapper">
                        <span class="currency-prefix">GHS</span>
                        <input 
                            type="number" 
                            step="0.01" 
                            min="0" 
                            class="medicine-price-input" 
                            data-medicine-id="${med.medicine_id || index}"
                            data-medicine-name="${med.name}"
                            value="${med.price || ''}"
                            placeholder="0.00"
                            ${canEditPrices ? '' : 'disabled'}
                        >
                    </div>
                `;
                
                medCard.appendChild(priceSection);
            }
            
            medList.appendChild(medCard);
        });
        
        // Calculate and display total
        calculateTotal();
        const warnings = document.getElementById('detailWarnings');
        if (prescription.warnings) {
            warnings.style.display = 'block';
            warnings.textContent = prescription.warnings;
        } else {
            warnings.style.display = 'none';
        }
        document.getElementById('holdReason').style.display = 'none';
        document.getElementById('infoMessage').textContent = '';
        const submitHoldBtn = document.getElementById('submitHoldBtn');
        if (submitHoldBtn) {
            submitHoldBtn.style.display = 'none';
        }
        updateDetailStatus();
        updateButtons();
    }

    function updateDetailStatus() {
        if (!selectedPrescription) return;
        const badge = document.getElementById('detailStatus');
        badge.textContent = selectedPrescription.status;
        badge.className = 'status-badge ' + (statusClasses[selectedPrescription.status] || '');
    }

    function calculateTotal() {
        if (!selectedPrescription) return;
        
        let total = 0;
        const priceInputs = document.querySelectorAll('.medicine-price-input');
        
        priceInputs.forEach(input => {
            const price = parseFloat(input.value) || 0;
            total += price;
        });
        
        document.getElementById('totalAmount').textContent = `GHS ${total.toFixed(2)}`;
        return total;
    }
    
    // Add event listener to recalculate when prices change
    document.addEventListener('input', (e) => {
        if (e.target.matches('.medicine-price-input')) {
            calculateTotal();
        }
    });

    function updateButtons() {
        if (!selectedPrescription) {
            document.getElementById('startReviewBtn').disabled = true;
            document.getElementById('awaitPaymentBtn').disabled = true;
            document.getElementById('readyPickupBtn').disabled = true;
            document.getElementById('readyDeliveryBtn').disabled = true;
            document.getElementById('dispensedBtn').disabled = true;
            document.getElementById('onHoldBtn').disabled = true;
            return;
        }
        const status = selectedPrescription.status;
        document.getElementById('startReviewBtn').disabled = status !== 'Sent to pharmacies';
        document.getElementById('awaitPaymentBtn').disabled = status !== 'Pharmacy reviewing';
        document.getElementById('readyPickupBtn').disabled = !['Awaiting patient payment', 'Payment received'].includes(status);
        document.getElementById('readyDeliveryBtn').disabled = !['Awaiting patient payment', 'Payment received'].includes(status);
        document.getElementById('dispensedBtn').disabled = !['Ready for pickup', 'Ready for delivery'].includes(status);
        document.getElementById('onHoldBtn').disabled = ['Dispensed', 'On hold'].includes(status);
    }

    // Add search and filter functionality
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        const dateFrom = document.getElementById('dateFrom').value;
        const dateTo = document.getElementById('dateTo').value;
        
        const filtered = pharmacyData.filter(prescription => {
            // Search filter (patient name or ID)
            const matchesSearch = !searchTerm || 
                prescription.patient.toLowerCase().includes(searchTerm) ||
                prescription.patientId.toLowerCase().includes(searchTerm) ||
                prescription.id.toLowerCase().includes(searchTerm);
            
            // Status filter
            const matchesStatus = !statusFilter || prescription.status === statusFilter;
            
            // Date filter
            const prescriptionDate = new Date(prescription.dateReceived);
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
        // Initialize chatbot with pharmacy context
        document.addEventListener('DOMContentLoaded', () => {
            chatbot = new MedLinkChatbot('pharmacy', {
                prescriptions: pharmacyData
            });
        });
    </script>
</body>
</html>

