<?php
/**
 * Template helper functions
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Calculate reading time for a post.
 *
 * @param int $post_id Post ID. Defaults to current post.
 * @return string Reading time string.
 */
function mrmurphy_reading_time( $post_id = null ) {
    if ( ! $post_id ) {
        $post_id = get_the_ID();
    }

    $content = get_post_field( 'post_content', $post_id );
    $word_count = str_word_count( wp_strip_all_tags( $content ) );
    $reading_time = ceil( $word_count / 200 ); // Assume 200 words per minute

    if ( $reading_time < 1 ) {
        $reading_time = 1;
    }

    return sprintf(
        /* translators: %d: Number of minutes */
        _n( '%d min read', '%d min read', $reading_time, 'mrmurphy' ),
        $reading_time
    );
}

/**
 * Get tools list from customizer.
 *
 * @return array Array of tool data.
 */
function mrmurphy_get_tools_list() {
    $tools_raw = get_theme_mod( 'mrmurphy_tools_list', '' );
    $tools = array();

    if ( empty( $tools_raw ) ) {
        return $tools;
    }

    $lines = explode( "\n", $tools_raw );

    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }

        $parts = explode( '|', $line );

        if ( count( $parts ) >= 1 ) {
            $tools[] = array(
                'name' => trim( $parts[0] ),
                'icon' => isset( $parts[1] ) ? esc_url( trim( $parts[1] ) ) : '',
                'url'  => isset( $parts[2] ) ? esc_url( trim( $parts[2] ) ) : '',
            );
        }
    }

    return $tools;
}

/**
 * Get social links from customizer.
 *
 * @return array Array of social link data.
 */
function mrmurphy_get_social_links() {
    $platforms = array(
        'github'    => array(
            'label' => __( 'GitHub', 'mrmurphy' ),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>',
        ),
        'twitter'   => array(
            'label' => __( 'Twitter', 'mrmurphy' ),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>',
        ),
        'linkedin'  => array(
            'label' => __( 'LinkedIn', 'mrmurphy' ),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        ),
        'bluesky'   => array(
            'label' => __( 'Bluesky', 'mrmurphy' ),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.054-3.968.391-7.5 1.192-7.5 5.166 0 3.974 4.537 6.225 9.493 6.225 4.956 0 9.493-2.251 9.493-6.225 0-3.974-3.532-4.775-7.5-5.166-.14-.014-.277-.032-.415-.054.14.017.279.036.415.056 2.67.296 5.568-.628 6.383-3.364.246-.828.624-5.789.624-6.479 0-.688-.139-1.86-.902-2.203-.659-.299-1.664-.621-4.3 1.24C16.046 4.747 13.087 8.686 12 10.8z"/></svg>',
        ),
        'mastodon'  => array(
            'label' => __( 'Mastodon', 'mrmurphy' ),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M23.268 5.313c-.35-2.578-2.617-4.61-5.304-5.004C17.51.242 15.792 0 11.813 0h-.03c-3.98 0-4.835.242-5.288.309C3.882.692 1.496 2.518.917 5.127.64 6.412.61 7.837.661 9.143c.074 1.874.088 3.745.26 5.611.118 1.24.325 2.47.62 3.68.55 2.237 2.777 4.098 4.96 4.857 2.336.792 4.849.923 7.256.38.265-.061.527-.132.786-.213.585-.184 1.27-.39 1.774-.753a.057.057 0 0 0 .023-.043v-1.809a.052.052 0 0 0-.02-.041.053.053 0 0 0-.046-.01 20.282 20.282 0 0 1-4.709.545c-2.73 0-3.463-1.284-3.674-1.818a5.593 5.593 0 0 1-.319-1.433.053.053 0 0 1 .066-.054c1.517.363 3.072.546 4.632.546.376 0 .75 0 1.125-.01 1.57-.044 3.224-.124 4.768-.422.038-.008.077-.015.11-.024 2.435-.464 4.753-1.92 4.989-5.604.008-.145.03-1.52.03-1.67.002-.512.167-3.63-.024-5.545zm-3.748 9.195h-2.561V8.29c0-1.309-.55-1.976-1.67-1.976-1.23 0-1.846.79-1.846 2.35v3.403h-2.546V8.663c0-1.56-.617-2.35-1.848-2.35-1.112 0-1.668.668-1.668 1.977v6.218H4.822V8.102c0-1.31.337-2.35 1.011-3.12.696-.77 1.608-1.164 2.74-1.164 1.311 0 2.302.5 2.962 1.498l.638 1.06.638-1.06c.66-.999 1.65-1.498 2.96-1.498 1.13 0 2.043.395 2.74 1.164.675.77 1.012 1.81 1.012 3.12z"/></svg>',
        ),
        'instagram' => array(
            'label' => __( 'Instagram', 'mrmurphy' ),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678c-3.405 0-6.162 2.76-6.162 6.162 0 3.405 2.76 6.162 6.162 6.162 3.405 0 6.162-2.76 6.162-6.162 0-3.405-2.76-6.162-6.162-6.162zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405c0 .795-.646 1.44-1.44 1.44-.795 0-1.44-.646-1.44-1.44 0-.794.646-1.439 1.44-1.439.793-.001 1.44.645 1.44 1.439z"/></svg>',
        ),
        'youtube'   => array(
            'label' => __( 'YouTube', 'mrmurphy' ),
            'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>',
        ),
    );

    $links = array();

    foreach ( $platforms as $platform => $data ) {
        $url = get_theme_mod( "mrmurphy_social_{$platform}", '' );
        if ( ! empty( $url ) ) {
            $links[] = array(
                'platform' => $platform,
                'url'      => $url,
                'label'    => $data['label'],
                'icon'     => $data['icon'],
            );
        }
    }

    return $links;
}

/**
 * Display post categories as tags.
 */
function mrmurphy_post_categories() {
    $categories = get_the_category();

    if ( empty( $categories ) ) {
        return;
    }

    echo '<div class="post-categories">';
    foreach ( $categories as $category ) {
        printf(
            '<a href="%s" class="tag">%s</a>',
            esc_url( get_category_link( $category->term_id ) ),
            esc_html( $category->name )
        );
    }
    echo '</div>';
}

/**
 * Display post tags.
 */
function mrmurphy_post_tags() {
    $tags = get_the_tags();

    if ( empty( $tags ) ) {
        return;
    }

    echo '<div class="post-tags">';
    foreach ( $tags as $tag ) {
        printf(
            '<a href="%s" class="tag">%s</a>',
            esc_url( get_tag_link( $tag->term_id ) ),
            esc_html( $tag->name )
        );
    }
    echo '</div>';
}

/**
 * Get work experience list from customizer.
 *
 * @return array Array of work experience data.
 */
function mrmurphy_get_work_experience_list() {
    $experience_raw = get_theme_mod( 'mrmurphy_work_experience_list', '' );
    $experience = array();

    if ( empty( $experience_raw ) ) {
        return $experience;
    }

    $lines = explode( "\n", $experience_raw );

    foreach ( $lines as $line ) {
        $line = trim( $line );
        if ( empty( $line ) ) {
            continue;
        }

        $parts = explode( '|', $line );

        if ( count( $parts ) >= 2 ) {
            $experience[] = array(
                'years'  => trim( $parts[0] ),
                'company' => trim( $parts[1] ),
                'role'   => isset( $parts[2] ) ? trim( $parts[2] ) : '',
                'url'    => isset( $parts[3] ) ? esc_url( trim( $parts[3] ) ) : '',
            );
        }
    }

    return $experience;
}

/**
 * Check if we're on the front page and it should show posts.
 */
function mrmurphy_is_posts_front_page() {
    return is_front_page() && 'posts' === get_option( 'show_on_front' );
}
