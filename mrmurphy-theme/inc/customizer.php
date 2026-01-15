<?php
/**
 * Theme Customizer settings
 *
 * @package MrMurphy
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add customizer settings and controls.
 */
function mrmurphy_customize_register( $wp_customize ) {

    // Add MrMurphy Theme panel
    $wp_customize->add_panel( 'mrmurphy_panel', array(
        'title'       => __( 'MrMurphy Theme', 'mrmurphy' ),
        'description' => __( 'Customize the MrMurphy theme settings.', 'mrmurphy' ),
        'priority'    => 30,
    ) );

    // ========== Profile Section ==========
    $wp_customize->add_section( 'mrmurphy_profile', array(
        'title'    => __( 'Profile', 'mrmurphy' ),
        'panel'    => 'mrmurphy_panel',
        'priority' => 10,
    ) );

    // Profile Avatar (Navigation)
    $wp_customize->add_setting( 'mrmurphy_profile_avatar', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'mrmurphy_profile_avatar', array(
        'label'    => __( 'Navigation Avatar', 'mrmurphy' ),
        'section'  => 'mrmurphy_profile',
        'settings' => 'mrmurphy_profile_avatar',
        'description' => __( 'Avatar shown in the navigation menu.', 'mrmurphy' ),
    ) ) );

    // Profile Avatar (Home Page)
    $wp_customize->add_setting( 'mrmurphy_profile_avatar_home', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'mrmurphy_profile_avatar_home', array(
        'label'    => __( 'Home Page Avatar', 'mrmurphy' ),
        'section'  => 'mrmurphy_profile',
        'settings' => 'mrmurphy_profile_avatar_home',
        'description' => __( 'Avatar shown on the home page bio section. If not set, will use the navigation avatar.', 'mrmurphy' ),
    ) ) );

    // Profile Name
    $wp_customize->add_setting( 'mrmurphy_profile_name', array(
        'default'           => get_bloginfo( 'name' ),
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_profile_name', array(
        'type'     => 'text',
        'label'    => __( 'Name', 'mrmurphy' ),
        'section'  => 'mrmurphy_profile',
        'settings' => 'mrmurphy_profile_name',
    ) );

    // Profile Title
    $wp_customize->add_setting( 'mrmurphy_profile_title', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_profile_title', array(
        'type'        => 'text',
        'label'       => __( 'Title / Role', 'mrmurphy' ),
        'section'     => 'mrmurphy_profile',
        'settings'    => 'mrmurphy_profile_title',
        'description' => __( 'e.g., "Software Engineer"', 'mrmurphy' ),
    ) );

    // Profile Location
    $wp_customize->add_setting( 'mrmurphy_profile_location', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_profile_location', array(
        'type'        => 'text',
        'label'       => __( 'Location', 'mrmurphy' ),
        'section'     => 'mrmurphy_profile',
        'settings'    => 'mrmurphy_profile_location',
        'description' => __( 'e.g., "Oahu, Hawaii"', 'mrmurphy' ),
    ) );

    // Profile Employment Status
    $wp_customize->add_setting( 'mrmurphy_profile_employment_status', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_profile_employment_status', array(
        'type'        => 'text',
        'label'       => __( 'Employment Status', 'mrmurphy' ),
        'section'     => 'mrmurphy_profile',
        'settings'    => 'mrmurphy_profile_employment_status',
        'description' => __( 'e.g., "Employed", "Available for hire"', 'mrmurphy' ),
    ) );

    // Profile Company Name
    $wp_customize->add_setting( 'mrmurphy_profile_company', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_profile_company', array(
        'type'        => 'text',
        'label'       => __( 'Company Name', 'mrmurphy' ),
        'section'     => 'mrmurphy_profile',
        'settings'    => 'mrmurphy_profile_company',
        'description' => __( 'e.g., "Automattic"', 'mrmurphy' ),
    ) );

    // Profile Company URL
    $wp_customize->add_setting( 'mrmurphy_profile_company_url', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
    ) );

    $wp_customize->add_control( 'mrmurphy_profile_company_url', array(
        'type'        => 'url',
        'label'       => __( 'Company URL', 'mrmurphy' ),
        'section'     => 'mrmurphy_profile',
        'settings'    => 'mrmurphy_profile_company_url',
        'description' => __( 'e.g., "https://automattic.com"', 'mrmurphy' ),
    ) );

    // Profile Bio
    $wp_customize->add_setting( 'mrmurphy_profile_bio', array(
        'default'           => get_bloginfo( 'description' ),
        'sanitize_callback' => 'wp_kses_post',
    ) );

    $wp_customize->add_control( 'mrmurphy_profile_bio', array(
        'type'     => 'textarea',
        'label'    => __( 'Bio', 'mrmurphy' ),
        'section'  => 'mrmurphy_profile',
        'settings' => 'mrmurphy_profile_bio',
    ) );

    // ========== Forms Section ==========
    $wp_customize->add_section( 'mrmurphy_forms', array(
        'title'       => __( 'Forms', 'mrmurphy' ),
        'description' => __( 'Add shortcodes from your preferred form plugins.', 'mrmurphy' ),
        'panel'       => 'mrmurphy_panel',
        'priority'    => 20,
    ) );

    // Contact Form Shortcode
    $wp_customize->add_setting( 'mrmurphy_contact_form', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_contact_form', array(
        'type'        => 'text',
        'label'       => __( 'Contact Form Shortcode', 'mrmurphy' ),
        'section'     => 'mrmurphy_forms',
        'settings'    => 'mrmurphy_contact_form',
        'description' => __( 'e.g., [contact-form-7 id="123"]', 'mrmurphy' ),
    ) );

    // Newsletter Form Shortcode
    $wp_customize->add_setting( 'mrmurphy_newsletter_form', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_newsletter_form', array(
        'type'        => 'text',
        'label'       => __( 'Newsletter Form Shortcode', 'mrmurphy' ),
        'section'     => 'mrmurphy_forms',
        'settings'    => 'mrmurphy_newsletter_form',
        'description' => __( 'e.g., [mailchimp_signup]', 'mrmurphy' ),
    ) );

    // ========== Tools Section ==========
    $wp_customize->add_section( 'mrmurphy_tools', array(
        'title'       => __( 'Tools & Stack', 'mrmurphy' ),
        'description' => __( 'Add tools to display on the front page. Enter one tool per line in format: name|icon_url|link_url', 'mrmurphy' ),
        'panel'       => 'mrmurphy_panel',
        'priority'    => 30,
    ) );

    // Tools List
    $wp_customize->add_setting( 'mrmurphy_tools_list', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_tools_list', array(
        'type'        => 'textarea',
        'label'       => __( 'Tools List', 'mrmurphy' ),
        'section'     => 'mrmurphy_tools',
        'settings'    => 'mrmurphy_tools_list',
        'description' => __( 'One per line: Tool Name|https://icon-url.png|https://tool-url.com', 'mrmurphy' ),
    ) );

    // ========== Work Experience Section ==========
    $wp_customize->add_section( 'mrmurphy_work_experience', array(
        'title'       => __( 'Work Experience', 'mrmurphy' ),
        'description' => __( 'Add work experience entries. Enter one per line in format: years|company|role|url (URL is optional)', 'mrmurphy' ),
        'panel'       => 'mrmurphy_panel',
        'priority'    => 35,
    ) );

    // Work Experience List
    $wp_customize->add_setting( 'mrmurphy_work_experience_list', array(
        'default'           => '',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );

    $wp_customize->add_control( 'mrmurphy_work_experience_list', array(
        'type'        => 'textarea',
        'label'       => __( 'Work Experience', 'mrmurphy' ),
        'section'     => 'mrmurphy_work_experience',
        'settings'    => 'mrmurphy_work_experience_list',
        'description' => __( 'One per line: Years|Company|Role|URL (URL is optional, e.g., "2014 – Present|Day One [Automattic]|Engineer, Architect, & Team Lead|https://dayoneapp.com")', 'mrmurphy' ),
    ) );

    // ========== Social Links Section ==========
    $wp_customize->add_section( 'mrmurphy_social', array(
        'title'    => __( 'Social Links', 'mrmurphy' ),
        'panel'    => 'mrmurphy_panel',
        'priority' => 40,
    ) );

    $social_platforms = array(
        'github'    => __( 'GitHub URL', 'mrmurphy' ),
        'twitter'   => __( 'Twitter/X URL', 'mrmurphy' ),
        'linkedin'  => __( 'LinkedIn URL', 'mrmurphy' ),
        'bluesky'   => __( 'Bluesky URL', 'mrmurphy' ),
        'mastodon'  => __( 'Mastodon URL', 'mrmurphy' ),
        'instagram' => __( 'Instagram URL', 'mrmurphy' ),
        'youtube'   => __( 'YouTube URL', 'mrmurphy' ),
    );

    foreach ( $social_platforms as $platform => $label ) {
        $wp_customize->add_setting( "mrmurphy_social_{$platform}", array(
            'default'           => '',
            'sanitize_callback' => 'esc_url_raw',
        ) );

        $wp_customize->add_control( "mrmurphy_social_{$platform}", array(
            'type'     => 'url',
            'label'    => $label,
            'section'  => 'mrmurphy_social',
            'settings' => "mrmurphy_social_{$platform}",
        ) );
    }
}
add_action( 'customize_register', 'mrmurphy_customize_register' );

/**
 * Render Customizer CSS.
 */
function mrmurphy_customizer_css() {
    // Any custom CSS based on customizer settings can go here
}
add_action( 'wp_head', 'mrmurphy_customizer_css' );
