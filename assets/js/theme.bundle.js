/* --- navigation.js --- */
/**
 * MrMurphy Theme Navigation
 *
 * Handles mobile menu toggle and mega menu behavior.
 */

(function() {
    'use strict';

    // DOM elements
    const menuToggle = document.querySelector('[data-menu-toggle]');
    const megaMenu = document.getElementById('mega-menu');
    const searchToggles = document.querySelectorAll('[data-search-toggle]');
    const searchModal = document.getElementById('search-modal');
    const searchCloses = document.querySelectorAll('[data-search-close]');
    const body = document.body;

    // State
    let isMenuOpen = false;
    let isSearchOpen = false;

    /**
     * Open the mega menu
     */
    function openMenu() {
        if (!megaMenu) return;

        const navPill = megaMenu.closest('.nav-pill');
        if (!navPill) return;

        isMenuOpen = true;
        navPill.classList.add('is-open');
        megaMenu.setAttribute('aria-hidden', 'false');

        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'true');
        }

        // Trap focus inside menu
        trapFocus(megaMenu);
    }

    /**
     * Close the mega menu
     */
    function closeMenu() {
        if (!megaMenu) return;

        const navPill = megaMenu.closest('.nav-pill');
        if (!navPill) return;

        isMenuOpen = false;
        navPill.classList.remove('is-open');
        megaMenu.setAttribute('aria-hidden', 'true');

        if (menuToggle) {
            menuToggle.setAttribute('aria-expanded', 'false');
            menuToggle.focus();
        }

        // Release focus trap
        releaseFocus();
    }

    /**
     * Toggle the mega menu
     */
    function toggleMenu() {
        if (isMenuOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    }

    /**
     * Focus trap variables
     */
    let focusTrapElement = null;
    let firstFocusableElement = null;
    let lastFocusableElement = null;

    /**
     * Trap focus within an element
     */
    function trapFocus(element) {
        focusTrapElement = element;

        const focusableElements = element.querySelectorAll(
            'a[href], button:not([disabled]), textarea, input, select, [tabindex]:not([tabindex="-1"])'
        );

        if (focusableElements.length === 0) return;

        firstFocusableElement = focusableElements[0];
        lastFocusableElement = focusableElements[focusableElements.length - 1];

        element.addEventListener('keydown', handleFocusTrap);
    }

    /**
     * Release focus trap
     */
    function releaseFocus() {
        if (focusTrapElement) {
            focusTrapElement.removeEventListener('keydown', handleFocusTrap);
        }
        focusTrapElement = null;
        firstFocusableElement = null;
        lastFocusableElement = null;
    }

    /**
     * Handle focus trap keydown
     */
    function handleFocusTrap(e) {
        if (e.key !== 'Tab') return;

        if (e.shiftKey) {
            // Shift + Tab
            if (document.activeElement === firstFocusableElement) {
                e.preventDefault();
                lastFocusableElement.focus();
            }
        } else {
            // Tab
            if (document.activeElement === lastFocusableElement) {
                e.preventDefault();
                firstFocusableElement.focus();
            }
        }
    }

    /**
     * Open search modal
     */
    function openSearch() {
        if (!searchModal) return;

        // Close menu if open
        if (isMenuOpen) {
            closeMenu();
        }

        isSearchOpen = true;
        searchModal.classList.add('is-open');
        searchModal.setAttribute('aria-hidden', 'false');
        body.style.overflow = 'hidden';

        // Focus the search input
        const searchInput = searchModal.querySelector('input[type="search"]');
        if (searchInput) {
            setTimeout(function() {
                searchInput.focus();
            }, 100);
        }

        // Trap focus inside search modal
        trapFocus(searchModal);
    }

    /**
     * Close search modal
     */
    function closeSearch() {
        if (!searchModal) return;

        isSearchOpen = false;
        searchModal.classList.remove('is-open');
        searchModal.setAttribute('aria-hidden', 'true');
        body.style.overflow = '';

        // Release focus trap
        releaseFocus();
    }

    /**
     * Handle keyboard navigation
     */
    function handleKeydown(e) {
        // Close menu on Escape
        if (e.key === 'Escape' && isMenuOpen) {
            closeMenu();
        }
        // Close search on Escape
        if (e.key === 'Escape' && isSearchOpen) {
            closeSearch();
        }
        // Open search with Cmd/Ctrl + K
        if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
            e.preventDefault();
            if (isSearchOpen) {
                closeSearch();
            } else {
                openSearch();
            }
        }
    }

    /**
     * Handle click outside menu
     */
    function handleClickOutside(e) {
        // Close menu if clicking outside
        if (isMenuOpen && megaMenu) {
            const navPill = megaMenu.closest('.nav-pill');
            if (navPill && !navPill.contains(e.target) && !menuToggle.contains(e.target)) {
                closeMenu();
            }
        }
        // Close search if clicking outside (on overlay)
        if (isSearchOpen && searchModal && e.target.classList.contains('search-modal__overlay')) {
            closeSearch();
        }
    }

    /**
     * Initialize event listeners
     */
    function init() {
        // Menu toggle button
        if (menuToggle) {
            menuToggle.addEventListener('click', toggleMenu);
        }

        // Search toggle - open search modal
        searchToggles.forEach(function(toggle) {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                openSearch();
            });
        });

        // Search close buttons
        searchCloses.forEach(function(closeBtn) {
            closeBtn.addEventListener('click', function() {
                closeSearch();
            });
        });

        // Keyboard navigation
        document.addEventListener('keydown', handleKeydown);

        // Close on click outside
        document.addEventListener('click', handleClickOutside);

        // Close menu on window resize (if switching to desktop)
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth >= 768 && isMenuOpen) {
                    closeMenu();
                }
            }, 250);
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();

/* --- theme-toggle.js --- */
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

/* --- embed-facade.js --- */
/**
 * Click-to-play embed facades in microblog preview cards.
 */
(function() {
    'use strict';

    function activateFacade(facade) {
        if (!facade || facade.classList.contains('is-playing')) {
            return;
        }

        var embedSrc = facade.getAttribute('data-embed-src');
        if (!embedSrc) {
            return;
        }

        var frame = facade.querySelector('.embed-facade__frame');
        if (!frame) {
            return;
        }

        var iframe = document.createElement('iframe');
        var separator = embedSrc.indexOf('?') === -1 ? '?' : '&';

        iframe.src = embedSrc + separator + 'autoplay=1';
        iframe.title = facade.getAttribute('data-embed-title') || 'Embedded video';
        iframe.setAttribute('loading', 'lazy');
        iframe.setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share');
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('referrerpolicy', 'strict-origin-when-cross-origin');

        frame.replaceChildren(iframe);
        facade.classList.add('is-playing');
    }

    document.addEventListener('click', function(event) {
        var facade = event.target.closest('.post-preview--microblog .embed-facade');
        if (!facade || facade.classList.contains('is-playing')) {
            return;
        }

        var playButton = event.target.closest('.embed-facade__play');
        if (!playButton || !facade.contains(playButton)) {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();
        activateFacade(facade);
    }, true);
})();

