<?php
/**
 * WebSocket Service
 *
 * Handles communication between WordPress and the WebSocket server
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/services
 */

class BCWP_WebSocket_Service {

    /**
     * Check if WebSocket is enabled.
     */
    public static function is_enabled() {
        return get_option( 'bcwp_websocket_enabled' ) === '1';
    }

    /**
     * Get WebSocket server URL.
     */
    public static function get_server_url() {
        return get_option( 'bcwp_websocket_url', 'http://localhost:3000' );
    }

    /**
     * Get WebSocket secret.
     */
    private static function get_secret() {
        return get_option( 'bcwp_websocket_secret', '' );
    }

    /**
     * Trigger a WebSocket event.
     */
    public static function trigger_event( $project_id, $event, $payload ) {
        if ( ! self::is_enabled() ) {
            return false;
        }

        $server_url = self::get_server_url();
        $secret = self::get_secret();

        if ( empty( $server_url ) || empty( $secret ) ) {
            return false;
        }

        $response = wp_remote_post( $server_url . '/api/trigger', array(
            'body' => json_encode( array(
                'projectId' => $project_id,
                'event' => $event,
                'payload' => $payload,
                'secret' => $secret,
            ) ),
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
            'timeout' => 5,
        ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'WebSocket trigger failed: ' . $response->get_error_message() );
            return false;
        }

        return true;
    }

    /**
     * Broadcast chat message.
     */
    public static function broadcast_chat_message( $project_id, $message ) {
        return self::trigger_event( $project_id, 'chat-message', array(
            'id' => $message->id,
            'content' => $message->content,
            'userId' => $message->user_id,
            'userName' => BCWP_Auth_Service::get_user_display_name( $message->user_id ),
            'avatar' => get_avatar_url( $message->user_id ),
            'createdAt' => $message->created_at,
        ) );
    }

    /**
     * Broadcast activity update.
     */
    public static function broadcast_activity( $project_id, $activity ) {
        return self::trigger_event( $project_id, 'activity-update', array(
            'id' => $activity->id,
            'type' => $activity->activity_type,
            'subjectType' => $activity->subject_type,
            'subjectId' => $activity->subject_id,
            'description' => $activity->description,
            'userId' => $activity->user_id,
            'userName' => BCWP_Auth_Service::get_user_display_name( $activity->user_id ),
            'createdAt' => $activity->created_at,
        ) );
    }

    /**
     * Broadcast notification to specific user.
     */
    public static function send_notification( $user_id, $notification ) {
        if ( ! self::is_enabled() ) {
            return false;
        }

        // For user-specific notifications, we need a different approach
        // The WebSocket server handles this via socket.io rooms
        return self::trigger_event( null, 'send-notification', array(
            'targetUserId' => $user_id,
            'notification' => $notification,
        ) );
    }

    /**
     * Generate JWT token for WebSocket authentication.
     */
    public static function generate_token( $user_id ) {
        $user = get_user_by( 'ID', $user_id );

        if ( ! $user ) {
            return null;
        }

        $payload = array(
            'user_id' => $user_id,
            'user_name' => $user->display_name,
            'user_email' => $user->user_email,
            'iat' => time(),
            'exp' => time() + ( 24 * 60 * 60 ), // 24 hours
        );

        // In production, use a proper JWT library
        // For now, we'll use a simple encoding
        $secret = get_option( 'bcwp_websocket_secret', wp_salt() );
        $header = base64_encode( json_encode( array( 'typ' => 'JWT', 'alg' => 'HS256' ) ) );
        $payload_encoded = base64_encode( json_encode( $payload ) );
        $signature = hash_hmac( 'sha256', $header . '.' . $payload_encoded, $secret, true );
        $signature_encoded = base64_encode( $signature );

        return $header . '.' . $payload_encoded . '.' . $signature_encoded;
    }

    /**
     * Get WebSocket configuration for frontend.
     */
    public static function get_frontend_config( $user_id ) {
        if ( ! self::is_enabled() ) {
            return array(
                'enabled' => false,
            );
        }

        return array(
            'enabled' => true,
            'url' => self::get_server_url(),
            'token' => self::generate_token( $user_id ),
        );
    }
}
