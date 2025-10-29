<?php
/**
 * Chat model class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_Chat {

    public static function create_message( $data ) {
        $data['user_id'] = get_current_user_id();

        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        return PFOB_Database::insert( 'chat_messages', $data );
    }

    public static function get_message( $message_id ) {
        return PFOB_Database::get_row( 'chat_messages', array( 'id' => $message_id ) );
    }

    public static function get_project_messages( $project_id, $limit = 50, $offset = 0 ) {
        return PFOB_Database::get_results(
            'chat_messages',
            array( 'project_id' => $project_id, 'is_deleted' => 0 ),
            array( 'created_at' => 'ASC' ),
            $limit,
            $offset
        );
    }

    public static function get_messages_since( $project_id, $since_id ) {
        global $wpdb;

        $sql = "SELECT * FROM " . PFOB_Database::get_table_name( 'chat_messages' ) . "
                WHERE project_id = %d AND id > %d AND is_deleted = 0
                ORDER BY created_at ASC";

        return $wpdb->get_results( $wpdb->prepare( $sql, $project_id, $since_id ) );
    }

    public static function update_message( $message_id, $data ) {
        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        return PFOB_Database::update( 'chat_messages', $data, array( 'id' => $message_id ) );
    }

    public static function delete_message( $message_id ) {
        return self::update_message( $message_id, array(
            'is_deleted' => 1,
            'deleted_at' => current_time( 'mysql' ),
        ) );
    }
}
