<?php
/**
 * Notification service class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_Notification_Service {

    public static function create( $user_id, $data ) {
        $notification_data = array(
            'user_id'      => $user_id,
            'type'         => $data['type'],
            'title'        => $data['title'],
            'message'      => $data['message'],
            'project_id'   => $data['project_id'] ?? null,
            'subject_type' => $data['subject_type'] ?? null,
            'subject_id'   => $data['subject_id'] ?? null,
            'action_url'   => $data['action_url'] ?? null,
            'icon'         => $data['icon'] ?? null,
            'metadata'     => isset( $data['metadata'] ) ? wp_json_encode( $data['metadata'] ) : null,
        );

        $notification_id = PFOB_Database::insert( 'notifications', $notification_data );

        // Send email if enabled
        if ( get_option( 'pfob_enable_email_notifications' ) ) {
            self::send_email_notification( $user_id, $notification_data );
        }

        return $notification_id;
    }

    public static function get_user_notifications( $user_id, $unread_only = false, $limit = 20 ) {
        $where = array( 'user_id' => $user_id );

        if ( $unread_only ) {
            $where['is_read'] = 0;
        }

        return PFOB_Database::get_results(
            'notifications',
            $where,
            array( 'created_at' => 'DESC' ),
            $limit
        );
    }

    public static function mark_as_read( $notification_id ) {
        return PFOB_Database::update(
            'notifications',
            array(
                'is_read' => 1,
                'read_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $notification_id )
        );
    }

    public static function mark_all_as_read( $user_id ) {
        global $wpdb;

        return $wpdb->query( $wpdb->prepare(
            "UPDATE " . PFOB_Database::get_table_name( 'notifications' ) . "
             SET is_read = 1, read_at = %s
             WHERE user_id = %d AND is_read = 0",
            current_time( 'mysql' ),
            $user_id
        ) );
    }

    public static function get_unread_count( $user_id ) {
        return PFOB_Database::count( 'notifications', array(
            'user_id' => $user_id,
            'is_read' => 0,
        ) );
    }

    private static function send_email_notification( $user_id, $notification_data ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return;
        }

        $to = $user->user_email;
        $subject = '[' . get_option( 'pfob_company_name' ) . '] ' . $notification_data['title'];
        $message = $notification_data['message'];

        if ( ! empty( $notification_data['action_url'] ) ) {
            $message .= "\n\n" . $notification_data['action_url'];
        }

        wp_mail( $to, $subject, $message );
    }
}
