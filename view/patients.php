<?php
/**
 * Patient Dashboard
 * Includes core functions for session management
 */
require_once __DIR__ . '/../settings/core.php';

// Redirect if not logged in
if (!is_logged_in() || get_user_type() !== 'patient') {
    header('Location: login.php');
    exit();
}

// Get user name from session
$userName = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Prescriptions - MedLink</title>
    <link rel="stylesheet" href="../css/patients.css">
    <link rel="stylesheet" href="../css/chatbot.css">
</head>
<body class="patient-body">
    <header class="patient-header">
        <div>
            <p class="eyebrow">Patient console</p>
            <h1>My MedLink Prescriptions</h1>
            <p class="subtitle">Manage prescriptions you have submitted to hospitals</p>
        </div>
        <div class="header-actions">
            <p class="logged-in">Logged in as: <strong><?php echo $userName; ?></strong></p>
            <a href="../actions/logout_action.php" class="ghost-btn">Log out</a>
        </div>
    </header>

    <section class="submit-card glass">
        <h2>Submit new prescription</h2>
        <form id="newPrescriptionForm">
            <div class="form-row">
                <div class="form-group">
                    <label for="prescriptionId">Prescription ID</label>
                    <input type="text" id="prescriptionId" required placeholder="RX-2025-001">
                </div>
                <div class="form-group">
                    <label for="hospitalName">Hospital name</label>
                    <select id="hospitalName" required>
                        <option value="">Loading hospitals...</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="doctorName">Doctor name</label>
                    <input type="text" id="doctorName" required placeholder="Dr. Mensah">
                </div>
                <div class="form-group">
                    <label for="visitDate">Visit date</label>
                    <input type="date" id="visitDate" required>
                </div>
            </div>
            <div class="medicines-section">
                <h3>Medicines</h3>
                <div class="medicine-input-row">
                    <input type="text" id="medName" placeholder="Medicine name" required>
                    <input type="text" id="medDosage" placeholder="Dosage" required>
                    <input type="text" id="medFrequency" placeholder="Frequency" required>
                    <input type="text" id="medDuration" placeholder="Duration" required>
                    <button type="button" class="ghost" id="addMedicineBtn">Add</button>
                    <button type="button" class="ghost" id="cancelEditBtn" style="display: none;">Cancel</button>
                </div>
                <ul id="medicinesList"></ul>
            </div>
            <div class="form-group">
                <label for="prescriptionImage">Upload prescription image (optional)</label>
                <input type="file" id="prescriptionImage" accept="image/*,application/pdf">
            </div>
            <button type="submit" class="primary submit-btn">Submit prescription</button>
        </form>
    </section>

    <section class="filters glass">
        <div class="search-group">
            <label for="searchInput">Search</label>
            <input type="text" id="searchInput" placeholder="Hospital or prescription ID">
        </div>
        <div class="filter-group">
            <label for="statusFilter">Status</label>
            <select id="statusFilter">
                <option value="">All</option>
                <option>Submitted by patient</option>
                <option>Hospital reviewing</option>
                <option>Clarification requested</option>
                <option>Sent to pharmacies</option>
                <option>Pharmacy reviewing</option>
                <option>Awaiting patient payment</option>
                <option>Ready for pickup</option>
                <option>Ready for delivery</option>
                <option>Dispensed</option>
                <option>On hold</option>
            </select>
        </div>
    </section>

    <section class="table-card glass">
        <div class="table-heading" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h2>My active prescriptions</h2>
                <p>Prescriptions that are still being processed by hospitals and pharmacies</p>
            </div>
            <button type="button" class="ghost" id="refreshPrescriptionsBtn" style="padding: 10px 20px; margin-top: 0;" title="Refresh to check for updates">
                🔄 Refresh
            </button>
        </div>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Prescription ID</th>
                        <th>Hospital</th>
                        <th>Current status</th>
                        <th>Last updated</th>
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
        <div class="details-content">
            <div>
                <h4>Hospital</h4>
                <p id="detailHospital">—</p>
            </div>
            <div id="pharmacyInfoBlock" style="display:none;">
                <h4>Reviewing Pharmacy</h4>
                <p id="detailPharmacyName">—</p>
                <p id="detailPharmacyLocation" style="color: #666; font-size: 0.9rem; margin-top: 4px;"></p>
                <p id="detailPharmacyContact" style="color: #666; font-size: 0.9rem; margin-top: 4px;"></p>
            </div>
            <div>
                <h4>Medicines</h4>
                <ul id="detailMedicines"></ul>
            </div>
            <div>
                <h4>Dates</h4>
                <p id="detailDates"></p>
            </div>
            <div>
                <h4>Timeline</h4>
                <ul class="timeline" id="detailTimeline"></ul>
            </div>
            <div class="payment-block" id="paymentBlock" style="display:none;">
                <h4>Payment required</h4>
                <p>Your prescription is ready. Please complete payment to proceed with pickup or delivery.</p>
                <div class="payment-amount">
                    <strong>Amount: GHS <span id="paymentAmount">0.00</span></strong>
                </div>
                <button class="primary" id="makePaymentBtn">Make payment</button>
            </div>
            <div class="clarification-block" id="clarificationBlock" style="display:none;">
                <h4>Clarification requested</h4>
                <p id="clarificationMessage"></p>
                <textarea id="clarificationResponse" placeholder="Type your response to the hospital here..." rows="4"></textarea>
                <button class="primary" id="sendResponseBtn">Send response</button>
            </div>
            <div class="completed-message" id="completedMessage" style="display:none;">
                <p>This prescription is complete.</p>
            </div>
        </div>
    </section>

    <template id="prescriptionRowTemplate">
        <tr>
            <td class="pres-id"></td>
            <td class="pres-hospital"></td>
            <td><span class="status-badge"></span></td>
            <td class="pres-updated"></td>
            <td><button class="ghost view-btn">View details</button></td>
        </tr>
    </template>

    <script>
    // Load approved hospitals from database
    async function loadHospitals() {
        try {
            const response = await fetch('../actions/get_hospitals_action.php');
            const result = await response.json();
            
            const hospitalSelect = document.getElementById('hospitalName');
            
            if (result.status === 'success' && result.hospitals && result.hospitals.length > 0) {
                hospitalSelect.innerHTML = '<option value="">Select hospital</option>';
                result.hospitals.forEach(hospital => {
                    const option = document.createElement('option');
                    option.value = hospital.name;
                    option.textContent = hospital.name;
                    option.dataset.hospitalId = hospital.hospital_id;
                    hospitalSelect.appendChild(option);
                });
            } else {
                hospitalSelect.innerHTML = '<option value="">No hospitals available</option>';
            }
        } catch (error) {
            console.error('Error loading hospitals:', error);
            document.getElementById('hospitalName').innerHTML = '<option value="">Error loading hospitals</option>';
        }
    }

    // Load hospitals when page loads
    loadHospitals();

    // Patient prescriptions array (will be loaded from database)
    let patientPrescriptions = [];
    
    // Load patient prescriptions from database
    async function loadPatientPrescriptions(preserveSelection = false) {
        try {
            // Store currently selected prescription ID if preserving selection
            const selectedPrescriptionId = preserveSelection && selectedPrescription ? selectedPrescription.id : null;
            
            const response = await fetch('../actions/get_patient_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                patientPrescriptions = result.prescriptions;
                renderTable(patientPrescriptions);
                
                // Re-select the prescription if it was selected before
                if (selectedPrescriptionId) {
                    const updatedPrescription = patientPrescriptions.find(p => p.id === selectedPrescriptionId);
                    if (updatedPrescription) {
                        selectedPrescription = updatedPrescription;
                        showDetails(updatedPrescription);
                    }
                }
            } else {
                console.error('Error loading prescriptions:', result.message);
                patientPrescriptions = [];
                renderTable(patientPrescriptions);
                if (!preserveSelection) {
                    selectedPrescription = null;
                }
            }
            return patientPrescriptions;
        } catch (error) {
            console.error('Error loading prescriptions:', error);
            patientPrescriptions = [];
            renderTable(patientPrescriptions);
            if (!preserveSelection) {
                selectedPrescription = null;
            }
            return [];
        }
    }
    
    // Load prescriptions when page loads
    loadPatientPrescriptions().then(() => {
        // Initialize chatbot after data is loaded
        if (typeof chatbot !== 'undefined') {
            chatbot.updateUserData({ prescriptions: patientPrescriptions });
        }
    });
    
    // Refresh button handler
    document.getElementById('refreshPrescriptionsBtn').addEventListener('click', async () => {
        const btn = document.getElementById('refreshPrescriptionsBtn');
        const originalText = btn.textContent;
        btn.disabled = true;
        btn.textContent = 'Refreshing...';
        await loadPatientPrescriptions(true); // Preserve selection while refreshing
        btn.disabled = false;
        btn.textContent = originalText;
    });
    
    // Auto-refresh prescriptions every 30 seconds to check for status updates
    setInterval(() => {
        if (document.visibilityState === 'visible') {
            loadPatientPrescriptions(true); // Preserve selection while refreshing
        }
    }, 30000); // Refresh every 30 seconds

    const statusClasses = {
        'Submitted by patient': 'badge-blue',
        'Hospital reviewing': 'badge-slate',
        'Clarification requested': 'badge-warning',
        'Sent to pharmacies': 'badge-purple',
        'Pharmacy reviewing': 'badge-indigo',
        'Awaiting patient payment': 'badge-orange',
        'Payment received': 'badge-green',
        'Ready for pickup': 'badge-green',
        'Ready for delivery': 'badge-teal',
        'Dispensed': 'badge-neutral',
        'On hold': 'badge-amber',
        'Waiting for hospital': 'badge-blue'
    };

    const tableBody = document.getElementById('prescriptionTable');
    const rowTemplate = document.getElementById('prescriptionRowTemplate');
    let selectedPrescription = null;
    let medicinesDraft = [];
    let editingMedicineIndex = null; // Track which medicine is being edited (null = adding new)

    function renderTable(data) {
        tableBody.innerHTML = '';
        data.forEach((entry, index) => {
            const row = rowTemplate.content.cloneNode(true);
            row.querySelector('.pres-id').textContent = entry.id;
            row.querySelector('.pres-hospital').textContent = entry.hospital;
            const badge = row.querySelector('.status-badge');
            badge.textContent = entry.status;
            badge.className = 'status-badge ' + (statusClasses[entry.status] || '');
            row.querySelector('.pres-updated').textContent = entry.lastUpdated;
            row.querySelector('.view-btn').dataset.index = index;
            tableBody.appendChild(row);
        });
    }

    function bindEvents() {
        document.addEventListener('click', (evt) => {
            if (evt.target.matches('.view-btn')) {
                const index = evt.target.dataset.index;
                const prescription = patientPrescriptions[index];
                // Redirect to cart if awaiting payment
                if (prescription.status === 'Awaiting patient payment') {
                    // Store prescription data in sessionStorage to pass to cart
                    sessionStorage.setItem('cartPrescription', JSON.stringify(prescription));
                    window.location.href = 'cart.php';
                } else {
                    showDetails(prescription);
                }
            }
        });

        document.getElementById('addMedicineBtn').addEventListener('click', () => {
            const name = document.getElementById('medName').value.trim();
            const dosage = document.getElementById('medDosage').value.trim();
            const frequency = document.getElementById('medFrequency').value.trim();
            const duration = document.getElementById('medDuration').value.trim();
            
            if (!name || !dosage || !frequency || !duration) {
                alert('Please fill in all medicine fields.');
                return;
            }
            
            const medicine = { name, dosage, frequency, duration };
            
            if (editingMedicineIndex !== null) {
                // Update existing medicine
                medicinesDraft[editingMedicineIndex] = medicine;
            } else {
                // Add new medicine
                medicinesDraft.push(medicine);
            }
            
            updateMedicinesList();
            resetMedicineForm();
        });
        
        // Function to reset medicine form (accessible throughout)
        window.resetMedicineForm = function() {
            document.getElementById('medName').value = '';
            document.getElementById('medDosage').value = '';
            document.getElementById('medFrequency').value = '';
            document.getElementById('medDuration').value = '';
            editingMedicineIndex = null;
            document.getElementById('addMedicineBtn').textContent = 'Add';
            document.getElementById('cancelEditBtn').style.display = 'none';
        };
        
        // Cancel edit button handler
        document.getElementById('cancelEditBtn').addEventListener('click', () => {
            resetMedicineForm();
        });

        document.getElementById('newPrescriptionForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const prescriptionId = document.getElementById('prescriptionId').value.trim();
            const hospitalSelect = document.getElementById('hospitalName');
            const selectedOption = hospitalSelect.options[hospitalSelect.selectedIndex];
            const hospitalId = selectedOption.dataset.hospitalId;
            const hospital = hospitalSelect.value;
            const doctor = document.getElementById('doctorName').value;
            const visitDate = document.getElementById('visitDate').value;
            const prescriptionImage = document.getElementById('prescriptionImage').files[0];
            
            // IMPORTANT: Capture currently-being-entered medicine before submitting
            const currentMedName = document.getElementById('medName').value.trim();
            const currentMedDosage = document.getElementById('medDosage').value.trim();
            const currentMedFrequency = document.getElementById('medFrequency').value.trim();
            const currentMedDuration = document.getElementById('medDuration').value.trim();
            
            // Check if user is currently entering a medicine
            const hasCurrentMedicine = currentMedName || currentMedDosage || currentMedFrequency || currentMedDuration;
            
            if (hasCurrentMedicine) {
                // If ANY field is filled, ALL must be filled
                if (!currentMedName || !currentMedDosage || !currentMedFrequency || !currentMedDuration) {
                    alert('Please complete all fields for the current medicine, or click "Add" before submitting.');
                    return;
                }
                
                // Automatically add the current medicine to the draft
                const currentMedicine = {
                    name: currentMedName,
                    dosage: currentMedDosage,
                    frequency: currentMedFrequency,
                    duration: currentMedDuration
                };
                
                if (editingMedicineIndex !== null) {
                    // Update existing medicine being edited
                    medicinesDraft[editingMedicineIndex] = currentMedicine;
                } else {
                    // Add as new medicine
                    medicinesDraft.push(currentMedicine);
                }
                
                // Update the visual list
                updateMedicinesList();
                // Clear the input fields
                resetMedicineForm();
            }
            
            // Now validate that we have at least one medicine
            if (!prescriptionId || !hospital || !hospitalId || !doctor || !visitDate || medicinesDraft.length === 0) {
                alert('Please fill all required fields and add at least one medicine.');
                return;
            }
            
            // Disable submit button and show loading
            const submitBtn = document.querySelector('.submit-btn');
            const originalBtnText = submitBtn.textContent;
            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';
            
            try {
                // Prepare form data
                const formData = new FormData();
                formData.append('prescription_code', prescriptionId);
                formData.append('hospital_id', hospitalId);
                formData.append('doctor_name', doctor);
                formData.append('visit_date', visitDate);
                formData.append('medicines', JSON.stringify(medicinesDraft));
                
                // Add image if selected
                if (prescriptionImage) {
                    formData.append('prescription_image', prescriptionImage);
                }
                
                // Submit to backend
                const response = await fetch('../actions/submit_prescription_action.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.status === 'success') {
                    medicinesDraft = [];
                    editingMedicineIndex = null;
                    resetMedicineForm();
                    updateMedicinesList(); // Clear the visual list
                    document.getElementById('newPrescriptionForm').reset();
                    document.getElementById('addMedicineBtn').textContent = 'Add';
                    loadHospitals(); // Reload hospitals dropdown
                    
                    // Reload prescriptions from database
                    await loadPatientPrescriptions();
                    
                    alert('Prescription submitted successfully!');
                } else {
                    alert('Error: ' + (result.message || 'Failed to submit prescription. Please try again.'));
                }
            } catch (error) {
                console.error('Error submitting prescription:', error);
                alert('An error occurred while submitting the prescription. Please try again.');
            } finally {
                // Re-enable submit button
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
            }
        });

        document.getElementById('makePaymentBtn').addEventListener('click', () => {
            if (!selectedPrescription) return;
            // Store prescription data in sessionStorage to pass to cart
            sessionStorage.setItem('cartPrescription', JSON.stringify(selectedPrescription));
            window.location.href = 'cart.php';
        });

        document.getElementById('sendResponseBtn').addEventListener('click', async () => {
            if (!selectedPrescription) {
                console.error('No prescription selected');
                return;
            }
            
            const response = document.getElementById('clarificationResponse').value;
            if (!response.trim()) {
                alert('Please enter a response.');
                return;
            }
            
            // Debug logging
            console.log('Selected prescription:', selectedPrescription);
            console.log('Prescription ID:', selectedPrescription.prescription_id);
            console.log('Response:', response);
            
            // Disable button and show loading state
            const sendBtn = document.getElementById('sendResponseBtn');
            const originalBtnText = sendBtn.textContent;
            sendBtn.disabled = true;
            sendBtn.textContent = 'Sending...';
            
            try {
                const requestData = {
                    prescription_id: selectedPrescription.prescription_id,
                    clarification_response: response
                };
                
                console.log('Sending request:', requestData);
                
                // Send clarification response to backend
                const apiResponse = await fetch('../actions/submit_clarification_response_action.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(requestData)
                });
                
                console.log('API Response status:', apiResponse.status);
                
                const result = await apiResponse.json();
                console.log('API Response:', result);
                
                if (result.status === 'success') {
                    // Clear the textarea
            document.getElementById('clarificationResponse').value = '';
                    
                    // Reload prescriptions from database to get updated data
                    await loadPatientPrescriptions(true); // Preserve selection
                    
                    // Show success message
                    alert('Your response has been sent to the hospital successfully!');
                } else {
                    console.error('API Error:', result.message);
                    alert('Error: ' + (result.message || 'Failed to send response. Please try again.'));
                }
            } catch (error) {
                console.error('Error sending clarification response:', error);
                alert('An error occurred while sending your response. Please try again.');
            } finally {
                // Re-enable button
                sendBtn.disabled = false;
                sendBtn.textContent = originalBtnText;
            }
        });
    }

    function updateMedicinesList() {
        const list = document.getElementById('medicinesList');
        list.innerHTML = '';
        medicinesDraft.forEach((med, index) => {
            const li = document.createElement('li');
            const content = document.createElement('span');
            content.className = 'medicine-item-content';
            content.textContent = `${med.name} — ${med.dosage}, ${med.frequency} for ${med.duration}`;
            
            const actions = document.createElement('div');
            actions.className = 'medicine-actions';
            
            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'edit-med';
            editBtn.dataset.index = index;
            editBtn.textContent = 'Edit';
            
            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'remove-med';
            removeBtn.dataset.index = index;
            removeBtn.textContent = 'Remove';
            
            actions.appendChild(editBtn);
            actions.appendChild(removeBtn);
            li.appendChild(content);
            li.appendChild(actions);
            list.appendChild(li);
        });
        
        // Add event listeners for edit buttons
        document.querySelectorAll('.edit-med').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.dataset.index);
                editMedicine(index);
            });
        });
        
        // Add event listeners for remove buttons
        document.querySelectorAll('.remove-med').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const index = parseInt(e.target.dataset.index);
                medicinesDraft.splice(index, 1);
                if (editingMedicineIndex === index) {
                    resetMedicineForm();
                } else if (editingMedicineIndex !== null && editingMedicineIndex > index) {
                    editingMedicineIndex--; // Adjust index if item before the edited one is removed
                }
                updateMedicinesList();
            });
        });
    }
    
    // Function to edit a medicine
    function editMedicine(index) {
        if (index < 0 || index >= medicinesDraft.length) {
            return;
        }
        
        const med = medicinesDraft[index];
        document.getElementById('medName').value = med.name;
        document.getElementById('medDosage').value = med.dosage;
        document.getElementById('medFrequency').value = med.frequency;
        document.getElementById('medDuration').value = med.duration;
        
        editingMedicineIndex = index;
        document.getElementById('addMedicineBtn').textContent = 'Update';
        document.getElementById('cancelEditBtn').style.display = 'inline-block';
        
        // Scroll to medicine inputs
        document.getElementById('medName').scrollIntoView({ behavior: 'smooth', block: 'center' });
        document.getElementById('medName').focus();
    }

    async function showDetails(prescription) {
        // Reload the latest prescription data from database before showing details
        // This ensures we always show the most up-to-date status
        try {
            const response = await fetch('../actions/get_patient_prescriptions_action.php');
            const result = await response.json();
            
            if (result.status === 'success' && result.prescriptions) {
                // Find the updated prescription with matching ID
                const updatedPrescription = result.prescriptions.find(p => p.id === prescription.id);
                if (updatedPrescription) {
                    prescription = updatedPrescription; // Use the fresh data from database
                    // Update the prescription in the array
                    const index = patientPrescriptions.findIndex(p => p.id === prescription.id);
                    if (index !== -1) {
                        patientPrescriptions[index] = updatedPrescription;
                    }
                }
            }
        } catch (error) {
            console.error('Error fetching updated prescription:', error);
            // Continue with the existing prescription data if fetch fails
        }
        
        selectedPrescription = prescription;
        
        // Scroll to details panel
        document.getElementById('detailsPanel').scrollIntoView({ 
            behavior: 'smooth', 
            block: 'start' 
        });
        
        document.getElementById('detailPrescriptionId').textContent = 'Prescription ' + prescription.id;
        document.getElementById('detailHospital').textContent = prescription.hospital;
        
        // Show pharmacy information if a pharmacy is assigned (especially when reviewing)
        const pharmacyInfoBlock = document.getElementById('pharmacyInfoBlock');
        if (prescription.pharmacy && prescription.pharmacy.name) {
            document.getElementById('detailPharmacyName').textContent = prescription.pharmacy.name;
            document.getElementById('detailPharmacyLocation').textContent = prescription.pharmacy.location ? `Location: ${prescription.pharmacy.location}` : '';
            document.getElementById('detailPharmacyContact').textContent = prescription.pharmacy.contact ? `Contact: ${prescription.pharmacy.contact}` : '';
            pharmacyInfoBlock.style.display = 'block';
        } else {
            pharmacyInfoBlock.style.display = 'none';
        }
        
        const medList = document.getElementById('detailMedicines');
        medList.innerHTML = '';
        prescription.medicines.forEach(med => {
            const li = document.createElement('li');
            // Show price if available (set by pharmacy)
            const priceText = med.price !== null && med.price !== undefined 
                ? ` — GHS ${parseFloat(med.price).toFixed(2)}` 
                : '';
            li.textContent = `${med.name} — ${med.dosage}, ${med.frequency} for ${med.duration}${priceText}`;
            medList.appendChild(li);
        });
        document.getElementById('detailDates').textContent = `Submitted: ${prescription.submittedDate} | Last updated: ${prescription.lastUpdated}`;
        const timeline = document.getElementById('detailTimeline');
        timeline.innerHTML = '';
        prescription.timeline.forEach(item => {
            const li = document.createElement('li');
            li.innerHTML = `<strong>${item.time}</strong> — ${item.text}`;
            timeline.appendChild(li);
        });
        const paymentBlock = document.getElementById('paymentBlock');
        const clarificationBlock = document.getElementById('clarificationBlock');
        const completedMessage = document.getElementById('completedMessage');
        
        if (prescription.status === 'Awaiting patient payment') {
            paymentBlock.style.display = 'block';
            clarificationBlock.style.display = 'none';
            completedMessage.style.display = 'none';
            // Calculate real total from medicine prices (or use totalAmount from prescription)
            let amount = 0;
            if (prescription.totalAmount !== null && prescription.totalAmount !== undefined) {
                amount = parseFloat(prescription.totalAmount);
            } else {
                // Calculate from individual medicine prices
                amount = prescription.medicines.reduce((sum, med) => {
                    return sum + (med.price !== null && med.price !== undefined ? parseFloat(med.price) : 0);
                }, 0);
            }
            document.getElementById('paymentAmount').textContent = amount.toFixed(2);
        } else if (prescription.status === 'Clarification requested' && prescription.clarificationMessage) {
            paymentBlock.style.display = 'none';
            clarificationBlock.style.display = 'block';
            completedMessage.style.display = 'none';
            document.getElementById('clarificationMessage').textContent = prescription.clarificationMessage;
        } else {
            paymentBlock.style.display = 'none';
            clarificationBlock.style.display = 'none';
            if (prescription.status === 'Dispensed') {
                completedMessage.style.display = 'block';
            } else {
                completedMessage.style.display = 'none';
            }
        }
        updateDetailStatus();
    }

    function updateDetailStatus() {
        if (!selectedPrescription) return;
        const badge = document.getElementById('detailStatus');
        badge.textContent = selectedPrescription.status;
        badge.className = 'status-badge ' + (statusClasses[selectedPrescription.status] || '');
    }

    // Check if returning from cart with updated prescription
    function checkForUpdatedPrescription() {
        const updatedPrescription = sessionStorage.getItem('updatedPrescription');
        if (updatedPrescription) {
            const updated = JSON.parse(updatedPrescription);
            // Find and update the prescription in the array
            const index = patientPrescriptions.findIndex(p => p.id === updated.id);
            if (index !== -1) {
                patientPrescriptions[index] = updated;
            }
            sessionStorage.removeItem('updatedPrescription');
            renderTable(patientPrescriptions);
        }
    }

    // Add search and filter functionality
    function applyFilters() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value;
        
        const filtered = patientPrescriptions.filter(prescription => {
            // Search filter
            const matchesSearch = !searchTerm || 
                prescription.hospital.toLowerCase().includes(searchTerm) ||
                prescription.id.toLowerCase().includes(searchTerm);
            
            // Status filter
            const matchesStatus = !statusFilter || prescription.status === statusFilter;
            
            return matchesSearch && matchesStatus;
        });
        
        renderTable(filtered);
    }
    
    // Attach event listeners for search and filters
    document.getElementById('searchInput').addEventListener('input', applyFilters);
    document.getElementById('statusFilter').addEventListener('change', applyFilters);

    checkForUpdatedPrescription();
    bindEvents();
    </script>

    <!-- MedLink AI Chatbot -->
    <script src="../js/chatbot.js"></script>
    <script>
        // Initialize chatbot with patient context
        document.addEventListener('DOMContentLoaded', () => {
            chatbot = new MedLinkChatbot('patient', {
                prescriptions: patientPrescriptions
            });
        });
    </script>
</body>
</html>

