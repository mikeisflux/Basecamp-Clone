<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $title ?? get_option( 'bcwp_company_name', 'Basecamp' ) ); ?></title>
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo BCWP_PLUGIN_URL; ?>assets/css/frontend.css">
    <?php BCWP_Assets::inline_css(); ?>
</head>
<body class="bcwp-body">
    <header class="bcwp-header">
        <div class="bcwp-header-left">
            <a href="<?php echo home_url( '/projectfob/' ); ?>" class="bcwp-logo">
                <?php echo esc_html( get_option( 'bcwp_company_name', 'ProjectFOB' ) ); ?>
            </a>
        </div>

        <nav class="bcwp-header-nav">
            <a href="<?php echo home_url( '/projectfob/' ); ?>" class="bcwp-nav-item">Home</a>
            <a href="<?php echo home_url( '/projectfob/lineup/' ); ?>" class="bcwp-nav-item">Lineup</a>
            <a href="<?php echo home_url( '/projectfob/my-stuff/' ); ?>" class="bcwp-nav-item">My Stuff</a>
            <a href="<?php echo home_url( '/projectfob/activity/' ); ?>" class="bcwp-nav-item">Activity</a>
            <a href="<?php echo home_url( '/projectfob/search/' ); ?>" class="bcwp-nav-item">Find</a>
        </nav>

        <div class="bcwp-header-right">
            <button class="bcwp-notifications-btn" id="notifications-btn">
                <span class="bcwp-icon">🔔</span>
                <span class="bcwp-notification-badge" id="notification-badge" style="display:none;"></span>
            </button>

            <div class="bcwp-user-menu">
                <?php echo BCWP_Auth_Service::get_user_avatar( get_current_user_id(), 32 ); ?>
                <span class="bcwp-user-name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
                <div class="bcwp-user-dropdown">
                    <?php if ( current_user_can( 'manage_options' ) ) : ?>
                        <a href="<?php echo admin_url( 'admin.php?page=projectfob' ); ?>">Settings</a>
                    <?php endif; ?>
                    <a href="<?php echo wp_logout_url( home_url( '/projectfob/' ) ); ?>">Sign out</a>
                </div>
            </div>
        </div>
    </header>

    <div id="notification-panel" class="bcwp-notification-panel" style="display:none;">
        <div class="bcwp-notification-header">
            <h3>Notifications</h3>
            <button id="mark-all-read">Mark all as read</button>
        </div>
        <div id="notification-list" class="bcwp-notification-list">
            <!-- Notifications loaded via JavaScript -->
        </div>
    </div>
