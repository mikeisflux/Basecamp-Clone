<?php
/**
 * Theme Service
 * Handles theme mode (light/dark/auto) and appearance preferences
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/services
 */

class PFOB_Theme_Service {

    /**
     * Initialize the theme service.
     */
    public static function init() {
        // Load theme CSS in frontend
        add_action( 'wp_head', array( __CLASS__, 'output_theme_css' ), 20 );

        // Add body class for theme mode
        add_filter( 'body_class', array( __CLASS__, 'add_body_classes' ) );
    }

    /**
     * Output theme CSS based on user preferences.
     */
    public static function output_theme_css() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $user_id = get_current_user_id();
        $theme_mode = get_user_meta( $user_id, 'pfob_theme_mode', true ) ?: 'light';
        $compact_mode = get_user_meta( $user_id, 'pfob_compact_mode', true );
        $show_avatars = get_user_meta( $user_id, 'pfob_show_avatars', true ) !== '0';

        // Get brand colors - only apply if user has customized them
        $brand_colors = get_user_meta( $user_id, 'pfob_brand_colors', true );
        $has_custom_colors = ! empty( $brand_colors ) && is_array( $brand_colors );

        ?>
<style id="pfob-user-theme">
<?php if ( $has_custom_colors ) : ?>
/* Custom Brand Colors */
body.pfob-body {
    <?php if ( isset( $brand_colors['background'] ) ) : ?>
    background-color: <?php echo esc_attr( $brand_colors['background'] ); ?>;
    <?php endif; ?>
    <?php if ( isset( $brand_colors['text'] ) ) : ?>
    color: <?php echo esc_attr( $brand_colors['text'] ); ?>;
    <?php endif; ?>
}

.pfob-btn-primary,
.pfob-btn.pfob-btn-primary,
button.pfob-btn-primary {
    <?php if ( isset( $brand_colors['primary'] ) ) : ?>
    background-color: <?php echo esc_attr( $brand_colors['primary'] ); ?>;
    border-color: <?php echo esc_attr( $brand_colors['primary'] ); ?>;
    <?php endif; ?>
}

.pfob-btn-primary:hover {
    opacity: 0.9;
}

.pfob-btn-secondary,
.pfob-btn.pfob-btn-secondary {
    <?php if ( isset( $brand_colors['secondary'] ) ) : ?>
    background-color: <?php echo esc_attr( $brand_colors['secondary'] ); ?>;
    border-color: <?php echo esc_attr( $brand_colors['secondary'] ); ?>;
    <?php endif; ?>
}

.pfob-btn-secondary:hover {
    opacity: 0.9;
}

<?php if ( isset( $brand_colors['tile'] ) ) : ?>
.pfob-card,
.pfob-project-card,
.pfob-message-item,
.pfob-todo-item,
.pfob-document-item,
.pfob-settings-section,
.admin-section {
    background-color: <?php echo esc_attr( $brand_colors['tile'] ); ?>;
}

.pfob-projects-grid .pfob-project-card {
    background-color: <?php echo esc_attr( $brand_colors['tile'] ); ?>;
    border: 1px solid <?php echo esc_attr( $brand_colors['tile'] ); ?>;
}
<?php endif; ?>

<?php if ( isset( $brand_colors['primary'] ) ) : ?>
.pfob-link,
a.pfob-link,
.pfob-nav-item:hover {
    color: <?php echo esc_attr( $brand_colors['primary'] ); ?>;
}

.pfob-project-card:hover {
    border-color: <?php echo esc_attr( $brand_colors['primary'] ); ?>;
}

.pfob-nav-item.active,
.pfob-tab.active {
    color: <?php echo esc_attr( $brand_colors['primary'] ); ?>;
    border-bottom-color: <?php echo esc_attr( $brand_colors['primary'] ); ?>;
}

.pfob-input:focus,
.pfob-textarea:focus,
.pfob-select:focus {
    border-color: <?php echo esc_attr( $brand_colors['primary'] ); ?>;
}
<?php endif; ?>

<?php if ( isset( $brand_colors['accent'] ) ) : ?>
.pfob-badge,
.pfob-notification-badge {
    background-color: <?php echo esc_attr( $brand_colors['accent'] ); ?>;
}
<?php endif; ?>

<?php if ( isset( $brand_colors['text'] ) ) : ?>
.pfob-text,
.pfob-main,
h1, h2, h3, h4, h5, h6 {
    color: <?php echo esc_attr( $brand_colors['text'] ); ?>;
}
<?php endif; ?>
<?php endif; ?>

:root {
    <?php if ( $theme_mode === 'dark' ) : ?>
    /* Dark Theme Colors */
    --pfob-bg-primary: #1a1a1a;
    --pfob-bg-secondary: #2d2d2d;
    --pfob-bg-tertiary: #3a3a3a;
    --pfob-text-primary: #ffffff;
    --pfob-text-secondary: #b0b0b0;
    --pfob-border-color: #404040;
    --pfob-card-bg: #2d2d2d;
    --pfob-hover-bg: #3a3a3a;
    <?php elseif ( $theme_mode === 'light' ) : ?>
    /* Light Theme Colors */
    --pfob-bg-primary: #ffffff;
    --pfob-bg-secondary: #f8f9fa;
    --pfob-bg-tertiary: #e9ecef;
    --pfob-text-primary: #212529;
    --pfob-text-secondary: #6c757d;
    --pfob-border-color: #dee2e6;
    --pfob-card-bg: #ffffff;
    --pfob-hover-bg: #f8f9fa;
    <?php endif; ?>
}

<?php if ( $theme_mode === 'dark' ) : ?>
/* Dark Mode Overrides */
body.pfob-body {
    background-color: var(--pfob-bg-primary);
    color: var(--pfob-text-primary);
}

.pfob-container,
.pfob-main {
    background-color: var(--pfob-bg-primary);
    color: var(--pfob-text-primary);
}

.pfob-card,
.pfob-message-item,
.pfob-todo-item,
.pfob-document-item,
.pfob-settings-content {
    background-color: var(--pfob-card-bg);
    border-color: var(--pfob-border-color);
    color: var(--pfob-text-primary);
}

.pfob-header {
    background-color: var(--pfob-bg-secondary);
    border-bottom-color: var(--pfob-border-color);
}

.pfob-input,
.pfob-select,
.pfob-textarea {
    background-color: var(--pfob-bg-tertiary);
    color: var(--pfob-text-primary);
    border-color: var(--pfob-border-color);
}

.pfob-btn-secondary {
    background-color: var(--pfob-bg-tertiary);
    color: var(--pfob-text-primary);
}

.pfob-modal-content {
    background-color: var(--pfob-card-bg);
    color: var(--pfob-text-primary);
}
<?php endif; ?>

<?php if ( $theme_mode === 'auto' ) : ?>
/* Auto Theme (follows system preference) */
@media (prefers-color-scheme: dark) {
    body.pfob-body {
        background-color: #1a1a1a;
        color: #ffffff;
    }

    .pfob-container,
    .pfob-main {
        background-color: #1a1a1a;
        color: #ffffff;
    }

    .pfob-card,
    .pfob-message-item,
    .pfob-todo-item,
    .pfob-document-item,
    .pfob-settings-content {
        background-color: #2d2d2d;
        border-color: #404040;
        color: #ffffff;
    }

    .pfob-header {
        background-color: #2d2d2d;
        border-bottom-color: #404040;
    }

    .pfob-input,
    .pfob-select,
    .pfob-textarea {
        background-color: #3a3a3a;
        color: #ffffff;
        border-color: #404040;
    }

    .pfob-btn-secondary {
        background-color: #3a3a3a;
        color: #ffffff;
    }

    .pfob-modal-content {
        background-color: #2d2d2d;
        color: #ffffff;
    }
}
<?php endif; ?>

<?php if ( $compact_mode ) : ?>
/* Compact Mode - Reduced Spacing */
.pfob-main {
    padding: 15px;
}

.pfob-card,
.pfob-message-item,
.pfob-todo-item {
    padding: 12px 15px;
    margin-bottom: 8px;
}

.pfob-page-header {
    margin-bottom: 20px;
}

.pfob-settings-content {
    padding: 20px;
}

.pfob-setting-group {
    margin-bottom: 20px;
    padding-bottom: 20px;
}
<?php endif; ?>

<?php if ( ! $show_avatars ) : ?>
/* Hide Avatars */
.pfob-user-avatar,
.pfob-avatar,
.avatar {
    display: none !important;
}

.pfob-message-header,
.pfob-comment-header {
    padding-left: 0 !important;
}
<?php endif; ?>
</style>
        <?php
    }

    /**
     * Add body classes based on theme settings.
     */
    public static function add_body_classes( $classes ) {
        if ( ! is_user_logged_in() ) {
            return $classes;
        }

        $user_id = get_current_user_id();
        $theme_mode = get_user_meta( $user_id, 'pfob_theme_mode', true ) ?: 'light';
        $compact_mode = get_user_meta( $user_id, 'pfob_compact_mode', true );

        $classes[] = 'pfob-theme-' . $theme_mode;

        if ( $compact_mode ) {
            $classes[] = 'pfob-compact-mode';
        }

        return $classes;
    }

    /**
     * Get user's theme mode.
     */
    public static function get_user_theme() {
        if ( ! is_user_logged_in() ) {
            return 'light';
        }

        $user_id = get_current_user_id();
        return get_user_meta( $user_id, 'pfob_theme_mode', true ) ?: 'light';
    }

    /**
     * Check if user has compact mode enabled.
     */
    public static function is_compact_mode() {
        if ( ! is_user_logged_in() ) {
            return false;
        }

        $user_id = get_current_user_id();
        return (bool) get_user_meta( $user_id, 'pfob_compact_mode', true );
    }

    /**
     * Check if user wants to see avatars.
     */
    public static function should_show_avatars() {
        if ( ! is_user_logged_in() ) {
            return true;
        }

        $user_id = get_current_user_id();
        return get_user_meta( $user_id, 'pfob_show_avatars', true ) !== '0';
    }
}
