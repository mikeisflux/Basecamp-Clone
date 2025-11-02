<?php
/**
 * ProjectFOB Templates Installer
 *
 * Upload this file AND projectfob-templates-2.6.8.zip to your WordPress root directory
 * Then visit: https://your-site.com/install-templates.php
 */

// Configuration
$templates_zip = 'projectfob-templates-2.6.8.zip';
$plugin_path = __DIR__ . '/wp-content/plugins/projectfob/templates/';

// Security check
$password = 'projectfob2024'; // Change this!
?>
<!DOCTYPE html>
<html>
<head>
    <title>ProjectFOB Templates Installer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .box { background: #f5f5f5; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .success { background: #d4edda; border-color: #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border-color: #f5c6cb; color: #721c24; }
        .warning { background: #fff3cd; border-color: #ffeeba; color: #856404; }
        input[type="password"], input[type="submit"] { padding: 10px; font-size: 16px; margin: 5px; }
        input[type="submit"] { background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer; }
        input[type="submit"]:hover { background: #005177; }
        pre { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 4px; overflow-x: auto; }
        h1 { color: #0073aa; }
        .step { margin: 10px 0; padding: 10px; background: white; border-left: 4px solid #0073aa; }
    </style>
</head>
<body>
    <h1>🚀 ProjectFOB Templates Installer v2.6.8</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify password
    if (!isset($_POST['password']) || $_POST['password'] !== $password) {
        echo '<div class="box error"><strong>Error:</strong> Invalid password.</div>';
        exit;
    }

    echo '<div class="box">';
    echo '<h2>Installation Progress</h2>';

    // Check if zip file exists
    if (!file_exists($templates_zip)) {
        echo '<div class="error">❌ Error: ' . htmlspecialchars($templates_zip) . ' not found in the same directory as this installer.</div>';
        echo '<p>Please upload the templates zip file to: <code>' . htmlspecialchars(__DIR__) . '</code></p>';
        echo '</div></body></html>';
        exit;
    }
    echo '<div class="step">✅ Found templates zip file: ' . htmlspecialchars($templates_zip) . '</div>';

    // Check if plugin directory exists
    if (!is_dir(dirname($plugin_path))) {
        echo '<div class="error">❌ Error: ProjectFOB plugin directory not found at:<br><code>' . htmlspecialchars(dirname($plugin_path)) . '</code></div>';
        echo '<p>Please install the ProjectFOB plugin first.</p>';
        echo '</div></body></html>';
        exit;
    }
    echo '<div class="step">✅ Found ProjectFOB plugin directory</div>';

    // Backup existing templates
    if (is_dir($plugin_path)) {
        $backup_path = dirname($plugin_path) . '/templates-backup-' . date('Y-m-d-His');
        if (rename($plugin_path, $backup_path)) {
            echo '<div class="step">✅ Backed up existing templates to:<br><code>' . htmlspecialchars($backup_path) . '</code></div>';
        } else {
            echo '<div class="warning">⚠️ Warning: Could not backup existing templates</div>';
        }
    }

    // Create templates directory
    if (!is_dir($plugin_path)) {
        if (!mkdir($plugin_path, 0755, true)) {
            echo '<div class="error">❌ Error: Could not create templates directory</div>';
            echo '</div></body></html>';
            exit;
        }
    }
    echo '<div class="step">✅ Templates directory ready</div>';

    // Extract zip file
    $zip = new ZipArchive;
    $res = $zip->open($templates_zip);

    if ($res === TRUE) {
        $zip->extractTo($plugin_path);
        $zip->close();
        echo '<div class="step">✅ Extracted templates to:<br><code>' . htmlspecialchars($plugin_path) . '</code></div>';
    } else {
        echo '<div class="error">❌ Error: Could not open zip file (Error code: ' . $res . ')</div>';
        echo '</div></body></html>';
        exit;
    }

    // Verify extraction
    $adminland_file = $plugin_path . 'frontend/adminland/index.php';
    if (file_exists($adminland_file)) {
        echo '<div class="step">✅ Verified adminland template exists</div>';

        // Check for the CSS fix
        $content = file_get_contents($adminland_file);
        if (strpos($content, 'margin-bottom: 24px') !== false) {
            echo '<div class="step">✅ Verified CSS fixes are present (no columns layout)</div>';
        }
    } else {
        echo '<div class="warning">⚠️ Warning: Could not verify adminland template</div>';
    }

    echo '</div>';
    echo '<div class="box success">';
    echo '<h2>🎉 Installation Complete!</h2>';
    echo '<p><strong>Templates v2.6.8 have been successfully installed.</strong></p>';
    echo '<div class="step">Next steps:</div>';
    echo '<ol>';
    echo '<li>Clear your WordPress cache (if using a caching plugin)</li>';
    echo '<li>Clear your browser cache (Ctrl+Shift+R or Cmd+Shift+R)</li>';
    echo '<li>Visit your adminland page to verify the column layout is fixed</li>';
    echo '<li><strong>DELETE THIS FILE (install-templates.php) for security</strong></li>';
    echo '</ol>';
    echo '</div>';

} else {
    // Show installation form
    ?>
    <div class="box warning">
        <h2>⚠️ Before You Start</h2>
        <ol>
            <li>Upload <strong>both</strong> files to your WordPress root directory:
                <ul>
                    <li><code>install-templates.php</code> (this file)</li>
                    <li><code>projectfob-templates-2.6.8.zip</code></li>
                </ul>
            </li>
            <li>Make sure the ProjectFOB plugin is already installed</li>
            <li>Backup your site (recommended)</li>
        </ol>
    </div>

    <div class="box">
        <h2>🔐 Enter Password to Install</h2>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter installation password" required>
            <input type="submit" value="Install Templates v2.6.8">
        </form>
        <p><small>Default password: <code>projectfob2024</code> (change in the PHP file)</small></p>
    </div>

    <div class="box">
        <h2>📋 What This Will Do</h2>
        <ol>
            <li>Backup your current templates to <code>templates-backup-[date]</code></li>
            <li>Extract new templates (v2.6.8) with column layout fixes</li>
            <li>Verify the installation</li>
        </ol>
    </div>

    <div class="box">
        <h2>🐛 What's Fixed in v2.6.8</h2>
        <ul>
            <li>✅ Removed ALL column layouts from adminland page</li>
            <li>✅ Fixed subscription details display (vertical stacking)</li>
            <li>✅ Fixed capabilities list (no more grid/flex columns)</li>
            <li>✅ Everything now displays as a standard web page</li>
        </ul>
    </div>
    <?php
}
?>

</body>
</html>
