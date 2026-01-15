<?php
/**
 * MrMurphy Theme functions and definitions
 *
 * @package MrMurphy
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

define( 'MRMURPHY_VERSION', '1.0.2' );
define( 'MRMURPHY_DIR', get_template_directory() );
define( 'MRMURPHY_URI', get_template_directory_uri() );

// Theme setup and supports
require_once MRMURPHY_DIR . '/inc/setup.php';

// Asset enqueueing
require_once MRMURPHY_DIR . '/inc/enqueue.php';

// Custom post types
require_once MRMURPHY_DIR . '/inc/custom-post-types.php';

// Customizer settings
require_once MRMURPHY_DIR . '/inc/customizer.php';

// Template helper functions
require_once MRMURPHY_DIR . '/inc/template-functions.php';

// Mega menu walker
require_once MRMURPHY_DIR . '/inc/mega-menu-walker.php';

/**
 * Filter navigation menu items to remove "coming soon" links
 */
function mrmurphy_filter_menu_items( $items, $args ) {
    if ( ! isset( $args->theme_location ) || 'primary' !== $args->theme_location ) {
        return $items;
    }

    foreach ( $items as $key => $item ) {
        $title_lower = strtolower( $item->title );
        // Remove items with "coming soon" in title or URL
        if ( strpos( $title_lower, 'coming soon' ) !== false || 
             strpos( $item->url, 'coming-soon' ) !== false ||
             strpos( $item->url, '#coming-soon' ) !== false ) {
            unset( $items[ $key ] );
        }
    }

    return $items;
}
add_filter( 'wp_nav_menu_objects', 'mrmurphy_filter_menu_items', 10, 2 );

/**
 * Customize comment form fields
 */
function mrmurphy_comment_form_fields( $fields ) {
    // Get current user email if logged in
    $commenter = wp_get_current_commenter();
    $req = get_option( 'require_name_email' );
    $aria_req = ( $req ? " aria-required='true'" : '' );
    
    // Email field (required, first, full width)
    $fields['email'] = '<p class="comment-form-email form__group"><label for="email" class="form__label">' . esc_html__( 'Email', 'mrmurphy' ) . ' <span class="required">*</span></label><input id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" maxlength="100" required="required" aria-describedby="email-desc"' . $aria_req . ' class="form__input" /></p>';
    
    // Name field (optional, second row, left column) - wrapped in container
    $fields['author'] = '<div class="comment-form-fields-row"><p class="comment-form-author form__group"><label for="author" class="form__label">' . esc_html__( 'Name', 'mrmurphy' ) . ' <span class="optional">(' . esc_html__( 'optional', 'mrmurphy' ) . ')</span></label><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245" class="form__input" /></p>';
    
    // Website field (optional, second row, right column) - close container after this
    $fields['url'] = '<p class="comment-form-url form__group"><label for="url" class="form__label">' . esc_html__( 'Website', 'mrmurphy' ) . ' <span class="optional">(' . esc_html__( 'optional', 'mrmurphy' ) . ')</span></label><input id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="200" class="form__input" /></p></div>';
    
    // Reorder fields: email first, then author and url
    // Remove cookies field - it's added to submit button area instead
    $ordered_fields = array();
    $ordered_fields['email'] = $fields['email'];
    $ordered_fields['author'] = $fields['author'];
    $ordered_fields['url'] = $fields['url'];
    
    return $ordered_fields;
}
add_filter( 'comment_form_default_fields', 'mrmurphy_comment_form_fields', 10 );

/**
 * Customize comment form defaults
 */
function mrmurphy_comment_form_defaults( $defaults ) {
    // Change "Save my name..." to "Remember me"
    $defaults['comment_notes_before'] = '<p class="comment-notes">' . esc_html__( 'Your email address will not be published. Required fields are marked *', 'mrmurphy' ) . '</p>';
    $defaults['label_submit'] = esc_html__( 'Post Comment', 'mrmurphy' );
    
    // Wrap submit button in a container
    $defaults['submit_field'] = '<p class="form-submit">%1$s %2$s</p>';
    
    return $defaults;
}
add_filter( 'comment_form_defaults', 'mrmurphy_comment_form_defaults' );

/**
 * Set posts per page to 10
 */
function mrmurphy_posts_per_page( $query ) {
    if ( ! is_admin() && $query->is_main_query() ) {
        if ( is_home() || is_archive() || is_search() ) {
            $query->set( 'posts_per_page', 10 );
        }
    }
}
add_action( 'pre_get_posts', 'mrmurphy_posts_per_page' );

/**
 * Move cookies consent to submit button area
 */
function mrmurphy_comment_form_submit_button( $submit_button, $args ) {
    // Create cookies consent checkbox HTML
    $commenter = wp_get_current_commenter();
    $consent = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';
    $cookies_html = '<p class="comment-form-cookies-consent"><label for="wp-comment-cookies-consent" class="form__label form__label--checkbox"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' class="form__checkbox" />' . esc_html__( 'Remember me', 'mrmurphy' ) . '</label></p>';
    
    return '<div class="comment-form-submit-row">' . $submit_button . $cookies_html . '</div>';
}
add_filter( 'comment_form_submit_button', 'mrmurphy_comment_form_submit_button', 10, 2 );

/**
 * Custom comment callback to restructure comment HTML
 */
function mrmurphy_comment_callback( $comment, $args, $depth ) {
    if ( 'div' === $args['style'] ) {
        $tag       = 'div';
        $add_below = 'comment';
    } else {
        $tag       = 'li';
        $add_below = 'div-comment';
    }

    $commenter = wp_get_current_commenter();
    $show_pending_links = isset( $commenter['comment_author'] ) && $commenter['comment_author'];
    
    if ( 'comment' === $comment->comment_type || '' === $comment->comment_type ) {
        ?>
        <<?php echo $tag; ?> <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?> id="comment-<?php comment_ID(); ?>">
        <?php if ( 'div' !== $args['style'] ) : ?>
        <div id="div-comment-<?php comment_ID(); ?>" class="comment-body">
        <?php endif; ?>
        
        <div class="comment__layout">
            <div class="comment__avatar">
                <?php
                if ( 0 != $args['avatar_size'] ) {
                    echo get_avatar( $comment, $args['avatar_size'] );
                }
                ?>
            </div>
            
            <div class="comment__header">
                <div class="comment__author">
                    <?php
                    $comment_author = get_comment_author_link( $comment );
                    if ( '0' == $comment->comment_approved && ! $show_pending_links ) {
                        echo $comment_author;
                    } else {
                        printf( '<cite class="fn">%s</cite>', $comment_author );
                    }
                    ?>
                </div>
                
                <div class="comment__meta">
                    <a href="<?php echo esc_url( get_comment_link( $comment, $args ) ); ?>">
                        <time datetime="<?php comment_time( 'c' ); ?>">
                            <?php
                            printf(
                                /* translators: 1: Comment date, 2: Comment time */
                                esc_html__( '%1$s at %2$s', 'mrmurphy' ),
                                get_comment_date( '', $comment ),
                                get_comment_time()
                            );
                            ?>
                        </time>
                    </a>
                    <?php edit_comment_link( esc_html__( '(Edit)', 'mrmurphy' ), '<span class="edit-link">', '</span>' ); ?>
                </div>
            </div>
            
            <?php if ( '0' == $comment->comment_approved ) : ?>
            <em class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'mrmurphy' ); ?></em>
            <?php endif; ?>
            
            <div class="comment__content">
                <?php comment_text(); ?>
            </div>
            
            <div class="comment__footer">
                <?php
                comment_reply_link(
                    array_merge(
                        $args,
                        array(
                            'add_below' => $add_below,
                            'depth'     => $depth,
                            'max_depth' => $args['max_depth'],
                            'before'    => '<div class="reply">',
                            'after'     => '</div>',
                        )
                    )
                );
                ?>
            </div>
        </div>
        
        <?php if ( 'div' !== $args['style'] ) : ?>
        </div>
        <?php endif; ?>
        <?php
    }
}


