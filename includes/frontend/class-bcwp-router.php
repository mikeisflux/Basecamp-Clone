<?php
/**
 * Custom router class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/frontend
 */

class BCWP_Router {

    public function __construct() {
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'route_request' ) );
    }

    public function add_rewrite_rules() {
        // Public Subscription Pages (No Auth Required)
        add_rewrite_rule( '^projectfob/pricing/?$', 'index.php?pfob_public_page=pricing', 'top' );
        add_rewrite_rule( '^projectfob/signup/?$', 'index.php?pfob_public_page=signup', 'top' );
        add_rewrite_rule( '^projectfob/subscription/success/?$', 'index.php?pfob_public_page=subscription-success', 'top' );
        add_rewrite_rule( '^projectfob/subscription/cancel/?$', 'index.php?pfob_public_page=subscription-cancel', 'top' );

        // ProjectFOB App Routes
        add_rewrite_rule( '^projectfob/?$', 'index.php?bcwp_page=dashboard', 'top' );

        // Lineup (timeline view)
        add_rewrite_rule( '^projectfob/lineup/?$', 'index.php?bcwp_page=lineup', 'top' );

        // My Stuff
        add_rewrite_rule( '^projectfob/my-stuff/?$', 'index.php?bcwp_page=my-stuff', 'top' );

        // Activity
        add_rewrite_rule( '^projectfob/activity/?$', 'index.php?bcwp_page=activity', 'top' );

        // Search
        add_rewrite_rule( '^projectfob/search/?$', 'index.php?bcwp_page=search', 'top' );

        // Analytics
        add_rewrite_rule( '^projectfob/analytics/?$', 'index.php?bcwp_page=analytics', 'top' );

        // Settings
        add_rewrite_rule( '^projectfob/settings/notifications/?$', 'index.php?bcwp_page=notifications', 'top' );
        add_rewrite_rule( '^projectfob/settings/calendar-integration/?$', 'index.php?bcwp_page=calendar-integration', 'top' );
        add_rewrite_rule( '^projectfob/settings/import-export/?$', 'index.php?bcwp_page=import-export', 'top' );

        // Projects
        add_rewrite_rule( '^projectfob/projects/new/?$', 'index.php?bcwp_page=project-create', 'top' );
        add_rewrite_rule( '^projectfob/projects/([^/]+)/?$', 'index.php?bcwp_page=project&bcwp_project=$matches[1]', 'top' );

        // Project tools
        add_rewrite_rule( '^projectfob/projects/([^/]+)/messages/?$', 'index.php?bcwp_page=messages&bcwp_project=$matches[1]', 'top' );
        add_rewrite_rule( '^projectfob/projects/([^/]+)/messages/([0-9]+)/?$', 'index.php?bcwp_page=message-single&bcwp_project=$matches[1]&bcwp_item=$matches[2]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/todos/?$', 'index.php?bcwp_page=todos&bcwp_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/documents/?$', 'index.php?bcwp_page=documents&bcwp_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/chat/?$', 'index.php?bcwp_page=chat&bcwp_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/schedule/?$', 'index.php?bcwp_page=schedule&bcwp_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/cards/?$', 'index.php?bcwp_page=cards&bcwp_project=$matches[1]', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'bcwp_page';
        $vars[] = 'bcwp_project';
        $vars[] = 'bcwp_item';
        $vars[] = 'pfob_public_page';
        return $vars;
    }

    public function route_request() {
        // Handle public pages (no auth required)
        $public_page = get_query_var( 'pfob_public_page' );
        if ( ! empty( $public_page ) ) {
            $this->route_public_page( $public_page );
            return;
        }

        // Handle authenticated pages
        $page = get_query_var( 'bcwp_page' );

        if ( empty( $page ) ) {
            return;
        }

        // Require authentication
        BCWP_Auth_Service::require_auth();

        // Load template based on page
        $template = $this->get_template_for_page( $page );

        if ( $template && file_exists( $template ) ) {
            // Set up global variables for templates
            global $bcwp_page, $bcwp_project, $bcwp_item;

            $bcwp_page = $page;
            $project_slug = get_query_var( 'bcwp_project' );
            $item_id = get_query_var( 'bcwp_item' );

            // Load project if specified
            if ( $project_slug ) {
                $bcwp_project = BCWP_Project::get_by_slug( $project_slug );

                if ( ! $bcwp_project ) {
                    wp_die( __( 'Project not found.', 'basecamp-wp-pro' ), 404 );
                }

                // Check permissions
                BCWP_Permission_Service::require_project_access( $bcwp_project->id );
            }

            // Load item if specified
            if ( $item_id ) {
                $bcwp_item = $item_id;
            }

            // Include template
            include $template;
            exit;
        }
    }

    private function get_template_for_page( $page ) {
        $template_dir = BCWP_PLUGIN_DIR . 'templates/frontend/';

        $templates = array(
            'dashboard'       => $template_dir . 'dashboard.php',
            'lineup'          => $template_dir . 'lineup.php',
            'my-stuff'        => $template_dir . 'my-stuff.php',
            'activity'        => $template_dir . 'activity-feed.php',
            'search'          => $template_dir . 'search.php',
            'analytics'       => $template_dir . 'analytics.php',
            'notifications'   => $template_dir . 'settings/notifications.php',
            'calendar-integration' => $template_dir . 'settings/calendar-integration.php',
            'import-export'   => $template_dir . 'settings/import-export.php',
            'project'         => $template_dir . 'project/single.php',
            'project-create'  => $template_dir . 'project/create.php',
            'messages'        => $template_dir . 'messages/board.php',
            'message-single'  => $template_dir . 'messages/single.php',
            'todos'           => $template_dir . 'todos/index.php',
            'documents'       => $template_dir . 'documents/index.php',
            'chat'            => $template_dir . 'chat/room.php',
            'schedule'        => $template_dir . 'schedule/calendar.php',
            'cards'           => $template_dir . 'cards/board.php',
        );

        return $templates[ $page ] ?? null;
    }

    /**
     * Route public pages (no authentication required).
     *
     * @param string $page Public page identifier.
     */
    private function route_public_page( $page ) {
        $template_dir = PFOB_PLUGIN_DIR . 'templates/public/';

        $templates = array(
            'pricing'              => $template_dir . 'pricing.php',
            'signup'               => $template_dir . 'signup.php',
            'subscription-success' => $template_dir . 'subscription-success.php',
            'subscription-cancel'  => $template_dir . 'subscription-cancel.php',
        );

        $template = $templates[ $page ] ?? null;

        if ( $template && file_exists( $template ) ) {
            include $template;
            exit;
        }
    }
}
