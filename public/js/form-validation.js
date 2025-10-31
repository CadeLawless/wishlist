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
        if (!form.length) return;

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
            field.on('input', () => {
                // Clear error immediately when user starts typing
                this.clearErrors(field);
                this.debounceValidation(field, fieldName, rules);
            });

            field.on('blur', () => {
                this.validateField(field, fieldName, rules);
            });
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
    }
};

// Export for use in other scripts
window.FormValidator = FormValidator;

