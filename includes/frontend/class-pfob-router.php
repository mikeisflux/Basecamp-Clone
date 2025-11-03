<?php
/**
 * Custom router class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/frontend
 */

class PFOB_Router {

    public function __construct() {
        add_action( 'init', array( $this, 'add_rewrite_rules' ) );
        add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
        add_action( 'template_redirect', array( $this, 'route_request' ) );
    }

    /**
     * Manually add rewrite rules (for use during activation).
     * Call this directly when you need to register rules outside of the init hook.
     */
    public static function register_rewrite_rules() {
        $instance = new self();
        $instance->add_rewrite_rules();
    }

    public function add_rewrite_rules() {
        // Public Subscription Pages (No Auth Required)
        add_rewrite_rule( '^projectfob/pricing/?$', 'index.php?pfob_public_page=pricing', 'top' );
        add_rewrite_rule( '^projectfob/signup/?$', 'index.php?pfob_public_page=signup', 'top' );
        add_rewrite_rule( '^projectfob/subscription/success/?$', 'index.php?pfob_public_page=subscription-success', 'top' );
        add_rewrite_rule( '^projectfob/subscription/cancel/?$', 'index.php?pfob_public_page=subscription-cancel', 'top' );
        add_rewrite_rule( '^projectfob/privacy-policy/?$', 'index.php?pfob_public_page=privacy-policy', 'top' );
        add_rewrite_rule( '^projectfob/terms-of-service/?$', 'index.php?pfob_public_page=terms-of-service', 'top' );

        // ProjectFOB App Routes
        add_rewrite_rule( '^projectfob/?$', 'index.php?pfob_page=dashboard', 'top' );

        // Lineup (timeline view)
        add_rewrite_rule( '^projectfob/lineup/?$', 'index.php?pfob_page=lineup', 'top' );

        // My Stuff
        add_rewrite_rule( '^projectfob/my-stuff/?$', 'index.php?pfob_page=my-stuff', 'top' );

        // Activity
        add_rewrite_rule( '^projectfob/activity/?$', 'index.php?pfob_page=activity', 'top' );

        // Adminland (Account Management)
        add_rewrite_rule( '^projectfob/adminland/?$', 'index.php?pfob_page=adminland', 'top' );
        add_rewrite_rule( '^projectfob/adminland/billing/?$', 'index.php?pfob_page=billing', 'top' );
        add_rewrite_rule( '^projectfob/adminland/storage/?$', 'index.php?pfob_page=storage', 'top' );
        add_rewrite_rule( '^projectfob/adminland/groups/?$', 'index.php?pfob_page=groups', 'top' );
        add_rewrite_rule( '^projectfob/adminland/companies/?$', 'index.php?pfob_page=companies', 'top' );
        add_rewrite_rule( '^projectfob/adminland/administrators/?$', 'index.php?pfob_page=administrators', 'top' );
        add_rewrite_rule( '^projectfob/adminland/invite-link/?$', 'index.php?pfob_page=invite-link', 'top' );
        add_rewrite_rule( '^projectfob/adminland/export/?$', 'index.php?pfob_page=export', 'top' );
        add_rewrite_rule( '^projectfob/adminland/public-items/?$', 'index.php?pfob_page=public-items', 'top' );
        add_rewrite_rule( '^projectfob/adminland/trash/?$', 'index.php?pfob_page=trash', 'top' );
        add_rewrite_rule( '^projectfob/adminland/upgrades/?$', 'index.php?pfob_page=upgrades', 'top' );
        add_rewrite_rule( '^projectfob/adminland/white-label/?$', 'index.php?pfob_page=white-label', 'top' );
        add_rewrite_rule( '^projectfob/adminland/api-access/?$', 'index.php?pfob_page=api-access', 'top' );

        // Timesheet (Add-on)
        add_rewrite_rule( '^projectfob/timesheet/?$', 'index.php?pfob_page=timesheet', 'top' );
        add_rewrite_rule( '^projectfob/timesheet/reports/?$', 'index.php?pfob_page=timesheet-reports', 'top' );
        add_rewrite_rule( '^projectfob/timesheet/settings/?$', 'index.php?pfob_page=timesheet-settings', 'top' );
        add_rewrite_rule( '^projectfob/timesheet/export/?$', 'index.php?pfob_page=timesheet-export', 'top' );

        // Admin Pro Pack (Add-on)
        add_rewrite_rule( '^projectfob/admin-pro/permissions/?$', 'index.php?pfob_page=admin-pro-permissions', 'top' );
        add_rewrite_rule( '^projectfob/admin-pro/access-logs/?$', 'index.php?pfob_page=admin-pro-access-logs', 'top' );
        add_rewrite_rule( '^projectfob/admin-pro/approval-workflows/?$', 'index.php?pfob_page=admin-pro-approval-workflows', 'top' );
        add_rewrite_rule( '^projectfob/admin-pro/custom-roles/?$', 'index.php?pfob_page=admin-pro-custom-roles', 'top' );

        // People Management
        add_rewrite_rule( '^projectfob/people/?$', 'index.php?pfob_page=people', 'top' );
        add_rewrite_rule( '^projectfob/people/invite/?$', 'index.php?pfob_page=invite', 'top' );
        add_rewrite_rule( '^projectfob/people/([^/]+)/projects/?$', 'index.php?pfob_page=user-projects&pfob_user=$matches[1]', 'top' );

        // Search
        add_rewrite_rule( '^projectfob/search/?$', 'index.php?pfob_page=search', 'top' );

        // Analytics
        add_rewrite_rule( '^projectfob/analytics/?$', 'index.php?pfob_page=analytics', 'top' );
        add_rewrite_rule( '^projectfob/advanced-analytics/?$', 'index.php?pfob_page=advanced-analytics', 'top' );

        // Personal Schedule (all events across all projects)
        add_rewrite_rule( '^projectfob/schedule/?$', 'index.php?pfob_page=my-schedule', 'top' );

        // Settings
        add_rewrite_rule( '^projectfob/settings/notifications/?$', 'index.php?pfob_page=notifications', 'top' );
        add_rewrite_rule( '^projectfob/settings/calendar-integration/?$', 'index.php?pfob_page=calendar-integration', 'top' );
        add_rewrite_rule( '^projectfob/settings/cloud-storage/?$', 'index.php?pfob_page=cloud-storage', 'top' );
        add_rewrite_rule( '^projectfob/settings/import-export/?$', 'index.php?pfob_page=import-export', 'top' );

        // Projects
        add_rewrite_rule( '^projectfob/projects/new/?$', 'index.php?pfob_page=project-create', 'top' );
        add_rewrite_rule( '^projectfob/projects/([^/]+)/?$', 'index.php?pfob_page=project&pfob_project=$matches[1]', 'top' );

        // Project tools
        add_rewrite_rule( '^projectfob/projects/([^/]+)/messages/?$', 'index.php?pfob_page=messages&pfob_project=$matches[1]', 'top' );
        add_rewrite_rule( '^projectfob/projects/([^/]+)/messages/([0-9]+)/?$', 'index.php?pfob_page=message-single&pfob_project=$matches[1]&pfob_item=$matches[2]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/todos/?$', 'index.php?pfob_page=todos&pfob_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/documents/?$', 'index.php?pfob_page=documents&pfob_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/chat/?$', 'index.php?pfob_page=chat&pfob_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/schedule/?$', 'index.php?pfob_page=schedule&pfob_project=$matches[1]', 'top' );

        add_rewrite_rule( '^projectfob/projects/([^/]+)/cards/?$', 'index.php?pfob_page=cards&pfob_project=$matches[1]', 'top' );
    }

    public function add_query_vars( $vars ) {
        $vars[] = 'pfob_page';
        $vars[] = 'pfob_project';
        $vars[] = 'pfob_item';
        $vars[] = 'pfob_user';
        $vars[] = 'pfob_public_page';
        return $vars;
    }

    public function route_request() {
        error_log( '[Router] route_request() called' );

        // Handle public pages (no auth required)
        $public_page = get_query_var( 'pfob_public_page' );
        if ( ! empty( $public_page ) ) {
            error_log( '[Router] Routing to public page: ' . $public_page );
            $this->route_public_page( $public_page );
            return;
        }

        // Handle authenticated pages
        $page = get_query_var( 'pfob_page' );
        error_log( '[Router] pfob_page query var: ' . ( $page ?: 'empty' ) );

        if ( empty( $page ) ) {
            error_log( '[Router] No page specified, returning' );
            return;
        }

        error_log( '[Router] Routing to authenticated page: ' . $page );

        // Require authentication
        error_log( '[Router] Checking authentication...' );
        PFOB_Auth_Service::require_auth();
        error_log( '[Router] Authentication passed' );

        // Require active subscription
        error_log( '[Router] Checking subscription...' );
        PFOB_Auth_Service::require_subscription();
        error_log( '[Router] Subscription check passed' );

        // Load template based on page
        $template = $this->get_template_for_page( $page );
        error_log( '[Router] Template path: ' . ( $template ?: 'null' ) );

        if ( $template && file_exists( $template ) ) {
            error_log( '[Router] Template exists, loading: ' . $template );

            // Set up global variables for templates
            global $pfob_page, $pfob_project, $pfob_item;

            $pfob_page = $page;
            $project_slug = get_query_var( 'pfob_project' );
            $item_id = get_query_var( 'pfob_item' );

            // Load project if specified
            if ( $project_slug ) {
                $pfob_project = PFOB_Project::get_by_slug( $project_slug );

                if ( ! $pfob_project ) {
                    wp_die( __( 'Project not found.', 'projectfob' ), 404 );
                }

                // Check permissions
                PFOB_Permission_Service::require_project_access( $pfob_project->id );
            }

            // Load item if specified
            if ( $item_id ) {
                $pfob_item = $item_id;
            }

            // Include template
            error_log( '[Router] Including template file' );
            include $template;
            error_log( '[Router] Template included successfully' );
            exit;
        } else {
            error_log( '[Router] Template not found or does not exist: ' . ( $template ?: 'null' ) );
        }
    }

    private function get_template_for_page( $page ) {
        $template_dir = PFOB_PLUGIN_DIR . 'templates/frontend/';

        $templates = array(
            'dashboard'       => $template_dir . 'dashboard.php',
            'lineup'          => $template_dir . 'lineup.php',
            'my-stuff'        => $template_dir . 'my-stuff.php',
            'activity'        => $template_dir . 'activity-feed.php',
            'adminland'       => $template_dir . 'adminland/index.php',
            'billing'         => $template_dir . 'adminland/billing.php',
            'storage'         => $template_dir . 'adminland/storage.php',
            'groups'          => $template_dir . 'adminland/groups.php',
            'companies'       => $template_dir . 'adminland/companies.php',
            'administrators'  => $template_dir . 'adminland/administrators.php',
            'invite-link'     => $template_dir . 'adminland/invite-link.php',
            'export'          => $template_dir . 'adminland/export.php',
            'public-items'    => $template_dir . 'adminland/public-items.php',
            'trash'           => $template_dir . 'adminland/trash.php',
            'upgrades'        => $template_dir . 'adminland/upgrades.php',
            'white-label'     => $template_dir . 'adminland/white-label.php',
            'api-access'      => $template_dir . 'adminland/api-access.php',
            'timesheet'       => $template_dir . 'timesheet/index.php',
            'timesheet-reports' => $template_dir . 'timesheet/reports.php',
            'timesheet-settings' => $template_dir . 'timesheet/settings.php',
            'timesheet-export' => $template_dir . 'timesheet/export.php',
            'admin-pro-permissions' => $template_dir . 'admin-pro/permissions.php',
            'admin-pro-access-logs' => $template_dir . 'admin-pro/access-logs.php',
            'admin-pro-approval-workflows' => $template_dir . 'admin-pro/approval-workflows.php',
            'admin-pro-custom-roles' => $template_dir . 'admin-pro/custom-roles.php',
            'people'          => $template_dir . 'people/index.php',
            'invite'          => $template_dir . 'people/invite.php',
            'user-projects'   => $template_dir . 'people/user-projects.php',
            'search'          => $template_dir . 'search.php',
            'analytics'       => $template_dir . 'analytics.php',
            'advanced-analytics' => $template_dir . 'advanced-analytics/index.php',
            'my-schedule'     => $template_dir . 'my-schedule.php',
            'notifications'   => $template_dir . 'settings/notifications.php',
            'calendar-integration' => $template_dir . 'settings/calendar-integration.php',
            'cloud-storage'   => $template_dir . 'settings/cloud-storage.php',
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
            'privacy-policy'       => $template_dir . 'privacy-policy.php',
            'terms-of-service'     => $template_dir . 'terms-of-service.php',
        );

        $template = $templates[ $page ] ?? null;

        if ( $template && file_exists( $template ) ) {
            include $template;
            exit;
        }
    }
}
