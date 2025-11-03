<?php
/**
 * Google Drive Integration Service
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_GDrive_Service {

    /**
     * Get Google OAuth authorization URL.
     */
    public static function get_authorization_url( $user_id ) {
        $client_id = get_option( 'pfob_google_drive_client_id' );
        $redirect_uri = self::get_redirect_uri();

        if ( empty( $client_id ) ) {
            return null;
        }

        // Store state token for CSRF protection
        $state = wp_generate_password( 32, false );
        update_user_meta( $user_id, 'pfob_gdrive_oauth_state', $state );

        $params = array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/drive.file https://www.googleapis.com/auth/userinfo.email',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        );

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params );
    }

    /**
     * Handle OAuth callback.
     */
    public static function handle_oauth_callback( $code, $state, $user_id ) {
        // Verify state token
        $stored_state = get_user_meta( $user_id, 'pfob_gdrive_oauth_state', true );
        if ( $state !== $stored_state ) {
            return new WP_Error( 'invalid_state', 'Invalid state parameter' );
        }

        // Exchange code for tokens
        $client_id = get_option( 'pfob_google_drive_client_id' );
        $client_secret = get_option( 'pfob_google_drive_client_secret' );
        $redirect_uri = self::get_redirect_uri();

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code'          => $code,
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri'  => $redirect_uri,
                'grant_type'    => 'authorization_code',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['access_token'] ) ) {
            // Store tokens
            update_user_meta( $user_id, 'pfob_gdrive_access_token', $body['access_token'] );

            if ( isset( $body['refresh_token'] ) ) {
                update_user_meta( $user_id, 'pfob_gdrive_refresh_token', $body['refresh_token'] );
            }

            // Store expiration
            $expires_at = time() + ( $body['expires_in'] ?? 3600 );
            update_user_meta( $user_id, 'pfob_gdrive_token_expires_at', $expires_at );

            // Get account info
            $account_info = self::get_account_info( $user_id );
            if ( $account_info && isset( $account_info['email'] ) ) {
                update_user_meta( $user_id, 'pfob_gdrive_account_email', $account_info['email'] );
            }

            // Create ProjectFOB folder
            self::ensure_project_folder( $user_id );

            // Clear state token
            delete_user_meta( $user_id, 'pfob_gdrive_oauth_state' );

            return true;
        }

        return new WP_Error( 'auth_failed', 'Failed to obtain access token' );
    }

    /**
     * Refresh access token.
     */
    private static function refresh_access_token( $user_id ) {
        $refresh_token = get_user_meta( $user_id, 'pfob_gdrive_refresh_token', true );

        if ( empty( $refresh_token ) ) {
            return false;
        }

        $client_id = get_option( 'pfob_google_drive_client_id' );
        $client_secret = get_option( 'pfob_google_drive_client_secret' );

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id'     => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
                'grant_type'    => 'refresh_token',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['access_token'] ) ) {
            update_user_meta( $user_id, 'pfob_gdrive_access_token', $body['access_token'] );

            $expires_at = time() + ( $body['expires_in'] ?? 3600 );
            update_user_meta( $user_id, 'pfob_gdrive_token_expires_at', $expires_at );

            return true;
        }

        return false;
    }

    /**
     * Get valid access token.
     */
    private static function get_valid_access_token( $user_id ) {
        $access_token = get_user_meta( $user_id, 'pfob_gdrive_access_token', true );
        $expires_at = get_user_meta( $user_id, 'pfob_gdrive_token_expires_at', true );

        // Check if token is expired or about to expire (5 min buffer)
        if ( empty( $access_token ) || $expires_at < ( time() + 300 ) ) {
            if ( ! self::refresh_access_token( $user_id ) ) {
                return null;
            }
            $access_token = get_user_meta( $user_id, 'pfob_gdrive_access_token', true );
        }

        return $access_token;
    }

    /**
     * Get account information.
     */
    public static function get_account_info( $user_id ) {
        $access_token = self::get_valid_access_token( $user_id );

        if ( empty( $access_token ) ) {
            return null;
        }

        $response = wp_remote_get( 'https://www.googleapis.com/oauth2/v2/userinfo', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    /**
     * Ensure ProjectFOB folder exists.
     */
    private static function ensure_project_folder( $user_id ) {
        $folder_id = get_user_meta( $user_id, 'pfob_gdrive_folder_id', true );

        if ( ! empty( $folder_id ) ) {
            return $folder_id;
        }

        $access_token = self::get_valid_access_token( $user_id );

        if ( empty( $access_token ) ) {
            return null;
        }

        // Create ProjectFOB folder
        $response = wp_remote_post( 'https://www.googleapis.com/drive/v3/files', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'name'     => 'ProjectFOB',
                'mimeType' => 'application/vnd.google-apps.folder',
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['id'] ) ) {
            update_user_meta( $user_id, 'pfob_gdrive_folder_id', $body['id'] );
            return $body['id'];
        }

        return null;
    }

    /**
     * Upload file to Google Drive.
     */
    public static function upload_file( $user_id, $file_path, $file_name ) {
        $access_token = self::get_valid_access_token( $user_id );

        if ( empty( $access_token ) ) {
            return new WP_Error( 'no_token', 'Not authenticated with Google Drive' );
        }

        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', 'File not found' );
        }

        $folder_id = self::ensure_project_folder( $user_id );
        $file_content = file_get_contents( $file_path );
        $mime_type = mime_content_type( $file_path );

        // Create file metadata
        $metadata = array(
            'name' => $file_name,
        );

        if ( $folder_id ) {
            $metadata['parents'] = array( $folder_id );
        }

        // Upload file using multipart upload
        $boundary = wp_generate_password( 32, false );

        $multipart = "--{$boundary}\r\n";
        $multipart .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
        $multipart .= json_encode( $metadata ) . "\r\n";
        $multipart .= "--{$boundary}\r\n";
        $multipart .= "Content-Type: {$mime_type}\r\n\r\n";
        $multipart .= $file_content . "\r\n";
        $multipart .= "--{$boundary}--";

        $response = wp_remote_post( 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => "multipart/related; boundary={$boundary}",
            ),
            'body'    => $multipart,
            'timeout' => 60,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['id'] ) ) {
            return $body;
        }

        return new WP_Error( 'upload_failed', 'Failed to upload file to Google Drive' );
    }

    /**
     * Get folder URL.
     */
    public static function get_folder_url( $user_id ) {
        $folder_id = get_user_meta( $user_id, 'pfob_gdrive_folder_id', true );

        if ( empty( $folder_id ) ) {
            return null;
        }

        return 'https://drive.google.com/drive/folders/' . $folder_id;
    }

    /**
     * Disconnect Google Drive.
     */
    public static function disconnect( $user_id ) {
        delete_user_meta( $user_id, 'pfob_gdrive_access_token' );
        delete_user_meta( $user_id, 'pfob_gdrive_refresh_token' );
        delete_user_meta( $user_id, 'pfob_gdrive_token_expires_at' );
        delete_user_meta( $user_id, 'pfob_gdrive_account_email' );
        delete_user_meta( $user_id, 'pfob_gdrive_oauth_state' );
        delete_user_meta( $user_id, 'pfob_gdrive_folder_id' );
        delete_user_meta( $user_id, 'pfob_gdrive_auto_sync' );
        delete_user_meta( $user_id, 'pfob_gdrive_folder_sync' );
        delete_user_meta( $user_id, 'pfob_gdrive_sharing' );
    }

    /**
     * Check if user is connected.
     */
    public static function is_connected( $user_id ) {
        $access_token = get_user_meta( $user_id, 'pfob_gdrive_access_token', true );
        $refresh_token = get_user_meta( $user_id, 'pfob_gdrive_refresh_token', true );

        return ! empty( $access_token ) || ! empty( $refresh_token );
    }

    /**
     * Get settings.
     */
    public static function get_settings( $user_id ) {
        return array(
            'is_connected'     => self::is_connected( $user_id ),
            'account_email'    => get_user_meta( $user_id, 'pfob_gdrive_account_email', true ),
            'auto_sync'        => get_user_meta( $user_id, 'pfob_gdrive_auto_sync', true ) === '1',
            'folder_sync'      => get_user_meta( $user_id, 'pfob_gdrive_folder_sync', true ) === '1',
            'sharing_enabled'  => get_user_meta( $user_id, 'pfob_gdrive_sharing', true ) === '1',
        );
    }

    /**
     * Update settings.
     */
    public static function update_settings( $user_id, $settings ) {
        if ( isset( $settings['auto_sync'] ) ) {
            update_user_meta( $user_id, 'pfob_gdrive_auto_sync', $settings['auto_sync'] ? '1' : '0' );
        }

        if ( isset( $settings['folder_sync'] ) ) {
            update_user_meta( $user_id, 'pfob_gdrive_folder_sync', $settings['folder_sync'] ? '1' : '0' );
        }

        if ( isset( $settings['sharing_enabled'] ) ) {
            update_user_meta( $user_id, 'pfob_gdrive_sharing', $settings['sharing_enabled'] ? '1' : '0' );
        }

        return true;
    }

    /**
     * Get redirect URI.
     */
    private static function get_redirect_uri() {
        return add_query_arg( 'service', 'gdrive', home_url( '/projectfob/settings/cloud-storage' ) );
    }
}
