<?php
/**
 * User REST API Endpoint
 *
 * Handles user preferences and settings
 */

class PFOB_User_Endpoint extends PFOB_REST_API {

    /**
     * Register routes
     */
    public function register_routes() {
        // Save user preference
        register_rest_route( $this->namespace, '/user/preferences', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'save_preference' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'key'   => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'value' => array(
                    'required' => true,
                ),
            ),
        ) );

        // Save project order
        register_rest_route( $this->namespace, '/user/project-order', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'save_project_order' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'order' => array(
                    'required' => true,
                    'type'     => 'array',
                ),
            ),
        ) );

        // Save document order
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/documents/order', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'save_document_order' ),
            'permission_callback' => array( $this, 'check_permission' ),
            'args'                => array(
                'order'      => array(
                    'required' => true,
                    'type'     => 'array',
                ),
                'folder_id' => array(
                    'type' => 'integer',
                ),
            ),
        ) );
    }

    /**
     * Save user preference
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function save_preference( $request ) {
        $user_id = get_current_user_id();
        $key = $request->get_param( 'key' );
        $value = $request->get_param( 'value' );

        // Whitelist allowed preference keys
        $allowed_keys = array(
            'pfob_projects_view',
            'pfob_files_view',
        );

        if ( ! in_array( $key, $allowed_keys ) ) {
            return $this->error_response( 'Invalid preference key', 'invalid_key', 400 );
        }

        update_user_meta( $user_id, $key, $value );

        return $this->success_response(
            array( 'key' => $key, 'value' => $value ),
            'Preference saved successfully'
        );
    }

    /**
     * Save project order
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function save_project_order( $request ) {
        $user_id = get_current_user_id();
        $order = $request->get_param( 'order' );

        // Save as user meta
        update_user_meta( $user_id, 'pfob_project_order', $order );

        return $this->success_response(
            array( 'order' => $order ),
            'Project order saved successfully'
        );
    }

    /**
     * Save document order
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function save_document_order( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $order = $request->get_param( 'order' );
        $folder_id = $request->get_param( 'folder_id' );

        // Check project access
        if ( ! PFOB_Permission_Service::can_access_project( $project_id, get_current_user_id() ) ) {
            return $this->error_response( 'You do not have access to this project', 'access_denied', 403 );
        }

        // Update display order for each document
        global $wpdb;
        $table = $wpdb->prefix . 'pfob_documents';

        foreach ( $order as $position => $doc_id ) {
            $wpdb->update(
                $table,
                array( 'display_order' => $position ),
                array( 'id' => intval( $doc_id ) ),
                array( '%d' ),
                array( '%d' )
            );
        }

        return $this->success_response(
            array( 'order' => $order ),
            'Document order saved successfully'
        );
    }

    /**
     * Check if user has permission
     *
     * @param WP_REST_Request $request Request object
     * @return bool
     */
    public function check_permission( $request ) {
        return is_user_logged_in();
    }
}
