/**
 * Color Cycling for Section Headers and Category Links
 * Cycles through Monokai Pro accent colors for each section title and category button
 */
(function() {
  'use strict';

  // Monokai Pro accent colors - these match the CSS variable names
  const monokaiColors = [
    'var(--color-pink)',
    'var(--color-orange)',
    'var(--color-yellow)',
    'var(--color-green)',
    'var(--color-cyan)',
    'var(--color-purple)'
  ];

  /**
   * Apply cycling colors to section titles
   */
  function applyColorCycle() {
    const sectionTitles = document.querySelectorAll('.section__title');

    sectionTitles.forEach((title, index) => {
      const colorIndex = index % monokaiColors.length;
      title.style.color = monokaiColors[colorIndex];
    });
  }

  /**
   * Apply cycling colors and tinted backgrounds to category links
   */
  function applyCategoryColors() {
    const categoryLinks = document.querySelectorAll('.mega-menu__categories a');

    categoryLinks.forEach((link, index) => {
      const colorIndex = index % monokaiColors.length;
      const colorVar = monokaiColors[colorIndex];
      
      // Set CSS custom property for the color
      link.style.setProperty('--category-color', colorVar);
      
      // Add data attribute for CSS targeting
      link.setAttribute('data-color-index', colorIndex);
    });
  }

  /**
   * Initialize all color cycling
   */
  function init() {
    applyColorCycle();
    applyCategoryColors();
  }

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  // Re-run when mega menu opens (in case categories are dynamically loaded)
  document.addEventListener('click', function(e) {
    if (e.target.closest('[data-menu-toggle]')) {
      setTimeout(applyCategoryColors, 100);
    }
  });
})();
