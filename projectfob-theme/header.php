<?php
/**
 * The header template
 *
 * @package ProjectFOB_Theme
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container">
        <div class="header-inner">
            <div class="site-branding">
                <?php
                if ( has_custom_logo() ) {
                    the_custom_logo();
                } else {
                    ?>
                    <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-title">
                        <?php bloginfo( 'name' ); ?>
                    </a>
                    <?php
                }
                ?>
            </div>

            <nav class="main-navigation">
                <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'menu_class'     => 'nav-menu',
                    'container'      => false,
                    'fallback_cb'    => 'projectfob_default_menu',
                ) );
                ?>
            </nav>

            <div class="header-actions">
                <?php if ( projectfob_plugin_is_active() ) : ?>
                    <?php if ( is_user_logged_in() ) : ?>
                        <a href="<?php echo esc_url( projectfob_get_app_url() ); ?>" class="btn btn-primary">Dashboard</a>
                    <?php else : ?>
                        <a href="<?php echo esc_url( wp_login_url( projectfob_get_app_url() ) ); ?>" class="btn btn-secondary">Sign In</a>
                        <a href="<?php echo esc_url( projectfob_get_signup_url() ); ?>" class="btn btn-primary">Get Started</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <button class="mobile-menu-toggle" aria-label="Toggle Menu">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>
    </div>
</header>

<main id="main" class="site-main">
