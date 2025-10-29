<?php
/**
 * Search REST API Endpoint
 *
 * Handles universal search across all content types
 */

class PFOB_Search_Endpoint {

    /**
     * Register routes
     */
    public function register_routes() {
        register_rest_route( 'pfob/v1', '/search', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'search' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
            'args'                => array(
                'q'          => array(
                    'required' => true,
                    'type'     => 'string',
                ),
                'project_id' => array(
                    'type' => 'integer',
                ),
                'types'      => array(
                    'type' => 'string',
                ),
            ),
        ) );
    }

    /**
     * Search across all content
     *
     * @param WP_REST_Request $request Request object
     * @return WP_REST_Response
     */
    public function search( $request ) {
        $query = $request->get_param( 'q' );
        $project_id = $request->get_param( 'project_id' );
        $types_param = $request->get_param( 'types' );

        if ( empty( $query ) || strlen( $query ) < 2 ) {
            return rest_ensure_response( array(
                'success' => false,
                'message' => 'Query must be at least 2 characters',
                'data'    => array(),
            ) );
        }

        $types = array();
        if ( ! empty( $types_param ) ) {
            $types = explode( ',', $types_param );
        }

        $results = PFOB_Search_Service::search( $query, $project_id, $types );

        // Format results
        $formatted = array();

        if ( ! empty( $results['projects'] ) ) {
            $formatted['projects'] = array_map( array( $this, 'format_project' ), $results['projects'] );
        }

        if ( ! empty( $results['messages'] ) ) {
            $formatted['messages'] = array_map( array( $this, 'format_message' ), $results['messages'] );
        }

        if ( ! empty( $results['todos'] ) ) {
            $formatted['todos'] = array_map( array( $this, 'format_todo' ), $results['todos'] );
        }

        if ( ! empty( $results['documents'] ) ) {
            $formatted['documents'] = array_map( array( $this, 'format_document' ), $results['documents'] );
        }

        if ( ! empty( $results['events'] ) ) {
            $formatted['events'] = array_map( array( $this, 'format_event' ), $results['events'] );
        }

        if ( ! empty( $results['cards'] ) ) {
            $formatted['cards'] = array_map( array( $this, 'format_card' ), $results['cards'] );
        }

        // Count total results
        $total = array_sum( array_map( 'count', $formatted ) );

        return rest_ensure_response( array(
            'success' => true,
            'data'    => $formatted,
            'total'   => $total,
            'query'   => $query,
        ) );
    }

    /**
     * Format project result
     */
    private function format_project( $project ) {
        return array(
            'id'    => $project->id,
            'title' => $project->name,
            'url'   => home_url( '/projectfob/projects/' . $project->slug ),
            'type'  => 'project',
            'icon'  => '📁',
        );
    }

    /**
     * Format message result
     */
    private function format_message( $message ) {
        $project = PFOB_Project::get( $message->project_id );

        return array(
            'id'      => $message->id,
            'title'   => $message->title,
            'excerpt' => wp_trim_words( $message->content, 20 ),
            'url'     => home_url( "/projectfob/projects/{$project->slug}/messages/{$message->id}" ),
            'type'    => 'message',
            'icon'    => '💬',
            'project' => $project->name,
        );
    }

    /**
     * Format todo result
     */
    private function format_todo( $todo ) {
        $project = PFOB_Project::get( $todo->project_id );

        return array(
            'id'      => $todo->id,
            'title'   => $todo->content,
            'excerpt' => $todo->description ? wp_trim_words( $todo->description, 20 ) : '',
            'url'     => home_url( "/projectfob/projects/{$project->slug}/todos" ),
            'type'    => 'todo',
            'icon'    => '✅',
            'project' => $project->name,
        );
    }

    /**
     * Format document result
     */
    private function format_document( $document ) {
        $project = PFOB_Project::get( $document->project_id );

        return array(
            'id'       => $document->id,
            'title'    => $document->name,
            'excerpt'  => $document->description ? wp_trim_words( $document->description, 20 ) : '',
            'url'      => home_url( "/projectfob/projects/{$project->slug}/documents" ),
            'type'     => 'document',
            'icon'     => '📄',
            'project'  => $project->name,
            'filesize' => size_format( $document->filesize ),
        );
    }

    /**
     * Format event result
     */
    private function format_event( $event ) {
        $project = PFOB_Project::get( $event->project_id );

        return array(
            'id'      => $event->id,
            'title'   => $event->title,
            'excerpt' => $event->description ? wp_trim_words( $event->description, 20 ) : '',
            'url'     => home_url( "/projectfob/projects/{$project->slug}/schedule" ),
            'type'    => 'event',
            'icon'    => '📅',
            'project' => $project->name,
            'date'    => date( 'M j, Y', strtotime( $event->start_datetime ) ),
        );
    }

    /**
     * Format card result
     */
    private function format_card( $card ) {
        $project = PFOB_Project::get( $card->project_id );

        return array(
            'id'      => $card->id,
            'title'   => $card->title,
            'excerpt' => $card->description ? wp_trim_words( $card->description, 20 ) : '',
            'url'     => home_url( "/projectfob/projects/{$project->slug}/cards" ),
            'type'    => 'card',
            'icon'    => '🎴',
            'project' => $project->name,
        );
    }

    /**
     * Check if user has permission
     *
     * @return bool
     */
    public function check_permission() {
        return is_user_logged_in();
    }
}
