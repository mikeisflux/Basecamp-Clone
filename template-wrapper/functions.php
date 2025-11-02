<?php
/**
 * ProjectFOB Templates Package - Auto-installer
 *
 * This "theme" is actually a templates package installer.
 * When activated, it copies templates to the ProjectFOB plugin and switches back to your previous theme.
 */

add_action('after_switch_theme', 'projectfob_templates_auto_install');

function projectfob_templates_auto_install() {
    // Get the template source directory (this "theme")
    $source_dir = get_template_directory() . '/templates/';

    // Get the plugin templates directory
    $plugin_dir = WP_PLUGIN_DIR . '/projectfob/templates/';

    // Check if ProjectFOB plugin exists
    if (!is_dir(WP_PLUGIN_DIR . '/projectfob/')) {
        wp_die(
            '<h1>ProjectFOB Plugin Not Found</h1>' .
            '<p>The ProjectFOB plugin must be installed before you can update templates.</p>' .
            '<p><a href="' . admin_url('themes.php') . '">Go back to themes</a></p>'
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
        $previous_theme = 'twentytwentyfour'; // Fallback
    }

    // Switch back to previous theme
    if ($success) {
        // Set a flag to show success message
        set_transient('projectfob_templates_installed', true, 30);

        // Switch back
        switch_theme($previous_theme);
    } else {
        wp_die(
            '<h1>Installation Failed</h1>' .
            '<p>Could not find templates in the package.</p>' .
            '<p><a href="' . admin_url('themes.php') . '">Go back to themes</a></p>'
        );
    }
}

/**
 * Recursively copy directory
 */
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

/**
 * Show success message after installation
 */
add_action('admin_notices', 'projectfob_templates_install_notice');

function projectfob_templates_install_notice() {
    if (get_transient('projectfob_templates_installed')) {
        delete_transient('projectfob_templates_installed');

        $backup_path = get_option('projectfob_templates_backup_path');
        delete_option('projectfob_templates_backup_path');

        echo '<div class="notice notice-success is-dismissible">';
        echo '<h2>🎉 ProjectFOB Templates v2.6.8 Installed Successfully!</h2>';
        echo '<p><strong>Your templates have been updated.</strong></p>';
        echo '<ul>';
        echo '<li>✅ Templates extracted to: <code>wp-content/plugins/projectfob/templates/</code></li>';
        if ($backup_path) {
            echo '<li>✅ Old templates backed up to: <code>' . esc_html(basename($backup_path)) . '</code></li>';
        }
        echo '</ul>';
        echo '<p><strong>Next steps:</strong></p>';
        echo '<ol>';
        echo '<li>Clear your WordPress cache (if using a caching plugin)</li>';
        echo '<li>Clear your browser cache (Ctrl+Shift+R or Cmd+Shift+R)</li>';
        echo '<li>Visit your adminland page to verify the fixes</li>';
        echo '</ol>';
        echo '<h3>What\'s Fixed in v2.6.8:</h3>';
        echo '<ul>';
        echo '<li>✅ Removed ALL column layouts from adminland page</li>';
        echo '<li>✅ Fixed subscription details to stack vertically</li>';
        echo '<li>✅ Fixed capabilities list (no more grid/flex columns)</li>';
        echo '</ul>';
        echo '</div>';
    }
}
