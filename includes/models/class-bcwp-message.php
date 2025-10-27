<?php
/**
 * Message model class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/models
 */

class BCWP_Message {

    public static function create( $data ) {
        $data['author_id'] = get_current_user_id();

        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        $message_id = BCWP_Database::insert( 'messages', $data );

        if ( $message_id ) {
            BCWP_Database::insert( 'activities', array(
                'project_id'   => $data['project_id'],
                'user_id'      => $data['author_id'],
                'action_type'  => 'message.created',
                'subject_type' => 'message',
                'subject_id'   => $message_id,
                'description'  => sprintf( 'Posted message: %s', $data['title'] ),
            ) );
        }

        return $message_id;
    }

    public static function get( $message_id ) {
        return BCWP_Database::get_row( 'messages', array( 'id' => $message_id ) );
    }

    public static function get_project_messages( $project_id, $limit = 20, $offset = 0 ) {
        return BCWP_Database::get_results(
            'messages',
            array( 'project_id' => $project_id, 'is_archived' => 0 ),
            array( 'is_pinned' => 'DESC', 'created_at' => 'DESC' ),
            $limit,
            $offset
        );
    }

    public static function update( $message_id, $data ) {
        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        return BCWP_Database::update( 'messages', $data, array( 'id' => $message_id ) );
    }

    public static function delete( $message_id ) {
        return BCWP_Database::delete( 'messages', array( 'id' => $message_id ) );
    }

    public static function pin( $message_id ) {
        return self::update( $message_id, array( 'is_pinned' => 1 ) );
    }

    public static function unpin( $message_id ) {
        return self::update( $message_id, array( 'is_pinned' => 0 ) );
    }

    public static function archive( $message_id ) {
        return self::update( $message_id, array( 'is_archived' => 1 ) );
    }
}
