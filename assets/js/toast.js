/**
 * TaskNest - Toast Notifications
 */

(function() {
    'use strict';
    
    const TOAST_TYPES = {
        SUCCESS: 'success',
        ERROR: 'error',
        WARNING: 'warning',
        INFO: 'info'
    };
    
    const DEFAULT_DURATION = 4000; // 4 seconds
    
    /**
     * Show toast notification
     */
    function show(message, type = TOAST_TYPES.INFO, duration = DEFAULT_DURATION) {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        // Create toast element
        const toast = document.createElement('div');
        const toastId = `toast-${Date.now()}`;
        
        toast.id = toastId;
        toast.className = `toast toast-${type}`;
        toast.setAttribute('role', 'alert');
        
        // Toast content
        const iconMap = {
            success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
            error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>',
            warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3.05h16.94a2 2 0 0 0 1.71-3.05L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>',
            info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
        };
        
        toast.innerHTML = `
            <div class="toast-icon">${iconMap[type] || ''}</div>
            <div class="toast-message">${TaskNest.encodeHTML(message)}</div>
            <button class="toast-close" aria-label="Close notification">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        `;
        
        // Add to container
        container.appendChild(toast);
        
        // Trigger animation
        requestAnimationFrame(() => {
            toast.classList.add('show');
        });
        
        // Setup close button
        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                removeToast(toastId);
            });
        }
        
        // Auto-remove after duration
        if (duration > 0) {
            setTimeout(() => {
                removeToast(toastId);
            }, duration);
        }
        
        return toastId;
    }
    
    /**
     * Remove toast
     */
    function removeToast(toastId) {
        const toast = document.getElementById(toastId);
        if (!toast) return;
        
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }
    
    /**
     * Show success toast
     */
    function success(message, duration) {
        return show(message, TOAST_TYPES.SUCCESS, duration);
    }
    
    /**
     * Show error toast
     */
    function error(message, duration) {
        return show(message, TOAST_TYPES.ERROR, duration);
    }
    
    /**
     * Show warning toast
     */
    function warning(message, duration) {
        return show(message, TOAST_TYPES.WARNING, duration);
    }
    
    /**
     * Show info toast
     */
    function info(message, duration) {
        return show(message, TOAST_TYPES.INFO, duration);
    }
    
    /**
     * Initialize toast CSS
     */
    function initToastStyles() {
        if (document.getElementById('toast-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'toast-styles';
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                gap: 10px;
            }
            
            .toast {
                display: flex;
                align-items: center;
                gap: 12px;
                min-width: 300px;
                max-width: 500px;
                padding: 16px;
                background-color: var(--bg-primary);
                border-left: 4px solid;
                border-radius: var(--radius-lg);
                box-shadow: var(--shadow-lg);
                animation: slideIn 0.3s ease-out;
                opacity: 0;
                transform: translateX(400px);
                transition: opacity 0.3s, transform 0.3s;
            }
            
            .toast.show {
                opacity: 1;
                transform: translateX(0);
            }
            
            .toast-success {
                border-color: var(--color-success);
                background: linear-gradient(90deg, rgba(16, 185, 129, 0.1) 0%, transparent 100%);
            }
            
            .toast-error {
                border-color: var(--color-danger);
                background: linear-gradient(90deg, rgba(239, 68, 68, 0.1) 0%, transparent 100%);
            }
            
            .toast-warning {
                border-color: var(--color-warning);
                background: linear-gradient(90deg, rgba(245, 158, 11, 0.1) 0%, transparent 100%);
            }
            
            .toast-info {
                border-color: var(--color-info);
                background: linear-gradient(90deg, rgba(59, 130, 246, 0.1) 0%, transparent 100%);
            }
            
            .toast-icon {
                flex-shrink: 0;
                width: 24px;
                height: 24px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .toast-success .toast-icon {
                color: var(--color-success);
            }
            
            .toast-error .toast-icon {
                color: var(--color-danger);
            }
            
            .toast-warning .toast-icon {
                color: var(--color-warning);
            }
            
            .toast-info .toast-icon {
                color: var(--color-info);
            }
            
            .toast-icon svg {
                width: 100%;
                height: 100%;
            }
            
            .toast-message {
                flex: 1;
                font-size: var(--font-size-sm);
                color: var(--text-primary);
            }
            
            .toast-close {
                flex-shrink: 0;
                background: none;
                border: none;
                color: var(--text-secondary);
                cursor: pointer;
                padding: 0;
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .toast-close:hover {
                color: var(--text-primary);
            }
            
            .toast-close svg {
                width: 100%;
                height: 100%;
            }
            
            @keyframes slideIn {
                from {
                    opacity: 0;
                    transform: translateX(400px);
                }
                to {
                    opacity: 1;
                    transform: translateX(0);
                }
            }
            
            @media (max-width: 480px) {
                .toast-container {
                    right: 10px;
                    left: 10px;
                }
                
                .toast {
                    min-width: auto;
                    max-width: none;
                }
            }
        `;
        
        document.head.appendChild(style);
    }
    
    /**
     * Initialize
     */
    function init() {
        initToastStyles();
    }
    
    /**
     * Expose to global object
     */
    if (typeof TaskNest !== 'undefined') {
        TaskNest.Toast = {
            show,
            success,
            error,
            warning,
            info,
            remove: removeToast
        };
    }
    
    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
