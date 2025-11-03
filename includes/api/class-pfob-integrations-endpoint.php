<?php
/**
 * Cloud Integrations REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Integrations_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Dropbox routes
        register_rest_route( $this->namespace, '/integrations/dropbox/status', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_dropbox_status' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/dropbox/auth-url', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_dropbox_auth_url' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/dropbox/callback', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_dropbox_callback' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
            'args'                => array(
                'code'  => array( 'required' => true, 'type' => 'string' ),
                'state' => array( 'required' => true, 'type' => 'string' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/integrations/dropbox/disconnect', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'disconnect_dropbox' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/dropbox/settings', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'update_dropbox_settings' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Google Drive routes
        register_rest_route( $this->namespace, '/integrations/gdrive/status', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_gdrive_status' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/gdrive/auth-url', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_gdrive_auth_url' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/gdrive/callback', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle_gdrive_callback' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
            'args'                => array(
                'code'  => array( 'required' => true, 'type' => 'string' ),
                'state' => array( 'required' => true, 'type' => 'string' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/integrations/gdrive/disconnect', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'disconnect_gdrive' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/gdrive/settings', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'update_gdrive_settings' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        register_rest_route( $this->namespace, '/integrations/gdrive/folder-url', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_gdrive_folder_url' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );
    }

    // ========== Dropbox Methods ==========

    /**
     * Get Dropbox connection status.
     */
    public function get_dropbox_status( $request ) {
        $user_id = get_current_user_id();
        $settings = PFOB_Dropbox_Service::get_settings( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => $settings,
        ), 200 );
    }

    /**
     * Get Dropbox authorization URL.
     */
    public function get_dropbox_auth_url( $request ) {
        $user_id = get_current_user_id();
        $auth_url = PFOB_Dropbox_Service::get_authorization_url( $user_id );

        if ( ! $auth_url ) {
            return new WP_Error( 'config_error', 'Dropbox integration not configured', array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array(
            'success'  => true,
            'data'     => array( 'auth_url' => $auth_url ),
        ), 200 );
    }

    /**
     * Handle Dropbox OAuth callback.
     */
    public function handle_dropbox_callback( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_params();

        $result = PFOB_Dropbox_Service::handle_oauth_callback(
            $params['code'],
            $params['state'],
            $user_id
        );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Dropbox connected successfully',
        ), 200 );
    }

    /**
     * Disconnect Dropbox.
     */
    public function disconnect_dropbox( $request ) {
        $user_id = get_current_user_id();
        PFOB_Dropbox_Service::disconnect( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Dropbox disconnected successfully',
        ), 200 );
    }

    /**
     * Update Dropbox settings.
     */
    public function update_dropbox_settings( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_params();

        PFOB_Dropbox_Service::update_settings( $user_id, $params );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Settings updated successfully',
        ), 200 );
    }

    // ========== Google Drive Methods ==========

    /**
     * Get Google Drive connection status.
     */
    public function get_gdrive_status( $request ) {
        $user_id = get_current_user_id();
        $settings = PFOB_GDrive_Service::get_settings( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => $settings,
        ), 200 );
    }

    /**
     * Get Google Drive authorization URL.
     */
    public function get_gdrive_auth_url( $request ) {
        $user_id = get_current_user_id();
        $auth_url = PFOB_GDrive_Service::get_authorization_url( $user_id );

        if ( ! $auth_url ) {
            return new WP_Error( 'config_error', 'Google Drive integration not configured', array( 'status' => 500 ) );
        }

        return new WP_REST_Response( array(
            'success'  => true,
            'data'     => array( 'auth_url' => $auth_url ),
        ), 200 );
    }

    /**
     * Handle Google Drive OAuth callback.
     */
    public function handle_gdrive_callback( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_params();

        $result = PFOB_GDrive_Service::handle_oauth_callback(
            $params['code'],
            $params['state'],
            $user_id
        );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Google Drive connected successfully',
        ), 200 );
    }

    /**
     * Disconnect Google Drive.
     */
    public function disconnect_gdrive( $request ) {
        $user_id = get_current_user_id();
        PFOB_GDrive_Service::disconnect( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Google Drive disconnected successfully',
        ), 200 );
    }

    /**
     * Update Google Drive settings.
     */
    public function update_gdrive_settings( $request ) {
        $user_id = get_current_user_id();
        $params = $request->get_params();

        PFOB_GDrive_Service::update_settings( $user_id, $params );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Settings updated successfully',
        ), 200 );
    }

    /**
     * Get Google Drive folder URL.
     */
    public function get_gdrive_folder_url( $request ) {
        $user_id = get_current_user_id();
        $folder_url = PFOB_GDrive_Service::get_folder_url( $user_id );

        if ( ! $folder_url ) {
            return new WP_Error( 'not_found', 'Folder not found', array( 'status' => 404 ) );
        }

        return new WP_REST_Response( array(
            'success'  => true,
            'data'     => array( 'folder_url' => $folder_url ),
        ), 200 );
    }
}
