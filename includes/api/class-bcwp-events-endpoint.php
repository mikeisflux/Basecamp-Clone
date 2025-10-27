<?php
/**
 * Events REST API Endpoint
 *
 * Handles calendar events and iCal export
 */

class BCWP_Events_Endpoint {

    /**
     * Register routes
     */
    public function register_routes() {
        // Get events
        register_rest_route( 'bcwp/v1', '/projects/(?P<project_id>\d+)/events', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'get_events' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Create event
        register_rest_route( 'bcwp/v1', '/projects/(?P<project_id>\d+)/events', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'create_event' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // iCal export
        register_rest_route( 'bcwp/v1', '/projects/(?P<project_id>\d+)/events/ical', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'export_ical' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Update event
        register_rest_route( 'bcwp/v1', '/events/(?P<id>\d+)', array(
            'methods'             => 'PATCH',
            'callback'            => array( $this, 'update_event' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );

        // Delete event
        register_rest_route( 'bcwp/v1', '/events/(?P<id>\d+)', array(
            'methods'             => 'DELETE',
            'callback'            => array( $this, 'delete_event' ),
            'permission_callback' => array( $this, 'check_permission' ),
        ) );
    }

    /**
     * Get events
     */
    public function get_events( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $start = $request->get_param( 'start' );
        $end = $request->get_param( 'end' );

        if ( ! BCWP_Permission_Service::can_access_project( $project_id ) ) {
            return new WP_Error( 'access_denied', 'Access denied', array( 'status' => 403 ) );
        }

        $events = BCWP_Event::get_project_events( $project_id, $start, $end );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $events,
        ) );
    }

    /**
     * Create event
     */
    public function create_event( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $params = $request->get_json_params();

        if ( ! BCWP_Permission_Service::can_create_content( $project_id, 'event' ) ) {
            return new WP_Error( 'access_denied', 'Access denied', array( 'status' => 403 ) );
        }

        $event_data = array(
            'project_id'     => $project_id,
            'title'          => sanitize_text_field( $params['title'] ),
            'description'    => isset( $params['description'] ) ? wp_kses_post( $params['description'] ) : '',
            'start_datetime' => sanitize_text_field( $params['start_datetime'] ),
            'end_datetime'   => sanitize_text_field( $params['end_datetime'] ),
            'location'       => isset( $params['location'] ) ? sanitize_text_field( $params['location'] ) : '',
            'all_day'        => isset( $params['all_day'] ) ? (int) $params['all_day'] : 0,
        );

        $event_id = BCWP_Event::create( $event_data );

        if ( ! $event_id ) {
            return new WP_Error( 'create_failed', 'Failed to create event', array( 'status' => 500 ) );
        }

        $event = BCWP_Event::get( $event_id );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $event,
        ) );
    }

    /**
     * Export events as iCal format
     */
    public function export_ical( $request ) {
        $project_id = $request->get_param( 'project_id' );

        if ( ! BCWP_Permission_Service::can_access_project( $project_id ) ) {
            return new WP_Error( 'access_denied', 'Access denied', array( 'status' => 403 ) );
        }

        $project = BCWP_Project::get( $project_id );
        $events = BCWP_Event::get_project_events( $project_id );

        // Generate iCal format
        $ical = "BEGIN:VCALENDAR\r\n";
        $ical .= "VERSION:2.0\r\n";
        $ical .= "PRODID:-//Basecamp WP Pro//Events Calendar//EN\r\n";
        $ical .= "CALSCALE:GREGORIAN\r\n";
        $ical .= "METHOD:PUBLISH\r\n";
        $ical .= "X-WR-CALNAME:" . $this->escape_ical_text( $project->name . ' Calendar' ) . "\r\n";
        $ical .= "X-WR-TIMEZONE:UTC\r\n";

        foreach ( $events as $event ) {
            $ical .= "BEGIN:VEVENT\r\n";
            $ical .= "UID:" . md5( $event->id . $event->created_at ) . "@basecampwp\r\n";
            $ical .= "DTSTAMP:" . $this->format_ical_date( $event->created_at ) . "\r\n";
            $ical .= "DTSTART:" . $this->format_ical_date( $event->start_datetime, $event->all_day ) . "\r\n";
            $ical .= "DTEND:" . $this->format_ical_date( $event->end_datetime, $event->all_day ) . "\r\n";
            $ical .= "SUMMARY:" . $this->escape_ical_text( $event->title ) . "\r\n";

            if ( ! empty( $event->description ) ) {
                $ical .= "DESCRIPTION:" . $this->escape_ical_text( $event->description ) . "\r\n";
            }

            if ( ! empty( $event->location ) ) {
                $ical .= "LOCATION:" . $this->escape_ical_text( $event->location ) . "\r\n";
            }

            $ical .= "STATUS:CONFIRMED\r\n";
            $ical .= "END:VEVENT\r\n";
        }

        $ical .= "END:VCALENDAR\r\n";

        // Set headers for file download
        header( 'Content-Type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $project->slug ) . '-calendar.ics"' );
        header( 'Cache-Control: no-cache, must-revalidate' );
        header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );

        echo $ical;
        exit;
    }

    /**
     * Update event
     */
    public function update_event( $request ) {
        $event_id = $request->get_param( 'id' );
        $params = $request->get_json_params();

        $event = BCWP_Event::get( $event_id );
        if ( ! $event ) {
            return new WP_Error( 'not_found', 'Event not found', array( 'status' => 404 ) );
        }

        if ( ! BCWP_Permission_Service::can_edit_content( 'event', $event_id ) ) {
            return new WP_Error( 'access_denied', 'Access denied', array( 'status' => 403 ) );
        }

        $update_data = array();

        if ( isset( $params['title'] ) ) {
            $update_data['title'] = sanitize_text_field( $params['title'] );
        }
        if ( isset( $params['description'] ) ) {
            $update_data['description'] = wp_kses_post( $params['description'] );
        }
        if ( isset( $params['start_datetime'] ) ) {
            $update_data['start_datetime'] = sanitize_text_field( $params['start_datetime'] );
        }
        if ( isset( $params['end_datetime'] ) ) {
            $update_data['end_datetime'] = sanitize_text_field( $params['end_datetime'] );
        }
        if ( isset( $params['location'] ) ) {
            $update_data['location'] = sanitize_text_field( $params['location'] );
        }

        $result = BCWP_Event::update( $event_id, $update_data );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $result,
        ) );
    }

    /**
     * Delete event
     */
    public function delete_event( $request ) {
        $event_id = $request->get_param( 'id' );

        if ( ! BCWP_Permission_Service::can_delete_content( 'event', $event_id ) ) {
            return new WP_Error( 'access_denied', 'Access denied', array( 'status' => 403 ) );
        }

        BCWP_Event::delete( $event_id );

        return rest_ensure_response( array(
            'success' => true,
        ) );
    }

    /**
     * Format date for iCal
     */
    private function format_ical_date( $datetime, $all_day = false ) {
        $dt = new DateTime( $datetime, new DateTimeZone( 'UTC' ) );

        if ( $all_day ) {
            return $dt->format( 'Ymd' );
        }

        return $dt->format( 'Ymd\THis\Z' );
    }

    /**
     * Escape text for iCal
     */
    private function escape_ical_text( $text ) {
        $text = str_replace( array( "\\", "\n", "\r", ",", ";" ), array( "\\\\", "\\n", "", "\\,", "\\;" ), $text );
        return $text;
    }

    /**
     * Check permission
     */
    public function check_permission() {
        return is_user_logged_in();
    }
}
