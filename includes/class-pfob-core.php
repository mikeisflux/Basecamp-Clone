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
        $this->define_hooks();
    }

    /**
     * Load required dependencies.
     *
     * NOTE: Dependencies are now loaded in projectfob.php before this class is instantiated.
     * This method is kept for backwards compatibility but does nothing.
     */
    private function load_dependencies() {
        // All dependencies are now loaded in projectfob.php
        // This ensures proper load order and prevents duplicate loading
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
