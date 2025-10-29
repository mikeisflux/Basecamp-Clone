<?php
/**
 * Permission service class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_Permission_Service {

    /**
     * Check if user can access project.
     *
     * @param int $project_id Project ID.
     * @param int $user_id    User ID (optional, defaults to current user).
     * @return bool
     */
    public static function can_access_project( $project_id, $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }

        // Admins can access all projects
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        // Check if user is a member
        return PFOB_Project::is_member( $project_id, $user_id );
    }

    /**
     * Check if user can edit project.
     *
     * @param int $project_id Project ID.
     * @param int $user_id    User ID (optional, defaults to current user).
     * @return bool
     */
    public static function can_edit_project( $project_id, $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }

        $project = PFOB_Project::get( $project_id );

        if ( ! $project ) {
            return false;
        }

        // Creator can edit
        if ( $project->creator_id == $user_id ) {
            return true;
        }

        // Admins can edit
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        // Check if user has admin role in project
        return self::has_project_role( $project_id, $user_id, 'admin' );
    }

    /**
     * Check if user has specific role in project.
     *
     * @param int    $project_id Project ID.
     * @param int    $user_id    User ID.
     * @param string $role       Required role.
     * @return bool
     */
    public static function has_project_role( $project_id, $user_id, $role ) {
        $member = PFOB_Database::get_row( 'project_members', array(
            'project_id' => $project_id,
            'user_id'    => $user_id,
        ) );

        return $member && $member->role === $role;
    }

    /**
     * Check if user can create content in project.
     *
     * @param int    $project_id Project ID.
     * @param string $type       Content type (message, todo, document, etc.).
     * @param int    $user_id    User ID (optional, defaults to current user).
     * @return bool
     */
    public static function can_create_content( $project_id, $type, $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }

        // Must be a member to create content
        return self::can_access_project( $project_id, $user_id );
    }

    /**
     * Check if user can edit content.
     *
     * @param string $type       Content type.
     * @param int    $content_id Content ID.
     * @param int    $user_id    User ID (optional, defaults to current user).
     * @return bool
     */
    public static function can_edit_content( $type, $content_id, $user_id = null ) {
        if ( $user_id === null ) {
            $user_id = get_current_user_id();
        }

        // Admins can edit everything
        if ( user_can( $user_id, 'manage_options' ) ) {
            return true;
        }

        // Get content
        $content = null;
        switch ( $type ) {
            case 'message':
                $content = PFOB_Message::get( $content_id );
                $author_field = 'author_id';
                break;
            case 'todo':
                $content = PFOB_Todo::get_item( $content_id );
                $author_field = 'created_by';
                break;
            case 'document':
                $content = PFOB_Document::get( $content_id );
                $author_field = 'uploaded_by';
                break;
            case 'chat':
                $content = PFOB_Chat::get_message( $content_id );
                $author_field = 'user_id';
                break;
            case 'event':
                $content = PFOB_Event::get( $content_id );
                $author_field = 'created_by';
                break;
            case 'card':
                $content = PFOB_Card::get_card( $content_id );
                $author_field = 'created_by';
                break;
        }

        if ( ! $content ) {
            return false;
        }

        // Author can edit their own content
        return $content->$author_field == $user_id;
    }

    /**
     * Check if user can delete content.
     *
     * @param string $type       Content type.
     * @param int    $content_id Content ID.
     * @param int    $user_id    User ID (optional, defaults to current user).
     * @return bool
     */
    public static function can_delete_content( $type, $content_id, $user_id = null ) {
        // Same rules as editing for now
        return self::can_edit_content( $type, $content_id, $user_id );
    }

    /**
     * Require project access or die.
     *
     * @param int $project_id Project ID.
     */
    public static function require_project_access( $project_id ) {
        if ( ! self::can_access_project( $project_id ) ) {
            wp_die( __( 'You do not have permission to access this project.', 'projectfob' ), 403 );
        }
    }
}
