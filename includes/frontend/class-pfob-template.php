<?php
/**
 * Template helper class.
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/frontend
 */

class PFOB_Template {

    public static function header( $title = '' ) {
        if ( empty( $title ) ) {
            $title = get_option( 'pfob_company_name', 'ProjectFOB' );
        }

        include PFOB_PLUGIN_DIR . 'templates/components/header.php';
    }

    public static function navigation() {
        include PFOB_PLUGIN_DIR . 'templates/components/navigation.php';
    }

    public static function footer() {
        include PFOB_PLUGIN_DIR . 'templates/components/footer.php';
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
            return __( 'just now', 'projectfob' );
        } elseif ( $diff < 3600 ) {
            $minutes = floor( $diff / 60 );
            return sprintf( _n( '%d minute ago', '%d minutes ago', $minutes, 'projectfob' ), $minutes );
        } elseif ( $diff < 86400 ) {
            $hours = floor( $diff / 3600 );
            return sprintf( _n( '%d hour ago', '%d hours ago', $hours, 'projectfob' ), $hours );
        } elseif ( $diff < 604800 ) {
            $days = floor( $diff / 86400 );
            return sprintf( _n( '%d day ago', '%d days ago', $days, 'projectfob' ), $days );
        } else {
            return date_i18n( get_option( 'pfob_date_format', 'F j, Y' ), $timestamp );
        }
    }

    public static function user_avatar( $user_id, $size = 40 ) {
        return get_avatar( $user_id, $size, '', '', array( 'class' => 'pfob-avatar' ) );
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
        <div class="pfob-empty-state">
            <h3><?php echo esc_html( $title ); ?></h3>
            <p><?php echo esc_html( $description ); ?></p>
            <?php if ( $action_text && $action_url ) : ?>
                <a href="<?php echo esc_url( $action_url ); ?>" class="pfob-btn pfob-btn-primary">
                    <?php echo esc_html( $action_text ); ?>
                </a>
            <?php endif; ?>
        </div>
        <?php
    }
}
