/**
 * Theme Toggle - Dark/Light Mode Switcher
 *
 * Handles manual theme switching with localStorage persistence.
 */

(function() {
    'use strict';

    const themeToggle = document.querySelector('[data-theme-toggle]');
    const html = document.documentElement;

    /**
     * Get current theme preference
     */
    function getThemePreference() {
        const stored = localStorage.getItem('theme-preference');
        if (stored) {
            return stored;
        }
        // Fall back to system preference
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    /**
     * Apply theme to document
     */
    function applyTheme(theme) {
        if (theme === 'dark') {
            html.classList.add('dark-mode');
            html.setAttribute('data-theme', 'dark');
        } else {
            html.classList.remove('dark-mode');
            html.setAttribute('data-theme', 'light');
        }
    }

    /**
     * Toggle theme
     */
    function toggleTheme() {
        const current = getThemePreference();
        const newTheme = current === 'dark' ? 'light' : 'dark';
        localStorage.setItem('theme-preference', newTheme);
        applyTheme(newTheme);
    }

    /**
     * Initialize theme on page load
     */
    function initTheme() {
        const theme = getThemePreference();
        applyTheme(theme);
    }

    /**
     * Listen for system preference changes
     */
    function watchSystemPreference() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', function(e) {
            // Only apply system preference if user hasn't set a manual preference
            if (!localStorage.getItem('theme-preference')) {
                applyTheme(e.matches ? 'dark' : 'light');
            }
        });
    }

    /**
     * Initialize
     */
    function init() {
        initTheme();
        watchSystemPreference();

        if (themeToggle) {
            themeToggle.addEventListener('click', toggleTheme);
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
