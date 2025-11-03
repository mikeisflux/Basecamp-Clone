<?php
/**
 * White Label Settings
 *
 * Required: Enterprise plan only
 */

// Check if user has White Label feature in their plan
$user_id = get_current_user_id();
$subscription = PFOB_Subscription::get_by_user_id( $user_id );

if ( ! $subscription ) {
    wp_die( __( 'Access denied. You must have an active subscription to access this page.', 'projectfob' ) );
}

$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

// Check if plan includes White Label feature
if ( ! $plan || empty( $plan['features']['white_label'] ) ) {
    wp_die( __( 'Access denied. White Label branding is only available on Enterprise plans. <a href="' . home_url( '/projectfob/adminland/billing' ) . '">Upgrade your plan</a>', 'projectfob' ) );
}

// Get current white label settings
$app_name = get_user_meta( $user_id, 'pfob_white_label_app_name', true ) ?: 'ProjectFOB';
$primary_color = get_user_meta( $user_id, 'pfob_white_label_primary_color', true ) ?: '#2d9061';
$secondary_color = get_user_meta( $user_id, 'pfob_white_label_secondary_color', true ) ?: '#667eea';
$hide_branding = get_user_meta( $user_id, 'pfob_white_label_hide_branding', true );
$custom_footer = get_user_meta( $user_id, 'pfob_white_label_custom_footer', true );

PFOB_Template::header( 'White Label Settings' );
?>

<div class="pfob-container">
    <?php PFOB_Template::navigation(); ?>

    <main class="pfob-main pfob-white-label">

        <header class="pfob-page-header">
            <div>
                <h1>🎨 White Label Branding</h1>
                <p class="pfob-subtitle">Customize the appearance and branding of your workspace</p>
            </div>
        </header>

        <div class="pfob-white-label-grid">
            <!-- Left Column: Settings -->
            <div class="pfob-settings-panel">

                <!-- Application Name -->
                <div class="pfob-settings-section">
                    <h2>Application Name</h2>
                    <p class="setting-description">Customize the name that appears throughout your workspace</p>

                    <div class="setting-field">
                        <label for="app-name">Application Name</label>
                        <input type="text"
                               id="app-name"
                               value="<?php echo esc_attr( $app_name ); ?>"
                               placeholder="Your Company Name"
                               class="pfob-input">
                        <span class="field-hint">This will replace "ProjectFOB" everywhere in the app</span>
                    </div>
                </div>

                <!-- Color Scheme -->
                <div class="pfob-settings-section">
                    <h2>Color Scheme</h2>
                    <p class="setting-description">Customize your brand colors</p>

                    <div class="setting-field">
                        <label for="primary-color">Primary Color</label>
                        <div class="color-picker-group">
                            <input type="color"
                                   id="primary-color"
                                   value="<?php echo esc_attr( $primary_color ); ?>"
                                   class="pfob-color-picker">
                            <input type="text"
                                   id="primary-color-text"
                                   value="<?php echo esc_attr( $primary_color ); ?>"
                                   class="pfob-input-sm"
                                   pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                        <span class="field-hint">Used for buttons, links, and primary UI elements</span>
                    </div>

                    <div class="setting-field">
                        <label for="secondary-color">Secondary Color</label>
                        <div class="color-picker-group">
                            <input type="color"
                                   id="secondary-color"
                                   value="<?php echo esc_attr( $secondary_color ); ?>"
                                   class="pfob-color-picker">
                            <input type="text"
                                   id="secondary-color-text"
                                   value="<?php echo esc_attr( $secondary_color ); ?>"
                                   class="pfob-input-sm"
                                   pattern="^#[0-9A-Fa-f]{6}$">
                        </div>
                        <span class="field-hint">Used for secondary UI elements and accents</span>
                    </div>
                </div>

                <!-- Branding Options -->
                <div class="pfob-settings-section">
                    <h2>Branding Options</h2>

                    <div class="setting-field">
                        <label class="checkbox-label">
                            <input type="checkbox"
                                   id="hide-branding"
                                   <?php checked( $hide_branding, '1' ); ?>>
                            <span>Hide "Powered by ProjectFOB" branding</span>
                        </label>
                        <span class="field-hint">Removes all ProjectFOB branding from the footer</span>
                    </div>

                    <div class="setting-field">
                        <label for="custom-footer">Custom Footer Text</label>
                        <input type="text"
                               id="custom-footer"
                               value="<?php echo esc_attr( $custom_footer ); ?>"
                               placeholder="© 2025 Your Company. All rights reserved."
                               class="pfob-input">
                        <span class="field-hint">Optional custom text for the footer</span>
                    </div>
                </div>

                <!-- Save Button -->
                <div class="pfob-settings-section">
                    <button id="save-settings-btn" class="pfob-btn pfob-btn-primary pfob-btn-large">
                        💾 Save White Label Settings
                    </button>
                    <button id="reset-settings-btn" class="pfob-btn pfob-btn-secondary">
                        🔄 Reset to Defaults
                    </button>
                </div>

                <div id="save-status" class="save-status"></div>

            </div>

            <!-- Right Column: Preview -->
            <div class="pfob-preview-panel">
                <h3>Live Preview</h3>
                <div class="preview-container" id="preview-container">
                    <div class="preview-header" id="preview-header">
                        <div class="preview-logo" id="preview-logo"><?php echo esc_html( $app_name ); ?></div>
                        <div class="preview-nav">
                            <a href="#" class="preview-nav-item">Dashboard</a>
                            <a href="#" class="preview-nav-item">Projects</a>
                            <a href="#" class="preview-nav-item">Team</a>
                        </div>
                    </div>
                    <div class="preview-content">
                        <h2 style="color: <?php echo esc_attr( $primary_color ); ?>;">Sample Page</h2>
                        <button class="preview-btn" id="preview-btn">Sample Button</button>
                        <a href="#" class="preview-link" id="preview-link">Sample Link</a>
                    </div>
                    <div class="preview-footer" id="preview-footer">
                        <?php if ( $hide_branding ) : ?>
                            <?php echo $custom_footer ? esc_html( $custom_footer ) : ''; ?>
                        <?php else : ?>
                            Powered by ProjectFOB
                            <?php echo $custom_footer ? ' | ' . esc_html( $custom_footer ) : ''; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <p class="preview-note">⚠️ Changes will apply to all users in your workspace after saving</p>
            </div>
        </div>

    </main>
</div>

<script>
const pfobData = {
    restUrl: '<?php echo rest_url( 'projectfob/v1' ); ?>',
    nonce: '<?php echo wp_create_nonce( 'wp_rest' ); ?>',
};

// Live preview updates
const appNameInput = document.getElementById('app-name');
const primaryColorInput = document.getElementById('primary-color');
const primaryColorText = document.getElementById('primary-color-text');
const secondaryColorInput = document.getElementById('secondary-color');
const secondaryColorText = document.getElementById('secondary-color-text');
const hideBrandingCheck = document.getElementById('hide-branding');
const customFooterInput = document.getElementById('custom-footer');

appNameInput.addEventListener('input', updatePreview);
primaryColorInput.addEventListener('input', (e) => {
    primaryColorText.value = e.target.value;
    updatePreview();
});
primaryColorText.addEventListener('input', (e) => {
    if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
        primaryColorInput.value = e.target.value;
        updatePreview();
    }
});
secondaryColorInput.addEventListener('input', (e) => {
    secondaryColorText.value = e.target.value;
    updatePreview();
});
secondaryColorText.addEventListener('input', (e) => {
    if (/^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
        secondaryColorInput.value = e.target.value;
        updatePreview();
    }
});
hideBrandingCheck.addEventListener('change', updatePreview);
customFooterInput.addEventListener('input', updatePreview);

function updatePreview() {
    const appName = appNameInput.value || 'ProjectFOB';
    const primaryColor = primaryColorInput.value;
    const secondaryColor = secondaryColorInput.value;
    const hideBranding = hideBrandingCheck.checked;
    const customFooter = customFooterInput.value;

    document.getElementById('preview-logo').textContent = appName;
    document.getElementById('preview-header').style.background = `linear-gradient(135deg, ${primaryColor} 0%, ${secondaryColor} 100%)`;
    document.getElementById('preview-btn').style.background = primaryColor;
    document.getElementById('preview-link').style.color = primaryColor;

    let footerText = '';
    if (!hideBranding) {
        footerText = 'Powered by ProjectFOB';
        if (customFooter) footerText += ' | ';
    }
    if (customFooter) footerText += customFooter;
    document.getElementById('preview-footer').textContent = footerText;
}

// Save settings
document.getElementById('save-settings-btn').addEventListener('click', async () => {
    const statusDiv = document.getElementById('save-status');
    const btn = document.getElementById('save-settings-btn');

    btn.disabled = true;
    statusDiv.innerHTML = '<p style="color: #0066cc;">Saving...</p>';

    try {
        const response = await fetch(`${pfobData.restUrl}/settings/white-label`, {
            method: 'POST',
            headers: {
                'X-WP-Nonce': pfobData.nonce,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                app_name: appNameInput.value,
                primary_color: primaryColorInput.value,
                secondary_color: secondaryColorInput.value,
                hide_branding: hideBrandingCheck.checked,
                custom_footer: customFooterInput.value
            })
        });

        const result = await response.json();

        if (result.success) {
            statusDiv.innerHTML = '<p style="color: #46b450;">✓ Settings saved successfully! Refresh the page to see changes.</p>';
            setTimeout(() => location.reload(), 2000);
        } else {
            statusDiv.innerHTML = '<p style="color: #dc3232;">Error: ' + (result.message || 'Failed to save') + '</p>';
            btn.disabled = false;
        }
    } catch (error) {
        statusDiv.innerHTML = '<p style="color: #dc3232;">Error saving settings. Please try again.</p>';
        btn.disabled = false;
    }
});

// Reset settings
document.getElementById('reset-settings-btn').addEventListener('click', () => {
    if (confirm('Are you sure you want to reset all white label settings to defaults?')) {
        appNameInput.value = 'ProjectFOB';
        primaryColorInput.value = '#2d9061';
        primaryColorText.value = '#2d9061';
        secondaryColorInput.value = '#667eea';
        secondaryColorText.value = '#667eea';
        hideBrandingCheck.checked = false;
        customFooterInput.value = '';
        updatePreview();
    }
});
</script>

<style>
.pfob-white-label {
    max-width: 1400px;
    margin: 0 auto;
}

.pfob-subtitle {
    color: #6b7280;
    margin-top: 5px;
}

.pfob-white-label-grid {
    display: grid;
    grid-template-columns: 1fr 500px;
    gap: 30px;
}

.pfob-settings-panel {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.pfob-settings-section {
    background: white;
    border-radius: 8px;
    padding: 25px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.pfob-settings-section h2 {
    margin: 0 0 10px 0;
    font-size: 18px;
    color: #111827;
}

.setting-description {
    color: #6b7280;
    font-size: 14px;
    margin-bottom: 20px;
}

.setting-field {
    margin-bottom: 20px;
}

.setting-field:last-child {
    margin-bottom: 0;
}

.setting-field label {
    display: block;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-size: 14px;
}

.field-hint {
    display: block;
    font-size: 13px;
    color: #9ca3af;
    margin-top: 5px;
}

.pfob-input, .pfob-input-sm {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.pfob-input-sm {
    width: 120px;
}

.color-picker-group {
    display: flex;
    gap: 10px;
    align-items: center;
}

.pfob-color-picker {
    width: 60px;
    height: 40px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    cursor: pointer;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    font-weight: normal;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.pfob-btn {
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 15px;
}

.pfob-btn-large {
    width: 100%;
    padding: 15px;
    font-size: 16px;
}

.pfob-btn-primary {
    background: #2d9061;
    color: white;
}

.pfob-btn-primary:hover:not(:disabled) {
    background: #247d52;
}

.pfob-btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.pfob-btn-secondary {
    background: #f3f4f6;
    color: #374151;
    margin-top: 10px;
}

.pfob-btn-secondary:hover {
    background: #e5e7eb;
}

.save-status {
    margin-top: 15px;
}

.pfob-preview-panel {
    position: sticky;
    top: 80px;
    align-self: flex-start;
}

.pfob-preview-panel h3 {
    margin: 0 0 15px 0;
    font-size: 16px;
    color: #374151;
}

.preview-container {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.preview-header {
    background: linear-gradient(135deg, #2d9061 0%, #667eea 100%);
    padding: 20px;
    color: white;
}

.preview-logo {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 15px;
}

.preview-nav {
    display: flex;
    gap: 20px;
}

.preview-nav-item {
    color: white;
    text-decoration: none;
    font-size: 14px;
    opacity: 0.9;
}

.preview-nav-item:hover {
    opacity: 1;
}

.preview-content {
    padding: 30px 20px;
}

.preview-content h2 {
    margin: 0 0 20px 0;
}

.preview-btn {
    background: #2d9061;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    margin-right: 15px;
}

.preview-link {
    color: #2d9061;
    text-decoration: none;
    font-weight: 600;
}

.preview-footer {
    background: #f9fafb;
    padding: 15px 20px;
    text-align: center;
    font-size: 13px;
    color: #6b7280;
    border-top: 1px solid #e5e7eb;
}

.preview-note {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    padding: 12px;
    margin-top: 15px;
    font-size: 13px;
    color: #92400e;
    border-radius: 4px;
}

@media (max-width: 1200px) {
    .pfob-white-label-grid {
        grid-template-columns: 1fr;
    }

    .pfob-preview-panel {
        position: relative;
        top: 0;
    }
}
</style>

<?php PFOB_Template::footer(); ?>
