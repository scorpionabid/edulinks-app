/**
 * EduLinks Admin Panel JavaScript
 * 
 * Main admin functionality and UI interactions
 */

// Global admin object
const EduLinksAdmin = {
    // Configuration
    config: {
        csrfToken: window.csrfToken || '',
        apiBase: '/api/v1',
        toastDuration: 5000,
        loadingClass: 'loading'
    },

    // Initialize admin panel
    init() {
        this.setupEventListeners();
        this.initializeComponents();
        this.setupFormValidation();
        this.setupAjaxDefaults();
        console.log('EduLinks Admin Panel initialized');
    },

    // Setup global event listeners
    setupEventListeners() {
        // Confirm delete buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-confirm-delete]')) {
                e.preventDefault();
                this.confirmDelete(e.target);
            }
        });

        // Loading states for forms
        document.addEventListener('submit', (e) => {
            if (e.target.matches('form')) {
                this.handleFormSubmit(e.target);
            }
        });

        // Auto-dismiss alerts
        this.setupAlertAutoDismiss();

        // Sidebar toggle (if exists)
        const sidebarToggle = document.querySelector('.sidebar-toggle');
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', this.toggleSidebar);
        }

        // Search functionality
        this.setupSearchHandlers();
    },

    // Initialize Bootstrap components
    initializeComponents() {
        // Initialize tooltips
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Initialize popovers
        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });

        // Initialize modals
        const modalElements = document.querySelectorAll('.modal');
        modalElements.forEach(modalEl => {
            new bootstrap.Modal(modalEl);
        });
    },

    // Setup form validation
    setupFormValidation() {
        const forms = document.querySelectorAll('.needs-validation');
        forms.forEach(form => {
            form.addEventListener('submit', (e) => {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });

        // Password confirmation validation
        const passwordConfirmFields = document.querySelectorAll('input[name="password_confirmation"]');
        passwordConfirmFields.forEach(field => {
            const passwordField = document.querySelector('input[name="password"]');
            if (passwordField) {
                field.addEventListener('input', () => {
                    if (field.value && passwordField.value) {
                        if (field.value !== passwordField.value) {
                            field.setCustomValidity('Şifrələr uyğun gəlmir');
                        } else {
                            field.setCustomValidity('');
                        }
                    }
                });
            }
        });
    },

    // Setup AJAX defaults
    setupAjaxDefaults() {
        // jQuery AJAX setup if available
        if (typeof $ !== 'undefined') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-Token': this.config.csrfToken
                }
            });
        }

        // Fetch API setup
        const originalFetch = window.fetch;
        window.fetch = function(url, options = {}) {
            if (!options.headers) {
                options.headers = {};
            }
            if (!options.headers['X-CSRF-Token']) {
                options.headers['X-CSRF-Token'] = EduLinksAdmin.config.csrfToken;
            }
            return originalFetch(url, options);
        };
    },

    // Confirm delete action
    confirmDelete(element) {
        const message = element.dataset.confirmMessage || 'Bu elementi silmək istədiyinizə əminsiniz?';
        const title = element.dataset.confirmTitle || 'Silməni təsdiqləyin';
        
        this.showConfirmModal(title, message, () => {
            if (element.tagName === 'FORM') {
                element.submit();
            } else if (element.href) {
                window.location.href = element.href;
            } else {
                const form = element.closest('form');
                if (form) {
                    form.submit();
                }
            }
        });
    },

    // Show confirmation modal
    showConfirmModal(title, message, onConfirm) {
        const modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.innerHTML = `
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                            ${title}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>${message}</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Ləğv et</button>
                        <button type="button" class="btn btn-danger" id="confirmBtn">Təsdiq et</button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        const bootstrapModal = new bootstrap.Modal(modal);
        
        modal.querySelector('#confirmBtn').addEventListener('click', () => {
            bootstrapModal.hide();
            onConfirm();
        });

        modal.addEventListener('hidden.bs.modal', () => {
            document.body.removeChild(modal);
        });

        bootstrapModal.show();
    },

    // Handle form submission
    handleFormSubmit(form) {
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn && !submitBtn.classList.contains(this.config.loadingClass)) {
            this.setLoadingState(submitBtn, true);
            
            // Remove loading state after 10 seconds as fallback
            setTimeout(() => {
                this.setLoadingState(submitBtn, false);
            }, 10000);
        }
    },

    // Set loading state for elements
    setLoadingState(element, loading) {
        if (loading) {
            element.classList.add(this.config.loadingClass);
            element.disabled = true;
            
            const originalText = element.innerHTML;
            element.dataset.originalText = originalText;
            element.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Yüklənir...';
        } else {
            element.classList.remove(this.config.loadingClass);
            element.disabled = false;
            
            if (element.dataset.originalText) {
                element.innerHTML = element.dataset.originalText;
                delete element.dataset.originalText;
            }
        }
    },

    // Setup alert auto-dismiss
    setupAlertAutoDismiss() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                bsAlert.close();
            }, this.config.toastDuration);
        });
    },

    // Toggle sidebar
    toggleSidebar() {
        const sidebar = document.querySelector('.admin-sidebar');
        if (sidebar) {
            sidebar.classList.toggle('show');
        }
    },

    // Setup search handlers
    setupSearchHandlers() {
        const searchInputs = document.querySelectorAll('input[type="search"], input[name="search"]');
        searchInputs.forEach(input => {
            let searchTimeout;
            input.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const form = input.closest('form');
                    if (form) {
                        form.submit();
                    }
                }, 500);
            });
        });
    },

    // Show toast notification
    showToast(message, type = 'info') {
        const toastContainer = this.getOrCreateToastContainer();
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${this.getToastIcon(type)} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        toastContainer.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => {
            toastContainer.removeChild(toast);
        });
    },

    // Get or create toast container
    getOrCreateToastContainer() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        return container;
    },

    // Get toast icon for type
    getToastIcon(type) {
        const icons = {
            success: 'check-circle',
            danger: 'exclamation-triangle',
            warning: 'exclamation-circle',
            info: 'info-circle'
        };
        return icons[type] || 'info-circle';
    },

    // AJAX helpers
    ajax: {
        get(url, data = {}) {
            const params = new URLSearchParams(data);
            return fetch(`${url}?${params}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': EduLinksAdmin.config.csrfToken
                }
            }).then(response => response.json());
        },

        post(url, data = {}) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': EduLinksAdmin.config.csrfToken
                },
                body: JSON.stringify(data)
            }).then(response => response.json());
        },

        put(url, data = {}) {
            return fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': EduLinksAdmin.config.csrfToken
                },
                body: JSON.stringify(data)
            }).then(response => response.json());
        },

        delete(url) {
            return fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-Token': EduLinksAdmin.config.csrfToken
                }
            }).then(response => response.json());
        }
    },

    // Utility functions
    utils: {
        formatDate(date, format = 'dd.mm.yyyy') {
            const d = new Date(date);
            const day = String(d.getDate()).padStart(2, '0');
            const month = String(d.getMonth() + 1).padStart(2, '0');
            const year = d.getFullYear();
            
            return format
                .replace('dd', day)
                .replace('mm', month)
                .replace('yyyy', year);
        },

        formatFileSize(bytes) {
            const sizes = ['B', 'KB', 'MB', 'GB'];
            if (bytes === 0) return '0 B';
            const i = Math.floor(Math.log(bytes) / Math.log(1024));
            return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i];
        },

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        throttle(func, limit) {
            let inThrottle;
            return function() {
                const args = arguments;
                const context = this;
                if (!inThrottle) {
                    func.apply(context, args);
                    inThrottle = true;
                    setTimeout(() => inThrottle = false, limit);
                }
            };
        }
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    EduLinksAdmin.init();
});

// Export for use in other scripts
window.EduLinksAdmin = EduLinksAdmin;