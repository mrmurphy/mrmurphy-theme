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
   * Apply cycling colors to post navigation headers
   */
  function applyPostNavigationColors() {
    const navHeaders = document.querySelectorAll('.post-navigation__header');

    navHeaders.forEach((header, index) => {
      const colorIndex = index % monokaiColors.length;
      header.style.color = monokaiColors[colorIndex];
    });
  }

  /**
   * Apply tinted frosted glass colors to entry footer categories and tags
   */
  function applyEntryFooterColors() {
    const categoryLinks = document.querySelectorAll('.entry-footer .cat-links a');
    const tagLinks = document.querySelectorAll('.entry-footer .tag-links a');
    let colorIndex = 0;

    // Apply colors to categories
    categoryLinks.forEach((link) => {
      const tintColor = monokaiColors[colorIndex % monokaiColors.length];
      // Convert CSS variable to rgba for tint overlay
      link.style.setProperty('--tag-tint-color', getTintColor(tintColor));
      colorIndex++;
    });

    // Apply colors to tags (continue color cycle)
    tagLinks.forEach((link) => {
      const tintColor = monokaiColors[colorIndex % monokaiColors.length];
      link.style.setProperty('--tag-tint-color', getTintColor(tintColor));
      colorIndex++;
    });
  }

  /**
   * Convert CSS color variable to rgba tint color
   */
  function getTintColor(cssVar) {
    // Map CSS variables to rgba tints
    const tintMap = {
      'var(--color-pink)': 'rgba(255, 97, 136, 0.15)',
      'var(--color-orange)': 'rgba(252, 152, 103, 0.15)',
      'var(--color-yellow)': 'rgba(255, 216, 102, 0.15)',
      'var(--color-green)': 'rgba(169, 220, 118, 0.15)',
      'var(--color-cyan)': 'rgba(120, 220, 232, 0.15)',
      'var(--color-purple)': 'rgba(171, 157, 242, 0.15)'
    };

    // Check if dark mode
    const isDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (isDark) {
      // Slightly more vibrant tints for dark mode
      const darkTintMap = {
        'var(--color-pink)': 'rgba(255, 97, 136, 0.2)',
        'var(--color-orange)': 'rgba(252, 152, 103, 0.2)',
        'var(--color-yellow)': 'rgba(255, 216, 102, 0.2)',
        'var(--color-green)': 'rgba(169, 220, 118, 0.2)',
        'var(--color-cyan)': 'rgba(120, 220, 232, 0.2)',
        'var(--color-purple)': 'rgba(171, 157, 242, 0.2)'
      };
      return darkTintMap[cssVar] || tintMap[cssVar] || 'rgba(138, 125, 181, 0.15)';
    }

    return tintMap[cssVar] || 'rgba(138, 125, 181, 0.15)';
  }

  /**
   * Initialize all color cycling
   */
  function init() {
    applyColorCycle();
    applyCategoryColors();
    applyPostNavigationColors();
    applyEntryFooterColors();
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
