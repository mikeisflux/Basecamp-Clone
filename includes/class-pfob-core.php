<?php
/**
 * The core plugin class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes
 */

class PFOB_Core {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     */
    protected $version;

    /**
     * Initialize the plugin.
     */
    public function __construct() {
        $this->version = PFOB_VERSION;
        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * Load required dependencies.
     */
    private function load_dependencies() {
        // Database
        require_once PFOB_PLUGIN_DIR . 'includes/database/class-pfob-database.php';
        require_once PFOB_PLUGIN_DIR . 'includes/database/class-pfob-schema.php';

        // Models
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-project.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-message.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-todo.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-document.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-chat.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-event.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-card.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-activity.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-comment.php';

        // Services
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-auth-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-permission-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-notification-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-email-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-file-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-search-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-digest-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-analytics-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-google-calendar-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-websocket-service.php';

        // API
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-rest-api.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-projects-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-messages-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-todos-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-chat-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-activities-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-search-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-events-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-import-export-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-settings-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-analytics-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-calendar-integration-endpoint.php';

        // Frontend
        require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-router.php';
        require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-template.php';
        require_once PFOB_PLUGIN_DIR . 'includes/frontend/class-pfob-assets.php';
    }

    /**
     * Register all hooks.
     */
    private function define_hooks() {
        // Initialize router
        $router = new PFOB_Router();

        // Initialize assets
        $assets = new PFOB_Assets();

        // Initialize REST API
        $rest_api = new PFOB_REST_API();

        // Initialize digest service
        PFOB_Digest_Service::init();

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
            __( 'ProjectFOB', 'projectfob' ),
            __( 'ProjectFOB', 'projectfob' ),
            'manage_options',
            'projectfob',
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
                <h2><?php _e( 'Welcome to ProjectFOB', 'projectfob' ); ?></h2>
                <p><?php _e( 'Your Basecamp clone is ready to use!', 'projectfob' ); ?></p>
                <p>
                    <a href="<?php echo esc_url( home_url( '/projectfob/' ) ); ?>" class="button button-primary">
                        <?php _e( 'Go to Dashboard', 'projectfob' ); ?>
                    </a>
                </p>
            </div>

            <div class="card">
                <h2><?php _e( 'Settings', 'projectfob' ); ?></h2>
                <form method="post" action="options.php">
                    <?php settings_fields( 'pfob_options' ); ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="pfob_company_name"><?php _e( 'Company Name', 'projectfob' ); ?></label>
                            </th>
                            <td>
                                <input type="text" id="pfob_company_name" name="pfob_company_name"
                                       value="<?php echo esc_attr( get_option( 'pfob_company_name' ) ); ?>"
                                       class="regular-text">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pfob_primary_color"><?php _e( 'Primary Color', 'projectfob' ); ?></label>
                            </th>
                            <td>
                                <input type="color" id="pfob_primary_color" name="pfob_primary_color"
                                       value="<?php echo esc_attr( get_option( 'pfob_primary_color', '#2d9061' ) ); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="pfob_enable_email_notifications">
                                    <?php _e( 'Email Notifications', 'projectfob' ); ?>
                                </label>
                            </th>
                            <td>
                                <input type="checkbox" id="pfob_enable_email_notifications"
                                       name="pfob_enable_email_notifications" value="1"
                                       <?php checked( get_option( 'pfob_enable_email_notifications' ), '1' ); ?>>
                                <label for="pfob_enable_email_notifications">
                                    <?php _e( 'Enable email notifications', 'projectfob' ); ?>
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
