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
        // Get document
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/documents/(?P<document_id>\d+)', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_document' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

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
     * Get a document.
     */
    public function get_document( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $document_id = $request->get_param( 'document_id' );

        $document = PFOB_Document::get( $document_id );

        if ( ! $document || $document->project_id != $project_id ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'Document not found',
            ), 404 );
        }

        // Generate download URL if file exists
        $download_url = null;
        if ( ! empty( $document->file_path ) ) {
            $download_url = WP_CONTENT_URL . $document->file_path;
        }

        // Get mime type from file
        $mime_type = ! empty( $document->mime_type ) ? $document->mime_type : 'application/octet-stream';

        return new WP_REST_Response( array(
            'success' => true,
            'data'    => array(
                'id'           => $document->id,
                'name'         => $document->name,
                'type'         => $document->type,
                'mime_type'    => $mime_type,
                'file_path'    => $document->file_path,
                'file_size'    => $document->file_size,
                'is_folder'    => $document->is_folder,
                'url'          => $download_url,
                'download_url' => $download_url,
                'created_at'   => $document->created_at,
            ),
        ), 200 );
    }

    /**
     * Upload a document.
     */
    public function upload_document( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $files = $request->get_file_params();

        if ( empty( $files['file'] ) ) {
            return new WP_REST_Response( array(
                'success' => false,
                'message' => 'No file uploaded',
            ), 400 );
        }

        $file = $files['file'];
        $parent_id = $request->get_param( 'parent_id' ) ? intval( $request->get_param( 'parent_id' ) ) : null;

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
        $mime_type = ! empty( $file_type['type'] ) ? $file_type['type'] : 'application/octet-stream';

        // Create document record
        $document_id = PFOB_Document::create( array(
            'project_id' => $project_id,
            'parent_id'  => $parent_id,
            'name'       => $filename,
            'type'       => $file_type['ext'] ?: 'file',
            'mime_type'  => $mime_type,
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
        PFOB_Activity::log( array(
            'project_id'   => $project_id,
            'user_id'      => get_current_user_id(),
            'action_type'  => 'uploaded',
            'subject_type' => 'document',
            'subject_id'   => $document_id,
            'description'  => 'Uploaded document: ' . $filename,
            'metadata'     => array(
                'filename' => $filename,
                'size'     => $file['size'],
            ),
        ) );

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
        PFOB_Activity::log( array(
            'project_id'   => $project_id,
            'user_id'      => get_current_user_id(),
            'action_type'  => 'created',
            'subject_type' => 'folder',
            'subject_id'   => $folder_id,
            'description'  => 'Created folder: ' . $name,
            'metadata'     => array(
                'folder_name' => $name,
            ),
        ) );

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
        PFOB_Activity::log( array(
            'project_id'   => $project_id,
            'user_id'      => get_current_user_id(),
            'action_type'  => 'deleted',
            'subject_type' => 'document',
            'subject_id'   => $document_id,
            'description'  => 'Deleted document: ' . $document->name,
            'metadata'     => array(
                'filename' => $document->name,
            ),
        ) );

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
