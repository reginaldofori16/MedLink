/**
 * Login.js
 * Validates login form using regex
 * Asynchronously invokes the login_customer_action script
 */

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('login-form');
    const roleRadios = document.querySelectorAll('input[name="loginrole"]');
    
    if (!loginForm) {
        console.error('Login form not found!');
        return;
    }
    
    // Validation regex patterns
    const patterns = {
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        governmentId: /^[a-zA-Z0-9\-]{3,100}$/ // For hospital/pharmacy IDs
    };
    
    // Error messages
    const errorMessages = {
        email: 'Please enter a valid email address.',
        governmentId: 'Government ID must be 3-100 characters (letters, numbers, hyphens only).',
        password: 'Password is required.',
        role: 'Please select a user type.'
    };
    
    /**
     * Show error message
     */
    function showError(field, message) {
        if (!field) return;
        
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.color = '#DC2626';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '4px';
        errorDiv.style.marginBottom = '8px';
        
        // Remove existing error for this field
        const existingError = field.parentNode.querySelector('.error-message');
        if (existingError) {
            existingError.remove();
        }
        
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    
    /**
     * Remove all errors from form
     */
    function clearErrors() {
        const errorMessages = loginForm.querySelectorAll('.error-message');
        errorMessages.forEach(msg => msg.remove());
        
        const errorFields = loginForm.querySelectorAll('.error');
        errorFields.forEach(field => field.classList.remove('error'));
    }
    
    /**
     * Show loading state
     */
    function showLoading(button, originalText) {
        if (button) {
            button.disabled = true;
            button.dataset.originalText = originalText;
            button.innerHTML = '<span class="loading-spinner"></span> Logging in...';
            button.style.opacity = '0.7';
            button.style.cursor = 'not-allowed';
        }
    }
    
    /**
     * Hide loading state
     */
    function hideLoading(button) {
        if (button) {
            button.disabled = false;
            button.innerHTML = button.dataset.originalText || 'Login';
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
        }
    }
    
    /**
     * Show success/error message
     */
    function showMessage(message, isSuccess) {
        const msgBox = document.getElementById('login-msg');
        if (msgBox) {
            msgBox.innerHTML = `<b>${message}</b>`;
            msgBox.style.display = 'block';
            if (isSuccess) {
                msgBox.style.background = 'linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%)';
                msgBox.style.color = '#065F46';
            } else {
                msgBox.style.background = 'linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%)';
                msgBox.style.color = '#991B1B';
            }
        }
    }
    
    /**
     * Validate login form
     */
    function validateLoginForm() {
        clearErrors();
        
        // Get selected role
        let selectedRole = null;
        roleRadios.forEach(radio => {
            if (radio.checked) {
                selectedRole = radio.value;
            }
        });
        
        if (!selectedRole) {
            showMessage('Please select a user type.', false);
            return false;
        }
        
        const usernameField = loginForm.querySelector('[name="username"]');
        const passwordField = loginForm.querySelector('[name="password"]');
        
        if (!usernameField || !passwordField) {
            console.error('Form fields not found');
            return false;
        }
        
        const username = usernameField.value.trim();
        const password = passwordField.value;
        
        let isValid = true;
        
        // Validate username based on role
        if (!username) {
            if (selectedRole === 'individual') {
                showError(usernameField, 'Email is required.');
            } else {
                showError(usernameField, 'Government ID is required.');
            }
            isValid = false;
        } else if (selectedRole === 'individual') {
            // Validate email format for individuals
            if (!patterns.email.test(username)) {
                showError(usernameField, errorMessages.email);
                isValid = false;
            }
        } else {
            // Validate government ID format for hospital/pharmacy
            if (!patterns.governmentId.test(username)) {
                showError(usernameField, errorMessages.governmentId);
                isValid = false;
            }
        }
        
        // Validate password
        if (!password) {
            showError(passwordField, errorMessages.password);
            isValid = false;
        } else if (password.length < 1) {
            showError(passwordField, errorMessages.password);
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Handle form submission
     */
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        console.log('Login form submitted');
        
        // Validate form
        if (!validateLoginForm()) {
            console.log('Login form validation failed');
            return;
        }
        
        // Get selected role
        let selectedRole = null;
        roleRadios.forEach(radio => {
            if (radio.checked) {
                selectedRole = radio.value;
            }
        });
        
        const usernameField = loginForm.querySelector('[name="username"]');
        const passwordField = loginForm.querySelector('[name="password"]');
        
        let formData = {};
        let actionUrl = '';
        
        // Prepare form data and action URL based on user type
        if (selectedRole === 'individual') {
            formData = {
                email: usernameField.value.trim().toLowerCase(),
                password: passwordField.value
            };
            actionUrl = '../actions/login_customer_action.php';
        } else if (selectedRole === 'hospital') {
            formData = {
                government_id: usernameField.value.trim(),
                password: passwordField.value
            };
            actionUrl = '../actions/login_hospital_action.php';
        } else if (selectedRole === 'pharmacy') {
            formData = {
                government_id: usernameField.value.trim(),
                password: passwordField.value
            };
            actionUrl = '../actions/login_pharmacy_action.php';
        }
        
        const submitBtn = loginForm.querySelector('button[type="submit"]');
        showLoading(submitBtn, 'Login');
        
        try {
            const response = await fetch(actionUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            hideLoading(submitBtn);
            
            if (result.status === 'success') {
                showMessage(result.message + ' Redirecting...', true);
                // Redirect to appropriate dashboard based on user type
                const redirectUrl = result.redirect_url || '../index.php';
                console.log('Login successful! Redirecting to:', redirectUrl);
                console.log('Full redirect URL will be:', window.location.origin + window.location.pathname.replace('login.php', '') + redirectUrl);
                
                // Redirect immediately (reduced timeout for better UX)
                setTimeout(() => {
                    console.log('Executing redirect to:', redirectUrl);
                    window.location.href = redirectUrl;
                }, 1000);
            } else {
                console.error('Login failed:', result.message);
                showMessage('Error: ' + result.message, false);
            }
        } catch (error) {
            console.error('Login error:', error);
            hideLoading(submitBtn);
            showMessage('Network error. Please check your connection and try again.', false);
        }
    });
});

