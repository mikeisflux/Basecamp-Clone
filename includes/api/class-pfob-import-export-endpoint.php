<?php
/**
 * Import/Export REST API Endpoint
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/api
 */

class PFOB_Import_Export_Endpoint extends PFOB_REST_API {

    protected $namespace = 'projectfob/v1';

    /**
     * Register routes.
     */
    public function register_routes() {
        // Export project to JSON
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/export', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'export_project_json' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Import project from JSON
        register_rest_route( $this->namespace, '/projects/import', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'import_project_json' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Export todos to CSV
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/todos/export/csv', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'export_todos_csv' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Export messages to CSV
        register_rest_route( $this->namespace, '/projects/(?P<project_id>\d+)/messages/export/csv', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'export_messages_csv' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );

        // Export all projects overview
        register_rest_route( $this->namespace, '/export/all', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'export_all_projects' ),
            'permission_callback' => array( $this, 'check_permission_with_subscription' ),
        ) );
    }

    /**
     * Check if user has permission.
     */
    public function check_permission( $request ) {
        return is_user_logged_in();
    }

    /**
     * Export project to JSON.
     */
    public function export_project_json( $request ) {
        global $wpdb;

        $project_id = $request->get_param( 'project_id' );
        $project = PFOB_Project::get( $project_id );

        if ( ! $project ) {
            return new WP_Error( 'not_found', 'Project not found', array( 'status' => 404 ) );
        }

        // Check permission
        if ( ! PFOB_Permission_Service::can_view_project( get_current_user_id(), $project_id ) ) {
            return new WP_Error( 'forbidden', 'No permission', array( 'status' => 403 ) );
        }

        // Build complete project export
        $export_data = array(
            'version' => '1.0.0',
            'exported_at' => current_time( 'mysql' ),
            'exported_by' => get_current_user_id(),
            'project' => array(
                'name' => $project->name,
                'slug' => $project->slug,
                'description' => $project->description,
                'color' => $project->color,
                'created_at' => $project->created_at,
            ),
            'messages' => $this->get_project_messages( $project_id ),
            'todos' => $this->get_project_todos( $project_id ),
            'documents' => $this->get_project_documents( $project_id ),
            'events' => $this->get_project_events( $project_id ),
            'team_members' => $this->get_project_members( $project_id ),
            'activities' => $this->get_project_activities( $project_id ),
        );

        // Send as JSON file download
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $project->slug ) . '-export-' . date( 'Y-m-d' ) . '.json"' );
        echo json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }

    /**
     * Import project from JSON.
     */
    public function import_project_json( $request ) {
        global $wpdb;

        $json_data = $request->get_json_params();

        if ( empty( $json_data ) ) {
            return new WP_Error( 'invalid_data', 'No data provided', array( 'status' => 400 ) );
        }

        // Validate format
        if ( ! isset( $json_data['version'] ) || ! isset( $json_data['project'] ) ) {
            return new WP_Error( 'invalid_format', 'Invalid export format', array( 'status' => 400 ) );
        }

        // Create new project
        $project_data = $json_data['project'];
        $unique_slug = $this->generate_unique_slug( $project_data['slug'] );

        $project = PFOB_Project::create( array(
            'name' => $project_data['name'] . ' (Imported)',
            'slug' => $unique_slug,
            'description' => $project_data['description'],
            'color' => $project_data['color'],
            'created_by' => get_current_user_id(),
        ) );

        if ( ! $project ) {
            return new WP_Error( 'create_failed', 'Failed to create project', array( 'status' => 500 ) );
        }

        $import_stats = array(
            'messages' => 0,
            'todos' => 0,
            'documents' => 0,
            'events' => 0,
        );

        // Import messages
        if ( ! empty( $json_data['messages'] ) ) {
            foreach ( $json_data['messages'] as $message_data ) {
                $message = PFOB_Message::create( array(
                    'project_id' => $project->id,
                    'title' => $message_data['title'],
                    'content' => $message_data['content'],
                    'category' => $message_data['category'],
                    'created_by' => get_current_user_id(),
                ) );

                if ( $message ) {
                    $import_stats['messages']++;

                    // Import comments for this message
                    if ( ! empty( $message_data['comments'] ) ) {
                        foreach ( $message_data['comments'] as $comment_data ) {
                            PFOB_Comment::create( array(
                                'subject_type' => 'message',
                                'subject_id' => $message->id,
                                'content' => $comment_data['content'],
                                'user_id' => get_current_user_id(),
                            ) );
                        }
                    }
                }
            }
        }

        // Import todos
        if ( ! empty( $json_data['todos'] ) ) {
            foreach ( $json_data['todos'] as $todo_data ) {
                $todo = PFOB_Todo::create( array(
                    'project_id' => $project->id,
                    'title' => $todo_data['title'],
                    'description' => $todo_data['description'],
                    'assigned_to' => null, // Don't preserve assignments on import
                    'due_date' => $todo_data['due_date'],
                    'is_completed' => false, // Reset completion status
                    'created_by' => get_current_user_id(),
                ) );

                if ( $todo ) {
                    $import_stats['todos']++;
                }
            }
        }

        // Import events
        if ( ! empty( $json_data['events'] ) ) {
            foreach ( $json_data['events'] as $event_data ) {
                $event = PFOB_Event::create( array(
                    'project_id' => $project->id,
                    'title' => $event_data['title'],
                    'description' => $event_data['description'],
                    'start_datetime' => $event_data['start_datetime'],
                    'end_datetime' => $event_data['end_datetime'],
                    'all_day' => $event_data['all_day'],
                    'created_by' => get_current_user_id(),
                ) );

                if ( $event ) {
                    $import_stats['events']++;
                }
            }
        }

        // Add current user as project member
        $wpdb->insert( $wpdb->prefix . 'pfob_project_members', array(
            'project_id' => $project->id,
            'user_id' => get_current_user_id(),
            'role' => 'admin',
        ) );

        return new WP_REST_Response( array(
            'success' => true,
            'message' => 'Project imported successfully',
            'data' => array(
                'project_id' => $project->id,
                'project_slug' => $project->slug,
                'stats' => $import_stats,
            ),
        ), 200 );
    }

    /**
     * Export todos to CSV.
     */
    public function export_todos_csv( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $project = PFOB_Project::get( $project_id );

        if ( ! $project ) {
            return new WP_Error( 'not_found', 'Project not found', array( 'status' => 404 ) );
        }

        $todos = PFOB_Todo::get_project_todos( $project_id );

        // Generate CSV
        $csv = '';
        $csv .= '"ID","Title","Description","Status","Assigned To","Due Date","Created By","Created At"' . "\n";

        foreach ( $todos as $todo ) {
            $assigned_name = $todo->assigned_to ? PFOB_Auth_Service::get_user_display_name( $todo->assigned_to ) : '';
            $created_name = PFOB_Auth_Service::get_user_display_name( $todo->created_by );
            $status = $todo->is_completed ? 'Completed' : 'Open';

            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $todo->id,
                $this->escape_csv( $todo->title ),
                $this->escape_csv( $todo->description ),
                $status,
                $this->escape_csv( $assigned_name ),
                $todo->due_date ? $todo->due_date : '',
                $this->escape_csv( $created_name ),
                $todo->created_at
            );
        }

        // Send as CSV file download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $project->slug ) . '-todos-' . date( 'Y-m-d' ) . '.csv"' );
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $csv;
        exit;
    }

    /**
     * Export messages to CSV.
     */
    public function export_messages_csv( $request ) {
        $project_id = $request->get_param( 'project_id' );
        $project = PFOB_Project::get( $project_id );

        if ( ! $project ) {
            return new WP_Error( 'not_found', 'Project not found', array( 'status' => 404 ) );
        }

        $messages = PFOB_Message::get_project_messages( $project_id );

        // Generate CSV
        $csv = '';
        $csv .= '"ID","Title","Content","Category","Author","Comments Count","Created At"' . "\n";

        foreach ( $messages as $message ) {
            $author_name = PFOB_Auth_Service::get_user_display_name( $message->created_by );
            $comments = PFOB_Comment::get_for_subject( 'message', $message->id );

            $csv .= sprintf(
                '"%s","%s","%s","%s","%s","%s","%s"' . "\n",
                $message->id,
                $this->escape_csv( $message->title ),
                $this->escape_csv( $message->content ),
                $this->escape_csv( $message->category ),
                $this->escape_csv( $author_name ),
                count( $comments ),
                $message->created_at
            );
        }

        // Send as CSV file download
        header( 'Content-Type: text/csv; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $project->slug ) . '-messages-' . date( 'Y-m-d' ) . '.csv"' );
        echo "\xEF\xBB\xBF"; // UTF-8 BOM
        echo $csv;
        exit;
    }

    /**
     * Export all projects overview.
     */
    public function export_all_projects( $request ) {
        global $wpdb;

        $user_id = get_current_user_id();

        // Get all projects user has access to
        $projects = PFOB_Project::get_user_projects( $user_id );

        $export_data = array(
            'version' => '1.0.0',
            'exported_at' => current_time( 'mysql' ),
            'exported_by' => $user_id,
            'projects_count' => count( $projects ),
            'projects' => array(),
        );

        foreach ( $projects as $project ) {
            $project_stats = array(
                'id' => $project->id,
                'name' => $project->name,
                'slug' => $project->slug,
                'description' => $project->description,
                'created_at' => $project->created_at,
                'stats' => array(
                    'messages' => count( PFOB_Message::get_project_messages( $project->id ) ),
                    'todos' => count( PFOB_Todo::get_project_todos( $project->id ) ),
                    'documents' => count( PFOB_Document::get_project_documents( $project->id ) ),
                    'events' => count( PFOB_Event::get_project_events( $project->id ) ),
                    'members' => count( $this->get_project_members( $project->id ) ),
                ),
            );

            $export_data['projects'][] = $project_stats;
        }

        // Send as JSON file download
        header( 'Content-Type: application/json; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename="projectfob-projects-overview-' . date( 'Y-m-d' ) . '.json"' );
        echo json_encode( $export_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
        exit;
    }

    /**
     * Helper: Get project messages with comments.
     */
    private function get_project_messages( $project_id ) {
        $messages = PFOB_Message::get_project_messages( $project_id );
        $result = array();

        foreach ( $messages as $message ) {
            $comments = PFOB_Comment::get_for_subject( 'message', $message->id );
            $comment_data = array();

            foreach ( $comments as $comment ) {
                $comment_data[] = array(
                    'content' => $comment->content,
                    'created_by' => PFOB_Auth_Service::get_user_display_name( $comment->user_id ),
                    'created_at' => $comment->created_at,
                );
            }

            $result[] = array(
                'title' => $message->title,
                'content' => $message->content,
                'category' => $message->category,
                'is_pinned' => $message->is_pinned,
                'created_by' => PFOB_Auth_Service::get_user_display_name( $message->created_by ),
                'created_at' => $message->created_at,
                'comments' => $comment_data,
            );
        }

        return $result;
    }

    /**
     * Helper: Get project todos.
     */
    private function get_project_todos( $project_id ) {
        $todos = PFOB_Todo::get_project_todos( $project_id );
        $result = array();

        foreach ( $todos as $todo ) {
            $result[] = array(
                'title' => $todo->title,
                'description' => $todo->description,
                'is_completed' => $todo->is_completed,
                'assigned_to' => $todo->assigned_to ? PFOB_Auth_Service::get_user_display_name( $todo->assigned_to ) : null,
                'due_date' => $todo->due_date,
                'created_by' => PFOB_Auth_Service::get_user_display_name( $todo->created_by ),
                'created_at' => $todo->created_at,
            );
        }

        return $result;
    }

    /**
     * Helper: Get project documents.
     */
    private function get_project_documents( $project_id ) {
        $documents = PFOB_Document::get_project_documents( $project_id );
        $result = array();

        foreach ( $documents as $doc ) {
            $result[] = array(
                'title' => $doc->title,
                'description' => $doc->description,
                'file_name' => $doc->file_name,
                'file_size' => $doc->file_size,
                'mime_type' => $doc->mime_type,
                'folder' => $doc->folder,
                'created_by' => PFOB_Auth_Service::get_user_display_name( $doc->created_by ),
                'created_at' => $doc->created_at,
            );
        }

        return $result;
    }

    /**
     * Helper: Get project events.
     */
    private function get_project_events( $project_id ) {
        $events = PFOB_Event::get_project_events( $project_id );
        $result = array();

        foreach ( $events as $event ) {
            $result[] = array(
                'title' => $event->title,
                'description' => $event->description,
                'start_datetime' => $event->start_datetime,
                'end_datetime' => $event->end_datetime,
                'all_day' => $event->all_day,
                'created_by' => PFOB_Auth_Service::get_user_display_name( $event->created_by ),
                'created_at' => $event->created_at,
            );
        }

        return $result;
    }

    /**
     * Helper: Get project members.
     */
    private function get_project_members( $project_id ) {
        global $wpdb;

        $members = $wpdb->get_results( $wpdb->prepare(
            "SELECT user_id, role FROM {$wpdb->prefix}pfob_project_members WHERE project_id = %d",
            $project_id
        ) );

        $result = array();
        foreach ( $members as $member ) {
            $result[] = array(
                'user_id' => $member->user_id,
                'name' => PFOB_Auth_Service::get_user_display_name( $member->user_id ),
                'role' => $member->role,
            );
        }

        return $result;
    }

    /**
     * Helper: Get project activities.
     */
    private function get_project_activities( $project_id ) {
        $activities = PFOB_Activity::get_project_activities( $project_id, 100 );
        $result = array();

        foreach ( $activities as $activity ) {
            $result[] = array(
                'type' => $activity->activity_type,
                'subject_type' => $activity->subject_type,
                'description' => $activity->description,
                'created_by' => PFOB_Auth_Service::get_user_display_name( $activity->user_id ),
                'created_at' => $activity->created_at,
            );
        }

        return $result;
    }

    /**
     * Helper: Escape CSV field.
     */
    private function escape_csv( $value ) {
        return str_replace( '"', '""', $value );
    }

    /**
     * Helper: Generate unique slug.
     */
    private function generate_unique_slug( $base_slug ) {
        global $wpdb;

        $slug = $base_slug;
        $counter = 1;

        while ( true ) {
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}pfob_projects WHERE slug = %s",
                $slug
            ) );

            if ( ! $exists ) {
                return $slug;
            }

            $slug = $base_slug . '-' . $counter;
            $counter++;
        }
    }
}
