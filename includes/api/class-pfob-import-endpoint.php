<?php
/**
 * Import API Endpoint
 *
 * Handles project imports from other PMS systems via ZIP file upload.
 *
 * @package ProjectFOB
 */

class PFOB_Import_Endpoint extends PFOB_REST_API {

    /**
     * Register routes.
     */
    public function register_routes() {
        // Import project from ZIP file
        register_rest_route(
            $this->namespace,
            '/projects/import',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'import_project' ),
                'permission_callback' => array( $this, 'check_user_permission' ),
            )
        );
    }

    /**
     * Import project from ZIP file.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function import_project( $request ) {
        // Get uploaded file
        $files = $request->get_file_params();

        if ( empty( $files['project_zip'] ) ) {
            return new WP_Error(
                'no_file',
                __( 'No file uploaded.', 'projectfob' ),
                array( 'status' => 400 )
            );
        }

        $zip_file = $files['project_zip'];

        // Validate file type
        $file_type = wp_check_filetype( $zip_file['name'] );
        if ( $file_type['ext'] !== 'zip' ) {
            return new WP_Error(
                'invalid_file_type',
                __( 'Only ZIP files are allowed.', 'projectfob' ),
                array( 'status' => 400 )
            );
        }

        // Get project name
        $project_name = sanitize_text_field( $request->get_param( 'project_name' ) );

        if ( empty( $project_name ) ) {
            return new WP_Error(
                'missing_project_name',
                __( 'Project name is required.', 'projectfob' ),
                array( 'status' => 400 )
            );
        }

        // Get current user
        $user_id = get_current_user_id();

        // Verify user has active subscription
        $subscription = PFOB_Subscription::get_by_user_id( $user_id );
        if ( ! $subscription || $subscription->status !== 'active' ) {
            return new WP_Error(
                'no_active_subscription',
                __( 'You need an active subscription to import projects.', 'projectfob' ),
                array( 'status' => 403 )
            );
        }

        // Create project
        global $wpdb;
        $projects_table = $wpdb->prefix . 'pfob_projects';

        $slug = sanitize_title( $project_name );
        $original_slug = $slug;
        $counter = 1;

        // Ensure unique slug
        while ( $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM {$projects_table} WHERE slug = %s",
            $slug
        ) ) > 0 ) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        $inserted = $wpdb->insert(
            $projects_table,
            array(
                'name'        => $project_name,
                'slug'        => $slug,
                'description' => __( 'Imported from other PMS', 'projectfob' ),
                'owner_id'    => $user_id,
                'created_at'  => current_time( 'mysql' ),
                'updated_at'  => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%d', '%s', '%s' )
        );

        if ( ! $inserted ) {
            return new WP_Error(
                'project_creation_failed',
                __( 'Failed to create project.', 'projectfob' ),
                array( 'status' => 500 )
            );
        }

        $project_id = $wpdb->insert_id;

        // Add user as project member
        $members_table = $wpdb->prefix . 'pfob_project_members';
        $wpdb->insert(
            $members_table,
            array(
                'project_id' => $project_id,
                'user_id'    => $user_id,
                'role'       => 'owner',
                'joined_at'  => current_time( 'mysql' ),
            ),
            array( '%d', '%d', '%s', '%s' )
        );

        // Extract ZIP file
        WP_Filesystem();
        global $wp_filesystem;

        $upload_dir = wp_upload_dir();
        $temp_dir = trailingslashit( $upload_dir['basedir'] ) . 'pfob-temp-import-' . time();

        if ( ! $wp_filesystem->mkdir( $temp_dir ) ) {
            // Cleanup project
            $wpdb->delete( $projects_table, array( 'id' => $project_id ), array( '%d' ) );
            $wpdb->delete( $members_table, array( 'project_id' => $project_id ), array( '%d' ) );

            return new WP_Error(
                'temp_dir_creation_failed',
                __( 'Failed to create temporary directory.', 'projectfob' ),
                array( 'status' => 500 )
            );
        }

        // Unzip file
        $unzip_result = unzip_file( $zip_file['tmp_name'], $temp_dir );

        if ( is_wp_error( $unzip_result ) ) {
            // Cleanup
            $wp_filesystem->rmdir( $temp_dir, true );
            $wpdb->delete( $projects_table, array( 'id' => $project_id ), array( '%d' ) );
            $wpdb->delete( $members_table, array( 'project_id' => $project_id ), array( '%d' ) );

            return new WP_Error(
                'unzip_failed',
                __( 'Failed to extract ZIP file: ', 'projectfob' ) . $unzip_result->get_error_message(),
                array( 'status' => 500 )
            );
        }

        // Upload all files to R2 with user-specific path
        $uploaded_files = array();
        $this->upload_directory_to_r2( $temp_dir, '', $user_id, $project_id, $uploaded_files );

        // Create document records for all uploaded files
        $documents_table = $wpdb->prefix . 'pfob_documents';

        foreach ( $uploaded_files as $file_info ) {
            $wpdb->insert(
                $documents_table,
                array(
                    'project_id'  => $project_id,
                    'name'        => $file_info['name'],
                    'file_path'   => $file_info['r2_path'],
                    'file_size'   => $file_info['size'],
                    'file_type'   => $file_info['type'],
                    'uploaded_by' => $user_id,
                    'uploaded_at' => current_time( 'mysql' ),
                ),
                array( '%d', '%s', '%s', '%d', '%s', '%d', '%s' )
            );
        }

        // Cleanup temp directory
        $wp_filesystem->rmdir( $temp_dir, true );

        // Log activity
        PFOB_Activity::log(
            $project_id,
            $user_id,
            'project_imported',
            sprintf(
                __( 'imported project "%s" with %d files', 'projectfob' ),
                $project_name,
                count( $uploaded_files )
            )
        );

        return rest_ensure_response(
            array(
                'success'       => true,
                'project_id'    => $project_id,
                'project_slug'  => $slug,
                'project_url'   => home_url( "/projectfob/projects/{$slug}" ),
                'files_count'   => count( $uploaded_files ),
                'message'       => sprintf(
                    __( 'Project "%s" imported successfully with %d files.', 'projectfob' ),
                    $project_name,
                    count( $uploaded_files )
                ),
            )
        );
    }

    /**
     * Recursively upload directory to R2 with user-specific path.
     *
     * @param string $dir_path Local directory path.
     * @param string $relative_path Relative path within the ZIP.
     * @param int $user_id User ID.
     * @param int $project_id Project ID.
     * @param array &$uploaded_files Array to store uploaded file info.
     */
    private function upload_directory_to_r2( $dir_path, $relative_path, $user_id, $project_id, &$uploaded_files ) {
        global $wp_filesystem;

        $items = $wp_filesystem->dirlist( $dir_path );

        if ( ! $items ) {
            return;
        }

        foreach ( $items as $item_name => $item_info ) {
            $item_path = trailingslashit( $dir_path ) . $item_name;
            $new_relative_path = $relative_path ? trailingslashit( $relative_path ) . $item_name : $item_name;

            // Skip hidden files and Mac metadata
            if ( strpos( $item_name, '.' ) === 0 || $item_name === '__MACOSX' ) {
                continue;
            }

            if ( $item_info['type'] === 'd' ) {
                // Recursively process subdirectories
                $this->upload_directory_to_r2( $item_path, $new_relative_path, $user_id, $project_id, $uploaded_files );
            } else {
                // Upload file to R2 with user-specific path
                // Path structure: uploads/user_{user_id}/project_{project_id}/{relative_path}
                $r2_path = "uploads/user_{$user_id}/project_{$project_id}/{$new_relative_path}";

                $content_type = mime_content_type( $item_path );
                $upload_result = PFOB_R2_Storage_Service::upload_file(
                    $item_path,
                    $r2_path,
                    $content_type
                );

                if ( ! is_wp_error( $upload_result ) ) {
                    $uploaded_files[] = array(
                        'name'     => basename( $new_relative_path ),
                        'r2_path'  => $r2_path,
                        'size'     => filesize( $item_path ),
                        'type'     => $content_type,
                    );
                }
            }
        }
    }
}
