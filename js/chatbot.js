/**
 * MedLink AI Chatbot
 * Context-aware intelligent assistant for patients, hospitals, and pharmacies
 */

class MedLinkChatbot {
    constructor(userType, userData = {}) {
        this.userType = userType; // 'patient', 'hospital', or 'pharmacy'
        this.userData = userData; // User's prescriptions and data
        this.messages = [];
        this.isOpen = false;
        this.init();
    }

    updateUserData(userData) {
        this.userData = userData;
    }

    init() {
        this.injectHTML();
        this.attachEventListeners();
        this.addWelcomeMessage();
    }

    injectHTML() {
        const chatHTML = `
            <button class="chat-button" id="chatButton" aria-label="Open chat">
                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm0 14H6l-2 2V4h16v12z"/>
                    <circle cx="8" cy="10" r="1.5"/>
                    <circle cx="12" cy="10" r="1.5"/>
                    <circle cx="16" cy="10" r="1.5"/>
                </svg>
            </button>

            <div class="chat-window" id="chatWindow">
                <div class="chat-header">
                    <div class="chat-header-content">
                        <div class="chat-avatar">🤖</div>
                        <div class="chat-header-text">
                            <h3>MedLink Assistant</h3>
                            <p>Here to help you!</p>
                        </div>
                    </div>
                    <button class="chat-close" id="chatClose">×</button>
                </div>

                <div class="chat-messages" id="chatMessages">
                    <!-- Messages will appear here -->
                </div>

                <div class="chat-quick-actions" id="quickActions">
                    <!-- Quick action buttons will appear here -->
                </div>

                <div class="chat-input-area">
                    <div class="chat-input-wrapper">
                        <input 
                            type="text" 
                            class="chat-input" 
                            id="chatInput" 
                            placeholder="Ask me anything..."
                            autocomplete="off"
                        >
                        <button class="chat-send-btn" id="chatSend">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', chatHTML);
    }

    attachEventListeners() {
        const chatButton = document.getElementById('chatButton');
        const chatClose = document.getElementById('chatClose');
        const chatSend = document.getElementById('chatSend');
        const chatInput = document.getElementById('chatInput');

        chatButton.addEventListener('click', () => this.toggleChat());
        chatClose.addEventListener('click', () => this.toggleChat());
        chatSend.addEventListener('click', () => this.sendMessage());
        chatInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('chatWindow');
        chatWindow.classList.toggle('open', this.isOpen);
        
        if (this.isOpen) {
            document.getElementById('chatInput').focus();
            this.showQuickActions();
        }
    }

    addWelcomeMessage() {
        const welcomeMessages = {
            patient: "👋 Hi! I'm your MedLink assistant. I can help you check prescription status, payment info, and answer questions about your medicines!",
            hospital: "👋 Hello! I'm here to help you manage prescriptions, check statuses, and guide you through the workflow!",
            pharmacy: "👋 Welcome! I can assist you with prescription processing, pricing, and workflow management!"
        };

        this.addBotMessage(welcomeMessages[this.userType] || "👋 Welcome to MedLink! How can I help you today?");
    }

    showQuickActions() {
        const quickActionsContainer = document.getElementById('quickActions');
        
        const actions = {
            patient: [
                "Check prescription status",
                "How do I pay?",
                "Which pharmacy has my prescription?",
                "What medicines do I need?"
            ],
            hospital: [
                "How to submit prescription?",
                "Check pending prescriptions",
                "Request clarification",
                "Prescription workflow"
            ],
            pharmacy: [
                "How to set prices?",
                "Check payment status",
                "Mark as ready for pickup",
                "Workflow guide"
            ]
        };

        const userActions = actions[this.userType] || actions.patient;
        
        quickActionsContainer.innerHTML = userActions
            .map(action => `<button class="quick-action-btn" onclick="chatbot.handleQuickAction('${action}')">${action}</button>`)
            .join('');
    }

    handleQuickAction(action) {
        this.addUserMessage(action);
        this.processMessage(action);
    }

    sendMessage() {
        const input = document.getElementById('chatInput');
        const message = input.value.trim();
        
        if (!message) return;
        
        this.addUserMessage(message);
        input.value = '';
        
        this.processMessage(message);
    }

    addUserMessage(message) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageHTML = `
            <div class="chat-message user">
                <div class="chat-message-avatar">👤</div>
                <div class="chat-message-content">${this.escapeHtml(message)}</div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        this.scrollToBottom();
    }

    addBotMessage(message) {
        const messagesContainer = document.getElementById('chatMessages');
        const messageHTML = `
            <div class="chat-message bot">
                <div class="chat-message-avatar">🤖</div>
                <div class="chat-message-content">${message}</div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', messageHTML);
        this.scrollToBottom();
    }

    showTypingIndicator() {
        const messagesContainer = document.getElementById('chatMessages');
        const typingHTML = `
            <div class="chat-message bot typing-indicator" id="typingIndicator">
                <div class="chat-message-avatar">🤖</div>
                <div class="chat-message-content">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        messagesContainer.insertAdjacentHTML('beforeend', typingHTML);
        this.scrollToBottom();
    }

    hideTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
    }

    processMessage(message) {
        this.showTypingIndicator();
        
        // Simulate thinking time
        setTimeout(() => {
            this.hideTypingIndicator();
            const response = this.generateResponse(message);
            this.addBotMessage(response);
        }, 800);
    }

    generateResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        // Check if asking about specific prescription ID
        const prescriptionIdMatch = message.match(/[A-Z]{2,3}-\d{3,4}-\d{3}/i) || message.match(/rx-\d+/i);
        if (prescriptionIdMatch && this.userData.prescriptions) {
            const prescriptionId = prescriptionIdMatch[0].toUpperCase();
            const prescription = this.userData.prescriptions.find(p => 
                p.id.toUpperCase() === prescriptionId
            );
            
            if (prescription) {
                return this.getSpecificPrescriptionInfo(prescription);
            } else {
                return `I couldn't find prescription ${prescriptionId} in your records. Here are your prescriptions:<br><br>${this.getPatientPrescriptionStatus()}`;
            }
        }
        
        // Patient responses
        if (this.userType === 'patient') {
            if (lowerMessage.includes('status') || lowerMessage.includes('check') || lowerMessage.includes('prescription')) {
                return this.getPatientPrescriptionStatus();
            }
            if (lowerMessage.includes('pay') || lowerMessage.includes('payment') || lowerMessage.includes('cost') || lowerMessage.includes('price')) {
                // Check for prescriptions awaiting payment
                if (this.userData.prescriptions && this.userData.prescriptions.length > 0) {
                    const awaitingPayment = this.userData.prescriptions.filter(p => 
                        p.status === 'Awaiting patient payment'
                    );
                    
                    if (awaitingPayment.length > 0) {
                        let response = `💳 <strong>Prescriptions Ready for Payment:</strong><br><br>`;
                        awaitingPayment.forEach(p => {
                            response += `<strong>${p.id}</strong><br>`;
                            if (p.totalAmount && parseFloat(p.totalAmount) > 0) {
                                response += `Amount: <strong>GHS ${parseFloat(p.totalAmount).toFixed(2)}</strong><br>`;
                            }
                            response += `<em>Click "Make Payment" on this prescription to pay!</em><br><br>`;
                        });
                        return response;
                    }
                }
                
                return `💳 <strong>Making Payment:</strong><br><br>
                1. Wait for pharmacy to set prices (status: "Awaiting patient payment")<br>
                2. Click "Make Payment" or "View details" on your prescription<br>
                3. Complete payment via Paystack<br>
                4. You'll see a confirmation page with confetti! 🎉<br><br>
                Your medicine will be ready for pickup after payment.`;
            }
            if (lowerMessage.includes('pharmacy') || lowerMessage.includes('where')) {
                return this.getPatientPharmacyInfo();
            }
            if (lowerMessage.includes('medicine') || lowerMessage.includes('drug')) {
                return this.getPatientMedicineInfo();
            }
            if (lowerMessage.includes('how long') || lowerMessage.includes('when')) {
                return `⏰ <strong>Timeline:</strong><br><br>
                • Hospital review: 1-2 hours<br>
                • Pharmacy pricing: 2-4 hours<br>
                • After payment: Ready for pickup within 24 hours<br><br>
                You'll see status updates in real-time on your dashboard!`;
            }
        }
        
        // Hospital responses
        if (this.userType === 'hospital') {
            if (lowerMessage.includes('submit') || lowerMessage.includes('how to')) {
                return `📋 <strong>Submitting a Prescription:</strong><br><br>
                1. Enter prescription ID and select hospital<br>
                2. Fill in doctor name and visit date<br>
                3. Add medicines with dosage, frequency, and duration<br>
                4. Upload prescription image (optional)<br>
                5. Click "Submit prescription"<br><br>
                The prescription will be sent to pharmacies for review!`;
            }
            if (lowerMessage.includes('pending') || lowerMessage.includes('check')) {
                return `You can view all pending prescriptions in your dashboard table. Use the filters to find specific prescriptions by status or date!`;
            }
            if (lowerMessage.includes('clarification') || lowerMessage.includes('request')) {
                return `📝 <strong>Requesting Clarification:</strong><br><br>
                1. View prescription details<br>
                2. Click "Request clarification"<br>
                3. Enter your question or concern<br>
                4. Patient will be notified immediately<br><br>
                You'll see their response in the timeline!`;
            }
            if (lowerMessage.includes('workflow') || lowerMessage.includes('process')) {
                return `📊 <strong>Hospital Workflow:</strong><br><br>
                1. Submit prescription → "Submitted by patient"<br>
                2. Review → "Hospital reviewing"<br>
                3. Send to pharmacies → "Sent to pharmacies"<br>
                4. Pharmacy processes → "Pharmacy reviewing"<br>
                5. Ready for payment → "Awaiting patient payment"<br>
                6. Completed → "Dispensed"`;
            }
        }
        
        // Pharmacy responses
        if (this.userType === 'pharmacy') {
            if (lowerMessage.includes('price') || lowerMessage.includes('pricing')) {
                return `💰 <strong>Setting Medicine Prices:</strong><br><br>
                1. Click "View details" on a prescription<br>
                2. Click "Start review" (if not started)<br>
                3. Enter prices for each medicine in GHS<br>
                4. Review the total amount<br>
                5. Click "Awaiting patient payment"<br><br>
                Patient will be notified to make payment!`;
            }
            if (lowerMessage.includes('payment') || lowerMessage.includes('received')) {
                return `After payment is received, the status changes to "Payment received". You can then mark it as "Ready for pickup" or "Ready for delivery"!`;
            }
            if (lowerMessage.includes('pickup') || lowerMessage.includes('ready')) {
                return `✅ <strong>Marking Ready for Pickup:</strong><br><br>
                1. Wait for payment to be received<br>
                2. Prepare the medicines<br>
                3. Click "Mark as ready for pickup"<br>
                4. Patient will be notified<br><br>
                When patient collects, mark as "Dispensed"!`;
            }
            if (lowerMessage.includes('workflow') || lowerMessage.includes('process')) {
                return `📊 <strong>Pharmacy Workflow:</strong><br><br>
                1. Receive → "Sent to pharmacies"<br>
                2. Start review → "Pharmacy reviewing"<br>
                3. Set prices → "Awaiting patient payment"<br>
                4. Payment received → "Payment received"<br>
                5. Prepare medicines → "Ready for pickup/delivery"<br>
                6. Complete → "Dispensed"`;
            }
        }
        
        // General responses
        if (lowerMessage.includes('help') || lowerMessage.includes('what can you')) {
            return `I can help you with:<br><br>
            ✅ Check prescription statuses<br>
            ✅ Explain the workflow<br>
            ✅ Answer questions about payments<br>
            ✅ Guide you through the process<br><br>
            Try using the quick action buttons or just ask me anything!`;
        }
        
        if (lowerMessage.includes('thank')) {
            return `You're welcome! 😊 Feel free to ask if you have more questions!`;
        }
        
        // Default response
        return `I'm here to help! Could you rephrase that? Or try one of the quick action buttons below. You can ask about:<br><br>
        • Prescription status<br>
        • Payment process<br>
        • Workflow steps<br>
        • Any questions about MedLink!`;
    }

    getPatientPrescriptionStatus() {
        if (this.userData.prescriptions && this.userData.prescriptions.length > 0) {
            let response = `📋 <strong>Your Prescriptions:</strong><br><br>`;
            
            // Show up to 3 most recent prescriptions
            const recentPrescriptions = this.userData.prescriptions.slice(0, 3);
            
            recentPrescriptions.forEach((prescription, index) => {
                response += `<strong>${index + 1}. ${prescription.id}</strong><br>`;
                response += `Hospital: ${prescription.hospital}<br>`;
                response += `Status: <strong>${prescription.status}</strong><br>`;
                response += `Updated: ${prescription.lastUpdated}<br>`;
                
                // Add action hint based on status
                if (prescription.status === 'Awaiting patient payment') {
                    response += `💡 <em>Click "Make Payment" to proceed!</em><br>`;
                } else if (prescription.status === 'Ready for pickup') {
                    response += `🎉 <em>Ready! Go pick up your medicines!</em><br>`;
                }
                
                response += `<br>`;
            });
            
            if (this.userData.prescriptions.length > 3) {
                response += `<em>And ${this.userData.prescriptions.length - 3} more prescriptions...</em>`;
            }
            
            return response;
        }
        return `📋 You don't have any prescriptions yet.<br><br>To get started:<br>1. Fill in the prescription form above<br>2. Add your medicines<br>3. Click "Submit prescription"`;
    }

    getSpecificPrescriptionInfo(prescription) {
        let response = `📋 <strong>Prescription ${prescription.id}</strong><br><br>`;
        response += `<strong>Hospital:</strong> ${prescription.hospital}<br>`;
        response += `<strong>Status:</strong> ${prescription.status}<br>`;
        response += `<strong>Last Updated:</strong> ${prescription.lastUpdated}<br>`;
        
        if (prescription.pharmacy && prescription.pharmacy.name) {
            response += `<strong>Pharmacy:</strong> ${prescription.pharmacy.name}<br>`;
        }
        
        if (prescription.medicines && prescription.medicines.length > 0) {
            response += `<br><strong>Medicines (${prescription.medicines.length}):</strong><br>`;
            prescription.medicines.forEach((med, i) => {
                response += `${i + 1}. ${med.name} - ${med.dosage}<br>`;
            });
        }
        
        if (prescription.totalAmount && parseFloat(prescription.totalAmount) > 0) {
            response += `<br><strong>Total Amount:</strong> GHS ${parseFloat(prescription.totalAmount).toFixed(2)}<br>`;
        }
        
        response += `<br>${this.getStatusHelp(prescription.status)}`;
        
        return response;
    }

    getPatientPharmacyInfo() {
        if (this.userData.prescriptions && this.userData.prescriptions.length > 0) {
            const latest = this.userData.prescriptions[0];
            if (latest.pharmacy && latest.pharmacy.name) {
                return `🏥 <strong>Pharmacy for ${latest.id}:</strong><br><br>
                <strong>Name:</strong> ${latest.pharmacy.name}<br>
                ${latest.pharmacy.location ? `<strong>Location:</strong> ${latest.pharmacy.location}<br>` : ''}
                ${latest.pharmacy.contact ? `<strong>Contact:</strong> ${latest.pharmacy.contact}<br>` : ''}`;
            }
        }
        return `Your prescription hasn't been assigned to a pharmacy yet. It will be assigned once the hospital completes the review!`;
    }

    getPatientMedicineInfo() {
        if (this.userData.prescriptions && this.userData.prescriptions.length > 0) {
            const latest = this.userData.prescriptions[0];
            if (latest.medicines && latest.medicines.length > 0) {
                let response = `💊 <strong>Medicines in ${latest.id}:</strong><br><br>`;
                
                latest.medicines.forEach((med, index) => {
                    response += `${index + 1}. <strong>${med.name}</strong><br>`;
                    response += `   Dosage: ${med.dosage}<br>`;
                    response += `   Frequency: ${med.frequency}<br>`;
                    response += `   Duration: ${med.duration}<br>`;
                    
                    if (med.price && parseFloat(med.price) > 0) {
                        response += `   Price: <strong>GHS ${parseFloat(med.price).toFixed(2)}</strong><br>`;
                    }
                    response += `<br>`;
                });
                
                return response;
            } else {
                return `💊 No medicines listed for ${latest.id} yet. The hospital is still reviewing your prescription.`;
            }
        }
        return `No prescriptions available. Submit a prescription to get started!`;
    }

    getStatusHelp(status) {
        const statusHelp = {
            'Submitted by patient': '⏳ Your prescription is waiting for hospital review.',
            'Hospital reviewing': '🏥 The hospital is reviewing your prescription.',
            'Sent to pharmacies': '📤 Your prescription has been sent to pharmacies.',
            'Pharmacy reviewing': '💊 A pharmacy is reviewing and pricing your medicines.',
            'Awaiting patient payment': '💳 Ready for payment! Click "Make Payment" to proceed.',
            'Payment received': '✅ Payment confirmed! Pharmacy is preparing your medicines.',
            'Ready for pickup': '🎉 Your medicines are ready! You can pick them up now.',
            'Ready for delivery': '🚚 Your medicines are out for delivery!',
            'Dispensed': '✅ Order complete! Thank you for using MedLink.',
        };
        return statusHelp[status] || '';
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatMessages');
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Global chatbot instance
let chatbot;

