/**
 * TaskNest - Main JavaScript
 */

(function() {
    'use strict';
    
    // Global namespace
    window.TaskNest = window.TaskNest || {};
    
    /**
     * Initialize application
     */
    TaskNest.init = function() {
        console.log('TaskNest initialized');
        this.setupEventListeners();
    };
    
    /**
     * Setup event listeners
     */
    TaskNest.setupEventListeners = function() {
        // User menu dropdown
        const userMenuBtn = document.getElementById('userMenuBtn');
        const userMenu = document.getElementById('userMenu');
        
        if (userMenuBtn && userMenu) {
            userMenuBtn.addEventListener('click', () => {
                userMenu.classList.toggle('active');
            });
            
            document.addEventListener('click', (e) => {
                if (!userMenu.contains(e.target) && !userMenuBtn.contains(e.target)) {
                    userMenu.classList.remove('active');
                }
            });
        }
    };
    
    /**
     * Utility: Fetch with CSRF token
     */
    TaskNest.fetch = function(url, options = {}) {
        const defaultOptions = {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken
            }
        };
        
        return fetch(url, { ...defaultOptions, ...options });
    };
    
    /**
     * Utility: Make JSON requests with error handling
     */
    TaskNest.request = async function(url, options = {}) {
        try {
            const response = await this.fetch(url, options);
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP Error: ${response.status}`);
            }
            
            return data;
        } catch (error) {
            if (debugMode) console.error('Request error:', error);
            throw error;
        }
    };
    
    /**
     * Utility: Debounce function
     */
    TaskNest.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };
    
    /**
     * Utility: Throttle function
     */
    TaskNest.throttle = function(func, limit) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    };
    
    /**
     * Utility: Check if element is in viewport
     */
    TaskNest.isInViewport = function(element) {
        const rect = element.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    };
    
    /**
     * Utility: Format date
     */
    TaskNest.formatDate = function(date, format = 'short') {
        const options = {
            short: { year: 'numeric', month: 'short', day: 'numeric' },
            long: { year: 'numeric', month: 'long', day: 'numeric' },
            full: { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }
        };
        
        return new Date(date).toLocaleDateString('en-US', options[format] || options.short);
    };
    
    /**
     * Utility: Format time
     */
    TaskNest.formatTime = function(date) {
        return new Date(date).toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        });
    };
    
    /**
     * Utility: Copy to clipboard
     */
    TaskNest.copyToClipboard = async function(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            console.error('Failed to copy to clipboard:', err);
            return false;
        }
    };
    
    /**
     * Utility: Generate UUID
     */
    TaskNest.generateUUID = function() {
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
            const r = Math.random() * 16 | 0;
            const v = c === 'x' ? r : (r & 0x3 | 0x8);
            return v.toString(16);
        });
    };
    
    /**
     * Utility: Validate email
     */
    TaskNest.validateEmail = function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    };
    
    /**
     * Utility: Encode HTML
     */
    TaskNest.encodeHTML = function(str) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#39;'
        };
        return str.replace(/[&<>"']/g, (m) => map[m]);
    };
    
    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            TaskNest.init();
        });
    } else {
        TaskNest.init();
    }
})();
