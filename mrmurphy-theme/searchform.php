<?php
/**
 * Custom search form template
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

$unique_id = wp_unique_id( 'search-form-' );
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
    <label for="<?php echo esc_attr( $unique_id ); ?>" class="screen-reader-text">
        <?php esc_html_e( 'Search for:', 'mrmurphy' ); ?>
    </label>

    <input
        type="search"
        id="<?php echo esc_attr( $unique_id ); ?>"
        class="search-form__input"
        placeholder="<?php esc_attr_e( 'Search&hellip;', 'mrmurphy' ); ?>"
        value="<?php echo get_search_query(); ?>"
        name="s"
    />

    <button type="submit" class="search-form__submit btn btn--primary">
        <span class="screen-reader-text"><?php esc_html_e( 'Search', 'mrmurphy' ); ?></span>
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
        </svg>
    </button>
</form>
