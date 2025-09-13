const ClientValidation = {
    rules: {
        title: {
            required: true,
            minLength: 3,
            maxLength: 255,
            pattern: /^.{3,255}$/,
            message: {
                required: 'Judul tugas wajib diisi',
                minLength: 'Judul tugas minimal 3 karakter',
                maxLength: 'Judul tugas maksimal 255 karakter',
                pattern: 'Format judul tidak valid'
            }
        },
        status: {
            required: true,
            options: ['To-Do', 'In Progress', 'Done'],
            message: {
                required: 'Status wajib dipilih',
                options: 'Status yang dipilih tidak valid'
            }
        },
        description: {
            required: false,
            maxLength: 2000,
            message: {
                maxLength: 'Deskripsi maksimal 2000 karakter'
            }
        },
        due_at: {
            required: false,
            type: 'datetime',
            futureOnly: true, 
            message: {
                type: 'Format tanggal tidak valid',
                futureOnly: 'Batas waktu tidak boleh tanggal lampau'
            }
        }
    },

    init: function() {
        this.bindEvents();
        this.setupRealTimeValidation();
        console.log('Client validation initialized');
    },

    bindEvents: function() {
        const self = this;
        
        // Validate on input/change
        $('[data-validation]').each(function() {
            const fieldName = $(this).data('validation');
            const $field = $(this);
            
            // Real-time validation with debounce
            let debounceTimer;
            $field.on('input change paste keyup', function() {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(function() {
                    self.validateField(fieldName, $field);
                }, 300);
            });
            
            $field.on('blur', function() {
                self.validateField(fieldName, $field);
            });
            
            $field.on('focus', function() {
                self.clearFieldError(fieldName);
            });
        });

        $('#taskForm').on('submit', function(e) {
            if (!self.validateForm()) {
                e.preventDefault();
                self.showFormErrors();
                return false;
            }
        });
    },

    setupRealTimeValidation: function() {
        const self = this;
        
        $('#description').on('input', function() {
            self.updateCharacterCounter();
            self.validateField('description', $(this));
        });
        
        $('#due_at').on('change', function() {
            self.validateDueDate();
        });
        
        $('#taskStatus').on('change', function() {
            self.validateField('status', $(this));
            self.handleStatusChangeEffects();
        });
        
        $('#title').on('input', function() {
            self.smartFormatTitle($(this));
        });
    },

    validateField: function(fieldName, $field) {
        const rule = this.rules[fieldName];
        if (!rule) return true;

        const value = $field.val();
        const isValid = this.runValidation(fieldName, value, rule);
        
        if (isValid.valid) {
            this.showFieldSuccess(fieldName, $field);
        } else {
            this.showFieldError(fieldName, $field, isValid.message);
        }
        
        return isValid.valid;
    },

    runValidation: function(fieldName, value, rule) {
        if (rule.required && (!value || value.trim() === '')) {
            return { valid: false, message: rule.message.required };
        }
        
        if (!rule.required && (!value || value.trim() === '')) {
            return { valid: true };
        }
        
        if (rule.minLength && value.length < rule.minLength) {
            return { valid: false, message: rule.message.minLength };
        }
        
        if (rule.maxLength && value.length > rule.maxLength) {
            return { valid: false, message: rule.message.maxLength };
        }
        
        if (rule.pattern && !rule.pattern.test(value)) {
            return { valid: false, message: rule.message.pattern };
        }
        
        if (rule.options && !rule.options.includes(value)) {
            return { valid: false, message: rule.message.options };
        }
        
        if (rule.type === 'datetime' && value) {
            const date = new Date(value);
            if (isNaN(date.getTime())) {
                return { valid: false, message: rule.message.type };
            }
            
            if (rule.futureOnly && $('#formMethod').val() === 'POST') {
                const now = new Date();
                if (date <= now) {
                    return { valid: false, message: rule.message.futureOnly };
                }
            }
        }
        
        return { valid: true };
    },

    validateForm: function() {
        let isValid = true;
        const self = this;
        
        $('[data-validation]').each(function() {
            const fieldName = $(this).data('validation');
            if (!self.validateField(fieldName, $(this))) {
                isValid = false;
            }
        });
        
        return isValid;
    },

    showFieldSuccess: function(fieldName, $field) {
        $field.removeClass('is-invalid').addClass('is-valid');
        $(`#${fieldName}-error`).hide().text('');
        
        this.addFieldIcon(fieldName, 'check-circle', 'text-success');
    },

    showFieldError: function(fieldName, $field, message) {
        $field.removeClass('is-valid').addClass('is-invalid');
        $(`#${fieldName}-error`).show().text(message);
        
        this.addFieldIcon(fieldName, 'exclamation-circle', 'text-danger');
        
        $field.addClass('shake');
        setTimeout(() => {
            $field.removeClass('shake');
        }, 600);
    },

    clearFieldError: function(fieldName) {
        const $field = $(`#${fieldName}`);
        $field.removeClass('is-invalid is-valid');
        $(`#${fieldName}-error`).hide().text('');
        this.removeFieldIcon(fieldName);
    },

    addFieldIcon: function(fieldName, iconName, className) {
        this.removeFieldIcon(fieldName);
        
        const $field = $(`#${fieldName}`);
        const $wrapper = $field.parent();
        
        if (!$wrapper.hasClass('position-relative')) {
            $wrapper.addClass('position-relative');
        }
        
        const $icon = $(`
            <i class="bi bi-${iconName} ${className} validation-icon" 
               style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); z-index: 5; pointer-events: none;">
            </i>
        `);
        
        $wrapper.append($icon);
    },

    removeFieldIcon: function(fieldName) {
        $(`#${fieldName}`).parent().find('.validation-icon').remove();
    },

    updateCharacterCounter: function() {
        const $textarea = $('#description');
        const $counter = $('#descriptionCount');
        const length = $textarea.val().length;
        const maxLength = 2000;
        
        $counter.text(length);
        
        if (length > maxLength * 0.9) { 
            $counter.removeClass('text-muted text-warning').addClass('text-danger');
        } else if (length > maxLength * 0.75) { 
            $counter.removeClass('text-muted text-danger').addClass('text-warning');
        } else {
            $counter.removeClass('text-warning text-danger').addClass('text-muted');
        }
        
        const percentage = (length / maxLength) * 100;
        let $progressBar = $('.character-progress');
        
        if ($progressBar.length === 0) {
            $progressBar = $(`
                <div class="progress character-progress mt-1" style="height: 3px;">
                    <div class="progress-bar" role="progressbar"></div>
                </div>
            `);
            $counter.parent().after($progressBar);
        }
        
        const $bar = $progressBar.find('.progress-bar');
        $bar.css('width', Math.min(percentage, 100) + '%');
        
        if (percentage > 90) {
            $bar.removeClass('bg-success bg-warning').addClass('bg-danger');
        } else if (percentage > 75) {
            $bar.removeClass('bg-success bg-danger').addClass('bg-warning');
        } else {
            $bar.removeClass('bg-warning bg-danger').addClass('bg-success');
        }
    },

    validateDueDate: function() {
        const $field = $('#due_at');
        const value = $field.val();
        
        if (!value) return;
        
        const dueDate = new Date(value);
        const now = new Date();
        const diffHours = (dueDate - now) / (1000 * 60 * 60);
        
        if (diffHours < 0) {
            this.showFieldWarning('due_at', 'Tanggal sudah lewat', 'warning');
        } else if (diffHours < 24) {
            this.showFieldWarning('due_at', 'Kurang dari 24 jam lagi', 'info');
        } else if (diffHours > 24 * 365) {
            this.showFieldWarning('due_at', 'Lebih dari 1 tahun ke depan', 'info');
        } else {
            this.clearFieldWarning('due_at');
        }
    },

    showFieldWarning: function(fieldName, message, type = 'warning') {
        const $field = $(`#${fieldName}`);
        let $warning = $field.parent().find('.field-warning');
        
        if ($warning.length === 0) {
            $warning = $(`<small class="field-warning text-${type}"></small>`);
            $field.after($warning);
        }
        
        $warning.text(message).removeClass('text-warning text-info text-danger').addClass(`text-${type}`);
    },

    clearFieldWarning: function(fieldName) {
        $(`#${fieldName}`).parent().find('.field-warning').remove();
    },

    smartFormatTitle: function($field) {
        let value = $field.val();
        
        value = value.replace(/(?:^|\. )([a-z])/g, function(match, p1) {
            return match.replace(p1, p1.toUpperCase());
        });
        
        value = value.replace(/\s+/g, ' ');
        
        if (value !== $field.val()) {
            const cursorPos = $field[0].selectionStart;
            $field.val(value);
            $field[0].setSelectionRange(cursorPos, cursorPos);
        }
    },

    handleStatusChangeEffects: function() {
        const status = $('#taskStatus').val();
        const $dueAtField = $('#due_at');
        
        if (status === 'Done') {
            $dueAtField.parent().addClass('opacity-75');
            this.showFieldWarning('status', 'Tugas selesai - batas waktu opsional', 'success');
        } else {
            $dueAtField.parent().removeClass('opacity-75');
            this.clearFieldWarning('status');
        }
    },

    showFormErrors: function() {
        const $errors = $('.is-invalid');
        if ($errors.length > 0) {
            $('html, body').animate({
                scrollTop: $errors.first().offset().top - 100
            }, 300);
            
            $errors.first().focus();
            
            showToast(`Harap perbaiki ${$errors.length} kesalahan pada form`, 'error');
        }
    },

    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
};

$(document).ready(function() {
    ClientValidation.init();
});