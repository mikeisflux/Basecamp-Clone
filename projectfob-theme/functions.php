<?php
/**
 * ProjectFOB Theme Functions
 *
 * @package ProjectFOB_Theme
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme setup
 */
function projectfob_theme_setup() {
    // Add default posts and comments RSS feed links to head
    add_theme_support( 'automatic-feed-links' );

    // Let WordPress manage the document title
    add_theme_support( 'title-tag' );

    // Enable support for Post Thumbnails
    add_theme_support( 'post-thumbnails' );

    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'projectfob-theme' ),
        'footer'  => __( 'Footer Menu', 'projectfob-theme' ),
    ) );

    // Switch default core markup to output valid HTML5
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );

    // Add theme support for custom logo
    add_theme_support( 'custom-logo', array(
        'height'      => 50,
        'width'       => 200,
        'flex-height' => true,
        'flex-width'  => true,
    ) );

    // Add support for responsive embedded content
    add_theme_support( 'responsive-embeds' );
}
add_action( 'after_setup_theme', 'projectfob_theme_setup' );

/**
 * Enqueue scripts and styles
 */
function projectfob_theme_scripts() {
    // Theme stylesheet
    wp_enqueue_style( 'projectfob-style', get_stylesheet_uri(), array(), '1.0.0' );

    // Custom theme styles
    wp_enqueue_style( 'projectfob-custom', get_template_directory_uri() . '/assets/css/custom.css', array(), '1.0.0' );

    // Theme JavaScript
    wp_enqueue_script( 'projectfob-script', get_template_directory_uri() . '/assets/js/main.js', array( 'jquery' ), '1.0.0', true );

    // Comment reply script
    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'projectfob_theme_scripts' );

/**
 * Register widget areas
 */
function projectfob_widgets_init() {
    register_sidebar( array(
        'name'          => __( 'Sidebar', 'projectfob-theme' ),
        'id'            => 'sidebar-1',
        'description'   => __( 'Add widgets here to appear in your sidebar.', 'projectfob-theme' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h2 class="widget-title">',
        'after_title'   => '</h2>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 1', 'projectfob-theme' ),
        'id'            => 'footer-1',
        'description'   => __( 'Add widgets here to appear in your footer.', 'projectfob-theme' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 2', 'projectfob-theme' ),
        'id'            => 'footer-2',
        'description'   => __( 'Add widgets here to appear in your footer.', 'projectfob-theme' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );

    register_sidebar( array(
        'name'          => __( 'Footer 3', 'projectfob-theme' ),
        'id'            => 'footer-3',
        'description'   => __( 'Add widgets here to appear in your footer.', 'projectfob-theme' ),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget'  => '</section>',
        'before_title'  => '<h3 class="widget-title">',
        'after_title'   => '</h3>',
    ) );
}
add_action( 'widgets_init', 'projectfob_widgets_init' );

/**
 * Check if ProjectFOB plugin is active
 */
function projectfob_plugin_is_active() {
    return defined( 'PFOB_VERSION' );
}

/**
 * Get ProjectFOB plugin URL
 */
function projectfob_get_app_url() {
    return home_url( '/projectfob/' );
}

/**
 * Get ProjectFOB pricing URL
 */
function projectfob_get_pricing_url() {
    return home_url( '/projectfob/pricing/' );
}

/**
 * Get ProjectFOB signup URL
 */
function projectfob_get_signup_url() {
    return home_url( '/projectfob/signup/' );
}

/**
 * Add body classes
 */
function projectfob_body_classes( $classes ) {
    if ( projectfob_plugin_is_active() ) {
        $classes[] = 'projectfob-active';
    }

    if ( is_front_page() ) {
        $classes[] = 'projectfob-home';
    }

    return $classes;
}
add_filter( 'body_class', 'projectfob_body_classes' );

/**
 * Customizer additions
 */
require get_template_directory() . '/inc/customizer.php';

/**
 * Default menu fallback
 */
function projectfob_default_menu() {
    $menu_items = array(
        array( 'title' => 'Home', 'url' => home_url( '/' ) ),
        array( 'title' => 'Pricing', 'url' => projectfob_get_pricing_url() ),
    );

    if ( is_user_logged_in() ) {
        $menu_items[] = array( 'title' => 'Dashboard', 'url' => projectfob_get_app_url() );
    }

    echo '<ul class="nav-menu">';
    foreach ( $menu_items as $item ) {
        echo '<li><a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a></li>';
    }
    echo '</ul>';
}

