<?php
/**
 * Dropbox Integration Service
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_Dropbox_Service {

    /**
     * Get Dropbox OAuth authorization URL.
     */
    public static function get_authorization_url( $user_id ) {
        $client_id = get_option( 'pfob_dropbox_client_id' );
        $redirect_uri = self::get_redirect_uri();

        if ( empty( $client_id ) ) {
            return null;
        }

        // Store state token for CSRF protection
        $state = wp_generate_password( 32, false );
        update_user_meta( $user_id, 'pfob_dropbox_oauth_state', $state );

        $params = array(
            'client_id'     => $client_id,
            'redirect_uri'  => $redirect_uri,
            'response_type' => 'code',
            'token_access_type' => 'offline',
            'state'         => $state,
        );

        return 'https://www.dropbox.com/oauth2/authorize?' . http_build_query( $params );
    }

    /**
     * Handle OAuth callback.
     */
    public static function handle_oauth_callback( $code, $state, $user_id ) {
        // Verify state token
        $stored_state = get_user_meta( $user_id, 'pfob_dropbox_oauth_state', true );
        if ( $state !== $stored_state ) {
            return new WP_Error( 'invalid_state', 'Invalid state parameter' );
        }

        // Exchange code for tokens
        $client_id = get_option( 'pfob_dropbox_client_id' );
        $client_secret = get_option( 'pfob_dropbox_client_secret' );
        $redirect_uri = self::get_redirect_uri();

        $response = wp_remote_post( 'https://api.dropboxapi.com/oauth2/token', array(
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
            update_user_meta( $user_id, 'pfob_dropbox_access_token', $body['access_token'] );

            if ( isset( $body['refresh_token'] ) ) {
                update_user_meta( $user_id, 'pfob_dropbox_refresh_token', $body['refresh_token'] );
            }

            // Get account info
            $account_info = self::get_account_info( $user_id );
            if ( $account_info && isset( $account_info['email'] ) ) {
                update_user_meta( $user_id, 'pfob_dropbox_account_email', $account_info['email'] );
            }

            // Clear state token
            delete_user_meta( $user_id, 'pfob_dropbox_oauth_state' );

            return true;
        }

        return new WP_Error( 'auth_failed', 'Failed to obtain access token' );
    }

    /**
     * Get account information.
     */
    public static function get_account_info( $user_id ) {
        $access_token = get_user_meta( $user_id, 'pfob_dropbox_access_token', true );

        if ( empty( $access_token ) ) {
            return null;
        }

        $response = wp_remote_post( 'https://api.dropboxapi.com/2/users/get_current_account', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => '{}',
        ) );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    /**
     * Upload file to Dropbox.
     */
    public static function upload_file( $user_id, $file_path, $dropbox_path ) {
        $access_token = get_user_meta( $user_id, 'pfob_dropbox_access_token', true );

        if ( empty( $access_token ) ) {
            return new WP_Error( 'no_token', 'Not authenticated with Dropbox' );
        }

        if ( ! file_exists( $file_path ) ) {
            return new WP_Error( 'file_not_found', 'File not found' );
        }

        $file_content = file_get_contents( $file_path );

        $response = wp_remote_post( 'https://content.dropboxapi.com/2/files/upload', array(
            'headers' => array(
                'Authorization'   => 'Bearer ' . $access_token,
                'Content-Type'    => 'application/octet-stream',
                'Dropbox-API-Arg' => json_encode( array(
                    'path' => $dropbox_path,
                    'mode' => 'overwrite',
                ) ),
            ),
            'body' => $file_content,
            'timeout' => 60,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['id'] ) ) {
            return $body;
        }

        return new WP_Error( 'upload_failed', 'Failed to upload file to Dropbox' );
    }

    /**
     * Create shared link.
     */
    public static function create_shared_link( $user_id, $dropbox_path ) {
        $access_token = get_user_meta( $user_id, 'pfob_dropbox_access_token', true );

        if ( empty( $access_token ) ) {
            return new WP_Error( 'no_token', 'Not authenticated with Dropbox' );
        }

        $response = wp_remote_post( 'https://api.dropboxapi.com/2/sharing/create_shared_link_with_settings', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type'  => 'application/json',
            ),
            'body' => json_encode( array(
                'path' => $dropbox_path,
            ) ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['url'] ) ) {
            return $body['url'];
        }

        return new WP_Error( 'link_failed', 'Failed to create shared link' );
    }

    /**
     * Disconnect Dropbox.
     */
    public static function disconnect( $user_id ) {
        delete_user_meta( $user_id, 'pfob_dropbox_access_token' );
        delete_user_meta( $user_id, 'pfob_dropbox_refresh_token' );
        delete_user_meta( $user_id, 'pfob_dropbox_account_email' );
        delete_user_meta( $user_id, 'pfob_dropbox_oauth_state' );
        delete_user_meta( $user_id, 'pfob_dropbox_auto_sync' );
        delete_user_meta( $user_id, 'pfob_dropbox_folder_sync' );
    }

    /**
     * Check if user is connected.
     */
    public static function is_connected( $user_id ) {
        $access_token = get_user_meta( $user_id, 'pfob_dropbox_access_token', true );
        return ! empty( $access_token );
    }

    /**
     * Get settings.
     */
    public static function get_settings( $user_id ) {
        return array(
            'is_connected'  => self::is_connected( $user_id ),
            'account_email' => get_user_meta( $user_id, 'pfob_dropbox_account_email', true ),
            'auto_sync'     => get_user_meta( $user_id, 'pfob_dropbox_auto_sync', true ) === '1',
            'folder_sync'   => get_user_meta( $user_id, 'pfob_dropbox_folder_sync', true ) === '1',
        );
    }

    /**
     * Update settings.
     */
    public static function update_settings( $user_id, $settings ) {
        if ( isset( $settings['auto_sync'] ) ) {
            update_user_meta( $user_id, 'pfob_dropbox_auto_sync', $settings['auto_sync'] ? '1' : '0' );
        }

        if ( isset( $settings['folder_sync'] ) ) {
            update_user_meta( $user_id, 'pfob_dropbox_folder_sync', $settings['folder_sync'] ? '1' : '0' );
        }

        return true;
    }

    /**
     * Get redirect URI.
     */
    private static function get_redirect_uri() {
        return add_query_arg( 'service', 'dropbox', home_url( '/projectfob/settings/cloud-storage' ) );
    }
}
