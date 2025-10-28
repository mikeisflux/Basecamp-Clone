<?php
/**
 * Admin Settings Page Template
 *
 * @package Warcampaign
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get current settings
$paypal_client_id = get_option( 'wc_paypal_client_id', '' );
$paypal_client_secret = get_option( 'wc_paypal_client_secret', '' );
$paypal_sandbox = get_option( 'wc_paypal_sandbox_mode', false );
$paypal_webhook_id = get_option( 'wc_paypal_webhook_id', '' );

$r2_access_key = get_option( 'wc_r2_access_key_id', '' );
$r2_secret_key = get_option( 'wc_r2_secret_access_key', '' );

$paypal_configured = ! empty( $paypal_client_id ) && ! empty( $paypal_client_secret );
$r2_configured = ! empty( $r2_access_key ) && ! empty( $r2_secret_key );
?>

<div class="wrap wc-admin-settings">
    <h1>Warcampaign Settings</h1>

    <?php settings_errors( 'wc_settings' ); ?>

    <div class="wc-settings-container">
        <form method="post" action="">
            <?php wp_nonce_field( 'wc_settings_save' ); ?>

            <!-- PayPal Configuration -->
            <div class="wc-settings-section">
                <div class="section-header">
                    <h2>
                        <span class="dashicons dashicons-money-alt"></span>
                        PayPal Subscription Billing
                        <?php if ( $paypal_configured ) : ?>
                            <span class="status-badge status-success">Configured</span>
                        <?php else : ?>
                            <span class="status-badge status-warning">Not Configured</span>
                        <?php endif; ?>
                    </h2>
                    <p class="description">Configure PayPal REST API credentials for subscription billing. All payments will go to: <strong>divinitycomicsinc@gmail.com</strong></p>
                </div>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="wc_paypal_client_id">Client ID</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="wc_paypal_client_id"
                                   name="wc_paypal_client_id"
                                   value="<?php echo esc_attr( $paypal_client_id ); ?>"
                                   class="regular-text"
                                   placeholder="PayPal Client ID">
                            <p class="description">
                                Get from: <a href="https://developer.paypal.com/dashboard/applications" target="_blank">PayPal Developer Dashboard</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_paypal_client_secret">Client Secret</label>
                        </th>
                        <td>
                            <input type="password"
                                   id="wc_paypal_client_secret"
                                   name="wc_paypal_client_secret"
                                   value="<?php echo esc_attr( $paypal_client_secret ); ?>"
                                   class="regular-text"
                                   placeholder="PayPal Client Secret">
                            <button type="button" class="button button-secondary" onclick="togglePasswordVisibility('wc_paypal_client_secret')">
                                <span class="dashicons dashicons-visibility"></span> Show
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Mode</th>
                        <td>
                            <label>
                                <input type="checkbox"
                                       name="wc_paypal_sandbox_mode"
                                       value="1"
                                       <?php checked( $paypal_sandbox ); ?>>
                                Enable Sandbox Mode (for testing)
                            </label>
                            <p class="description">
                                Use sandbox credentials for testing. Uncheck for live production payments.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_paypal_webhook_id">Webhook ID</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="wc_paypal_webhook_id"
                                   name="wc_paypal_webhook_id"
                                   value="<?php echo esc_attr( $paypal_webhook_id ); ?>"
                                   class="regular-text"
                                   placeholder="Webhook ID (optional)">
                            <p class="description">
                                <strong>Webhook URL:</strong> <code><?php echo esc_url( rest_url( 'warcampaign/v1/webhooks/paypal' ) ); ?></code><br>
                                Add this URL in PayPal Developer Dashboard > Webhooks. Subscribe to all BILLING.SUBSCRIPTION.* events.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Connection Test</th>
                        <td>
                            <button type="button"
                                    id="test-paypal-btn"
                                    class="button button-secondary"
                                    <?php echo $paypal_configured ? '' : 'disabled'; ?>>
                                <span class="dashicons dashicons-cloud"></span> Test PayPal Connection
                            </button>
                            <span id="paypal-test-result" class="test-result"></span>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Sync Plans</th>
                        <td>
                            <button type="button"
                                    id="sync-plans-btn"
                                    class="button button-secondary"
                                    <?php echo $paypal_configured ? '' : 'disabled'; ?>>
                                <span class="dashicons dashicons-update"></span> Sync Subscription Plans to PayPal
                            </button>
                            <p class="description">
                                Create/update all 4 subscription plans in PayPal (Starter, Professional, Business, Enterprise).
                            </p>
                            <span id="sync-plans-result" class="test-result"></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Cloudflare R2 Configuration -->
            <div class="wc-settings-section">
                <div class="section-header">
                    <h2>
                        <span class="dashicons dashicons-cloud-upload"></span>
                        Cloudflare R2 Storage
                        <?php if ( $r2_configured ) : ?>
                            <span class="status-badge status-success">Configured</span>
                        <?php else : ?>
                            <span class="status-badge status-warning">Not Configured</span>
                        <?php endif; ?>
                    </h2>
                    <p class="description">Configure Cloudflare R2 for cloud file storage. All user uploads will be stored here.</p>
                </div>

                <table class="form-table">
                    <tr>
                        <th scope="row">Bucket Details</th>
                        <td>
                            <p><strong>Bucket Name:</strong> <code>warcampaign</code></p>
                            <p><strong>Endpoint:</strong> <code><?php echo esc_html( WC_R2_ENDPOINT ); ?></code></p>
                            <p><strong>Location:</strong> Eastern North America (ENAM)</p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_r2_access_key_id">Access Key ID</label>
                        </th>
                        <td>
                            <input type="text"
                                   id="wc_r2_access_key_id"
                                   name="wc_r2_access_key_id"
                                   value="<?php echo esc_attr( $r2_access_key ); ?>"
                                   class="regular-text"
                                   placeholder="R2 Access Key ID">
                            <p class="description">
                                Get from: <a href="https://dash.cloudflare.com/?to=/:account/r2" target="_blank">Cloudflare Dashboard</a> > R2 > Manage R2 API Tokens
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="wc_r2_secret_access_key">Secret Access Key</label>
                        </th>
                        <td>
                            <input type="password"
                                   id="wc_r2_secret_access_key"
                                   name="wc_r2_secret_access_key"
                                   value="<?php echo esc_attr( $r2_secret_key ); ?>"
                                   class="regular-text"
                                   placeholder="R2 Secret Access Key">
                            <button type="button" class="button button-secondary" onclick="togglePasswordVisibility('wc_r2_secret_access_key')">
                                <span class="dashicons dashicons-visibility"></span> Show
                            </button>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">Connection Test</th>
                        <td>
                            <button type="button"
                                    id="test-r2-btn"
                                    class="button button-secondary"
                                    <?php echo $r2_configured ? '' : 'disabled'; ?>>
                                <span class="dashicons dashicons-cloud"></span> Test R2 Connection
                            </button>
                            <span id="r2-test-result" class="test-result"></span>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Save Button -->
            <p class="submit">
                <button type="submit" name="wc_save_settings" class="button button-primary button-large">
                    <span class="dashicons dashicons-yes"></span> Save All Settings
                </button>
            </p>
        </form>

        <!-- Help Section -->
        <div class="wc-settings-section wc-help-section">
            <h2><span class="dashicons dashicons-info"></span> Setup Guide</h2>

            <div class="help-grid">
                <div class="help-card">
                    <h3>1. Get PayPal Credentials</h3>
                    <ol>
                        <li>Go to <a href="https://developer.paypal.com/dashboard/applications" target="_blank">PayPal Developer Dashboard</a></li>
                        <li>Log in with: <strong>divinitycomicsinc@gmail.com</strong></li>
                        <li>Click "Create App" (or select existing app)</li>
                        <li>Copy Client ID and Secret</li>
                        <li>For live mode, switch from Sandbox to Live</li>
                    </ol>
                </div>

                <div class="help-card">
                    <h3>2. Get R2 Credentials</h3>
                    <ol>
                        <li>Go to <a href="https://dash.cloudflare.com" target="_blank">Cloudflare Dashboard</a></li>
                        <li>Navigate to R2 > Manage R2 API Tokens</li>
                        <li>Click "Create API Token"</li>
                        <li>Select "Read & Write" permissions</li>
                        <li>Copy Access Key ID and Secret Access Key</li>
                    </ol>
                </div>

                <div class="help-card">
                    <h3>3. Set Up Webhook</h3>
                    <ol>
                        <li>In PayPal Dashboard, go to Webhooks</li>
                        <li>Click "Add Webhook"</li>
                        <li>Enter URL: <code><?php echo esc_url( rest_url( 'warcampaign/v1/webhooks/paypal' ) ); ?></code></li>
                        <li>Subscribe to: BILLING.SUBSCRIPTION.* and PAYMENT.SALE.*</li>
                        <li>Copy Webhook ID and paste above</li>
                    </ol>
                </div>

                <div class="help-card">
                    <h3>4. Sync Plans & Test</h3>
                    <ol>
                        <li>Save your PayPal credentials above</li>
                        <li>Click "Test PayPal Connection"</li>
                        <li>Click "Sync Subscription Plans to PayPal"</li>
                        <li>Test R2 connection</li>
                        <li>Visit <a href="<?php echo site_url( '/warcampaign/pricing' ); ?>" target="_blank"><?php echo site_url( '/warcampaign/pricing' ); ?></a></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.wc-admin-settings {
    max-width: 1200px;
}

.wc-settings-container {
    background: #fff;
    margin-top: 20px;
}

.wc-settings-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px 30px;
    margin-bottom: 20px;
}

.section-header {
    border-bottom: 1px solid #e0e0e0;
    padding-bottom: 15px;
    margin-bottom: 20px;
}

.section-header h2 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0 0 10px 0;
}

.section-header .dashicons {
    font-size: 24px;
    width: 24px;
    height: 24px;
}

.status-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 10px;
}

.status-success {
    background: #d4edda;
    color: #155724;
}

.status-warning {
    background: #fff3cd;
    color: #856404;
}

.form-table th {
    width: 200px;
    font-weight: 600;
}

.form-table input[type="text"],
.form-table input[type="password"] {
    width: 400px;
}

.test-result {
    margin-left: 10px;
    font-weight: 600;
}

.test-result.success {
    color: #46b450;
}

.test-result.error {
    color: #dc3232;
}

.test-result .dashicons {
    vertical-align: middle;
}

.wc-help-section {
    background: #f8f9fa !important;
}

.help-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.help-card {
    background: white;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.help-card h3 {
    margin-top: 0;
    color: #2271b1;
}

.help-card ol {
    padding-left: 20px;
    margin-bottom: 0;
}

.help-card li {
    margin-bottom: 8px;
    line-height: 1.6;
}

.help-card code {
    background: #f0f0f1;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
}
</style>

<script>
function togglePasswordVisibility(fieldId) {
    const field = document.getElementById(fieldId);
    const button = event.target.closest('button');

    if (field.type === 'password') {
        field.type = 'text';
        button.innerHTML = '<span class="dashicons dashicons-hidden"></span> Hide';
    } else {
        field.type = 'password';
        button.innerHTML = '<span class="dashicons dashicons-visibility"></span> Show';
    }
}

jQuery(document).ready(function($) {
    // Test PayPal Connection
    $('#test-paypal-btn').on('click', function() {
        const $btn = $(this);
        const $result = $('#paypal-test-result');

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Testing...');
        $result.removeClass('success error').text('');

        $.ajax({
            url: wcAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'wc_test_paypal_connection',
                nonce: wcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.addClass('success').html('<span class="dashicons dashicons-yes"></span> ' + response.data.message);
                } else {
                    $result.addClass('error').html('<span class="dashicons dashicons-no"></span> ' + response.data.message);
                }
            },
            error: function() {
                $result.addClass('error').html('<span class="dashicons dashicons-no"></span> Connection test failed');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-cloud"></span> Test PayPal Connection');
            }
        });
    });

    // Test R2 Connection
    $('#test-r2-btn').on('click', function() {
        const $btn = $(this);
        const $result = $('#r2-test-result');

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Testing...');
        $result.removeClass('success error').text('');

        $.ajax({
            url: wcAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'wc_test_r2_connection',
                nonce: wcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.addClass('success').html('<span class="dashicons dashicons-yes"></span> ' + response.data.message);
                } else {
                    $result.addClass('error').html('<span class="dashicons dashicons-no"></span> ' + response.data.message);
                }
            },
            error: function() {
                $result.addClass('error').html('<span class="dashicons dashicons-no"></span> Connection test failed');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-cloud"></span> Test R2 Connection');
            }
        });
    });

    // Sync PayPal Plans
    $('#sync-plans-btn').on('click', function() {
        const $btn = $(this);
        const $result = $('#sync-plans-result');

        $btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Syncing...');
        $result.removeClass('success error').text('');

        $.ajax({
            url: wcAdmin.ajaxurl,
            method: 'POST',
            data: {
                action: 'wc_sync_paypal_plans',
                nonce: wcAdmin.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.addClass('success').html('<span class="dashicons dashicons-yes"></span> ' + response.data.message);
                } else {
                    $result.addClass('error').html('<span class="dashicons dashicons-no"></span> ' + response.data.message);
                }
            },
            error: function() {
                $result.addClass('error').html('<span class="dashicons dashicons-no"></span> Sync failed');
            },
            complete: function() {
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> Sync Subscription Plans to PayPal');
            }
        });
    });
});
</script>

<style>
.dashicons.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>
