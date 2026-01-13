<?php
/**
 * Comments template
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password,
 * return early without loading the comments.
 */
if ( post_password_required() ) {
    return;
}
?>

<div id="comments" class="comments-area" style="margin-top: var(--space-12);">

    <?php if ( have_comments() ) : ?>
        <h2 class="comments-title">
            <?php
            $comment_count = get_comments_number();
            if ( '1' === $comment_count ) {
                printf(
                    /* translators: %s: Post title */
                    esc_html__( 'One comment on &ldquo;%s&rdquo;', 'mrmurphy' ),
                    '<span>' . wp_kses_post( get_the_title() ) . '</span>'
                );
            } else {
                printf(
                    /* translators: 1: Comment count number, 2: Post title */
                    esc_html( _nx( '%1$s comment on &ldquo;%2$s&rdquo;', '%1$s comments on &ldquo;%2$s&rdquo;', $comment_count, 'comments title', 'mrmurphy' ) ),
                    number_format_i18n( $comment_count ),
                    '<span>' . wp_kses_post( get_the_title() ) . '</span>'
                );
            }
            ?>
        </h2>

        <?php the_comments_navigation(); ?>

        <ol class="comment-list">
            <?php
            wp_list_comments( array(
                'style'       => 'ol',
                'short_ping'  => true,
                'avatar_size' => 60,
            ) );
            ?>
        </ol>

        <?php
        the_comments_navigation();

        if ( ! comments_open() ) :
        ?>
            <p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'mrmurphy' ); ?></p>
        <?php endif; ?>

    <?php endif; ?>

    <?php
    comment_form( array(
        'class_form'    => 'comment-form form',
        'class_submit'  => 'btn btn--primary',
        'title_reply'   => esc_html__( 'Leave a Comment', 'mrmurphy' ),
        'comment_field' => '<p class="comment-form-comment form__group"><label for="comment" class="form__label">' . esc_html__( 'Comment', 'mrmurphy' ) . '</label><textarea id="comment" name="comment" class="form__textarea" cols="45" rows="8" maxlength="65525" required="required"></textarea></p>',
    ) );
    ?>

</div>
