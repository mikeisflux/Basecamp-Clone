<?php
/**
 * Storage Management Page
 *
 * Allows subscribers to view storage usage and manage files.
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$user_id = get_current_user_id();
$subscription = PFOB_Subscription::get_by_user_id( $user_id );

if ( ! $subscription ) {
    wp_die( __( 'No active subscription found.', 'projectfob' ) );
}

// Get subscription plan configuration
$plans_config = include PFOB_PLUGIN_DIR . 'includes/config/subscription-plans.php';
$plan = isset( $plans_config[ $subscription->plan_id ] ) ? $plans_config[ $subscription->plan_id ] : null;

if ( ! $plan ) {
    wp_die( __( 'Invalid subscription plan.', 'projectfob' ) );
}

// Get storage limit from plan
$storage_limit_gb = isset( $plan['features']['storage_gb'] ) ? $plan['features']['storage_gb'] : 10;
$is_unlimited = $storage_limit_gb == 999999;

// Calculate storage usage
global $wpdb;
$documents_table = $wpdb->prefix . 'pfob_documents';

// Get all documents for this user's account
$total_storage_query = "
    SELECT SUM(file_size) as total_bytes
    FROM {$documents_table}
    WHERE uploaded_by IN (
        SELECT ID FROM {$wpdb->users}
        WHERE ID = %d OR ID IN (
            SELECT user_id FROM {$wpdb->usermeta}
            WHERE meta_key = 'pfob_account_owner' AND meta_value = %d
        )
    )
";

$result = $wpdb->get_row( $wpdb->prepare( $total_storage_query, $user_id, $user_id ) );
$total_bytes = $result->total_bytes ?? 0;

// Convert to GB
$used_gb = $total_bytes / (1024 * 1024 * 1024);
$used_percentage = $is_unlimited ? 0 : min( 100, ( $used_gb / $storage_limit_gb ) * 100 );

// Get file breakdown by type
$file_breakdown_query = "
    SELECT
        mime_type,
        COUNT(*) as file_count,
        SUM(file_size) as total_size
    FROM {$documents_table}
    WHERE uploaded_by IN (
        SELECT ID FROM {$wpdb->users}
        WHERE ID = %d OR ID IN (
            SELECT user_id FROM {$wpdb->usermeta}
            WHERE meta_key = 'pfob_account_owner' AND meta_value = %d
        )
    )
    GROUP BY mime_type
    ORDER BY total_size DESC
    LIMIT 10
";

$file_breakdown = $wpdb->get_results( $wpdb->prepare( $file_breakdown_query, $user_id, $user_id ) );

// Get largest files
$largest_files_query = "
    SELECT
        name as title,
        file_size,
        mime_type,
        created_at
    FROM {$documents_table}
    WHERE uploaded_by IN (
        SELECT ID FROM {$wpdb->users}
        WHERE ID = %d OR ID IN (
            SELECT user_id FROM {$wpdb->usermeta}
            WHERE meta_key = 'pfob_account_owner' AND meta_value = %d
        )
    )
    ORDER BY file_size DESC
    LIMIT 20
";

$largest_files = $wpdb->get_results( $wpdb->prepare( $largest_files_query, $user_id, $user_id ) );

PFOB_Template::header( 'Storage' );
?>

<div class="storage-page-wrapper">
    <a href="<?php echo home_url( '/projectfob/adminland' ); ?>" class="back-link">← Back to Adminland</a>

    <h1 class="page-title">Storage Management</h1>
    <p class="page-subtitle">Monitor your storage usage and manage files across all projects.</p>

    <!-- Storage Overview -->
    <div class="storage-section">
        <h2 class="section-title">Storage Usage</h2>

        <div class="storage-overview">
            <div class="storage-chart">
                <div class="chart-circle">
                    <svg viewBox="0 0 100 100" class="circular-chart">
                        <circle class="circle-bg" cx="50" cy="50" r="40"></circle>
                        <circle class="circle-progress"
                                cx="50" cy="50" r="40"
                                style="stroke-dasharray: <?php echo $used_percentage * 2.51; ?> 251;"></circle>
                    </svg>
                    <div class="chart-text">
                        <div class="chart-value"><?php echo number_format( $used_gb, 2 ); ?> GB</div>
                        <div class="chart-label">Used</div>
                    </div>
                </div>
            </div>

            <div class="storage-details">
                <div class="detail-row">
                    <span class="detail-label">Used Storage:</span>
                    <span class="detail-value"><?php echo number_format( $used_gb, 2 ); ?> GB</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Storage Limit:</span>
                    <span class="detail-value">
                        <?php echo $is_unlimited ? 'Unlimited' : number_format( $storage_limit_gb, 0 ) . ' GB'; ?>
                    </span>
                </div>
                <?php if ( ! $is_unlimited ) : ?>
                <div class="detail-row">
                    <span class="detail-label">Available:</span>
                    <span class="detail-value"><?php echo number_format( $storage_limit_gb - $used_gb, 2 ); ?> GB</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Usage:</span>
                    <span class="detail-value"><?php echo number_format( $used_percentage, 1 ); ?>%</span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( ! $is_unlimited && $used_percentage > 80 ) : ?>
        <div class="storage-warning">
            <strong>⚠️ Warning:</strong> You're using <?php echo number_format( $used_percentage, 0 ); ?>% of your storage.
            <?php if ( $used_percentage > 95 ) : ?>
            Consider upgrading your plan or deleting unused files.
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Storage Breakdown by File Type -->
    <?php if ( ! empty( $file_breakdown ) ) : ?>
    <div class="storage-section">
        <h2 class="section-title">Storage by File Type</h2>

        <div class="file-types-list">
            <?php foreach ( $file_breakdown as $type ) :
                $type_name = trim( str_replace( '"', '', $type->mime_type ?? 'unknown' ) );
                $type_size_gb = $type->total_size / (1024 * 1024 * 1024);
                $type_percentage = $total_bytes > 0 ? ( $type->total_size / $total_bytes ) * 100 : 0;
            ?>
            <div class="file-type-row">
                <div class="type-info">
                    <span class="type-icon"><?php echo get_file_type_icon( $type_name ); ?></span>
                    <div class="type-details">
                        <div class="type-name"><?php echo esc_html( $type_name ); ?></div>
                        <div class="type-count"><?php echo number_format( $type->file_count ); ?> files</div>
                    </div>
                </div>
                <div class="type-size">
                    <div class="size-value"><?php echo format_bytes( $type->total_size ); ?></div>
                    <div class="size-bar">
                        <div class="size-bar-fill" style="width: <?php echo $type_percentage; ?>%;"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Largest Files -->
    <?php if ( ! empty( $largest_files ) ) : ?>
    <div class="storage-section">
        <h2 class="section-title">Largest Files</h2>

        <div class="files-table">
            <div class="file-header">
                <div class="file-col">File Name</div>
                <div class="file-col">Type</div>
                <div class="file-col">Size</div>
                <div class="file-col">Uploaded</div>
            </div>

            <?php foreach ( $largest_files as $file ) :
                $mime_type = trim( str_replace( '"', '', $file->mime_type ?? 'unknown' ) );
            ?>
            <div class="file-row">
                <div class="file-col file-name">
                    <span class="file-icon"><?php echo get_file_type_icon( $mime_type ); ?></span>
                    <?php echo esc_html( $file->title ); ?>
                </div>
                <div class="file-col">
                    <?php echo esc_html( $mime_type ); ?>
                </div>
                <div class="file-col">
                    <?php echo format_bytes( $file->file_size ); ?>
                </div>
                <div class="file-col">
                    <?php echo date( 'M j, Y', strtotime( $file->created_at ) ); ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Upgrade Storage -->
    <?php if ( ! $is_unlimited && $used_percentage > 50 ) : ?>
    <div class="storage-section upgrade-section">
        <h2 class="section-title">Need More Storage?</h2>
        <p>Upgrade your plan to get more storage space and additional features.</p>
        <a href="<?php echo home_url( '/projectfob/adminland/billing' ); ?>" class="action-button primary-button">
            View Plans
        </a>
    </div>
    <?php endif; ?>
</div>

<style>
/* SIMPLE VERTICAL LAYOUT - NO COLUMNS */
.storage-page-wrapper {
    max-width: 900px;
    margin: 40px auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.back-link {
    display: inline-block;
    margin-bottom: 16px;
    color: #0066cc;
    text-decoration: none;
    font-size: 15px;
}

.back-link:hover {
    text-decoration: underline;
}

.page-title {
    font-size: 32px;
    margin: 0 0 8px 0;
    color: #333;
}

.page-subtitle {
    font-size: 16px;
    color: #666;
    margin: 0 0 32px 0;
}

.storage-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 32px;
    margin-bottom: 24px;
}

.section-title {
    font-size: 20px;
    margin: 0 0 20px 0;
    color: #333;
}

.storage-overview {
    display: flex;
    gap: 32px;
    align-items: center;
    flex-wrap: wrap;
}

.storage-chart {
    flex: 0 0 200px;
}

.chart-circle {
    position: relative;
    width: 200px;
    height: 200px;
}

.circular-chart {
    width: 100%;
    height: 100%;
}

.circle-bg {
    fill: none;
    stroke: #f0f0f0;
    stroke-width: 8;
}

.circle-progress {
    fill: none;
    stroke: #0066cc;
    stroke-width: 8;
    stroke-linecap: round;
    transform: rotate(-90deg);
    transform-origin: 50% 50%;
    transition: stroke-dasharray 0.6s ease;
}

.chart-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.chart-value {
    font-size: 28px;
    font-weight: 600;
    color: #333;
}

.chart-label {
    font-size: 14px;
    color: #666;
}

.storage-details {
    flex: 1;
    min-width: 250px;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    padding: 12px 0;
    border-bottom: 1px solid #f5f5f5;
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: #666;
    font-size: 15px;
}

.detail-value {
    color: #333;
    font-size: 15px;
    font-weight: 600;
}

.storage-warning {
    margin-top: 20px;
    padding: 16px;
    background: #fff3cd;
    border: 1px solid #ffc107;
    border-radius: 6px;
    color: #856404;
}

.file-types-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.file-type-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    gap: 20px;
}

.type-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.type-icon {
    font-size: 24px;
}

.type-details {
    flex: 1;
}

.type-name {
    font-size: 15px;
    font-weight: 600;
    color: #333;
}

.type-count {
    font-size: 13px;
    color: #666;
}

.type-size {
    flex: 0 0 150px;
    text-align: right;
}

.size-value {
    font-size: 16px;
    font-weight: 600;
    color: #0066cc;
    margin-bottom: 4px;
}

.size-bar {
    height: 6px;
    background: #f0f0f0;
    border-radius: 3px;
    overflow: hidden;
}

.size-bar-fill {
    height: 100%;
    background: #0066cc;
    border-radius: 3px;
}

.files-table {
    border: 1px solid #e0e0e0;
    border-radius: 6px;
    overflow: hidden;
}

.file-header {
    display: flex;
    background: #f8f9fa;
    padding: 12px 16px;
    font-weight: 600;
    font-size: 13px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.file-row {
    display: flex;
    padding: 16px;
    border-top: 1px solid #f0f0f0;
    align-items: center;
}

.file-col {
    flex: 1;
    font-size: 14px;
    color: #333;
}

.file-col:first-child {
    flex: 2;
}

.file-name {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 500;
}

.file-icon {
    font-size: 20px;
}

.upgrade-section {
    background: #f0f7ff;
    border-color: #0066cc;
    border-left: 4px solid #0066cc;
}

.action-button {
    display: inline-block;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 500;
    text-decoration: none;
    margin-top: 12px;
}

.primary-button {
    background: #0066cc;
    color: white;
}

.primary-button:hover {
    background: #0052a3;
}

@media (max-width: 768px) {
    .storage-overview {
        flex-direction: column;
    }

    .file-header {
        display: none;
    }

    .file-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }

    .file-col {
        flex: none !important;
    }

    .file-type-row {
        flex-direction: column;
        align-items: flex-start;
    }

    .type-size {
        width: 100%;
    }
}
</style>

<?php
PFOB_Template::footer();

// Helper functions
function get_file_type_icon( $mime_type ) {
    $type_map = array(
        'image' => '🖼️',
        'video' => '🎥',
        'audio' => '🎵',
        'pdf' => '📄',
        'document' => '📝',
        'spreadsheet' => '📊',
        'presentation' => '📽️',
        'archive' => '📦',
        'text' => '📄',
    );

    foreach ( $type_map as $key => $icon ) {
        if ( stripos( $mime_type, $key ) !== false ) {
            return $icon;
        }
    }

    return '📎';
}

function format_bytes( $bytes ) {
    if ( $bytes >= 1073741824 ) {
        return number_format( $bytes / 1073741824, 2 ) . ' GB';
    } elseif ( $bytes >= 1048576 ) {
        return number_format( $bytes / 1048576, 2 ) . ' MB';
    } elseif ( $bytes >= 1024 ) {
        return number_format( $bytes / 1024, 2 ) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}
