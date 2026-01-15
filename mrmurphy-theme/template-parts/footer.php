<?php
/**
 * Site footer template part
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;
?>
    </main><!-- #main-content -->

    <footer class="site-footer" role="contentinfo">
        <div class="site-footer__inner container container--full">
            <?php if ( has_nav_menu( 'footer' ) || has_nav_menu( 'social' ) ) : ?>
            <div class="footer-content">
                <?php if ( has_nav_menu( 'footer' ) ) : ?>
                    <nav class="footer-nav" aria-label="<?php esc_attr_e( 'Footer menu', 'mrmurphy' ); ?>">
                        <?php
                        wp_nav_menu( array(
                            'theme_location' => 'footer',
                            'menu_class'     => '',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ) );
                        ?>
                    </nav>
                <?php endif; ?>

                <?php if ( has_nav_menu( 'social' ) ) : ?>
                    <nav class="social-links" aria-label="<?php esc_attr_e( 'Social links', 'mrmurphy' ); ?>">
                        <?php
                        wp_nav_menu( array(
                            'theme_location' => 'social',
                            'menu_class'     => '',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                            'link_before'    => '<span class="screen-reader-text">',
                            'link_after'     => '</span>',
                        ) );
                        ?>
                    </nav>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="site-footer__copyright">
                <p>
                    &copy; <?php echo esc_html( date( 'Y' ) ); ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                </p>
            </div>
        </div>
    </footer>

</div><!-- .site -->

<?php wp_footer(); ?>

</body>
</html>
