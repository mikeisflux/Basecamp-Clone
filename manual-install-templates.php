<?php
/**
 * Manual Templates Installer
 *
 * Upload this file to your WordPress root directory
 * Then visit: https://projectfob.com/manual-install-templates.php
 */

$password = 'install2024'; // Change this for security

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['password'] === $password) {

    // Path to the templates zip file (upload it to WordPress root)
    $zip_file = __DIR__ . '/projectfob-templates-2.6.8.zip';
    $plugin_templates_dir = __DIR__ . '/wp-content/plugins/projectfob/templates/';

    if (!file_exists($zip_file)) {
        die('ERROR: projectfob-templates-2.6.8.zip not found. Upload it to WordPress root directory.');
    }

    // Backup existing templates
    if (is_dir($plugin_templates_dir)) {
        $backup_dir = __DIR__ . '/wp-content/plugins/projectfob/templates-backup-' . date('Y-m-d-His');
        rename($plugin_templates_dir, $backup_dir);
        echo "<p>✅ Backed up old templates to: " . basename($backup_dir) . "</p>";
    }

    // Extract only the templates folder from the zip
    $zip = new ZipArchive;
    if ($zip->open($zip_file) === TRUE) {
        // Create templates directory
        mkdir($plugin_templates_dir, 0755, true);

        // Extract only files inside the templates/ folder
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (strpos($filename, 'templates/') === 0) {
                $destination = $plugin_templates_dir . substr($filename, 10); // Remove 'templates/' prefix

                if (substr($filename, -1) == '/') {
                    // It's a directory
                    mkdir($destination, 0755, true);
                } else {
                    // It's a file
                    $dir = dirname($destination);
                    if (!is_dir($dir)) {
                        mkdir($dir, 0755, true);
                    }
                    copy("zip://" . $zip_file . "#" . $filename, $destination);
                }
            }
        }
        $zip->close();

        echo "<h2 style='color: green;'>✅ SUCCESS! Templates Installed</h2>";
        echo "<p><strong>Next steps:</strong></p>";
        echo "<ol>";
        echo "<li>Delete this file (manual-install-templates.php) for security</li>";
        echo "<li>Delete projectfob-templates-2.6.8.zip from WordPress root</li>";
        echo "<li>Clear all caches (WordPress cache, browser cache, CDN)</li>";
        echo "<li>Hard refresh the page (Ctrl+Shift+R or Cmd+Shift+R)</li>";
        echo "<li>Visit adminland page to verify columns are gone</li>";
        echo "</ol>";

        // Verify the fix is in place
        $adminland_file = $plugin_templates_dir . 'frontend/adminland/index.php';
        if (file_exists($adminland_file)) {
            $content = file_get_contents($adminland_file);
            if (strpos($content, 'FORCE EVERYTHING TO STACK VERTICALLY') !== false) {
                echo "<p style='color: green;'><strong>✅ Verified: Aggressive CSS fixes are present!</strong></p>";
            } else {
                echo "<p style='color: red;'><strong>⚠️ Warning: CSS fixes not found in installed template</strong></p>";
            }
        }

    } else {
        echo "<h2 style='color: red;'>❌ ERROR: Could not open zip file</h2>";
    }

} else {
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manual Templates Installer</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .box { background: #f5f5f5; border: 1px solid #ddd; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .warning { background: #fff3cd; border-color: #ffeeba; color: #856404; }
        input { padding: 10px; margin: 10px 0; font-size: 16px; }
        button { padding: 10px 20px; background: #0073aa; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 16px; }
        button:hover { background: #005177; }
    </style>
</head>
<body>
    <h1>📦 Manual Templates Installer</h1>

    <div class="box warning">
        <h3>Before You Start:</h3>
        <ol>
            <li>Upload <strong>projectfob-templates-2.6.8.zip</strong> to your WordPress root directory (same folder as wp-config.php)</li>
            <li>Upload <strong>this file (manual-install-templates.php)</strong> to WordPress root directory</li>
            <li>Make sure ProjectFOB plugin is installed</li>
        </ol>
    </div>

    <div class="box">
        <h3>Enter Password to Install:</h3>
        <form method="POST">
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit">Install Templates</button>
        </form>
        <p><small>Default password: <code>install2024</code></small></p>
    </div>

</body>
</html>
<?php
}
