<?php
/**
 * Flush WordPress Rewrite Rules for ProjectFOB
 *
 * INSTRUCTIONS:
 * 1. Upload this file to your WordPress root directory (same folder as wp-config.php)
 * 2. Access it in your browser: https://yourdomain.com/flush-rewrite-rules.php
 * 3. You should see a success message
 * 4. DELETE this file after use for security
 * 5. Try accessing your ProjectFOB pages again
 */

// Load WordPress
require_once( __DIR__ . '/wp-load.php' );

// Check if user is admin
if ( ! current_user_can( 'manage_options' ) ) {
    die( 'ERROR: You must be logged in as an administrator to flush rewrite rules.' );
}

// Flush rewrite rules
flush_rewrite_rules();

?>
<!DOCTYPE html>
<html>
<head>
    <title>Rewrite Rules Flushed</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f0f0f0;
            padding: 40px;
            margin: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2d9061;
            margin: 0 0 20px 0;
        }
        .success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .instructions {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
        .instructions h3 {
            margin: 0 0 10px 0;
            color: #856404;
        }
        .instructions ol {
            margin: 0;
            padding-left: 20px;
        }
        .instructions li {
            margin-bottom: 8px;
            color: #856404;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #2d9061;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px 5px;
        }
        .btn:hover {
            background: #247a50;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Rewrite Rules Flushed Successfully</h1>

        <div class="success">
            WordPress rewrite rules have been flushed. Your ProjectFOB routes should now work correctly.
        </div>

        <div class="instructions">
            <h3>⚠️ IMPORTANT - Security</h3>
            <ol>
                <li><strong>DELETE this file immediately</strong> for security reasons</li>
                <li>File to delete: <code>flush-rewrite-rules.php</code></li>
                <li>Location: Your WordPress root directory</li>
            </ol>
        </div>

        <h3>Next Steps:</h3>
        <ol>
            <li>Delete <code>flush-rewrite-rules.php</code> from your server</li>
            <li>Try accessing ProjectFOB pages again</li>
            <li>Test creating a project</li>
        </ol>

        <div style="margin-top: 30px;">
            <a href="<?php echo site_url( '/projectfob/' ); ?>" class="btn">Go to ProjectFOB Dashboard</a>
            <a href="<?php echo site_url( '/projectfob/pricing' ); ?>" class="btn">View Pricing Page</a>
        </div>

        <p style="margin-top: 30px; color: #666; font-size: 14px;">
            <strong>Troubleshooting:</strong><br>
            If you still see "No route was found" errors:<br>
            1. Go to WordPress Admin → Settings → Permalinks<br>
            2. Click "Save Changes" (don't change anything, just save)<br>
            3. This will also flush rewrite rules
        </p>
    </div>
</body>
</html>
