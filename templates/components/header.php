<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $title ?? get_option( 'pfob_company_name', 'ProjectFOB' ) ); ?></title>
    <?php wp_head(); ?>
    <link rel="stylesheet" href="<?php echo PFOB_PLUGIN_URL; ?>assets/css/frontend.css">
    <?php PFOB_Assets::inline_css(); ?>
</head>
<body class="pfob-body">
    <header class="pfob-header">
        <div class="pfob-header-left">
            <a href="<?php echo home_url( '/projectfob/' ); ?>" class="pfob-logo">
                <?php echo esc_html( get_option( 'pfob_company_name', 'ProjectFOB' ) ); ?>
            </a>
        </div>

        <nav class="pfob-header-nav">
            <a href="<?php echo home_url( '/projectfob/' ); ?>" class="pfob-nav-item">Home</a>
            <a href="<?php echo home_url( '/projectfob/lineup/' ); ?>" class="pfob-nav-item">Lineup</a>
            <a href="<?php echo home_url( '/projectfob/my-stuff/' ); ?>" class="pfob-nav-item">My Stuff</a>
            <a href="<?php echo home_url( '/projectfob/activity/' ); ?>" class="pfob-nav-item">Activity</a>
            <a href="<?php echo home_url( '/projectfob/search/' ); ?>" class="pfob-nav-item">Find</a>
        </nav>

        <div class="pfob-header-right">
            <button class="pfob-notifications-btn" id="notifications-btn">
                <span class="pfob-icon">🔔</span>
                <span class="pfob-notification-badge" id="notification-badge" style="display:none;"></span>
            </button>

            <div class="pfob-user-menu">
                <?php echo PFOB_Auth_Service::get_user_avatar( get_current_user_id(), 32 ); ?>
                <span class="pfob-user-name"><?php echo esc_html( wp_get_current_user()->display_name ); ?></span>
                <div class="pfob-user-dropdown">
                    <?php if ( PFOB_Subscription::is_active( get_current_user_id() ) ) : ?>
                        <a href="<?php echo admin_url( 'admin.php?page=projectfob' ); ?>">Admin</a>
                    <?php endif; ?>
                    <a href="<?php echo wp_logout_url( home_url( '/projectfob/' ) ); ?>">Sign out</a>
                </div>
            </div>
        </div>
    </header>

    <div id="notification-panel" class="pfob-notification-panel" style="display:none;">
        <div class="pfob-notification-header">
            <h3>Notifications</h3>
            <button id="mark-all-read">Mark all as read</button>
        </div>
        <div id="notification-list" class="pfob-notification-list">
            <!-- Notifications loaded via JavaScript -->
        </div>
    </div>
