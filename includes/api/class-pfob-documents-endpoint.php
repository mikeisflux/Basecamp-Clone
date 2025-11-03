<?php
/**
 * Documents REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Documents_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Upload document
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/documents/upload', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'upload_document' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Create folder
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/documents/folder', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_folder' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Delete document
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/documents/(?P<document_id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( $this, 'delete_document' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Rename document
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/documents/(?P<document_id>\d+)/rename', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'rename_document' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Move document
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/documents/(?P<document_id>\d+)/move', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'move_document' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );
    }

    /**
     * Upload a document.
     */
    public function upload_document( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $files = $request->get_file_params();
        $params = $request->get_body_params();

        if ( empty( $files['file'] ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'No file uploaded',
            ), 400 );
        }

        $file = $files['file'];
        $parent_id = isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null;

        // Validate file
        if ( $file['error'] !== UPLOAD_ERR_OK ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'File upload error: ' . $file['error'],
            ), 400 );
        }

        // Check file size (max 100MB)
        $max_size = 100 * 1024 * 1024;
        if ( $file['size'] > $max_size ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'File size exceeds 100MB limit',
            ), 400 );
        }

        // Create upload directory if it doesn't exist
        $upload_dir = WP_CONTENT_DIR . '/uploads/projectfob/documents';
        if ( ! file_exists( $upload_dir ) ) {
            wp_mkdir_p( $upload_dir );
        }

        // Generate unique filename
        $filename = sanitize_file_name( $file['name'] );
        $file_path = $upload_dir . '/' . uniqid() . '_' . $filename;

        // Move uploaded file
        if ( ! move_uploaded_file( $file['tmp_name'], $file_path ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to save file',
            ), 500 );
        }

        // Get file info
        $file_type = wp_check_filetype( $filename );
        $relative_path = str_replace( WP_CONTENT_DIR, '', $file_path );

        // Create document record
        $document_id = PFOB_Document::create( array(
            'project_id' => $project_id,
            'parent_id'  => $parent_id,
            'name'       => $filename,
            'type'       => $file_type['ext'] ?: 'file',
            'file_path'  => $relative_path,
            'file_size'  => $file['size'],
            'is_folder'  => 0,
        ) );

        if ( ! $document_id ) {
            // Clean up file if database insert fails
            unlink( $file_path );
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to create document record',
            ), 500 );
        }

        // Log activity
        PFOB_Activity::log(
            'document_uploaded',
            $project_id,
            get_current_user_id(),
            array(
                'document_id'   => $document_id,
                'document_name' => $filename,
            )
        );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Document uploaded successfully',
            'data'    => array(
                'id'   => $document_id,
                'name' => $filename,
            ),
        ), 200 );
    }

    /**
     * Create a folder.
     */
    public function create_folder( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $params = $request->get_json_params();

        if ( empty( $params['name'] ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Folder name is required',
            ), 400 );
        }

        $name = sanitize_text_field( $params['name'] );
        $parent_id = isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null;

        $folder_id = PFOB_Document::create_folder( $project_id, $name, $parent_id );

        if ( ! $folder_id ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to create folder',
            ), 500 );
        }

        // Log activity
        PFOB_Activity::log(
            'folder_created',
            $project_id,
            get_current_user_id(),
            array(
                'folder_id'   => $folder_id,
                'folder_name' => $name,
            )
        );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Folder created successfully',
            'data'    => array(
                'id'   => $folder_id,
                'name' => $name,
            ),
        ), 200 );
    }

    /**
     * Delete a document.
     */
    public function delete_document( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $document_id = $request->get_param( 'document_id' );

        $document = PFOB_Document::get( $document_id );

        if ( ! $document || $document->project_id != $project_id ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Document not found',
            ), 404 );
        }

        $success = PFOB_Document::delete( $document_id );

        if ( ! $success ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to delete document',
            ), 500 );
        }

        // Log activity
        PFOB_Activity::log(
            'document_deleted',
            $project_id,
            get_current_user_id(),
            array(
                'document_name' => $document->name,
            )
        );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Document deleted successfully',
        ), 200 );
    }

    /**
     * Rename a document.
     */
    public function rename_document( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $document_id = $request->get_param( 'document_id' );
        $params = $request->get_json_params();

        if ( empty( $params['name'] ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'New name is required',
            ), 400 );
        }

        $document = PFOB_Document::get( $document_id );

        if ( ! $document || $document->project_id != $project_id ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Document not found',
            ), 404 );
        }

        $new_name = sanitize_text_field( $params['name'] );
        $success = PFOB_Document::update( $document_id, array( 'name' => $new_name ) );

        if ( ! $success ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to rename document',
            ), 500 );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Document renamed successfully',
        ), 200 );
    }

    /**
     * Move a document to a different folder.
     */
    public function move_document( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $document_id = $request->get_param( 'document_id' );
        $params = $request->get_json_params();

        $document = PFOB_Document::get( $document_id );

        if ( ! $document || $document->project_id != $project_id ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Document not found',
            ), 404 );
        }

        $new_parent_id = isset( $params['parent_id'] ) ? intval( $params['parent_id'] ) : null;
        $success = PFOB_Document::update( $document_id, array( 'parent_id' => $new_parent_id ) );

        if ( ! $success ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Failed to move document',
            ), 500 );
        }

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Document moved successfully',
        ), 200 );
    }
}
