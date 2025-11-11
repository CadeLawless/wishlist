/**
 * Form Validation Library
 * Provides real-time client-side validation matching server-side rules
 */

const FormValidator = {
    // Configuration
    config: {
        debounceDelay: 1000, // Wait 1 second after user stops typing
        minUsernameLength: 3,
        maxUsernameLength: 50,
        minNameLength: 2,
        maxNameLength: 50,
        minPasswordLength: 8,
    },

    // Store timeout IDs for debouncing
    timeouts: {},

    /**
     * Initialize validation on a form
     */
    init: function(formSelector, validationRules) {
        const form = $(formSelector);
        
        if (!form.length) {
            return;
        }

        // Disable HTML5 validation to use our custom validation
        form.attr('novalidate', 'novalidate');

        // Store validation rules on the form for later use
        form.data('validationRules', validationRules);

        // Set up validation for each field
        Object.keys(validationRules).forEach(fieldName => {
            const field = form.find(`[name="${fieldName}"]`);
            if (!field.length) return;

            const rules = validationRules[fieldName];

            // Determine where to place error message
            let errorInsertionPoint;
            if (rules.errorContainer) {
                // Custom error placement - find the specified container
                errorInsertionPoint = field.closest('.small-input, .large-input').find(rules.errorContainer);
                if (!errorInsertionPoint.length) {
                    errorInsertionPoint = $(rules.errorContainer);
                }
            } else {
                // Default: place after the field itself
                errorInsertionPoint = field;
            }

            // Add error message container if it doesn't exist
            if (!errorInsertionPoint.next('.validation-error').length) {
                errorInsertionPoint.after('<div class="validation-error"></div>');
            }

            // Set up event listeners
            const fieldType = field.prop('tagName').toLowerCase();
            
            // Use 'input' for text inputs, 'change' for selects
            const inputEvent = fieldType === 'select' ? 'change' : (field.prop('type') === 'hidden' ? 'change' : 'input');
            
            field.on(inputEvent, () => {
                // Clear error immediately when user starts typing
                this.clearErrors(field, rules);
                this.debounceValidation(field, fieldName, rules);
            });

            field.on('blur', () => {
                this.validateField(field, fieldName, rules);
            });
        });

        // Prevent form submission if there are validation errors
        const self = this;
        let isSubmitting = false; // Flag to prevent recursive validation
        
        form.on('submit', function(e) {
            // If already validated and submitting, allow it through
            if (isSubmitting) {
                return true;
            }
            
            // Prevent default submission
            e.preventDefault();
            
            const isValid = self.validateFormBeforeSubmit($(this), validationRules);
            
            if (!isValid) {
                // Scroll to first error
                const firstError = $(this).find('.invalid').first();
                if (firstError.length) {
                    firstError[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstError.focus();
                }
                
                return false;
            }
            
            // Form is valid - submit it
            isSubmitting = true;
            
            // Submit the form using native submit (bypasses jQuery event handlers)
            this.submit();
        });
    },

    /**
     * Debounce validation - wait for user to stop typing
     */
    debounceValidation: function(field, fieldName, rules) {
        // Clear existing timeout
        if (this.timeouts[fieldName]) {
            clearTimeout(this.timeouts[fieldName]);
        }

        // Set new timeout
        this.timeouts[fieldName] = setTimeout(() => {
            this.validateField(field, fieldName, rules);
        }, this.config.debounceDelay);
    },

    /**
     * Validate a single field
     */
    validateField: function(field, fieldName, rules) {
        const value = field.val() ? field.val().trim() : '';
        const errors = [];

        // Required validation (can be boolean or function)
        const isRequired = typeof rules.required === 'function' ? rules.required() : rules.required;
        if (isRequired && !value) {
            if(rules.requiredMsg === undefined){
                errors.push(this.formatFieldName(fieldName) + ' is required.');
            }else{
                errors.push(rules.requiredMsg);
            }
        }

        // Only run other validations if field has a value
        if (value) {
            // Length validation
            if (rules.minLength && value.length < rules.minLength) {
                errors.push(this.formatFieldName(fieldName) + ` must be at least ${rules.minLength} characters.`);
            }
            if (rules.maxLength && value.length > rules.maxLength) {
                errors.push(this.formatFieldName(fieldName) + ` must not exceed ${rules.maxLength} characters.`);
            }

            // Email validation
            if (rules.email && !this.isValidEmail(value)) {
                errors.push('Please enter a valid email address.');
            }

            // Password validation
            if (rules.password) {
                const passwordErrors = this.validatePassword(value);
                errors.push(...passwordErrors);
            }

            // Confirm password match
            if (rules.confirmPassword) {
                const originalPassword = $(rules.confirmPassword).val();
                if (value !== originalPassword) {
                    errors.push('Passwords do not match.');
                }
            }

            // Currency validation (US format: 9,999.99)
            if (rules.currency && !this.isValidCurrency(value)) {
                errors.push('Please enter a valid price (e.g., 19.99 or 1,999.99).');
            }

            // Numeric validation (positive integers only)
            if (rules.numeric && !this.isValidNumeric(value)) {
                errors.push('Please enter a valid number.');
            }

            // URL validation
            if (rules.url && !this.isValidUrl(value)) {
                errors.push('Please enter a valid URL (e.g., https://example.com).');
            }

            // Custom validation function
            if (rules.custom && typeof rules.custom === 'function') {
                const customError = rules.custom(value, field);
                if (customError) {
                    errors.push(customError);
                }
            }

            // AJAX validation for username/email uniqueness
            if (rules.checkUnique && errors.length === 0) {
                this.checkUniqueness(field, fieldName, value, rules.checkUnique, rules);
                return; // Exit early, AJAX will handle displaying results
            }
        }

        // Display errors or clear them
        this.displayErrors(field, errors, rules);
    },

    /**
     * Validate password strength
     */
    validatePassword: function(password) {
        const errors = [];

        if (password.length < this.config.minPasswordLength) {
            errors.push('Password must be at least 8 characters long.');
        }

        if (!/[A-Z]/.test(password)) {
            errors.push('Password must contain at least one uppercase letter.');
        }

        if (!/[a-z]/.test(password)) {
            errors.push('Password must contain at least one lowercase letter.');
        }

        if (!/[0-9]/.test(password)) {
            errors.push('Password must contain at least one number.');
        }

        return errors;
    },

    /**
     * Validate email format
     */
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    /**
     * Validate currency format (US format: 9,999.99)
     */
    isValidCurrency: function(value) {
        // Allow commas for thousands and up to 2 decimal places
        // Matches: 19.99, 1999.99, 1,999.99, $19.99, etc.
        const currencyRegex = /^(?=.*?\d)(([1-9]\d{0,2}(,\d{3})*)|\d+)?(\.\d{1,2})?$/;
        return currencyRegex.test(value);
    },

    /**
     * Validate numeric format (positive integers)
     */
    isValidNumeric: function(value) {
        const numericRegex = /^\d+$/;
        return numericRegex.test(value);
    },

    /**
     * Validate URL format
     */
    isValidUrl: function(value) {
        try {
            new URL(value);
            return true;
        } catch (_) {
            return false;
        }
    },

    /**
     * Display validation errors
     */
    displayErrors: function(field, errors, rules) {
        // Determine where the error container is based on rules
        let errorContainer;
        let invalidTarget = field; // Default: apply invalid class to field itself
        
        if (rules && rules.errorContainer) {
            // Custom error placement
            const customContainer = field.closest('.small-input, .large-input').find(rules.errorContainer);
            errorContainer = customContainer.length ? customContainer.next('.validation-error') : $(rules.errorContainer).next('.validation-error');
            
            // Custom invalid target
            if (rules.invalidTarget) {
                invalidTarget = field.closest('.small-input, .large-input').find(rules.invalidTarget);
                if (!invalidTarget.length) {
                    invalidTarget = $(rules.invalidTarget);
                }
            }
        } else {
            // Default: error container right after field
            errorContainer = field.next('.validation-error');
        }

        if (errors.length > 0) {
            // Show errors
            invalidTarget.addClass('invalid');
            errorContainer.html(errors.map(err => `<span class="error-item">${err}</span>`).join(''));
            errorContainer.show();
        } else {
            // Clear errors
            this.clearErrors(field, rules);
        }
    },

    /**
     * Clear validation errors from a field
     */
    clearErrors: function(field, rules) {
        // Determine targets based on rules
        let errorContainer;
        let invalidTarget = field; // Default
        
        if (rules && rules.errorContainer) {
            const customContainer = field.closest('.small-input, .large-input').find(rules.errorContainer);
            errorContainer = customContainer.length ? customContainer.next('.validation-error') : $(rules.errorContainer).next('.validation-error');
            
            if (rules.invalidTarget) {
                invalidTarget = field.closest('.small-input, .large-input').find(rules.invalidTarget);
                if (!invalidTarget.length) {
                    invalidTarget = $(rules.invalidTarget);
                }
            }
        } else {
            errorContainer = field.next('.validation-error');
        }
        
        invalidTarget.removeClass('invalid');
        field.removeClass('invalid'); // Always clear from field itself too
        errorContainer.html('').hide();
    },

    /**
     * Check uniqueness via AJAX (username or email)
     */
    checkUniqueness: function(field, fieldName, value, endpoint, rules) {
        const self = this;
        
        $.ajax({
            url: endpoint,
            method: 'POST',
            data: { [fieldName]: value },
            dataType: 'json',
            success: function(response) {
                if (!response.available) {
                    const errors = [self.formatFieldName(fieldName) + ' already exists.'];
                    self.displayErrors(field, errors, rules);
                } else {
                    self.displayErrors(field, [], rules);
                }
            },
            error: function() {
                // Silently fail - don't show error to user for AJAX issues
                self.displayErrors(field, [], rules);
            }
        });
    },

    /**
     * Format field name for display
     */
    formatFieldName: function(fieldName) {
        // Convert snake_case or camelCase to Title Case
        return fieldName
            .replace(/_/g, ' ')
            .replace(/([A-Z])/g, ' $1')
            .split(' ')
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
            .join(' ');
    },

    /**
     * Validate entire form before submission
     */
    validateForm: function(formSelector, validationRules) {
        const form = $(formSelector);
        let isValid = true;

        Object.keys(validationRules).forEach(fieldName => {
            const field = form.find(`[name="${fieldName}"]`);
            if (!field.length) return;

            const rules = validationRules[fieldName];
            this.validateField(field, fieldName, rules);

            // Check if field has errors
            if (field.hasClass('invalid')) {
                isValid = false;
            }
        });

        return isValid;
    },

    /**
     * Validate form before submission (synchronous check)
     */
    validateFormBeforeSubmit: function(form, validationRules) {
        let isValid = true;
    
        Object.keys(validationRules).forEach(fieldName => {
            const field = form.find(`[name="${fieldName}"]`);
            if (!field.length) return;
    
            const rules = validationRules[fieldName];
            
            // Handle file inputs specially (they don't have a .val() that works for validation)
            const isFileInput = field.prop('type') === 'file';
            let value = '';
            
            if (isFileInput) {
                // For file inputs, check if files are selected
                const hasFiles = field[0].files && field[0].files.length > 0;
                value = hasFiles ? 'file-selected' : ''; // Use placeholder value
            } else {
                value = field.val() ? field.val().trim() : '';
            }
            
            const errors = [];

            // Required validation (can be boolean or function, now supports custom messages)
            const isRequired = typeof rules.required === 'function' ? rules.required() : rules.required;
            
            // For file inputs with custom validation, skip standard required check if custom exists
            // (custom validation will handle it)
            if (!isFileInput || !rules.custom) {
                if (isRequired && !value) {
                    if (rules.requiredMsg === undefined) {
                        errors.push(this.formatFieldName(fieldName) + ' is required.');
                    } else {
                        errors.push(rules.requiredMsg);
                    }
                }
            }
    
            // For file inputs, always run custom validation if it exists
            // For other fields, only check other validations if field has a value
            if (value || (isFileInput && rules.custom)) {
                // Length validation (skip for file inputs)
                if (!isFileInput) {
                    if (rules.minLength && value.length < rules.minLength) {
                        errors.push(this.formatFieldName(fieldName) + ` must be at least ${rules.minLength} characters.`);
                    }
                    if (rules.maxLength && value.length > rules.maxLength) {
                        errors.push(this.formatFieldName(fieldName) + ` must not exceed ${rules.maxLength} characters.`);
                    }
                }
    
                // Email validation
                if (rules.email && !this.isValidEmail(value)) {
                    errors.push('Please enter a valid email address.');
                }
    
                // Password validation
                if (rules.password) {
                    const passwordErrors = this.validatePassword(value);
                    errors.push(...passwordErrors);
                }
    
                // Confirm password validation
                if (rules.confirmPassword) {
                    const originalPassword = $(rules.confirmPassword).val();
                    if (value !== originalPassword) {
                        errors.push('Passwords do not match.');
                    }
                }

                // Currency validation (US format: 9,999.99)
                if (rules.currency && !this.isValidCurrency(value)) {
                    errors.push('Please enter a valid price (e.g., 19.99 or 1,999.99).');
                }

                // Numeric validation (positive integers only)
                if (rules.numeric && !this.isValidNumeric(value)) {
                    errors.push('Please enter a valid number.');
                }

                // URL validation
                if (rules.url && !this.isValidUrl(value)) {
                    errors.push('Please enter a valid URL (e.g., https://example.com).');
                }

                // Custom validation function (runs for all fields, especially important for file inputs)
                if (rules.custom && typeof rules.custom === 'function') {
                    const customError = rules.custom(value, field);
                    if (customError) {
                        errors.push(customError);
                    }
                }
            }
    
            // Display or clear errors
            this.displayErrors(field, errors, rules);
    
            // Update overall validity flag
            if (errors.length > 0 || field.hasClass('invalid')) {
                isValid = false;
            }
        });
    
        // If any AJAX validation has already flagged fields as invalid
        if (form.find('.invalid').length > 0) {
            isValid = false;
        }
    
        return isValid;
    }
};

// Export for use in other scripts
window.FormValidator = FormValidator;

