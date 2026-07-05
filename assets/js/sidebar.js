/**
 * TaskNest - Sidebar Management
 */

(function() {
    'use strict';
    
    const SIDEBAR_STATE_KEY = 'tasknest-sidebar-state';
    
    /**
     * Initialize sidebar
     */
    function initSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const toggle = document.getElementById('sidebarToggle');
        const close = document.getElementById('sidebarClose');
        
        if (!sidebar) return;
        
        const savedState = localStorage.getItem(SIDEBAR_STATE_KEY);
        if (savedState === 'collapsed' && window.innerWidth > 1024) {
            sidebar.classList.add('collapsed');
        }
        
        if (toggle) {
            toggle.addEventListener('click', () => {
                sidebar.classList.toggle('active');
                document.body.classList.toggle('sidebar-open', sidebar.classList.contains('active'));
            });
        }
        
        if (close) {
            close.addEventListener('click', () => {
                closeSidebar();
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(e.target) && (!toggle || !toggle.contains(e.target))) {
                    closeSidebar();
                }
            }
        });
        
        setupNavLinks(sidebar);
        handleWindowResize();
    }
    
    /**
     * Toggle sidebar
     */
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.toggle('active');
        }
    }
    
    /**
     * Open sidebar
     */
    function openSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.add('active');
        }
    }
    
    /**
     * Close sidebar
     */
    function closeSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }
    }
    
    /**
     * Setup navigation links
     */
    function setupNavLinks(sidebar) {
        const navLinks = sidebar.querySelectorAll('.nav-link');
        const currentPage = window.location.pathname;
        
        navLinks.forEach(link => {
            const href = link.getAttribute('href');
            
            // Check if current page matches
            if (href && currentPage.includes(href)) {
                const navItem = link.closest('.nav-item');
                if (navItem) {
                    navItem.classList.add('active');
                }
            }
        });
    }
    
    /**
     * Handle window resize
     */
    function handleWindowResize() {
        window.addEventListener('resize', () => {
            const sidebar = document.querySelector('.sidebar');
            if (!sidebar) return;
            
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        });
    }
    
    /**
     * Expose to global object
     */
    if (typeof TaskNest !== 'undefined') {
        TaskNest.Sidebar = {
            init: initSidebar,
            toggle: toggleSidebar,
            open: openSidebar,
            close: closeSidebar
        };
    }
    
    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }
})();
