<?php
/**
 * Template helper class.
 *
 * @package    Basecamp_WP_Pro
 * @subpackage Basecamp_WP_Pro/includes/frontend
 */

class BCWP_Template {

    public static function header( $title = '' ) {
        if ( empty( $title ) ) {
            $title = get_option( 'bcwp_company_name', 'Basecamp' );
        }

        include BCWP_PLUGIN_DIR . 'templates/components/header.php';
    }

    public static function navigation() {
        include BCWP_PLUGIN_DIR . 'templates/components/navigation.php';
    }

    public static function footer() {
        // Footer content if needed
    }

    public static function get_project_url( $project ) {
        return home_url( '/projectfob/projects/' . $project->slug . '/' );
    }

    public static function get_tool_url( $project, $tool ) {
        return home_url( '/projectfob/projects/' . $project->slug . '/' . $tool . '/' );
    }

    public static function format_date( $date ) {
        $timestamp = strtotime( $date );
        $now = current_time( 'timestamp' );
        $diff = $now - $timestamp;

        if ( $diff < 60 ) {
            return __( 'just now', 'basecamp-wp-pro' );
        } elseif ( $diff < 3600 ) {
            $minutes = floor( $diff / 60 );
            return sprintf( _n( '%d minute ago', '%d minutes ago', $minutes, 'basecamp-wp-pro' ), $minutes );
        } elseif ( $diff < 86400 ) {
            $hours = floor( $diff / 3600 );
            return sprintf( _n( '%d hour ago', '%d hours ago', $hours, 'basecamp-wp-pro' ), $hours );
        } elseif ( $diff < 604800 ) {
            $days = floor( $diff / 86400 );
            return sprintf( _n( '%d day ago', '%d days ago', $days, 'basecamp-wp-pro' ), $days );
        } else {
            return date_i18n( get_option( 'bcwp_date_format', 'F j, Y' ), $timestamp );
        }
    }

    public static function user_avatar( $user_id, $size = 40 ) {
        return get_avatar( $user_id, $size, '', '', array( 'class' => 'bcwp-avatar' ) );
    }

    public static function project_icon( $tool_type ) {
        $icons = array(
            'messages'  => '💬',
            'todos'     => '✓',
            'documents' => '📁',
            'chat'      => '💭',
            'schedule'  => '📅',
            'cards'     => '📋',
        );

        return $icons[ $tool_type ] ?? '•';
    }

    public static function empty_state( $title, $description, $action_text = '', $action_url = '' ) {
        ?>
        <div class="bcwp-empty-state">
            <h3><?php echo esc_html( $title ); ?></h3>
            <p><?php echo esc_html( $description ); ?></p>
            <?php if ( $action_text && $action_url ) : ?>
                <a href="<?php echo esc_url( $action_url ); ?>" class="bcwp-btn bcwp-btn-primary">
                    <?php echo esc_html( $action_text ); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}
