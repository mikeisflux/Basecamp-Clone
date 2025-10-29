<?php
/**
 * PHP Syntax Checker for ProjectFOB Plugin
 *
 * Run this from command line: php check-syntax.php
 * Or upload to WordPress root and visit in browser
 */

// Check if running from command line
$is_cli = php_sapi_name() === 'cli';

if (!$is_cli) {
    echo '<!DOCTYPE html><html><head><title>ProjectFOB Syntax Check</title><style>
    body { font-family: monospace; padding: 20px; background: #1e1e1e; color: #d4d4d4; }
    .error { color: #f48771; background: #3c1f1e; padding: 10px; margin: 10px 0; border-left: 4px solid #f48771; }
    .success { color: #8bc34a; background: #1e2f1e; padding: 10px; margin: 10px 0; border-left: 4px solid #8bc34a; }
    .file { color: #4fc3f7; font-weight: bold; }
    </style></head><body>';
}

echo "🔍 ProjectFOB PHP Syntax Checker\n";
echo "=================================\n\n";

$plugin_dir = __DIR__;
$errors = [];
$checked = 0;

// Find all PHP files
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($plugin_dir)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $filepath = $file->getPathname();

        // Skip vendor, node_modules, build directories
        if (strpos($filepath, '/vendor/') !== false ||
            strpos($filepath, '/node_modules/') !== false ||
            strpos($filepath, '/build/') !== false ||
            strpos($filepath, '/.git/') !== false) {
            continue;
        }

        $checked++;
        $relative_path = str_replace($plugin_dir . '/', '', $filepath);

        // Check syntax
        $output = [];
        $return_var = 0;
        exec("php -l " . escapeshellarg($filepath) . " 2>&1", $output, $return_var);

        if ($return_var !== 0) {
            $errors[] = [
                'file' => $relative_path,
                'error' => implode("\n", $output)
            ];

            if ($is_cli) {
                echo "❌ ERROR: $relative_path\n";
                echo "   " . implode("\n   ", $output) . "\n\n";
            } else {
                echo "<div class='error'>";
                echo "<div class='file'>❌ $relative_path</div>";
                echo "<pre>" . htmlspecialchars(implode("\n", $output)) . "</pre>";
                echo "</div>";
            }
        }
    }
}

echo "\n";
echo "📊 Summary\n";
echo "----------\n";
echo "Files checked: $checked\n";
echo "Errors found: " . count($errors) . "\n";

if (count($errors) === 0) {
    if ($is_cli) {
        echo "\n✅ All files passed syntax check!\n";
    } else {
        echo "<div class='success'>✅ All files passed syntax check!</div>";
    }
} else {
    echo "\n❌ Found " . count($errors) . " file(s) with syntax errors.\n";
    echo "Fix these errors and try activating again.\n";
}

if (!$is_cli) {
    echo '</body></html>';
}
