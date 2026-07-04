/**
 * TaskNest - Theme Management
 */

(function() {
    'use strict';
    
    const THEME_STORAGE_KEY = 'tasknest-theme';
    const LIGHT_THEME = 'light';
    const DARK_THEME = 'dark';
    
    /**
     * Initialize theme
     */
    function initTheme() {
        const bodyTheme = document.body.getAttribute('data-theme');
        const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
        const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        const initialTheme = bodyTheme || savedTheme || (prefersDark ? DARK_THEME : LIGHT_THEME);

        setTheme(initialTheme);

        window.matchMedia('(prefers-color-scheme: dark)').addListener((e) => {
            if (!localStorage.getItem(THEME_STORAGE_KEY)) {
                setTheme(e.matches ? DARK_THEME : LIGHT_THEME);
            }
        });

        setupThemeToggle();
    }
    
    /**
     * Set theme
     */
    function setTheme(theme) {
        const html = document.documentElement;
        const body = document.body;
        
        // Validate theme
        if (![LIGHT_THEME, DARK_THEME].includes(theme)) {
            theme = LIGHT_THEME;
        }
        
        // Update body attribute
        body.setAttribute('data-theme', theme);
        
        // Update HTML class
        html.classList.remove(LIGHT_THEME, DARK_THEME);
        html.classList.add(theme);
        
        localStorage.setItem(THEME_STORAGE_KEY, theme);
        document.body.setAttribute('data-theme', theme);
        window.dispatchEvent(new CustomEvent('theme-changed', { detail: { theme } }));
    }
    
    /**
     * Get current theme
     */
    function getCurrentTheme() {
        return document.body.getAttribute('data-theme') || LIGHT_THEME;
    }
    
    /**
     * Toggle theme
     */
    function toggleTheme() {
        const currentTheme = getCurrentTheme();
        const newTheme = currentTheme === LIGHT_THEME ? DARK_THEME : LIGHT_THEME;
        setTheme(newTheme);
    }
    
    /**
     * Setup theme toggle button
     */
    function setupThemeToggle() {
        const toggleBtn = document.getElementById('themeToggle');
        
        if (!toggleBtn) return;
        
        toggleBtn.addEventListener('click', () => {
            toggleTheme();
        });
    }
    
    /**
     * Expose to global object
     */
    if (typeof TaskNest !== 'undefined') {
        TaskNest.Theme = {
            init: initTheme,
            set: setTheme,
            get: getCurrentTheme,
            toggle: toggleTheme
        };
    }
    
    /**
     * Initialize on DOM ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTheme);
    } else {
        initTheme();
    }
})();
