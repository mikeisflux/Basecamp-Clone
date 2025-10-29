<?php
/**
 * Event model class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_Event {

    public static function create( $data ) {
        $data['created_by'] = get_current_user_id();

        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }

        return PFOB_Database::insert( 'events', $data );
    }

    public static function get( $event_id ) {
        return PFOB_Database::get_row( 'events', array( 'id' => $event_id ) );
    }

    public static function get_project_events( $project_id, $start_date = null, $end_date = null ) {
        global $wpdb;

        $sql = "SELECT * FROM " . PFOB_Database::get_table_name( 'events' ) . "
                WHERE project_id = %d";

        $params = array( $project_id );

        if ( $start_date ) {
            $sql .= " AND end_datetime >= %s";
            $params[] = $start_date;
        }

        if ( $end_date ) {
            $sql .= " AND start_datetime <= %s";
            $params[] = $end_date;
        }

        $sql .= " ORDER BY start_datetime ASC";

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    public static function update( $event_id, $data ) {
        if ( isset( $data['metadata'] ) && is_array( $data['metadata'] ) ) {
            $data['metadata'] = wp_json_encode( $data['metadata'] );
        }
        return PFOB_Database::update( 'events', $data, array( 'id' => $event_id ) );
    }

    public static function delete( $event_id ) {
        return PFOB_Database::delete( 'events', array( 'id' => $event_id ) );
    }

    public static function get_upcoming_events( $project_id, $limit = 10 ) {
        global $wpdb;

        $sql = "SELECT * FROM " . PFOB_Database::get_table_name( 'events' ) . "
                WHERE project_id = %d AND start_datetime >= %s
                ORDER BY start_datetime ASC
                LIMIT %d";

        return $wpdb->get_results( $wpdb->prepare(
            $sql,
            $project_id,
            current_time( 'mysql' ),
            $limit
        ) );
    }
}
