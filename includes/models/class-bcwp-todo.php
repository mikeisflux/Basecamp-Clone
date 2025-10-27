<?php
/**
 * Todo model class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/models
 */

class BCWP_Todo {

    public static function create_list( $data ) {
        $data['created_by'] = get_current_user_id();
        return BCWP_Database::insert( 'todo_lists', $data );
    }

    public static function get_list( $list_id ) {
        return BCWP_Database::get_row( 'todo_lists', array( 'id' => $list_id ) );
    }

    public static function get_project_lists( $project_id ) {
        return BCWP_Database::get_results(
            'todo_lists',
            array( 'project_id' => $project_id, 'is_archived' => 0 ),
            array( 'position' => 'ASC' )
        );
    }

    public static function update_list( $list_id, $data ) {
        return BCWP_Database::update( 'todo_lists', $data, array( 'id' => $list_id ) );
    }

    public static function delete_list( $list_id ) {
        return BCWP_Database::delete( 'todo_lists', array( 'id' => $list_id ) );
    }

    public static function create_item( $data ) {
        $data['created_by'] = get_current_user_id();

        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        $item_id = BCWP_Database::insert( 'todo_items', $data );

        if ( $item_id && ! empty( $data['assignee_id'] ) ) {
            // Send notification to assignee
            $list = self::get_list( $data['list_id'] );
            BCWP_Database::insert( 'notifications', array(
                'user_id'      => $data['assignee_id'],
                'project_id'   => $list->project_id,
                'type'         => 'todo.assigned',
                'subject_type' => 'todo',
                'subject_id'   => $item_id,
                'title'        => 'You were assigned a to-do',
                'message'      => $data['content'],
            ) );
        }

        return $item_id;
    }

    public static function get_item( $item_id ) {
        return BCWP_Database::get_row( 'todo_items', array( 'id' => $item_id ) );
    }

    public static function get_list_items( $list_id ) {
        return BCWP_Database::get_results(
            'todo_items',
            array( 'list_id' => $list_id ),
            array( 'position' => 'ASC' )
        );
    }

    public static function update_item( $item_id, $data ) {
        if ( isset( $data['attachments'] ) && is_array( $data['attachments'] ) ) {
            $data['attachments'] = wp_json_encode( $data['attachments'] );
        }
        return BCWP_Database::update( 'todo_items', $data, array( 'id' => $item_id ) );
    }

    public static function delete_item( $item_id ) {
        return BCWP_Database::delete( 'todo_items', array( 'id' => $item_id ) );
    }

    public static function complete_item( $item_id ) {
        return self::update_item( $item_id, array(
            'is_completed' => 1,
            'completed_at' => current_time( 'mysql' ),
            'completed_by' => get_current_user_id(),
        ) );
    }

    public static function uncomplete_item( $item_id ) {
        return self::update_item( $item_id, array(
            'is_completed' => 0,
            'completed_at' => null,
            'completed_by' => null,
        ) );
    }

    public static function get_user_assigned_items( $user_id, $project_id = null ) {
        global $wpdb;

        $sql = "SELECT ti.*, tl.name as list_name, tl.project_id
                FROM " . BCWP_Database::get_table_name( 'todo_items' ) . " ti
                INNER JOIN " . BCWP_Database::get_table_name( 'todo_lists' ) . " tl
                ON ti.list_id = tl.id
                WHERE ti.assignee_id = %d AND ti.is_completed = 0";

        $params = array( $user_id );

        if ( $project_id ) {
            $sql .= " AND tl.project_id = %d";
            $params[] = $project_id;
        }

        $sql .= " ORDER BY ti.due_date ASC, ti.created_at ASC";

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }
}
