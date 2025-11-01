<?php
/**
 * Invitations API Endpoint
 *
 * Handles user invitations with 3 user types:
 * - team_member: Full access, can create projects
 * - contractor: Limited access, cannot create projects
 * - client: View-only access to assigned projects
 *
 * @package ProjectFOB
 */

class PFOB_Invitations_Endpoint extends PFOB_REST_API {

    /**
     * Register routes.
     */
    public function register_routes() {
        // Send invitation
        register_rest_route(
            $this->namespace,
            '/invitations/send',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'send_invitation' ),
                'permission_callback' => array( $this, 'check_user_permission' ),
            )
        );

        // Accept invitation
        register_rest_route(
            $this->namespace,
            '/invitations/accept/(?P<token>[a-zA-Z0-9]+)',
            array(
                'methods'             => 'POST',
                'callback'            => array( $this, 'accept_invitation' ),
                'permission_callback' => '__return_true', // Public endpoint
            )
        );

        // List account users (only show users belonging to this subscriber)
        register_rest_route(
            $this->namespace,
            '/account/users',
            array(
                'methods'             => 'GET',
                'callback'            => array( $this, 'list_account_users' ),
                'permission_callback' => array( $this, 'check_user_permission' ),
            )
        );
    }

    /**
     * Send invitation to a new user.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function send_invitation( $request ) {
        $inviter_id = get_current_user_id();

        // Get form data
        $full_name = sanitize_text_field( $request->get_param( 'full_name' ) );
        $email = sanitize_email( $request->get_param( 'email' ) );
        $user_type = sanitize_text_field( $request->get_param( 'user_type' ) );
        $job_title = sanitize_text_field( $request->get_param( 'job_title' ) );
        $organization = sanitize_text_field( $request->get_param( 'organization' ) );
        $personal_note = sanitize_textarea_field( $request->get_param( 'personal_note' ) );

        // Validation
        if ( empty( $full_name ) || empty( $email ) ) {
            return new WP_Error(
                'missing_required_fields',
                __( 'Full name and email are required.', 'projectfob' ),
                array( 'status' => 400 )
            );
        }

        // Validate user type
        $valid_types = array( 'team_member', 'contractor', 'client' );
        if ( ! in_array( $user_type, $valid_types, true ) ) {
            return new WP_Error(
                'invalid_user_type',
                __( 'Invalid user type.', 'projectfob' ),
                array( 'status' => 400 )
            );
        }

        // Check if user already exists in this account
        global $wpdb;
        $existing_user = $wpdb->get_var( $wpdb->prepare(
            "SELECT u.ID FROM {$wpdb->users} u
            INNER JOIN {$wpdb->usermeta} um ON u.ID = um.user_id
            WHERE u.user_email = %s
            AND um.meta_key = 'pfob_account_owner'
            AND um.meta_value = %d",
            $email,
            $inviter_id
        ) );

        if ( $existing_user ) {
            return new WP_Error(
                'user_already_invited',
                __( 'This user has already been invited to your account.', 'projectfob' ),
                array( 'status' => 400 )
            );
        }

        // Generate invitation token
        $token = wp_generate_password( 32, false );
        $expires_at = date( 'Y-m-d H:i:s', strtotime( '+7 days' ) );

        // Map user type to role
        $role_map = array(
            'team_member' => 'coworker',
            'contractor'  => 'contractor',
            'client'      => 'client',
        );
        $role = $role_map[ $user_type ];

        // Create invitation record
        $invitations_table = $wpdb->prefix . 'pfob_invitations';
        $inserted = $wpdb->insert(
            $invitations_table,
            array(
                'email'       => $email,
                'token'       => $token,
                'type'        => 'user',
                'inviter_id'  => $inviter_id,
                'role'        => $role,
                'message'     => $personal_note,
                'metadata'    => wp_json_encode( array(
                    'full_name'    => $full_name,
                    'job_title'    => $job_title,
                    'organization' => $organization,
                    'user_type'    => $user_type,
                ) ),
                'status'      => 'pending',
                'expires_at'  => $expires_at,
                'created_at'  => current_time( 'mysql' ),
            ),
            array( '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s' )
        );

        if ( ! $inserted ) {
            return new WP_Error(
                'invitation_failed',
                __( 'Failed to create invitation.', 'projectfob' ),
                array( 'status' => 500 )
            );
        }

        $invitation_id = $wpdb->insert_id;

        // Send invitation email
        $this->send_invitation_email(
            $email,
            $full_name,
            $token,
            $user_type,
            $personal_note,
            $inviter_id
        );

        // Get invitation URL for setting up project access
        $set_projects_url = home_url( "/projectfob/people/{$email}/projects" );

        return rest_ensure_response( array(
            'success'          => true,
            'message'          => sprintf(
                __( 'Invitation sent to %s. They will receive an email with instructions to join.', 'projectfob' ),
                $full_name
            ),
            'invitation_id'    => $invitation_id,
            'set_projects_url' => $set_projects_url,
        ) );
    }

    /**
     * Send invitation email.
     *
     * @param string $email Email address.
     * @param string $full_name Full name.
     * @param string $token Invitation token.
     * @param string $user_type User type.
     * @param string $personal_note Personal note from inviter.
     * @param int    $inviter_id Inviter user ID.
     */
    private function send_invitation_email( $email, $full_name, $token, $user_type, $personal_note, $inviter_id ) {
        $inviter = get_userdata( $inviter_id );
        $organization = get_user_meta( $inviter_id, 'pfob_organization', true ) ?: get_bloginfo( 'name' );

        $accept_url = home_url( "/projectfob/accept-invitation/{$token}" );

        $user_type_label = array(
            'team_member' => 'Team Member',
            'contractor'  => 'Contractor',
            'client'      => 'Client',
        )[ $user_type ];

        $subject = sprintf(
            __( 'You\'ve been invited to join %s on ProjectFOB', 'projectfob' ),
            $organization
        );

        $message = sprintf(
            "Hi %s,\n\n" .
            "%s has invited you to join %s on ProjectFOB as a %s.\n\n",
            $full_name,
            $inviter->display_name,
            $organization,
            $user_type_label
        );

        if ( ! empty( $personal_note ) ) {
            $message .= "Personal message from {$inviter->display_name}:\n";
            $message .= "\"{$personal_note}\"\n\n";
        }

        $message .= "To accept this invitation and set up your account, click the link below:\n\n";
        $message .= $accept_url . "\n\n";
        $message .= "This invitation will expire in 7 days.\n\n";
        $message .= "If you have any questions, please contact {$inviter->user_email}.\n\n";
        $message .= "Best regards,\n";
        $message .= "The ProjectFOB Team";

        wp_mail( $email, $subject, $message );
    }

    /**
     * Accept invitation.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response|WP_Error
     */
    public function accept_invitation( $request ) {
        $token = sanitize_text_field( $request->get_param( 'token' ) );
        $password = sanitize_text_field( $request->get_param( 'password' ) );

        // Find invitation
        global $wpdb;
        $invitations_table = $wpdb->prefix . 'pfob_invitations';

        $invitation = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$invitations_table}
            WHERE token = %s
            AND status = 'pending'
            AND expires_at > NOW()",
            $token
        ) );

        if ( ! $invitation ) {
            return new WP_Error(
                'invalid_invitation',
                __( 'Invitation not found or has expired.', 'projectfob' ),
                array( 'status' => 404 )
            );
        }

        $metadata = json_decode( $invitation->metadata, true );
        $full_name = $metadata['full_name'];
        $job_title = $metadata['job_title'];
        $organization = $metadata['organization'];
        $user_type = $metadata['user_type'];

        // Create WordPress user
        $user_id = wp_create_user(
            $invitation->email,
            $password,
            $invitation->email
        );

        if ( is_wp_error( $user_id ) ) {
            return $user_id;
        }

        // Update user data
        wp_update_user( array(
            'ID'           => $user_id,
            'display_name' => $full_name,
            'first_name'   => explode( ' ', $full_name )[0],
            'last_name'    => substr( $full_name, strlen( explode( ' ', $full_name )[0] ) + 1 ),
        ) );

        // **IMPORTANT**: Tag user with account owner so they don't show in WordPress admin
        update_user_meta( $user_id, 'pfob_account_owner', $invitation->inviter_id );
        update_user_meta( $user_id, 'pfob_user_type', $user_type );
        update_user_meta( $user_id, 'pfob_role', $invitation->role );
        update_user_meta( $user_id, 'pfob_organization', $organization );
        update_user_meta( $user_id, 'pfob_job_title', $job_title );

        // Mark invitation as accepted
        $wpdb->update(
            $invitations_table,
            array(
                'status'      => 'accepted',
                'accepted_at' => current_time( 'mysql' ),
            ),
            array( 'id' => $invitation->id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        // Log in the user
        wp_set_current_user( $user_id );
        wp_set_auth_cookie( $user_id );

        return rest_ensure_response( array(
            'success' => true,
            'message' => __( 'Welcome! Your account has been created.', 'projectfob' ),
            'user_id' => $user_id,
            'redirect_url' => home_url( '/projectfob/' ),
        ) );
    }

    /**
     * List users belonging to the current subscriber's account.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response
     */
    public function list_account_users( $request ) {
        $account_owner_id = get_current_user_id();

        // Get all users who belong to this account owner
        $args = array(
            'meta_query' => array(
                array(
                    'key'   => 'pfob_account_owner',
                    'value' => $account_owner_id,
                ),
            ),
            'orderby' => 'display_name',
            'order'   => 'ASC',
        );

        $users = get_users( $args );

        $users_data = array();
        foreach ( $users as $user ) {
            $user_type = get_user_meta( $user->ID, 'pfob_user_type', true );
            $role = get_user_meta( $user->ID, 'pfob_role', true );
            $organization = get_user_meta( $user->ID, 'pfob_organization', true );
            $job_title = get_user_meta( $user->ID, 'pfob_job_title', true );

            $users_data[] = array(
                'id'           => $user->ID,
                'name'         => $user->display_name,
                'email'        => $user->user_email,
                'user_type'    => $user_type,
                'role'         => $role,
                'organization' => $organization,
                'job_title'    => $job_title,
            );
        }

        return rest_ensure_response( array(
            'success' => true,
            'users'   => $users_data,
        ) );
    }
}
