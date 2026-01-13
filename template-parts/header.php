<?php
/**
 * Site header template part
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light dark">
    <script>
    // Prevent flash of wrong theme - runs before CSS loads
    (function() {
        var stored = localStorage.getItem('theme-preference');
        var theme = stored || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        document.documentElement.setAttribute('data-theme', theme);
        if (theme === 'dark') document.documentElement.classList.add('dark-mode');
    })();
    </script>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#main-content">
    <?php esc_html_e( 'Skip to content', 'mrmurphy' ); ?>
</a>

<div class="site">
    <header class="site-header" role="banner">
        <div class="site-header__inner">
            <?php
            $profile_avatar = get_theme_mod( 'mrmurphy_profile_avatar' );
            $profile_name = get_theme_mod( 'mrmurphy_profile_name', get_bloginfo( 'name' ) );
            ?>
            <div class="nav-pill">
                <div class="nav-pill__top">
                    <!-- Left: Avatar -->
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-pill__avatar-link" rel="home">
                        <?php if ( $profile_avatar ) : ?>
                            <div class="nav-pill__avatar">
                                <img src="<?php echo esc_url( $profile_avatar ); ?>" alt="<?php echo esc_attr( $profile_name ); ?>">
                            </div>
                        <?php elseif ( has_custom_logo() ) : ?>
                            <div class="nav-pill__avatar">
                                <?php
                                $logo_id = get_theme_mod( 'custom_logo' );
                                $logo = wp_get_attachment_image_src( $logo_id, 'full' );
                                if ( $logo ) :
                                ?>
                                    <img src="<?php echo esc_url( $logo[0] ); ?>" alt="<?php echo esc_attr( $profile_name ); ?>">
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </a>

                    <!-- Center: Name -->
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="nav-pill__name" rel="home">
                        <?php echo esc_html( $profile_name ); ?>
                    </a>

                    <!-- Right: Actions -->
                    <div class="nav-pill__actions">
                        <button class="header-search__toggle" aria-label="<?php esc_attr_e( 'Search', 'mrmurphy' ); ?>" data-search-toggle>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <path d="m21 21-4.3-4.3"></path>
                            </svg>
                        </button>
                        <button class="theme-toggle" aria-label="<?php esc_attr_e( 'Toggle color scheme', 'mrmurphy' ); ?>" data-theme-toggle>
                            <svg class="theme-toggle__icon theme-toggle__icon--light" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="5"></circle>
                                <line x1="12" y1="1" x2="12" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="23"></line>
                                <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line>
                                <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line>
                                <line x1="1" y1="12" x2="3" y2="12"></line>
                                <line x1="21" y1="12" x2="23" y2="12"></line>
                                <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line>
                                <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line>
                            </svg>
                            <svg class="theme-toggle__icon theme-toggle__icon--dark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
                            </svg>
                        </button>
                        <button class="menu-toggle" aria-label="<?php esc_attr_e( 'Menu', 'mrmurphy' ); ?>" aria-expanded="false" data-menu-toggle>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="4" x2="20" y1="12" y2="12"></line>
                                <line x1="4" x2="20" y1="6" y2="6"></line>
                                <line x1="4" x2="20" y1="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <?php get_template_part( 'template-parts/mega-menu' ); ?>
            </div>
        </div>
    </header>

    <!-- Search Modal -->
    <div class="search-modal" id="search-modal" aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e( 'Search', 'mrmurphy' ); ?>">
        <div class="search-modal__overlay" data-search-close></div>
        <div class="search-modal__content">
            <button class="search-modal__close" aria-label="<?php esc_attr_e( 'Close search', 'mrmurphy' ); ?>" data-search-close>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 6 6 18"></path>
                    <path d="m6 6 12 12"></path>
                </svg>
            </button>
            <div class="search-modal__form-wrapper">
                <?php get_search_form(); ?>
            </div>
        </div>
    </div>

    <main id="main-content" class="site-main" role="main">
