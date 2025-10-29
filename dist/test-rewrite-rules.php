<?php
/**
 * Test script to verify rewrite rules are working
 *
 * Add this to your WordPress root and visit: yoursite.com/test-rewrite-rules.php
 */

// Load WordPress
require_once( dirname(__FILE__) . '/../../../wp-load.php' );

// Check if user is admin
if ( ! current_user_can( 'manage_options' ) ) {
    die( 'You must be an administrator to view this page.' );
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ProjectFOB Rewrite Rules Test</title>
    <style>
        body { font-family: sans-serif; padding: 20px; max-width: 1200px; margin: 0 auto; }
        h1 { color: #333; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .section { background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        th { background: #333; color: white; }
        code { background: #e0e0e0; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>ProjectFOB Rewrite Rules Test</h1>

    <div class="section">
        <h2>Plugin Status</h2>
        <?php if ( is_plugin_active( 'projectfob/projectfob.php' ) ) : ?>
            <p class="success">✓ ProjectFOB plugin is ACTIVE</p>
        <?php else : ?>
            <p class="error">✗ ProjectFOB plugin is NOT ACTIVE</p>
        <?php endif; ?>

        <?php if ( defined( 'PFOB_VERSION' ) ) : ?>
            <p class="success">✓ PFOB_VERSION constant defined: <?php echo PFOB_VERSION; ?></p>
        <?php else : ?>
            <p class="error">✗ PFOB_VERSION constant not defined</p>
        <?php endif; ?>
    </div>

    <div class="section">
        <h2>Rewrite Rules</h2>
        <?php
        global $wp_rewrite;
        $rules = get_option( 'rewrite_rules' );

        $projectfob_rules = array();
        if ( is_array( $rules ) ) {
            foreach ( $rules as $pattern => $rewrite ) {
                if ( strpos( $pattern, 'projectfob' ) !== false || strpos( $rewrite, 'pfob_' ) !== false ) {
                    $projectfob_rules[$pattern] = $rewrite;
                }
            }
        }

        if ( ! empty( $projectfob_rules ) ) {
            echo '<p class="success">✓ Found ' . count( $projectfob_rules ) . ' ProjectFOB rewrite rules</p>';
            echo '<table>';
            echo '<tr><th>Pattern</th><th>Rewrite</th></tr>';
            foreach ( $projectfob_rules as $pattern => $rewrite ) {
                echo '<tr>';
                echo '<td><code>' . esc_html( $pattern ) . '</code></td>';
                echo '<td><code>' . esc_html( $rewrite ) . '</code></td>';
                echo '</tr>';
            }
            echo '</table>';
        } else {
            echo '<p class="error">✗ No ProjectFOB rewrite rules found!</p>';
            echo '<p><strong>Action:</strong> Go to <a href="' . admin_url( 'options-permalink.php' ) . '">Settings > Permalinks</a> and click Save Changes</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Query Vars</h2>
        <?php
        global $wp;
        if ( in_array( 'pfob_page', $wp->public_query_vars ) ) {
            echo '<p class="success">✓ pfob_page query var registered</p>';
        } else {
            echo '<p class="error">✗ pfob_page query var NOT registered</p>';
        }

        if ( in_array( 'pfob_public_page', $wp->public_query_vars ) ) {
            echo '<p class="success">✓ pfob_public_page query var registered</p>';
        } else {
            echo '<p class="error">✗ pfob_public_page query var NOT registered</p>';
        }
        ?>
    </div>

    <div class="section">
        <h2>Test URLs</h2>
        <p>Try visiting these URLs:</p>
        <ul>
            <li><a href="<?php echo home_url( '/projectfob/pricing/' ); ?>" target="_blank"><?php echo home_url( '/projectfob/pricing/' ); ?></a> - Pricing page (public)</li>
            <li><a href="<?php echo home_url( '/projectfob/' ); ?>" target="_blank"><?php echo home_url( '/projectfob/' ); ?></a> - Dashboard (requires login)</li>
        </ul>
    </div>

    <div class="section">
        <h2>Manual Fix</h2>
        <p>If rewrite rules aren't showing above, click this button to flush them manually:</p>
        <?php
        if ( isset( $_GET['flush'] ) && $_GET['flush'] === '1' ) {
            flush_rewrite_rules();
            echo '<p class="success">✓ Rewrite rules flushed! <a href="' . remove_query_arg( 'flush' ) . '">Refresh this page</a></p>';
        } else {
            echo '<p><a href="' . add_query_arg( 'flush', '1' ) . '" class="button">Flush Rewrite Rules Now</a></p>';
        }
        ?>
    </div>

</body>
</html>
