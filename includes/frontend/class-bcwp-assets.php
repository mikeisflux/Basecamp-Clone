<?php
/**
 * Assets manager class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/frontend
 */

class BCWP_Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function enqueue_assets() {
        // Only load on Basecamp pages
        if ( ! $this->is_basecamp_page() ) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'bcwp-frontend',
            BCWP_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            BCWP_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'bcwp-frontend',
            BCWP_PLUGIN_URL . 'assets/js/frontend.js',
            array(),
            BCWP_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'bcwp-frontend', 'bcwpData', array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'restUrl'     => rest_url( 'bcwp/v1' ),
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'currentUser' => $this->get_current_user_data(),
            'strings'     => array(
                'confirm_delete' => __( 'Are you sure you want to delete this?', 'basecamp-wp-pro' ),
                'error'          => __( 'An error occurred. Please try again.', 'basecamp-wp-pro' ),
                'success'        => __( 'Success!', 'basecamp-wp-pro' ),
            ),
        ) );
    }

    public function enqueue_admin_assets( $hook ) {
        // Only load on plugin admin page
        if ( $hook !== 'toplevel_page_basecamp-wp-pro' ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    private function is_basecamp_page() {
        $page = get_query_var( 'bcwp_page' );
        return ! empty( $page );
    }

    private function get_current_user_data() {
        $user = wp_get_current_user();

        if ( ! $user->exists() ) {
            return null;
        }

        return array(
            'id'          => $user->ID,
            'name'        => $user->display_name,
            'email'       => $user->user_email,
            'avatar'      => get_avatar_url( $user->ID ),
            'is_admin'    => current_user_can( 'manage_options' ),
        );
    }

    public static function inline_css() {
        $primary_color = get_option( 'bcwp_primary_color', '#2d9061' );
        $background_color = get_option( 'bcwp_background_color', '#f7f6f3' );

        ?>
        <style>
            :root {
                --bcwp-primary-color: <?php echo esc_attr( $primary_color ); ?>;
                --bcwp-background-color: <?php echo esc_attr( $background_color ); ?>;
            }
        </style>
        <?php
    }
}
