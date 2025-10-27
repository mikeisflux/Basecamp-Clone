<?php
/**
 * Search service class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/services
 */

class BCWP_Search_Service {

    public static function search( $query, $project_id = null, $types = array() ) {
        $results = array();

        if ( empty( $types ) ) {
            $types = array( 'projects', 'messages', 'todos', 'documents', 'events', 'cards' );
        }

        foreach ( $types as $type ) {
            $method = 'search_' . $type;
            if ( method_exists( __CLASS__, $method ) ) {
                $results[ $type ] = self::$method( $query, $project_id );
            }
        }

        return $results;
    }

    private static function search_projects( $query, $project_id = null ) {
        global $wpdb;

        $user_id = get_current_user_id();

        $sql = "SELECT p.* FROM " . BCWP_Database::get_table_name( 'projects' ) . " p
                INNER JOIN " . BCWP_Database::get_table_name( 'project_members' ) . " pm
                ON p.id = pm.project_id
                WHERE pm.user_id = %d
                AND p.status = 'active'
                AND (p.name LIKE %s OR p.description LIKE %s)
                ORDER BY p.name ASC
                LIMIT 10";

        $search_term = '%' . $wpdb->esc_like( $query ) . '%';

        return $wpdb->get_results( $wpdb->prepare( $sql, $user_id, $search_term, $search_term ) );
    }

    private static function search_messages( $query, $project_id = null ) {
        global $wpdb;

        $sql = "SELECT * FROM " . BCWP_Database::get_table_name( 'messages' ) . "
                WHERE is_archived = 0
                AND (title LIKE %s OR content LIKE %s)";

        $params = array();
        $search_term = '%' . $wpdb->esc_like( $query ) . '%';
        $params[] = $search_term;
        $params[] = $search_term;

        if ( $project_id ) {
            $sql .= " AND project_id = %d";
            $params[] = $project_id;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 10";

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    private static function search_todos( $query, $project_id = null ) {
        global $wpdb;

        $sql = "SELECT ti.*, tl.project_id FROM " . BCWP_Database::get_table_name( 'todo_items' ) . " ti
                INNER JOIN " . BCWP_Database::get_table_name( 'todo_lists' ) . " tl
                ON ti.list_id = tl.id
                WHERE (ti.content LIKE %s OR ti.description LIKE %s)";

        $params = array();
        $search_term = '%' . $wpdb->esc_like( $query ) . '%';
        $params[] = $search_term;
        $params[] = $search_term;

        if ( $project_id ) {
            $sql .= " AND tl.project_id = %d";
            $params[] = $project_id;
        }

        $sql .= " ORDER BY ti.created_at DESC LIMIT 10";

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    private static function search_documents( $query, $project_id = null ) {
        global $wpdb;

        $sql = "SELECT * FROM " . BCWP_Database::get_table_name( 'documents' ) . "
                WHERE (name LIKE %s OR description LIKE %s)";

        $params = array();
        $search_term = '%' . $wpdb->esc_like( $query ) . '%';
        $params[] = $search_term;
        $params[] = $search_term;

        if ( $project_id ) {
            $sql .= " AND project_id = %d";
            $params[] = $project_id;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 10";

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    private static function search_events( $query, $project_id = null ) {
        global $wpdb;

        $sql = "SELECT * FROM " . BCWP_Database::get_table_name( 'events' ) . "
                WHERE (title LIKE %s OR description LIKE %s)";

        $params = array();
        $search_term = '%' . $wpdb->esc_like( $query ) . '%';
        $params[] = $search_term;
        $params[] = $search_term;

        if ( $project_id ) {
            $sql .= " AND project_id = %d";
            $params[] = $project_id;
        }

        $sql .= " ORDER BY start_datetime DESC LIMIT 10";

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }

    private static function search_cards( $query, $project_id = null ) {
        global $wpdb;

        $sql = "SELECT * FROM " . BCWP_Database::get_table_name( 'cards' ) . "
                WHERE is_archived = 0
                AND (title LIKE %s OR description LIKE %s)";

        $params = array();
        $search_term = '%' . $wpdb->esc_like( $query ) . '%';
        $params[] = $search_term;
        $params[] = $search_term;

        if ( $project_id ) {
            $sql .= " AND project_id = %d";
            $params[] = $project_id;
        }

        $sql .= " ORDER BY created_at DESC LIMIT 10";

        return $wpdb->get_results( $wpdb->prepare( $sql, $params ) );
    }
}
