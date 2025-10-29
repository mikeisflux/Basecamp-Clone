<?php
/**
 * Theme Customizer
 *
 * @package ProjectFOB_Theme
 */

function projectfob_customize_register( $wp_customize ) {
    // Add ProjectFOB Settings Section
    $wp_customize->add_section( 'projectfob_settings', array(
        'title'    => __( 'ProjectFOB Settings', 'projectfob-theme' ),
        'priority' => 30,
    ) );

    // Primary Color
    $wp_customize->add_setting( 'projectfob_primary_color', array(
        'default'           => '#667eea',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'projectfob_primary_color', array(
        'label'    => __( 'Primary Color', 'projectfob-theme' ),
        'section'  => 'projectfob_settings',
        'settings' => 'projectfob_primary_color',
    ) ) );

    // Secondary Color
    $wp_customize->add_setting( 'projectfob_secondary_color', array(
        'default'           => '#764ba2',
        'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'projectfob_secondary_color', array(
        'label'    => __( 'Secondary Color', 'projectfob-theme' ),
        'section'  => 'projectfob_settings',
        'settings' => 'projectfob_secondary_color',
    ) ) );

    // Hero Title
    $wp_customize->add_setting( 'projectfob_hero_title', array(
        'default'           => 'Every Great Plan Deploys from the FOB',
        'sanitize_callback' => 'sanitize_text_field',
    ) );

    $wp_customize->add_control( 'projectfob_hero_title', array(
        'label'    => __( 'Hero Title', 'projectfob-theme' ),
        'section'  => 'projectfob_settings',
        'type'     => 'text',
    ) );

    // Hero Subtitle
    $wp_customize->add_setting( 'projectfob_hero_subtitle', array(
        'default'           => 'Complete project management and team collaboration platform.',
        'sanitize_callback' => 'sanitize_textarea_field',
    ) );

    $wp_customize->add_control( 'projectfob_hero_subtitle', array(
        'label'    => __( 'Hero Subtitle', 'projectfob-theme' ),
        'section'  => 'projectfob_settings',
        'type'     => 'textarea',
    ) );
}
add_action( 'customize_register', 'projectfob_customize_register' );

/**
 * Output custom CSS for customizer options
 */
function projectfob_customizer_css() {
    $primary_color = get_theme_mod( 'projectfob_primary_color', '#667eea' );
    $secondary_color = get_theme_mod( 'projectfob_secondary_color', '#764ba2' );
    ?>
    <style type="text/css">
        :root {
            --pfob-primary: <?php echo esc_attr( $primary_color ); ?>;
            --pfob-secondary: <?php echo esc_attr( $secondary_color ); ?>;
        }
    </style>
    <?php
}
add_action( 'wp_head', 'projectfob_customizer_css' );
