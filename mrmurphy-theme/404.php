<?php
/**
 * 404 (Not Found) template
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );
?>

<div class="error-404">
    <div class="container" style="text-align: center; padding-top: var(--space-16); padding-bottom: var(--space-16);">
        <h1 class="page-title" style="font-size: var(--font-size-4xl);">
            <?php esc_html_e( '404', 'mrmurphy' ); ?>
        </h1>

        <p class="page-description" style="font-size: var(--font-size-xl); margin-bottom: var(--space-8);">
            <?php esc_html_e( 'Oops! That page can\'t be found.', 'mrmurphy' ); ?>
        </p>

        <p style="color: var(--color-fg-muted); margin-bottom: var(--space-8);">
            <?php esc_html_e( 'It looks like nothing was found at this location. Maybe try a search?', 'mrmurphy' ); ?>
        </p>

        <div style="max-width: 400px; margin: 0 auto var(--space-8);">
            <?php get_search_form(); ?>
        </div>

        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn--primary">
            <?php esc_html_e( 'Go Home', 'mrmurphy' ); ?>
        </a>
    </div>
</div>

<?php get_template_part( 'template-parts/footer' ); ?>
