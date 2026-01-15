<?php
/**
 * Front page template
 *
 * @package MrMurphy
 */

get_template_part( 'template-parts/header' );

// Get customizer values
$profile_avatar = get_theme_mod( 'mrmurphy_profile_avatar' );
$profile_avatar_home = get_theme_mod( 'mrmurphy_profile_avatar_home', '' );
// Use home page avatar if set, otherwise fall back to navigation avatar
$profile_avatar_display = ! empty( $profile_avatar_home ) ? $profile_avatar_home : $profile_avatar;
$profile_name = get_theme_mod( 'mrmurphy_profile_name', get_bloginfo( 'name' ) );
$profile_title = get_theme_mod( 'mrmurphy_profile_title', '' );
$profile_location = get_theme_mod( 'mrmurphy_profile_location', '' );
$profile_employment_status = get_theme_mod( 'mrmurphy_profile_employment_status', '' );
$profile_company = get_theme_mod( 'mrmurphy_profile_company', '' );
$profile_company_url = get_theme_mod( 'mrmurphy_profile_company_url', '' );
$profile_bio = get_theme_mod( 'mrmurphy_profile_bio', get_bloginfo( 'description' ) );
$contact_form_shortcode = get_theme_mod( 'mrmurphy_contact_form', '' );
$newsletter_shortcode = get_theme_mod( 'mrmurphy_newsletter_form', '' );
?>

<div class="front-page">
    <!-- Hero / Profile Section -->
    <section class="section section--hero" aria-labelledby="hero-heading">
        <div class="container">
            <div class="profile-card">
                <?php if ( $profile_avatar_display ) : ?>
                    <div class="profile-card__avatar">
                        <img src="<?php echo esc_url( $profile_avatar_display ); ?>" alt="<?php echo esc_attr( $profile_name ); ?>">
                    </div>
                <?php elseif ( function_exists( 'get_avatar_url' ) ) : ?>
                    <div class="profile-card__avatar">
                        <?php echo get_avatar( get_option( 'admin_email' ), 120 ); ?>
                    </div>
                <?php endif; ?>

                <div class="profile-card__content">
                    <h1 id="hero-heading" class="profile-card__name">
                        <?php printf( esc_html__( "%s", 'mrmurphy' ), esc_html( $profile_name ) ); ?>
                    </h1>
                    <?php if ( $profile_title ) : ?>
                        <p class="profile-card__role"><?php echo esc_html( $profile_title ); ?></p>
                    <?php endif; ?>

                    <?php if ( $profile_location || $profile_employment_status ) : ?>
                        <div class="profile-card__meta">
                            <?php if ( $profile_location ) : ?>
                                <span class="profile-card__location">
                                    📍 <?php echo esc_html( $profile_location ); ?>
                                </span>
                            <?php endif; ?>
                            <?php if ( $profile_employment_status ) : ?>
                                <?php if ( $profile_company_url ) : ?>
                                <a href="<?php echo esc_url( $profile_company_url ); ?>" class="profile-card__employment-btn" target="_blank" rel="noopener noreferrer">
                                <?php else : ?>
                                <span class="profile-card__employment-btn">
                                <?php endif; ?>
                                    <span class="profile-card__employment-status">
                                        <?php echo esc_html( $profile_employment_status ); ?>
                                    </span>
                                    <?php if ( $profile_company ) : ?>
                                        <span class="profile-card__employment-divider"></span>
                                        <span class="profile-card__employment-company">
                                            <?php echo esc_html( $profile_company ); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php if ( $profile_company_url ) : ?>
                                </a>
                                <?php else : ?>
                                </span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $profile_bio ) : ?>
                        <div class="profile-card__bio">
                            <?php echo wp_kses_post( wpautop( $profile_bio ) ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-card__actions">
                        <a href="#contact" class="btn btn--primary">
                            <?php esc_html_e( 'Get in Touch', 'mrmurphy' ); ?>
                        </a>
                        <?php if ( get_option( 'page_for_posts' ) ) : ?>
                            <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="btn btn--secondary">
                                <?php esc_html_e( 'Read Blog', 'mrmurphy' ); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Projects Section -->
    <?php
    $projects = new WP_Query( array(
        'post_type'      => 'mrmurphy_project',
        'posts_per_page' => 6,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ) );
    ?>
    <section class="section section--projects" aria-labelledby="projects-heading">
        <div class="container">
            <h2 id="projects-heading" class="section__title">
                <?php esc_html_e( 'Featured Projects', 'mrmurphy' ); ?>
            </h2>
            <?php if ( $projects->have_posts() ) : ?>
            <div class="grid grid--2">
                <?php while ( $projects->have_posts() ) : $projects->the_post(); ?>
                    <article class="project-card card">
                        <?php
                        $project_icon = get_post_meta( get_the_ID(), '_mrmurphy_project_icon', true );
                        $project_url = get_post_meta( get_the_ID(), '_mrmurphy_project_url', true );
                        $project_status = get_post_meta( get_the_ID(), '_mrmurphy_project_status', true );
                        ?>

                        <?php if ( $project_icon ) : ?>
                            <div class="project-card__icon">
                                <img src="<?php echo esc_url( $project_icon ); ?>" alt="">
                            </div>
                        <?php elseif ( has_post_thumbnail() ) : ?>
                            <div class="project-card__icon">
                                <?php the_post_thumbnail( 'mrmurphy-square-md' ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="project-card__content">
                            <h3 class="project-card__title">
                                <?php if ( $project_url ) : ?>
                                    <a href="<?php echo esc_url( $project_url ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php the_title(); ?>
                                    </a>
                                <?php else : ?>
                                    <?php the_title(); ?>
                                <?php endif; ?>
                            </h3>

                            <?php if ( $project_status ) : ?>
                                <span class="project-card__status">
                                    <?php echo esc_html( $project_status ); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ( has_excerpt() ) : ?>
                            <p class="project-card__description">
                                <?php echo esc_html( get_the_excerpt() ); ?>
                            </p>
                        <?php endif; ?>
                    </article>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>
            <?php else : ?>
            <!-- Skeleton placeholder for empty projects -->
            <div class="grid grid--2">
                <?php for ( $i = 0; $i < 4; $i++ ) : ?>
                <div class="skeleton-card">
                    <div class="skeleton-card__icon skeleton"></div>
                    <div class="skeleton-card__content">
                        <div class="skeleton-card__title skeleton"></div>
                        <div class="skeleton-card__text skeleton"></div>
                        <div class="skeleton-card__text skeleton-card__text--short skeleton"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            <div class="skeleton-empty-state">
                <p><?php esc_html_e( 'No projects yet', 'mrmurphy' ); ?></p>
                <p class="skeleton-empty-state__hint"><?php esc_html_e( 'Add projects via Projects in the WordPress admin', 'mrmurphy' ); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Tools & Stack Section -->
    <?php $tools = mrmurphy_get_tools_list(); ?>
    <section class="section section--tools surface-inset" aria-labelledby="tools-heading">
        <div class="container">
            <h2 id="tools-heading" class="section__title">
                <?php esc_html_e( 'Tools & Stack', 'mrmurphy' ); ?>
            </h2>
            <?php if ( ! empty( $tools ) ) : ?>
            <div class="grid grid--4">
                <?php foreach ( $tools as $tool ) : ?>
                    <?php if ( ! empty( $tool['url'] ) ) : ?>
                        <a href="<?php echo esc_url( $tool['url'] ); ?>" class="tool-badge" target="_blank" rel="noopener noreferrer">
                    <?php else : ?>
                        <div class="tool-badge">
                    <?php endif; ?>

                        <?php if ( ! empty( $tool['icon'] ) ) : ?>
                            <div class="tool-badge__icon">
                                <img src="<?php echo esc_url( $tool['icon'] ); ?>" alt="">
                            </div>
                        <?php endif; ?>

                        <span class="tool-badge__name">
                            <?php echo esc_html( $tool['name'] ); ?>
                        </span>

                    <?php if ( ! empty( $tool['url'] ) ) : ?>
                        </a>
                    <?php else : ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <!-- Skeleton placeholder for empty tools -->
            <div class="grid grid--4">
                <?php for ( $i = 0; $i < 8; $i++ ) : ?>
                <div class="skeleton-badge">
                    <div class="skeleton-badge__icon skeleton"></div>
                    <div class="skeleton-badge__name skeleton"></div>
                </div>
                <?php endfor; ?>
            </div>
            <div class="skeleton-empty-state">
                <p><?php esc_html_e( 'No tools added yet', 'mrmurphy' ); ?></p>
                <p class="skeleton-empty-state__hint"><?php esc_html_e( 'Add tools in Appearance > Customize > MrMurphy Theme', 'mrmurphy' ); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Latest Posts Section -->
    <?php
    $latest_posts = new WP_Query( array(
        'posts_per_page'      => 3,
        'post_status'         => 'publish',
        'ignore_sticky_posts' => true,
    ) );

    if ( $latest_posts->have_posts() ) :
    ?>
    <section class="section section--posts" aria-labelledby="posts-heading">
        <div class="container">
            <h2 id="posts-heading" class="section__title">
                <?php esc_html_e( 'Latest Posts', 'mrmurphy' ); ?>
            </h2>
            <div class="posts-list">
                <?php while ( $latest_posts->have_posts() ) : $latest_posts->the_post(); ?>
                    <?php get_template_part( 'template-parts/content/content', 'excerpt' ); ?>
                <?php endwhile; ?>
                <?php wp_reset_postdata(); ?>
            </div>

            <?php if ( get_option( 'page_for_posts' ) ) : ?>
                <div style="text-align: center; margin-top: var(--space-8);">
                    <a href="<?php echo esc_url( get_permalink( get_option( 'page_for_posts' ) ) ); ?>" class="btn btn--secondary">
                        <?php esc_html_e( 'View All Posts', 'mrmurphy' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Work Experience Section -->
    <?php $work_experience = mrmurphy_get_work_experience_list(); ?>
    <section class="section section--work-experience surface-inset" aria-labelledby="work-experience-heading">
        <div class="container">
            <h2 id="work-experience-heading" class="section__title">
                <?php esc_html_e( 'Work Experience', 'mrmurphy' ); ?>
            </h2>
            <?php if ( ! empty( $work_experience ) ) : ?>
            <div class="work-experience-list">
                <?php foreach ( $work_experience as $job ) : ?>
                    <div class="work-experience-item">
                        <div class="work-experience__years">
                            <?php echo esc_html( $job['years'] ); ?>
                        </div>
                        <div class="work-experience__details">
                            <h4 class="work-experience__title">
                                <?php if ( ! empty( $job['url'] ) ) : ?>
                                    <a href="<?php echo esc_url( $job['url'] ); ?>" target="_blank" rel="noopener noreferrer">
                                        <?php echo esc_html( $job['company'] ); ?>
                                    </a>
                                <?php else : ?>
                                    <?php echo esc_html( $job['company'] ); ?>
                                <?php endif; ?>
                                <?php if ( ! empty( $job['role'] ) ) : ?>
                                    <span class="work-experience__role">/ <?php echo esc_html( $job['role'] ); ?></span>
                                <?php endif; ?>
                            </h4>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <!-- Skeleton placeholder for empty work experience -->
            <div class="work-experience-list">
                <?php for ( $i = 0; $i < 4; $i++ ) : ?>
                <div class="skeleton-work">
                    <div class="skeleton-work__years skeleton"></div>
                    <div class="skeleton-work__details">
                        <div class="skeleton-work__title skeleton"></div>
                        <div class="skeleton-work__role skeleton"></div>
                    </div>
                </div>
                <?php endfor; ?>
            </div>
            <div class="skeleton-empty-state">
                <p><?php esc_html_e( 'No work experience added yet', 'mrmurphy' ); ?></p>
                <p class="skeleton-empty-state__hint"><?php esc_html_e( 'Add experience in Appearance > Customize > MrMurphy Theme', 'mrmurphy' ); ?></p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="section section--contact" aria-labelledby="contact-heading">
        <div class="container">
            <h2 id="contact-heading" class="section__title">
                <?php esc_html_e( 'Get in Touch', 'mrmurphy' ); ?>
            </h2>

            <?php if ( $contact_form_shortcode ) : ?>
                <div class="contact-form-wrapper">
                    <?php echo do_shortcode( $contact_form_shortcode ); ?>
                </div>
            <?php else : ?>
                <div class="contact-form-placeholder card">
                    <p><?php esc_html_e( 'Contact form will appear here. Add a shortcode from your preferred form plugin (Contact Form 7, Gravity Forms, etc.) in the Customizer under "MrMurphy Theme > Contact Form".', 'mrmurphy' ); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Newsletter Section -->
    <?php if ( $newsletter_shortcode ) : ?>
    <section class="section section--newsletter" aria-labelledby="newsletter-heading">
        <div class="container">
            <h2 id="newsletter-heading" class="section__title">
                <?php esc_html_e( 'Stay Updated', 'mrmurphy' ); ?>
            </h2>

            <div class="newsletter-form-wrapper">
                <?php echo do_shortcode( $newsletter_shortcode ); ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>

<?php get_template_part( 'template-parts/footer' ); ?>
