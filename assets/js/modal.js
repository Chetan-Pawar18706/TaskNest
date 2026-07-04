/**
 * TaskNest - Modal Management
 */

(function() {
    'use strict';
    
    const modals = new Map();
    
    /**
     * Create modal
     */
    function create(id, options = {}) {
        const {
            title = '',
            content = '',
            size = 'md', // sm, md, lg
            closable = true,
            backdrop = true,
            onClose = null,
            onConfirm = null
        } = options;
        
        // Return existing modal if it exists
        if (modals.has(id)) {
            return modals.get(id);
        }
        
        const container = document.getElementById('modalContainer');
        if (!container) return null;
        
        // Create modal HTML
        const modalHTML = `
            <div class="modal-wrapper" id="modal-${id}" data-modal-id="${id}">
                <div class="modal-backdrop"></div>
                <div class="modal modal-${size}">
                    <div class="modal-content">
                        ${title ? `<div class="modal-header">
                            <h2 class="modal-title">${TaskNest.encodeHTML(title)}</h2>
                            ${closable ? '<button class="modal-close" aria-label="Close modal"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></button>' : ''}
                        </div>` : ''}
                        <div class="modal-body">
                            ${content}
                        </div>
                        ${onConfirm ? `<div class="modal-footer">
                            <button class="btn btn-secondary modal-cancel">Cancel</button>
                            <button class="btn btn-primary modal-confirm">Confirm</button>
                        </div>` : ''}
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', modalHTML);
        
        const wrapper = document.getElementById(`modal-${id}`);
        const modal = {
            id,
            wrapper,
            options,
            open: () => openModal(id),
            close: () => closeModal(id),
            destroy: () => destroyModal(id)
        };
        
        modals.set(id, modal);
        setupModalEvents(id);
        
        return modal;
    }
    
    /**
     * Setup modal events
     */
    function setupModalEvents(id) {
        const wrapper = document.getElementById(`modal-${id}`);
        if (!wrapper) return;
        
        const modal = modals.get(id);
        const closeBtn = wrapper.querySelector('.modal-close');
        const backdrop = wrapper.querySelector('.modal-backdrop');
        const cancelBtn = wrapper.querySelector('.modal-cancel');
        const confirmBtn = wrapper.querySelector('.modal-confirm');
        
        if (closeBtn) {
            closeBtn.addEventListener('click', () => closeModal(id));
        }
        
        if (backdrop && modal.options.backdrop !== false) {
            backdrop.addEventListener('click', () => closeModal(id));
        }
        
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => closeModal(id));
        }
        
        if (confirmBtn && modal.options.onConfirm) {
            confirmBtn.addEventListener('click', () => {
                modal.options.onConfirm();
                closeModal(id);
            });
        }
        
        // Close on Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape' && isOpen(id)) {
                closeModal(id);
            }
        };
        
        document.addEventListener('keydown', handleEscape);
        modal.escapeHandler = handleEscape;
    }
    
    /**
     * Open modal
     */
    function openModal(id) {
        const wrapper = document.getElementById(`modal-${id}`);
        if (!wrapper) return;
        
        // Hide body scroll
        document.body.style.overflow = 'hidden';
        
        // Show modal
        requestAnimationFrame(() => {
            wrapper.classList.add('show');
        });
    }
    
    /**
     * Close modal
     */
    function closeModal(id) {
        const wrapper = document.getElementById(`modal-${id}`);
        if (!wrapper) return;
        
        const modal = modals.get(id);
        
        wrapper.classList.remove('show');
        
        // Restore body scroll
        if (!hasOpenModals()) {
            document.body.style.overflow = '';
        }
        
        // Call onClose callback
        if (modal && modal.options.onClose) {
            modal.options.onClose();
        }
    }
    
    /**
     * Check if modal is open
     */
    function isOpen(id) {
        const wrapper = document.getElementById(`modal-${id}`);
        return wrapper && wrapper.classList.contains('show');
    }
    
    /**
     * Destroy modal
     */
    function destroyModal(id) {
        const wrapper = document.getElementById(`modal-${id}`);
        if (wrapper) {
            wrapper.remove();
        }
        
        const modal = modals.get(id);
        if (modal && modal.escapeHandler) {
            document.removeEventListener('keydown', modal.escapeHandler);
        }
        
        modals.delete(id);
    }
    
    /**
     * Check if any modals are open
     */
    function hasOpenModals() {
        for (let modal of modals.values()) {
            if (isOpen(modal.id)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Close all modals
     */
    function closeAll() {
        for (let id of modals.keys()) {
            closeModal(id);
        }
    }
    
    /**
     * Initialize modal CSS
     */
    function initModalStyles() {
        if (document.getElementById('modal-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'modal-styles';
        style.textContent = `
            .modal-container {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: var(--z-modal);
                pointer-events: none;
            }
            
            .modal-wrapper {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: var(--z-modal);
                pointer-events: none;
            }
            
            .modal-wrapper.show {
                pointer-events: auto;
            }
            
            .modal-wrapper .modal-backdrop {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                opacity: 0;
                transition: opacity 0.3s;
            }
            
            .modal-wrapper.show .modal-backdrop {
                opacity: 1;
            }
            
            .modal-wrapper .modal {
                position: relative;
                background-color: var(--bg-primary);
                border-radius: var(--radius-2xl);
                box-shadow: var(--shadow-xl);
                max-height: 90vh;
                overflow-y: auto;
                opacity: 0;
                transform: scale(0.95);
                transition: opacity 0.3s, transform 0.3s;
                z-index: 1;
            }
            
            .modal-wrapper.show .modal {
                opacity: 1;
                transform: scale(1);
            }
            
            .modal-wrapper .modal-sm {
                width: 90%;
                max-width: 400px;
            }
            
            .modal-wrapper .modal-md {
                width: 90%;
                max-width: 600px;
            }
            
            .modal-wrapper .modal-lg {
                width: 90%;
                max-width: 800px;
            }
            
            .modal-wrapper .modal-content {
                display: flex;
                flex-direction: column;
            }
            
            .modal-wrapper .modal-header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: var(--spacing-lg);
                border-bottom: 1px solid var(--border-color);
                flex-shrink: 0;
            }
            
            .modal-wrapper .modal-title {
                margin: 0;
                font-size: var(--font-size-2xl);
            }
            
            .modal-wrapper .modal-close {
                background: none;
                border: none;
                color: var(--text-secondary);
                cursor: pointer;
                padding: 0;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: color var(--transition-fast);
            }
            
            .modal-wrapper .modal-close:hover {
                color: var(--text-primary);
            }
            
            .modal-wrapper .modal-close svg {
                width: 24px;
                height: 24px;
            }
            
            .modal-wrapper .modal-body {
                padding: var(--spacing-lg);
                overflow-y: auto;
                flex: 1;
            }
            
            .modal-wrapper .modal-footer {
                display: flex;
                align-items: center;
                justify-content: flex-end;
                gap: var(--spacing-md);
                padding: var(--spacing-lg);
                border-top: 1px solid var(--border-color);
                flex-shrink: 0;
            }
            
            @media (max-width: 480px) {
                .modal-wrapper .modal-sm,
                .modal-wrapper .modal-md,
                .modal-wrapper .modal-lg {
                    width: 95%;
                    max-width: 100%;
                }
                
                .modal-wrapper .modal-header,
                .modal-wrapper .modal-body,
                .modal-wrapper .modal-footer {
                    padding: var(--spacing-md);
                }
            }
        `;
        
        document.head.appendChild(style);
    }
    
    /**
     * Initialize
     */
    function init() {
        initModalStyles();
    }
    
    /**
     * Expose to global object
     */
    if (typeof TaskNest !== 'undefined') {
        TaskNest.Modal = {
            create,
            open: openModal,
            close: closeModal,
            isOpen,
            destroy: destroyModal,
            closeAll,
            list: () => Array.from(modals.keys())
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
