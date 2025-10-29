<?php
/**
 * Project model class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/models
 */

class PFOB_Project {

    /**
     * Create a new project.
     *
     * @param array $data Project data.
     * @return int|false Project ID on success, false on failure.
     */
    public static function create( $data ) {
        // Ensure slug is unique
        if ( empty( $data['slug'] ) ) {
            $data['slug'] = sanitize_title( $data['name'] );
        }

        $data['slug'] = self::generate_unique_slug( $data['slug'] );
        $data['creator_id'] = get_current_user_id();
        $data['status'] = $data['status'] ?? 'active';

        // Encode settings as JSON if it's an array
        if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
            $data['settings'] = wp_json_encode( $data['settings'] );
        }

        $project_id = PFOB_Database::insert( 'projects', $data );

        if ( $project_id ) {
            // Add creator as admin member
            self::add_member( $project_id, $data['creator_id'], 'admin' );

            // Create default project tools
            self::create_default_tools( $project_id );

            // Log activity
            PFOB_Database::insert( 'activities', array(
                'project_id'   => $project_id,
                'user_id'      => $data['creator_id'],
                'action_type'  => 'project.created',
                'subject_type' => 'project',
                'subject_id'   => $project_id,
                'description'  => sprintf( 'Created project "%s"', $data['name'] ),
            ) );
        }

        return $project_id;
    }

    /**
     * Get project by ID.
     *
     * @param int $project_id Project ID.
     * @return object|null Project object or null if not found.
     */
    public static function get( $project_id ) {
        $project = PFOB_Database::get_row( 'projects', array( 'id' => $project_id ) );

        if ( $project && ! empty( $project->settings ) ) {
            $project->settings = json_decode( $project->settings, true );
        }

        return $project;
    }

    /**
     * Get project by slug.
     *
     * @param string $slug Project slug.
     * @return object|null Project object or null if not found.
     */
    public static function get_by_slug( $slug ) {
        $project = PFOB_Database::get_row( 'projects', array( 'slug' => $slug ) );

        if ( $project && ! empty( $project->settings ) ) {
            $project->settings = json_decode( $project->settings, true );
        }

        return $project;
    }

    /**
     * Get all projects for a user.
     *
     * @param int $user_id User ID.
     * @return array Array of project objects.
     */
    public static function get_user_projects( $user_id ) {
        global $wpdb;

        $sql = "SELECT p.* FROM " . PFOB_Database::get_table_name( 'projects' ) . " p
                INNER JOIN " . PFOB_Database::get_table_name( 'project_members' ) . " pm
                ON p.id = pm.project_id
                WHERE pm.user_id = %d AND p.status = 'active'
                ORDER BY p.updated_at DESC";

        return $wpdb->get_results( $wpdb->prepare( $sql, $user_id ) );
    }

    /**
     * Update project.
     *
     * @param int   $project_id Project ID.
     * @param array $data       Update data.
     * @return int|false Number of rows updated, false on error.
     */
    public static function update( $project_id, $data ) {
        // Encode settings as JSON if it's an array
        if ( isset( $data['settings'] ) && is_array( $data['settings'] ) ) {
            $data['settings'] = wp_json_encode( $data['settings'] );
        }

        $result = PFOB_Database::update(
            'projects',
            $data,
            array( 'id' => $project_id )
        );

        if ( $result !== false ) {
            // Log activity
            PFOB_Database::insert( 'activities', array(
                'project_id'   => $project_id,
                'user_id'      => get_current_user_id(),
                'action_type'  => 'project.updated',
                'subject_type' => 'project',
                'subject_id'   => $project_id,
                'description'  => 'Updated project',
                'changes'      => wp_json_encode( $data ),
            ) );
        }

        return $result;
    }

    /**
     * Delete project (soft delete).
     *
     * @param int $project_id Project ID.
     * @return int|false Number of rows updated, false on error.
     */
    public static function delete( $project_id ) {
        return self::update( $project_id, array( 'status' => 'deleted' ) );
    }

    /**
     * Archive project.
     *
     * @param int $project_id Project ID.
     * @return int|false Number of rows updated, false on error.
     */
    public static function archive( $project_id ) {
        return self::update( $project_id, array( 'status' => 'archived' ) );
    }

    /**
     * Add member to project.
     *
     * @param int    $project_id Project ID.
     * @param int    $user_id    User ID.
     * @param string $role       Member role.
     * @return int|false Insert ID on success, false on failure.
     */
    public static function add_member( $project_id, $user_id, $role = 'member' ) {
        // Check if already a member
        if ( self::is_member( $project_id, $user_id ) ) {
            return false;
        }

        $member_id = PFOB_Database::insert( 'project_members', array(
            'project_id' => $project_id,
            'user_id'    => $user_id,
            'role'       => $role,
            'joined_at'  => current_time( 'mysql' ),
        ) );

        if ( $member_id ) {
            // Log activity
            $user = get_userdata( $user_id );
            PFOB_Database::insert( 'activities', array(
                'project_id'   => $project_id,
                'user_id'      => get_current_user_id(),
                'action_type'  => 'member.added',
                'subject_type' => 'user',
                'subject_id'   => $user_id,
                'description'  => sprintf( 'Added %s to the project', $user->display_name ),
            ) );
        }

        return $member_id;
    }

    /**
     * Remove member from project.
     *
     * @param int $project_id Project ID.
     * @param int $user_id    User ID.
     * @return int|false Number of rows deleted, false on error.
     */
    public static function remove_member( $project_id, $user_id ) {
        return PFOB_Database::delete( 'project_members', array(
            'project_id' => $project_id,
            'user_id'    => $user_id,
        ) );
    }

    /**
     * Check if user is a member of project.
     *
     * @param int $project_id Project ID.
     * @param int $user_id    User ID.
     * @return bool True if member, false otherwise.
     */
    public static function is_member( $project_id, $user_id ) {
        return PFOB_Database::exists( 'project_members', array(
            'project_id' => $project_id,
            'user_id'    => $user_id,
        ) );
    }

    /**
     * Get project members.
     *
     * @param int $project_id Project ID.
     * @return array Array of member objects.
     */
    public static function get_members( $project_id ) {
        global $wpdb;

        $sql = "SELECT u.*, pm.role, pm.joined_at
                FROM {$wpdb->users} u
                INNER JOIN " . PFOB_Database::get_table_name( 'project_members' ) . " pm
                ON u.ID = pm.user_id
                WHERE pm.project_id = %d
                ORDER BY pm.joined_at ASC";

        return $wpdb->get_results( $wpdb->prepare( $sql, $project_id ) );
    }

    /**
     * Get project tools.
     *
     * @param int $project_id Project ID.
     * @return array Array of tool objects.
     */
    public static function get_tools( $project_id ) {
        return PFOB_Database::get_results(
            'project_tools',
            array( 'project_id' => $project_id ),
            array( 'position' => 'ASC' )
        );
    }

    /**
     * Create default project tools.
     *
     * @param int $project_id Project ID.
     */
    private static function create_default_tools( $project_id ) {
        $default_tools = array(
            array( 'tool_type' => 'messages', 'position' => 1 ),
            array( 'tool_type' => 'todos', 'position' => 2 ),
            array( 'tool_type' => 'documents', 'position' => 3 ),
            array( 'tool_type' => 'chat', 'position' => 4 ),
            array( 'tool_type' => 'schedule', 'position' => 5 ),
            array( 'tool_type' => 'cards', 'position' => 6 ),
        );

        foreach ( $default_tools as $tool ) {
            PFOB_Database::insert( 'project_tools', array(
                'project_id' => $project_id,
                'tool_type'  => $tool['tool_type'],
                'is_enabled' => 1,
                'position'   => $tool['position'],
            ) );
        }
    }

    /**
     * Generate unique slug.
     *
     * @param string $slug Base slug.
     * @return string Unique slug.
     */
    private static function generate_unique_slug( $slug ) {
        $original_slug = $slug;
        $counter = 1;

        while ( PFOB_Database::exists( 'projects', array( 'slug' => $slug ) ) ) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
