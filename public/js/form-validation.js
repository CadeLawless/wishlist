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

            // Add error message container if it doesn't exist
            if (!field.next('.validation-error').length) {
                field.after('<div class="validation-error"></div>');
            }

            // Set up event listeners
            const fieldType = field.prop('tagName').toLowerCase();
            
            // Use 'input' for text inputs, 'change' for selects
            const inputEvent = fieldType === 'select' ? 'change' : 'input';
            
            field.on(inputEvent, () => {
                // Clear error immediately when user starts typing
                this.clearErrors(field);
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
            e.stopImmediatePropagation();
            
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
        const value = field.val().trim();
        const errors = [];

        // Required validation
        if (rules.required && !value) {
            errors.push(this.formatFieldName(fieldName) + ' is required.');
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

            // AJAX validation for username/email uniqueness
            if (rules.checkUnique && errors.length === 0) {
                this.checkUniqueness(field, fieldName, value, rules.checkUnique);
                return; // Exit early, AJAX will handle displaying results
            }
        }

        // Display errors or clear them
        this.displayErrors(field, errors);
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
     * Display validation errors
     */
    displayErrors: function(field, errors) {
        const errorContainer = field.next('.validation-error');

        if (errors.length > 0) {
            // Show errors
            field.addClass('invalid');
            errorContainer.html(errors.map(err => `<span class="error-item">${err}</span>`).join(''));
            errorContainer.show();
        } else {
            // Clear errors
            this.clearErrors(field);
        }
    },

    /**
     * Clear validation errors from a field
     */
    clearErrors: function(field) {
        field.removeClass('invalid');
        const errorContainer = field.next('.validation-error');
        errorContainer.html('').hide();
    },

    /**
     * Check uniqueness via AJAX (username or email)
     */
    checkUniqueness: function(field, fieldName, value, endpoint) {
        const self = this;
        
        $.ajax({
            url: '/wishlist' + endpoint,
            method: 'POST',
            data: { [fieldName]: value },
            dataType: 'json',
            success: function(response) {
                if (!response.available) {
                    const errors = [self.formatFieldName(fieldName) + ' already exists.'];
                    self.displayErrors(field, errors);
                } else {
                    self.displayErrors(field, []);
                }
            },
            error: function() {
                // Silently fail - don't show error to user for AJAX issues
                self.displayErrors(field, []);
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

        // First, validate all fields synchronously (non-AJAX validations)
        Object.keys(validationRules).forEach(fieldName => {
            const field = form.find(`[name="${fieldName}"]`);
            if (!field.length) return;

            const rules = validationRules[fieldName];
            const value = field.val().trim();
            const errors = [];

            // Check if field already has an invalid state (from AJAX validation)
            const hadInvalidClass = field.hasClass('invalid');
            const existingErrors = field.next('.validation-error').html();

            // Required validation
            if (rules.required && !value) {
                errors.push(this.formatFieldName(fieldName) + ' is required.');
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
            }

            // Only display new errors if we have some, or if there were no existing AJAX errors
            if (errors.length > 0) {
                this.displayErrors(field, errors);
            } else if (hadInvalidClass && existingErrors) {
                // Preserve AJAX validation error
                // Don't call displayErrors as it would clear the existing error
            } else {
                this.displayErrors(field, errors);
            }

            // Check if field has errors (either new or existing)
            if (errors.length > 0 || field.hasClass('invalid')) {
                isValid = false;
            }
        });

        // Also check if any fields currently have the invalid class (from AJAX validation)
        if (form.find('.invalid').length > 0) {
            isValid = false;
        }

        return isValid;
    }
};

// Export for use in other scripts
window.FormValidator = FormValidator;

