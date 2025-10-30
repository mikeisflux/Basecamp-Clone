<?php
/**
 * Assets manager class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/frontend
 */

class PFOB_Assets {

    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
    }

    public function enqueue_assets() {
        // Only load on ProjectFOB pages
        if ( ! $this->is_projectfob_page() ) {
            return;
        }

        // CSS
        wp_enqueue_style(
            'pfob-frontend',
            PFOB_PLUGIN_URL . 'assets/css/frontend.css',
            array(),
            PFOB_VERSION
        );

        // JavaScript
        wp_enqueue_script(
            'pfob-frontend',
            PFOB_PLUGIN_URL . 'assets/js/frontend.js',
            array(),
            PFOB_VERSION,
            true
        );

        // Localize script
        wp_localize_script( 'pfob-frontend', 'pfobData', array(
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'restUrl'     => rest_url( 'projectfob/v1' ),
            'nonce'       => wp_create_nonce( 'wp_rest' ),
            'currentUser' => $this->get_current_user_data(),
            'strings'     => array(
                'confirm_delete' => __( 'Are you sure you want to delete this?', 'projectfob' ),
                'error'          => __( 'An error occurred. Please try again.', 'projectfob' ),
                'success'        => __( 'Success!', 'projectfob' ),
            ),
        ) );
    }

    public function enqueue_admin_assets( $hook ) {
        // Only load on plugin admin page
        if ( $hook !== 'toplevel_page_projectfob' ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
    }

    private function is_projectfob_page() {
        $page = get_query_var( 'pfob_page' );
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
        $primary_color = get_option( 'pfob_primary_color', '#2d9061' );
        $background_color = get_option( 'pfob_background_color', '#f7f6f3' );

        ?>
        <style>
            :root {
                --pfob-primary-color: <?php echo esc_attr( $primary_color ); ?>;
                --pfob-background-color: <?php echo esc_attr( $background_color ); ?>;
            }
        </style>
        <?php
    }
}
