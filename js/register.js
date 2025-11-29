/**
 * Register.js
 * Validates registration forms using regex
 * Asynchronously invokes the appropriate registration action scripts
 */

document.addEventListener('DOMContentLoaded', function() {
    const individualForm = document.getElementById('individual-form');
    const hospitalForm = document.getElementById('hospital-form');
    const pharmacyForm = document.getElementById('pharmacy-form');
    
    // Debug: Check if forms exist
    if (!individualForm) {
        console.error('Individual form not found!');
    }
    if (!hospitalForm) {
        console.error('Hospital form not found!');
    }
    if (!pharmacyForm) {
        console.error('Pharmacy form not found!');
    }
    
    // Validation regex patterns (made more lenient)
    const patterns = {
        fullName: /^[a-zA-Z\s'\-\.]{2,255}$/, // Allow dots, more flexible
        name: /^[a-zA-Z0-9\s'\-\.]{2,255}$/, // For hospital/pharmacy names
        email: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
        phone: /^(\+233|0)[0-9]{9,10}$/, // Allow 9-10 digits
        password: /^.{8,}$/, // Just check minimum length - we'll validate requirements separately
        country: /^[a-zA-Z\s'\-\.]{2,100}$/, // Allow dots
        city: /^[a-zA-Z\s'\-\.]{2,100}$/, // Allow dots
        location: /^[a-zA-Z0-9\s,'\-\.]{2,255}$/,
        governmentId: /^[a-zA-Z0-9\-]{3,100}$/,
        contact: /^[a-zA-Z0-9@.\+\s\-]{3,255}$/ // Phone or email
    };
    
    // Error messages
    const errorMessages = {
        fullName: 'Full name must be 2-255 characters (letters, spaces, hyphens, apostrophes, and dots allowed).',
        name: 'Name must be 2-255 characters.',
        email: 'Please enter a valid email address.',
        phone: 'Phone number must be in Ghana format: +233XXXXXXXXX or 0XXXXXXXXX (9-10 digits).',
        password: 'Password must be at least 8 characters with: uppercase letter, lowercase letter, number, and special character.',
        passwordMatch: 'Passwords do not match.',
        country: 'Country name must be 2-100 characters (letters, spaces, hyphens, apostrophes, and dots allowed).',
        city: 'City name must be 2-100 characters (letters, spaces, hyphens, apostrophes, and dots allowed).',
        location: 'Location must be 2-255 characters.',
        governmentId: 'Government ID must be 3-100 characters (letters, numbers, hyphens only).',
        contact: 'Contact information must be 3-255 characters (phone or email).'
    };
    
    /**
     * Show error message
     */
    function showError(field, message, form) {
        if (!field) return;
        
        field.classList.add('error');
        const errorDiv = document.createElement('div');
        errorDiv.id = `${field.name}-error`;
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        errorDiv.style.color = '#DC2626';
        errorDiv.style.fontSize = '0.875rem';
        errorDiv.style.marginTop = '4px';
        errorDiv.style.marginBottom = '8px';
        
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }
    
    /**
     * Remove all errors from a form
     */
    function clearErrors(form) {
        const errorMessages = form.querySelectorAll('.error-message');
        errorMessages.forEach(msg => msg.remove());
        
        const errorFields = form.querySelectorAll('.error');
        errorFields.forEach(field => field.classList.remove('error'));
    }
    
    /**
     * Show loading state
     */
    function showLoading(button, originalText) {
        if (button) {
            button.disabled = true;
            button.dataset.originalText = originalText;
            button.innerHTML = '<span class="loading-spinner"></span> Registering...';
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
            button.innerHTML = button.dataset.originalText || 'Register';
            button.style.opacity = '1';
            button.style.cursor = 'pointer';
        }
    }
    
    /**
     * Show success/error message
     */
    function showMessage(message, isSuccess) {
        const thankyouBox = document.getElementById('thankyou-msg');
        if (thankyouBox) {
            thankyouBox.innerHTML = `<b>${message}</b>`;
            thankyouBox.style.display = 'block';
            if (isSuccess) {
                thankyouBox.style.background = 'linear-gradient(135deg, #D1FAE5 0%, #A7F3D0 100%)';
                thankyouBox.style.color = '#065F46';
            } else {
                thankyouBox.style.background = 'linear-gradient(135deg, #FEE2E2 0%, #FECACA 100%)';
                thankyouBox.style.color = '#991B1B';
            }
        }
    }
    
    /**
     * Validate individual form
     */
    function validateIndividualForm() {
        if (!individualForm) {
            console.error('Individual form not found for validation');
            return false;
        }
        
        clearErrors(individualForm);
        
        const fullNameField = individualForm.querySelector('[name="full_name"]');
        const emailField = individualForm.querySelector('[name="email"]');
        const phoneField = individualForm.querySelector('[name="phone"]');
        const password1Field = individualForm.querySelector('[name="password1"]');
        const password2Field = individualForm.querySelector('[name="password2"]');
        const countryField = individualForm.querySelector('[name="country"]');
        const cityField = individualForm.querySelector('[name="city"]');
        
        if (!fullNameField || !emailField || !phoneField || !password1Field || !password2Field || !countryField || !cityField) {
            console.error('One or more form fields not found');
            return false;
        }
        
        const formData = {
            full_name: fullNameField.value.trim(),
            email: emailField.value.trim().toLowerCase(),
            phone: phoneField.value.trim().replace(/\s+/g, ''),
            password: password1Field.value,
            password2: password2Field.value,
            country: countryField.value.trim(),
            city: cityField.value.trim()
        };
        
        let isValid = true;
        
        // Validate each field with detailed logging
        console.log('=== Starting Validation ===');
        console.log('Form data:', formData);
        
        // Full name validation (more lenient - just check length and basic characters)
        if (!formData.full_name || formData.full_name.length < 2) {
            console.log('❌ Full name validation failed: Too short or empty');
            showError(fullNameField, 'Full name must be at least 2 characters long.', individualForm);
            isValid = false;
        } else if (!patterns.fullName.test(formData.full_name)) {
            console.log('❌ Full name validation failed: Invalid characters in:', formData.full_name);
            showError(fullNameField, errorMessages.fullName, individualForm);
            isValid = false;
        } else {
            console.log('✅ Full name valid');
        }
        
        // Email validation
        if (!formData.email || !patterns.email.test(formData.email)) {
            console.log('❌ Email validation failed:', formData.email);
            showError(emailField, errorMessages.email, individualForm);
            isValid = false;
        } else {
            console.log('✅ Email valid');
        }
        
        // Phone validation
        if (!formData.phone || !patterns.phone.test(formData.phone)) {
            console.log('❌ Phone validation failed:', formData.phone);
            console.log('   Expected format: +233XXXXXXXXX or 0XXXXXXXXX (9-10 digits)');
            showError(phoneField, errorMessages.phone, individualForm);
            isValid = false;
        } else {
            console.log('✅ Phone valid');
        }
        
        // Password validation (with detailed breakdown - more lenient)
        if (!formData.password || formData.password.length < 8) {
            console.log('❌ Password validation failed: Too short (length:', formData.password.length, ')');
            showError(password1Field, 'Password must be at least 8 characters long.', individualForm);
            isValid = false;
        } else {
            // Check for required character types (more lenient - allow any special characters)
            const hasLower = /[a-z]/.test(formData.password);
            const hasUpper = /[A-Z]/.test(formData.password);
            const hasNumber = /\d/.test(formData.password);
            // Allow any special character, not just specific ones
            const hasSpecial = /[^a-zA-Z0-9]/.test(formData.password);
            
            console.log('Password check:', {
                length: formData.password.length,
                hasLowercase: hasLower,
                hasUppercase: hasUpper,
                hasNumber: hasNumber,
                hasSpecial: hasSpecial,
                passwordPreview: formData.password.substring(0, 2) + '***' // Show first 2 chars for debugging
            });
            
            if (!hasLower) {
                console.log('❌ Password missing: lowercase letter');
                showError(password1Field, 'Password must contain at least one lowercase letter (a-z).', individualForm);
                isValid = false;
            } else if (!hasUpper) {
                console.log('❌ Password missing: uppercase letter');
                showError(password1Field, 'Password must contain at least one uppercase letter (A-Z).', individualForm);
                isValid = false;
            } else if (!hasNumber) {
                console.log('❌ Password missing: number');
                showError(password1Field, 'Password must contain at least one number (0-9).', individualForm);
                isValid = false;
            } else if (!hasSpecial) {
                console.log('❌ Password missing: special character');
                showError(password1Field, 'Password must contain at least one special character (!@#$%^&* etc.).', individualForm);
                isValid = false;
            } else {
                console.log('✅ Password valid - all requirements met');
            }
        }
        
        // Password match validation
        if (formData.password !== formData.password2) {
            console.log('❌ Password match validation failed');
            showError(password2Field, errorMessages.passwordMatch, individualForm);
            isValid = false;
        } else {
            console.log('✅ Passwords match');
        }
        
        // Country validation
        if (!formData.country || formData.country.length < 2) {
            console.log('❌ Country validation failed: Too short or empty');
            showError(countryField, 'Country name must be at least 2 characters long.', individualForm);
            isValid = false;
        } else if (!patterns.country.test(formData.country)) {
            console.log('❌ Country validation failed:', formData.country);
            showError(countryField, errorMessages.country, individualForm);
            isValid = false;
        } else {
            console.log('✅ Country valid');
        }
        
        // City validation
        if (!formData.city || formData.city.length < 2) {
            console.log('❌ City validation failed: Too short or empty');
            showError(cityField, 'City name must be at least 2 characters long.', individualForm);
            isValid = false;
        } else if (!patterns.city.test(formData.city)) {
            console.log('❌ City validation failed:', formData.city);
            showError(cityField, errorMessages.city, individualForm);
            isValid = false;
        } else {
            console.log('✅ City valid');
        }
        
        if (isValid) {
            console.log('✅✅✅ All validations passed! Form can be submitted.');
        } else {
            console.log('❌❌❌ Validation failed. Please fix the errors above.');
        }
        console.log('=== End Validation ===');
        
        return isValid;
    }
    
    /**
     * Validate hospital form
     */
    function validateHospitalForm() {
        clearErrors(hospitalForm);
        
        const formData = {
            name: hospitalForm.querySelector('[name="hospital_name"]').value.trim(),
            government_id: hospitalForm.querySelector('[name="hospital_id"]').value.trim(),
            contact: hospitalForm.querySelector('[name="hospital_contact"]').value.trim(),
            password: hospitalForm.querySelector('[name="hospital_password1"]').value,
            password2: hospitalForm.querySelector('[name="hospital_password2"]').value
        };
        
        let isValid = true;
        
        if (!formData.name || !patterns.name.test(formData.name)) {
            showError(hospitalForm.querySelector('[name="hospital_name"]'), errorMessages.name, hospitalForm);
            isValid = false;
        }
        
        if (!formData.government_id || !patterns.governmentId.test(formData.government_id)) {
            showError(hospitalForm.querySelector('[name="hospital_id"]'), errorMessages.governmentId, hospitalForm);
            isValid = false;
        }
        
        if (!formData.contact || !patterns.contact.test(formData.contact)) {
            showError(hospitalForm.querySelector('[name="hospital_contact"]'), errorMessages.contact, hospitalForm);
            isValid = false;
        }
        
        if (!formData.password || formData.password.length < 8) {
            showError(hospitalForm.querySelector('[name="hospital_password1"]'), 'Password must be at least 8 characters long.', hospitalForm);
            isValid = false;
        }
        
        if (formData.password !== formData.password2) {
            showError(hospitalForm.querySelector('[name="hospital_password2"]'), errorMessages.passwordMatch, hospitalForm);
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Validate pharmacy form
     */
    function validatePharmacyForm() {
        clearErrors(pharmacyForm);
        
        const formData = {
            name: pharmacyForm.querySelector('[name="pharmacy_name"]').value.trim(),
            government_id: pharmacyForm.querySelector('[name="pharmacy_id"]').value.trim(),
            contact: pharmacyForm.querySelector('[name="pharmacy_contact"]').value.trim(),
            location: pharmacyForm.querySelector('[name="pharmacy_location"]').value.trim(),
            password: pharmacyForm.querySelector('[name="pharmacy_password1"]').value,
            password2: pharmacyForm.querySelector('[name="pharmacy_password2"]').value
        };
        
        let isValid = true;
        
        if (!formData.name || !patterns.name.test(formData.name)) {
            showError(pharmacyForm.querySelector('[name="pharmacy_name"]'), errorMessages.name, pharmacyForm);
            isValid = false;
        }
        
        if (!formData.government_id || !patterns.governmentId.test(formData.government_id)) {
            showError(pharmacyForm.querySelector('[name="pharmacy_id"]'), errorMessages.governmentId, pharmacyForm);
            isValid = false;
        }
        
        if (!formData.contact || !patterns.contact.test(formData.contact)) {
            showError(pharmacyForm.querySelector('[name="pharmacy_contact"]'), errorMessages.contact, pharmacyForm);
            isValid = false;
        }
        
        if (!formData.location || !patterns.location.test(formData.location)) {
            showError(pharmacyForm.querySelector('[name="pharmacy_location"]'), errorMessages.location, pharmacyForm);
            isValid = false;
        }
        
        if (!formData.password || formData.password.length < 8) {
            showError(pharmacyForm.querySelector('[name="pharmacy_password1"]'), 'Password must be at least 8 characters long.', pharmacyForm);
            isValid = false;
        }
        
        if (formData.password !== formData.password2) {
            showError(pharmacyForm.querySelector('[name="pharmacy_password2"]'), errorMessages.passwordMatch, pharmacyForm);
            isValid = false;
        }
        
        return isValid;
    }
    
    /**
     * Handle individual form submission
     */
    if (individualForm) {
        individualForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            console.log('Individual form submitted'); // Debug
            
            if (!validateIndividualForm()) {
                console.log('Individual form validation failed'); // Debug
                return;
            }
            
            console.log('Individual form validation passed'); // Debug
            
            const fullNameField = individualForm.querySelector('[name="full_name"]');
            const emailField = individualForm.querySelector('[name="email"]');
            const phoneField = individualForm.querySelector('[name="phone"]');
            const password1Field = individualForm.querySelector('[name="password1"]');
            const countryField = individualForm.querySelector('[name="country"]');
            const cityField = individualForm.querySelector('[name="city"]');
            
            if (!fullNameField || !emailField || !phoneField || !password1Field || !countryField || !cityField) {
                console.error('Form fields not found');
                showMessage('Form error. Please refresh the page and try again.', false);
                return;
            }
            
            const formData = {
                full_name: fullNameField.value.trim(),
                email: emailField.value.trim().toLowerCase(),
                phone: phoneField.value.trim().replace(/\s+/g, ''),
                password: password1Field.value,
                country: countryField.value.trim(),
                city: cityField.value.trim(),
                user_role: 2
            };
            
            const submitBtn = individualForm.querySelector('button[type="submit"]');
            if (!submitBtn) {
                console.error('Submit button not found');
                showMessage('Form error. Please refresh the page and try again.', false);
                return;
            }
            
            showLoading(submitBtn, 'Register as Individual');
            
            try {
                const response = await fetch('../actions/register_customer_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                hideLoading(submitBtn);
                
                if (result.status === 'success') {
                    showMessage(result.message + ' Redirecting to login page...', true);
                    individualForm.reset();
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage('Error: ' + result.message, false);
                }
            } catch (error) {
                console.error('Registration error:', error);
                hideLoading(submitBtn);
                showMessage('Network error. Please check your connection and try again.', false);
            }
        });
    }
    
    /**
     * Handle hospital form submission
     */
    if (hospitalForm) {
        hospitalForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validateHospitalForm()) {
                return;
            }
            
            const formData = {
                name: hospitalForm.querySelector('[name="hospital_name"]').value.trim(),
                government_id: hospitalForm.querySelector('[name="hospital_id"]').value.trim(),
                contact: hospitalForm.querySelector('[name="hospital_contact"]').value.trim(),
                password: hospitalForm.querySelector('[name="hospital_password1"]').value
            };
            
            const submitBtn = hospitalForm.querySelector('button[type="submit"]');
            showLoading(submitBtn, 'Register Hospital');
            
            try {
                const response = await fetch('../actions/register_hospital_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                hideLoading(submitBtn);
                
                if (result.status === 'success') {
                    showMessage(result.message, true);
                    hospitalForm.reset();
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage('Error: ' + result.message, false);
                }
            } catch (error) {
                console.error('Registration error:', error);
                hideLoading(submitBtn);
                showMessage('Network error. Please check your connection and try again.', false);
            }
        });
    }
    
    /**
     * Handle pharmacy form submission
     */
    if (pharmacyForm) {
        pharmacyForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!validatePharmacyForm()) {
                return;
            }
            
            const formData = {
                name: pharmacyForm.querySelector('[name="pharmacy_name"]').value.trim(),
                government_id: pharmacyForm.querySelector('[name="pharmacy_id"]').value.trim(),
                contact: pharmacyForm.querySelector('[name="pharmacy_contact"]').value.trim(),
                location: pharmacyForm.querySelector('[name="pharmacy_location"]').value.trim(),
                password: pharmacyForm.querySelector('[name="pharmacy_password1"]').value
            };
            
            const submitBtn = pharmacyForm.querySelector('button[type="submit"]');
            showLoading(submitBtn, 'Register Pharmacy');
            
            try {
                const response = await fetch('../actions/register_pharmacy_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const result = await response.json();
                hideLoading(submitBtn);
                
                if (result.status === 'success') {
                    showMessage(result.message, true);
                    pharmacyForm.reset();
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showMessage('Error: ' + result.message, false);
                }
            } catch (error) {
                console.error('Registration error:', error);
                hideLoading(submitBtn);
                showMessage('Network error. Please check your connection and try again.', false);
            }
        });
    }
});
