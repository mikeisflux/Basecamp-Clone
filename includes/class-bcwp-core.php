<?php
/**
 * The core plugin class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes
 */

class BCWP_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $version;

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->version = BCWP_VERSION;
        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * Load required dependencies.
     */
    private function load_dependencies() {
        // Database
        require_once BCWP_PLUGIN_DIR . 'includes/database/class-bcwp-database.php';
        require_once BCWP_PLUGIN_DIR . 'includes/database/class-bcwp-schema.php';

        // Models
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-project.php';
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-message.php';
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-todo.php';
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-document.php';
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-chat.php';
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-event.php';
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-card.php';
        require_once BCWP_PLUGIN_DIR . 'includes/models/class-bcwp-activity.php';

        // Services
        require_once BCWP_PLUGIN_DIR . 'includes/services/class-bcwp-auth-service.php';
        require_once BCWP_PLUGIN_DIR . 'includes/services/class-bcwp-permission-service.php';
        require_once BCWP_PLUGIN_DIR . 'includes/services/class-bcwp-notification-service.php';
        require_once BCWP_PLUGIN_DIR . 'includes/services/class-bcwp-email-service.php';
        require_once BCWP_PLUGIN_DIR . 'includes/services/class-bcwp-file-service.php';
        require_once BCWP_PLUGIN_DIR . 'includes/services/class-bcwp-search-service.php';

        // API
        require_once BCWP_PLUGIN_DIR . 'includes/api/class-bcwp-rest-api.php';
        require_once BCWP_PLUGIN_DIR . 'includes/api/class-bcwp-projects-endpoint.php';
        require_once BCWP_PLUGIN_DIR . 'includes/api/class-bcwp-messages-endpoint.php';
        require_once BCWP_PLUGIN_DIR . 'includes/api/class-bcwp-todos-endpoint.php';
        require_once BCWP_PLUGIN_DIR . 'includes/api/class-bcwp-chat-endpoint.php';
        require_once BCWP_PLUGIN_DIR . 'includes/api/class-bcwp-activities-endpoint.php';

        // Frontend
        require_once BCWP_PLUGIN_DIR . 'includes/frontend/class-bcwp-router.php';
        require_once BCWP_PLUGIN_DIR . 'includes/frontend/class-bcwp-template.php';
        require_once BCWP_PLUGIN_DIR . 'includes/frontend/class-bcwp-assets.php';
    }

    /**
     * Register all hooks.
     */
    private function define_hooks() {
        // Initialize router
        $router = new BCWP_Router();

        // Initialize assets
        $assets = new BCWP_Assets();

        // Initialize REST API
        $rest_api = new BCWP_REST_API();

        // Add admin menu
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );

        // Register REST API routes
        add_action( 'rest_api_init', array( $rest_api, 'register_routes' ) );
    }

    /**
     * Add admin menu page.
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Basecamp WP Pro', 'basecamp-wp-pro' ),
            __( 'Basecamp', 'basecamp-wp-pro' ),
            'manage_options',
            'basecamp-wp-pro',
            array( $this, 'display_admin_page' ),
            'dashicons-groups',
            30
        );
    }

    /**
     * Display admin page.
     */
    public function display_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <div class="card">
                <h2><?php _e( 'Welcome to Basecamp WP Pro', 'basecamp-wp-pro' ); ?></h2>
                <p><?php _e( 'Your Basecamp clone is ready to use!', 'basecamp-wp-pro' ); ?></p>
                <p>
                    <a href="<?php echo esc_url( home_url( '/basecamp/' ) ); ?>" class="button button-primary">
                        <?php _e( 'Go to Dashboard', 'basecamp-wp-pro' ); ?>
                    </a>
                </p>
            </div>

            <div class="card">
                <h2><?php _e( 'Settings', 'basecamp-wp-pro' ); ?></h2>
                <form method="post" action="options.php">
                    <?php settings_fields( 'bcwp_options' ); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="bcwp_company_name"><?php _e( 'Company Name', 'basecamp-wp-pro' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="bcwp_company_name" name="bcwp_company_name"
                                       value="<?php echo esc_attr( get_option( 'bcwp_company_name' ) ); ?>"
                                       class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="bcwp_primary_color"><?php _e( 'Primary Color', 'basecamp-wp-pro' ); ?></label>
                            </th>
                            <td>
                                <input type="color" id="bcwp_primary_color" name="bcwp_primary_color"
                                       value="<?php echo esc_attr( get_option( 'bcwp_primary_color', '#2d9061' ) ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="bcwp_enable_email_notifications">
                                    <?php _e( 'Email Notifications', 'basecamp-wp-pro' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="bcwp_enable_email_notifications"
                                       name="bcwp_enable_email_notifications" value="1"
                                       <?php checked( get_option( 'bcwp_enable_email_notifications' ), '1' ); ?>>
                                <label for="bcwp_enable_email_notifications">
                                    <?php _e( 'Enable email notifications', 'basecamp-wp-pro' ); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                    <?php submit_button(); ?>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * Run the plugin.
     */
    public function run() {
        // Plugin is loaded and running
    }

    /**
     * Get the plugin version.
     */
    public function get_version() {
        return $this->version;
    }
}
