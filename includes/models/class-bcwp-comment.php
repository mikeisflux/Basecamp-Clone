<?php
/**
 * Comment Model
 *
 * Handles comments on messages and other content
 */

class BCWP_Comment {

    /**
     * Create a comment
     *
     * @param array $data Comment data
     * @return int|false Comment ID or false on failure
     */
    public static function create( $data ) {
        $defaults = array(
            'subject_type' => 'message',
            'subject_id'   => 0,
            'user_id'      => get_current_user_id(),
            'content'      => '',
            'created_at'   => current_time( 'mysql' ),
            'updated_at'   => current_time( 'mysql' ),
        );

        $data = wp_parse_args( $data, $defaults );

        return BCWP_Database::insert( 'comments', $data );
    }

    /**
     * Get a comment by ID
     *
     * @param int $comment_id Comment ID
     * @return object|null
     */
    public static function get( $comment_id ) {
        global $wpdb;

        return $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM " . BCWP_Database::get_table_name( 'comments' ) . " WHERE id = %d",
            $comment_id
        ) );
    }

    /**
     * Get comments for a subject
     *
     * @param string $subject_type Type of subject (message, card, etc.)
     * @param int    $subject_id   Subject ID
     * @return array Comments
     */
    public static function get_for_subject( $subject_type, $subject_id ) {
        global $wpdb;

        return $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM " . BCWP_Database::get_table_name( 'comments' ) . "
            WHERE subject_type = %s AND subject_id = %d
            ORDER BY created_at ASC",
            $subject_type,
            $subject_id
        ) );
    }

    /**
     * Update a comment
     *
     * @param int   $comment_id Comment ID
     * @param array $data       Comment data
     * @return bool Success
     */
    public static function update( $comment_id, $data ) {
        $data['updated_at'] = current_time( 'mysql' );

        return BCWP_Database::update( 'comments', $data, array( 'id' => $comment_id ) );
    }

    /**
     * Delete a comment
     *
     * @param int $comment_id Comment ID
     * @return bool Success
     */
    public static function delete( $comment_id ) {
        return BCWP_Database::delete( 'comments', array( 'id' => $comment_id ) );
    }

    /**
     * Get comment count for a subject
     *
     * @param string $subject_type Type of subject
     * @param int    $subject_id   Subject ID
     * @return int Count
     */
    public static function get_count( $subject_type, $subject_id ) {
        global $wpdb;

        return (int) $wpdb->get_var( $wpdb->prepare(
            "SELECT COUNT(*) FROM " . BCWP_Database::get_table_name( 'comments' ) . "
            WHERE subject_type = %s AND subject_id = %d",
            $subject_type,
            $subject_id
        ) );
    }
}
