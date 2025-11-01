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
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-subscription.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-billing-history.php';
        require_once PFOB_PLUGIN_DIR . 'includes/models/class-pfob-usage.php';

        // Services
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-auth-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-permission-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-notification-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-search-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-email-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-digest-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-paypal-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-r2-storage-service.php';
        require_once PFOB_PLUGIN_DIR . 'includes/services/class-pfob-file-service.php';
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
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-notifications-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-paypal-webhook-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-subscription-endpoint.php';
        require_once PFOB_PLUGIN_DIR . 'includes/api/class-pfob-user-endpoint.php';

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

        // Register REST API routes
        add_action( 'rest_api_init', array( $rest_api, 'register_routes' ) );

        // Flush rewrite rules if needed (after activation)
        add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 999 );
    }

    /**
     * Flush rewrite rules if flag is set (after activation).
     */
    public function maybe_flush_rewrite_rules() {
        if ( get_option( 'pfob_flush_rewrite_rules' ) === '1' ) {
            flush_rewrite_rules();
            delete_option( 'pfob_flush_rewrite_rules' );
        }
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
