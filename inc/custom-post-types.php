<?php
/**
 * Custom Post Types
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register Projects custom post type.
 */
function mrmurphy_register_project_post_type() {
    $labels = array(
        'name'                  => _x( 'Projects', 'Post type general name', 'mrmurphy' ),
        'singular_name'         => _x( 'Project', 'Post type singular name', 'mrmurphy' ),
        'menu_name'             => _x( 'Projects', 'Admin Menu text', 'mrmurphy' ),
        'name_admin_bar'        => _x( 'Project', 'Add New on Toolbar', 'mrmurphy' ),
        'add_new'               => __( 'Add New', 'mrmurphy' ),
        'add_new_item'          => __( 'Add New Project', 'mrmurphy' ),
        'new_item'              => __( 'New Project', 'mrmurphy' ),
        'edit_item'             => __( 'Edit Project', 'mrmurphy' ),
        'view_item'             => __( 'View Project', 'mrmurphy' ),
        'all_items'             => __( 'All Projects', 'mrmurphy' ),
        'search_items'          => __( 'Search Projects', 'mrmurphy' ),
        'parent_item_colon'     => __( 'Parent Projects:', 'mrmurphy' ),
        'not_found'             => __( 'No projects found.', 'mrmurphy' ),
        'not_found_in_trash'    => __( 'No projects found in Trash.', 'mrmurphy' ),
        'featured_image'        => _x( 'Project Icon', 'Overrides the "Featured Image" phrase', 'mrmurphy' ),
        'set_featured_image'    => _x( 'Set project icon', 'Overrides the "Set featured image" phrase', 'mrmurphy' ),
        'remove_featured_image' => _x( 'Remove project icon', 'Overrides the "Remove featured image" phrase', 'mrmurphy' ),
        'use_featured_image'    => _x( 'Use as project icon', 'Overrides the "Use as featured image" phrase', 'mrmurphy' ),
    );

    $args = array(
        'labels'             => $labels,
        'public'             => false,
        'publicly_queryable' => false,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 20,
        'menu_icon'          => 'dashicons-portfolio',
        'supports'           => array( 'title', 'excerpt', 'thumbnail', 'page-attributes' ),
        'show_in_rest'       => true,
    );

    register_post_type( 'mrmurphy_project', $args );
}
add_action( 'init', 'mrmurphy_register_project_post_type' );

/**
 * Add meta boxes for project custom fields.
 */
function mrmurphy_project_meta_boxes() {
    add_meta_box(
        'mrmurphy_project_details',
        __( 'Project Details', 'mrmurphy' ),
        'mrmurphy_project_meta_box_callback',
        'mrmurphy_project',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'mrmurphy_project_meta_boxes' );

/**
 * Project meta box callback.
 */
function mrmurphy_project_meta_box_callback( $post ) {
    wp_nonce_field( 'mrmurphy_project_meta_box', 'mrmurphy_project_meta_box_nonce' );

    $project_url = get_post_meta( $post->ID, '_mrmurphy_project_url', true );
    $project_icon = get_post_meta( $post->ID, '_mrmurphy_project_icon', true );
    $project_status = get_post_meta( $post->ID, '_mrmurphy_project_status', true );
    ?>

    <p>
        <label for="mrmurphy_project_url">
            <strong><?php esc_html_e( 'Project URL', 'mrmurphy' ); ?></strong>
        </label>
        <br>
        <input
            type="url"
            id="mrmurphy_project_url"
            name="mrmurphy_project_url"
            value="<?php echo esc_url( $project_url ); ?>"
            style="width: 100%; max-width: 500px;"
            placeholder="https://example.com"
        >
        <br>
        <span class="description"><?php esc_html_e( 'External link to the project.', 'mrmurphy' ); ?></span>
    </p>

    <p>
        <label for="mrmurphy_project_icon">
            <strong><?php esc_html_e( 'Project Icon URL', 'mrmurphy' ); ?></strong>
        </label>
        <br>
        <input
            type="url"
            id="mrmurphy_project_icon"
            name="mrmurphy_project_icon"
            value="<?php echo esc_url( $project_icon ); ?>"
            style="width: 100%; max-width: 500px;"
            placeholder="https://example.com/icon.png"
        >
        <br>
        <span class="description"><?php esc_html_e( 'URL to the project icon (optional, uses featured image if not set).', 'mrmurphy' ); ?></span>
    </p>

    <p>
        <label for="mrmurphy_project_status">
            <strong><?php esc_html_e( 'Project Status', 'mrmurphy' ); ?></strong>
        </label>
        <br>
        <select id="mrmurphy_project_status" name="mrmurphy_project_status" style="width: 200px;">
            <option value="" <?php selected( $project_status, '' ); ?>><?php esc_html_e( '— None —', 'mrmurphy' ); ?></option>
            <option value="active" <?php selected( $project_status, 'active' ); ?>><?php esc_html_e( 'Active', 'mrmurphy' ); ?></option>
            <option value="in-development" <?php selected( $project_status, 'in-development' ); ?>><?php esc_html_e( 'In Development', 'mrmurphy' ); ?></option>
            <option value="completed" <?php selected( $project_status, 'completed' ); ?>><?php esc_html_e( 'Completed', 'mrmurphy' ); ?></option>
            <option value="archived" <?php selected( $project_status, 'archived' ); ?>><?php esc_html_e( 'Archived', 'mrmurphy' ); ?></option>
        </select>
        <br>
        <span class="description"><?php esc_html_e( 'Current status of the project.', 'mrmurphy' ); ?></span>
    </p>

    <?php
}

/**
 * Save project meta box data.
 */
function mrmurphy_save_project_meta( $post_id ) {
    // Check nonce
    if ( ! isset( $_POST['mrmurphy_project_meta_box_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['mrmurphy_project_meta_box_nonce'], 'mrmurphy_project_meta_box' ) ) {
        return;
    }

    // Check autosave
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check permissions
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save project URL
    if ( isset( $_POST['mrmurphy_project_url'] ) ) {
        update_post_meta( $post_id, '_mrmurphy_project_url', esc_url_raw( $_POST['mrmurphy_project_url'] ) );
    }

    // Save project icon
    if ( isset( $_POST['mrmurphy_project_icon'] ) ) {
        update_post_meta( $post_id, '_mrmurphy_project_icon', esc_url_raw( $_POST['mrmurphy_project_icon'] ) );
    }

    // Save project status
    if ( isset( $_POST['mrmurphy_project_status'] ) ) {
        $valid_statuses = array( '', 'active', 'in-development', 'completed', 'archived' );
        $status = sanitize_text_field( $_POST['mrmurphy_project_status'] );
        if ( in_array( $status, $valid_statuses, true ) ) {
            update_post_meta( $post_id, '_mrmurphy_project_status', $status );
        }
    }
}
add_action( 'save_post_mrmurphy_project', 'mrmurphy_save_project_meta' );

/**
 * Add custom columns to project admin list.
 */
function mrmurphy_project_admin_columns( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[ $key ] = $value;
        if ( 'title' === $key ) {
            $new_columns['project_status'] = __( 'Status', 'mrmurphy' );
            $new_columns['project_url'] = __( 'URL', 'mrmurphy' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_mrmurphy_project_posts_columns', 'mrmurphy_project_admin_columns' );

/**
 * Populate custom columns in project admin list.
 */
function mrmurphy_project_admin_column_content( $column, $post_id ) {
    switch ( $column ) {
        case 'project_status':
            $status = get_post_meta( $post_id, '_mrmurphy_project_status', true );
            if ( $status ) {
                $status_labels = array(
                    'active'         => __( 'Active', 'mrmurphy' ),
                    'in-development' => __( 'In Development', 'mrmurphy' ),
                    'completed'      => __( 'Completed', 'mrmurphy' ),
                    'archived'       => __( 'Archived', 'mrmurphy' ),
                );
                echo esc_html( $status_labels[ $status ] ?? $status );
            } else {
                echo '—';
            }
            break;

        case 'project_url':
            $url = get_post_meta( $post_id, '_mrmurphy_project_url', true );
            if ( $url ) {
                printf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">%s</a>',
                    esc_url( $url ),
                    esc_html__( 'View', 'mrmurphy' )
                );
            } else {
                echo '—';
            }
            break;
    }
}
add_action( 'manage_mrmurphy_project_posts_custom_column', 'mrmurphy_project_admin_column_content', 10, 2 );

/**
 * Make status column sortable.
 */
function mrmurphy_project_sortable_columns( $columns ) {
    $columns['project_status'] = 'project_status';
    return $columns;
}
add_filter( 'manage_edit-mrmurphy_project_sortable_columns', 'mrmurphy_project_sortable_columns' );
