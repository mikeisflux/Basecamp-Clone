<?php
/**
 * Google Calendar Integration Service
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/services
 */

class BCWP_Google_Calendar_Service {

    /**
     * Get Google OAuth authorization URL.
     */
    public static function get_authorization_url( $user_id ) {
        $client_id = get_option( 'bcwp_google_client_id' );
        $redirect_uri = self::get_redirect_uri();

        if ( empty( $client_id ) ) {
            return null;
        }

        // Store state token for CSRF protection
        $state = wp_generate_password( 32, false );
        update_user_meta( $user_id, 'bcwp_google_oauth_state', $state );

        $params = array(
            'client_id' => $client_id,
            'redirect_uri' => $redirect_uri,
            'response_type' => 'code',
            'scope' => 'https://www.googleapis.com/auth/calendar.events',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        );

        return 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query( $params );
    }

    /**
     * Handle OAuth callback.
     */
    public static function handle_oauth_callback( $code, $state, $user_id ) {
        // Verify state token
        $stored_state = get_user_meta( $user_id, 'bcwp_google_oauth_state', true );
        if ( $state !== $stored_state ) {
            return new WP_Error( 'invalid_state', 'Invalid state parameter' );
        }

        // Exchange code for tokens
        $client_id = get_option( 'bcwp_google_client_id' );
        $client_secret = get_option( 'bcwp_google_client_secret' );
        $redirect_uri = self::get_redirect_uri();

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'code' => $code,
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'redirect_uri' => $redirect_uri,
                'grant_type' => 'authorization_code',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['access_token'] ) ) {
            // Store tokens
            update_user_meta( $user_id, 'bcwp_google_access_token', $body['access_token'] );

            if ( isset( $body['refresh_token'] ) ) {
                update_user_meta( $user_id, 'bcwp_google_refresh_token', $body['refresh_token'] );
            }

            // Store expiration
            $expires_at = time() + ( $body['expires_in'] ?? 3600 );
            update_user_meta( $user_id, 'bcwp_google_token_expires_at', $expires_at );

            // Clear state token
            delete_user_meta( $user_id, 'bcwp_google_oauth_state' );

            return true;
        }

        return new WP_Error( 'auth_failed', 'Failed to obtain access token' );
    }

    /**
     * Refresh access token.
     */
    private static function refresh_access_token( $user_id ) {
        $refresh_token = get_user_meta( $user_id, 'bcwp_google_refresh_token', true );

        if ( empty( $refresh_token ) ) {
            return false;
        }

        $client_id = get_option( 'bcwp_google_client_id' );
        $client_secret = get_option( 'bcwp_google_client_secret' );

        $response = wp_remote_post( 'https://oauth2.googleapis.com/token', array(
            'body' => array(
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'refresh_token' => $refresh_token,
                'grant_type' => 'refresh_token',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return false;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['access_token'] ) ) {
            update_user_meta( $user_id, 'bcwp_google_access_token', $body['access_token'] );

            $expires_at = time() + ( $body['expires_in'] ?? 3600 );
            update_user_meta( $user_id, 'bcwp_google_token_expires_at', $expires_at );

            return true;
        }

        return false;
    }

    /**
     * Get valid access token.
     */
    private static function get_valid_access_token( $user_id ) {
        $access_token = get_user_meta( $user_id, 'bcwp_google_access_token', true );
        $expires_at = get_user_meta( $user_id, 'bcwp_google_token_expires_at', true );

        // Check if token is expired or about to expire (5 min buffer)
        if ( empty( $access_token ) || $expires_at < ( time() + 300 ) ) {
            if ( ! self::refresh_access_token( $user_id ) ) {
                return null;
            }
            $access_token = get_user_meta( $user_id, 'bcwp_google_access_token', true );
        }

        return $access_token;
    }

    /**
     * Sync event to Google Calendar.
     */
    public static function sync_event_to_google( $event, $user_id ) {
        $access_token = self::get_valid_access_token( $user_id );

        if ( ! $access_token ) {
            return new WP_Error( 'no_token', 'Not authenticated with Google' );
        }

        // Get or create calendar ID
        $calendar_id = get_user_meta( $user_id, 'bcwp_google_calendar_id', true );

        if ( empty( $calendar_id ) ) {
            $calendar_id = 'primary'; // Use primary calendar
        }

        // Prepare event data
        $google_event = array(
            'summary' => $event->title,
            'description' => $event->description,
            'start' => array(
                'dateTime' => gmdate( 'c', strtotime( $event->start_datetime ) ),
                'timeZone' => wp_timezone_string(),
            ),
            'end' => array(
                'dateTime' => gmdate( 'c', strtotime( $event->end_datetime ) ),
                'timeZone' => wp_timezone_string(),
            ),
        );

        if ( $event->all_day ) {
            $google_event['start'] = array(
                'date' => gmdate( 'Y-m-d', strtotime( $event->start_datetime ) ),
            );
            $google_event['end'] = array(
                'date' => gmdate( 'Y-m-d', strtotime( $event->end_datetime ) ),
            );
        }

        // Check if event already synced (stored in metadata JSON column)
        $metadata = $event->metadata ? json_decode( $event->metadata, true ) : array();
        $google_event_id = isset( $metadata['google_calendar_ids'][ $user_id ] ) ? $metadata['google_calendar_ids'][ $user_id ] : null;

        if ( $google_event_id ) {
            // Update existing event
            $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendar_id}/events/{$google_event_id}";
            $method = 'PUT';
        } else {
            // Create new event
            $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendar_id}/events";
            $method = 'POST';
        }

        $response = wp_remote_request( $url, array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Content-Type' => 'application/json',
            ),
            'body' => json_encode( $google_event ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( isset( $body['id'] ) ) {
            // Store Google event ID in metadata column
            if ( ! isset( $metadata['google_calendar_ids'] ) ) {
                $metadata['google_calendar_ids'] = array();
            }
            $metadata['google_calendar_ids'][ $user_id ] = $body['id'];

            // Update event metadata
            BCWP_Event::update( $event->id, array(
                'metadata' => json_encode( $metadata )
            ) );

            return true;
        }

        return new WP_Error( 'sync_failed', 'Failed to sync event to Google Calendar' );
    }

    /**
     * Delete event from Google Calendar.
     */
    public static function delete_event_from_google( $event_id, $user_id ) {
        $access_token = self::get_valid_access_token( $user_id );

        if ( ! $access_token ) {
            return new WP_Error( 'no_token', 'Not authenticated with Google' );
        }

        // Get event to access metadata
        $event = BCWP_Event::get( $event_id );

        if ( ! $event ) {
            return new WP_Error( 'event_not_found', 'Event not found' );
        }

        $metadata = $event->metadata ? json_decode( $event->metadata, true ) : array();
        $google_event_id = isset( $metadata['google_calendar_ids'][ $user_id ] ) ? $metadata['google_calendar_ids'][ $user_id ] : null;

        if ( empty( $google_event_id ) ) {
            return true; // Nothing to delete
        }

        $calendar_id = get_user_meta( $user_id, 'bcwp_google_calendar_id', true ) ?: 'primary';
        $url = "https://www.googleapis.com/calendar/v3/calendars/{$calendar_id}/events/{$google_event_id}";

        $response = wp_remote_request( $url, array(
            'method' => 'DELETE',
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Remove from metadata
        unset( $metadata['google_calendar_ids'][ $user_id ] );
        BCWP_Event::update( $event_id, array(
            'metadata' => json_encode( $metadata )
        ) );

        return true;
    }

    /**
     * Disconnect Google Calendar.
     */
    public static function disconnect( $user_id ) {
        delete_user_meta( $user_id, 'bcwp_google_access_token' );
        delete_user_meta( $user_id, 'bcwp_google_refresh_token' );
        delete_user_meta( $user_id, 'bcwp_google_token_expires_at' );
        delete_user_meta( $user_id, 'bcwp_google_calendar_id' );
        delete_user_meta( $user_id, 'bcwp_google_oauth_state' );
    }

    /**
     * Check if user is connected.
     */
    public static function is_connected( $user_id ) {
        $access_token = get_user_meta( $user_id, 'bcwp_google_access_token', true );
        $refresh_token = get_user_meta( $user_id, 'bcwp_google_refresh_token', true );

        return ! empty( $access_token ) || ! empty( $refresh_token );
    }

    /**
     * Get redirect URI.
     */
    private static function get_redirect_uri() {
        return home_url( '/projectfob/settings/calendar-integration' );
    }

    /**
     * Get Google Calendar settings.
     */
    public static function get_settings( $user_id ) {
        return array(
            'is_connected' => self::is_connected( $user_id ),
            'auto_sync' => get_user_meta( $user_id, 'bcwp_google_auto_sync', true ) === '1',
        );
    }

    /**
     * Update settings.
     */
    public static function update_settings( $user_id, $settings ) {
        if ( isset( $settings['auto_sync'] ) ) {
            update_user_meta( $user_id, 'bcwp_google_auto_sync', $settings['auto_sync'] ? '1' : '0' );
        }
    }
}
