<?php
/**
 * Email service class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/services
 */

class BCWP_Email_Service {

    public static function send( $to, $subject, $message, $headers = array() ) {
        // Add default headers
        $default_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_option( 'bcwp_company_name' ) . ' <' . get_option( 'admin_email' ) . '>',
        );

        $headers = array_merge( $default_headers, $headers );

        // Wrap message in template
        $message = self::wrap_in_template( $message );

        return wp_mail( $to, $subject, $message, $headers );
    }

    public static function send_invitation( $email, $project_name, $inviter_name, $invitation_url ) {
        $subject = sprintf( __( 'You\'ve been invited to %s', 'basecamp-wp-pro' ), $project_name );

        $message = sprintf(
            __( '<p><strong>%s</strong> has invited you to collaborate on <strong>%s</strong>.</p>', 'basecamp-wp-pro' ),
            $inviter_name,
            $project_name
        );

        $message .= sprintf(
            '<p><a href="%s" style="display: inline-block; padding: 12px 24px; background-color: #2d9061; color: #ffffff; text-decoration: none; border-radius: 4px;">Accept Invitation</a></p>',
            esc_url( $invitation_url )
        );

        return self::send( $email, $subject, $message );
    }

    public static function send_assignment_notification( $user_id, $item_type, $item_title, $assigner_name, $item_url ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return false;
        }

        $subject = sprintf( __( 'You were assigned: %s', 'basecamp-wp-pro' ), $item_title );

        $message = sprintf(
            __( '<p><strong>%s</strong> assigned you a %s:</p>', 'basecamp-wp-pro' ),
            $assigner_name,
            $item_type
        );

        $message .= sprintf( '<p><strong>%s</strong></p>', esc_html( $item_title ) );

        $message .= sprintf(
            '<p><a href="%s" style="display: inline-block; padding: 12px 24px; background-color: #2d9061; color: #ffffff; text-decoration: none; border-radius: 4px;">View Item</a></p>',
            esc_url( $item_url )
        );

        return self::send( $user->user_email, $subject, $message );
    }

    private static function wrap_in_template( $content ) {
        $company_name = get_option( 'bcwp_company_name' );
        $primary_color = get_option( 'bcwp_primary_color', '#2d9061' );

        $template = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        </head>
        <body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f7f6f3;">
            <table role="presentation" style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 40px 20px;">
                        <table role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                            <tr>
                                <td style="padding: 40px;">
                                    <h1 style="margin: 0 0 20px 0; color: ' . esc_attr( $primary_color ) . '; font-size: 24px; font-weight: 600;">' . esc_html( $company_name ) . '</h1>
                                    ' . $content . '
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 20px 40px; background-color: #f7f6f3; border-top: 1px solid #e5e5e5; border-radius: 0 0 8px 8px;">
                                    <p style="margin: 0; color: #666666; font-size: 12px;">
                                        This is an automated message from ' . esc_html( $company_name ) . '.
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </body>
        </html>';

        return $template;
    }
}
