<?php
/**
 * ProjectFOB Templates Auto-installer
 */

add_action('after_switch_theme', 'projectfob_templates_auto_install');

function projectfob_templates_auto_install() {
    $source_dir = get_template_directory() . '/templates/';
    $plugin_dir = WP_PLUGIN_DIR . '/projectfob/templates/';

    if (!is_dir(WP_PLUGIN_DIR . '/projectfob/')) {
        wp_die(
            '<h1>ProjectFOB Plugin Not Found</h1>' .
            '<p>Install ProjectFOB plugin first.</p>' .
            '<p><a href="' . admin_url('themes.php') . '">Go back</a></p>'
        );
    }

    // Backup existing templates
    if (is_dir($plugin_dir)) {
        $backup_dir = WP_PLUGIN_DIR . '/projectfob/templates-backup-' . date('Y-m-d-His');
        @rename($plugin_dir, $backup_dir);
        add_option('projectfob_templates_backup_path', $backup_dir);
    }

    // Copy new templates
    if (is_dir($source_dir)) {
        projectfob_recursive_copy($source_dir, $plugin_dir);
        $success = true;
    } else {
        $success = false;
    }

    // Get previous theme
    $previous_theme = get_option('theme_switched');
    if (empty($previous_theme)) {
        $previous_theme = 'twentytwentyfour';
    }

    // Switch back
    if ($success) {
        set_transient('projectfob_templates_installed', true, 30);
        switch_theme($previous_theme);
    } else {
        wp_die('<h1>Installation Failed</h1><p>Templates not found.</p>');
    }
}

function projectfob_recursive_copy($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst, 0755, true);

    while (($file = readdir($dir)) !== false) {
        if ($file != '.' && $file != '..') {
            if (is_dir($src . '/' . $file)) {
                projectfob_recursive_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }

    closedir($dir);
}

add_action('admin_notices', 'projectfob_templates_install_notice');

function projectfob_templates_install_notice() {
    if (get_transient('projectfob_templates_installed')) {
        delete_transient('projectfob_templates_installed');
        $backup_path = get_option('projectfob_templates_backup_path');
        delete_option('projectfob_templates_backup_path');

        echo '<div class="notice notice-success is-dismissible">';
        echo '<h2>ProjectFOB Templates v2.6.8 Installed!</h2>';
        echo '<p>Templates updated successfully.</p>';
        echo '<p>Clear your caches and refresh the page.</p>';
        echo '</div>';
    }
}
