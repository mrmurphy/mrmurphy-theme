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
