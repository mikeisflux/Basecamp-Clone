<?php
/**
 * The main template file - Homepage
 *
 * @package ProjectFOB_Theme
 */

get_header();
?>

<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="hero-content">
            <h1 class="hero-title">Every Great Plan Deploys from the FOB</h1>
            <p class="hero-subtitle">Complete project management and team collaboration platform. Get started with ProjectFOB today.</p>
            <div class="hero-cta">
                <?php if ( projectfob_plugin_is_active() ) : ?>
                    <a href="<?php echo esc_url( projectfob_get_pricing_url() ); ?>" class="btn btn-primary btn-large">View Pricing</a>
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( projectfob_get_app_url() ); ?>" class="btn btn-secondary btn-large">Go to Dashboard</a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( wp_login_url( projectfob_get_app_url() ) ); ?>" class="btn btn-secondary btn-large">Sign In</a>
                    <?php endif; ?>
                <?php else : ?>
                    <p class="warning-message">ProjectFOB plugin is not activated. Please activate the plugin to use this theme.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="features-section py-5">
    <div class="container">
        <h2 class="text-center mb-4">Everything You Need to Manage Projects</h2>

        <div class="features-grid">
            <div class="feature-card card">
                <div class="feature-icon">📋</div>
                <h3>Projects & Tasks</h3>
                <p>Create projects, assign tasks, set deadlines, and track progress all in one place.</p>
            </div>

            <div class="feature-card card">
                <div class="feature-icon">💬</div>
                <h3>Team Communication</h3>
                <p>Message boards, real-time chat, and comments keep your team connected.</p>
            </div>

            <div class="feature-card card">
                <div class="feature-icon">📁</div>
                <h3>Document Management</h3>
                <p>Store and organize files, documents, and assets with cloud storage.</p>
            </div>

            <div class="feature-card card">
                <div class="feature-icon">📅</div>
                <h3>Calendar & Schedule</h3>
                <p>Track events, milestones, and deadlines with integrated calendar.</p>
            </div>

            <div class="feature-card card">
                <div class="feature-icon">📊</div>
                <h3>Analytics & Reports</h3>
                <p>Gain insights into team productivity and project performance.</p>
            </div>

            <div class="feature-card card">
                <div class="feature-icon">🔔</div>
                <h3>Notifications</h3>
                <p>Stay updated with real-time notifications and email digests.</p>
            </div>
        </div>
    </div>
</section>

<!-- Pricing CTA Section -->
<section class="cta-section py-5">
    <div class="container text-center">
        <h2 class="mb-3">Ready to Get Started?</h2>
        <p class="mb-4">Choose the perfect plan for your team</p>
        <?php if ( projectfob_plugin_is_active() ) : ?>
            <a href="<?php echo esc_url( projectfob_get_pricing_url() ); ?>" class="btn btn-primary btn-large">See Pricing Plans</a>
        <?php endif; ?>
    </div>
</section>

<?php if ( have_posts() ) : ?>
    <!-- Blog Posts Section -->
    <section class="blog-section py-5">
        <div class="container">
            <h2 class="text-center mb-4">Latest Updates</h2>
            <div class="posts-grid">
                <?php
                while ( have_posts() ) :
                    the_post();
                    ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card card' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="post-thumbnail">
                                <?php the_post_thumbnail( 'medium' ); ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-content">
                            <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                            <div class="post-meta">
                                <span class="post-date"><?php echo get_the_date(); ?></span>
                                <span class="post-author">by <?php the_author(); ?></span>
                            </div>
                            <div class="post-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                            <a href="<?php the_permalink(); ?>" class="read-more">Read More →</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php
            the_posts_pagination( array(
                'mid_size'  => 2,
                'prev_text' => __( '← Previous', 'projectfob-theme' ),
                'next_text' => __( 'Next →', 'projectfob-theme' ),
            ) );
            ?>
        </div>
    </section>
<?php endif; ?>

<?php
get_footer();
