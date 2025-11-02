<?php
/**
 * Admin Billing & Revenue Page
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Get billing statistics
$start_of_month = date( 'Y-m-01' );
$end_of_month = date( 'Y-m-t' );

$stats = PFOB_Billing_History::get_statistics( $start_of_month, $end_of_month );
$total_revenue = PFOB_Billing_History::get_total_revenue();
$monthly_revenue = PFOB_Billing_History::get_total_revenue( $start_of_month, $end_of_month );
$revenue_by_month = PFOB_Billing_History::get_revenue_by_period( date( 'Y-01-01' ), date( 'Y-12-31' ), 'month' );

// Get recent transactions
$recent_transactions = PFOB_Billing_History::get_recent( 50 );
?>

<div class="wrap pfob-admin-billing">
    <h1>Billing & Revenue</h1>

    <!-- Revenue Stats -->
    <div class="pfob-stats-grid">
        <div class="pfob-stat-card">
            <div class="stat-icon" style="background: #28a745;">
                <span class="dashicons dashicons-chart-line"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">$<?php echo number_format( $total_revenue, 2 ); ?></div>
            </div>
        </div>

        <div class="pfob-stat-card">
            <div class="stat-icon" style="background: #2271b1;">
                <span class="dashicons dashicons-calendar-alt"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">This Month</div>
                <div class="stat-value">$<?php echo number_format( $monthly_revenue, 2 ); ?></div>
            </div>
        </div>

        <div class="pfob-stat-card">
            <div class="stat-icon" style="background: #ffc107;">
                <span class="dashicons dashicons-money-alt"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Total Transactions</div>
                <div class="stat-value"><?php echo number_format( $stats['total_transactions'] ?? 0 ); ?></div>
            </div>
        </div>

        <div class="pfob-stat-card">
            <div class="stat-icon" style="background: #17a2b8;">
                <span class="dashicons dashicons-chart-bar"></span>
            </div>
            <div class="stat-content">
                <div class="stat-label">Average Transaction</div>
                <div class="stat-value">$<?php echo number_format( $stats['average_transaction'] ?? 0, 2 ); ?></div>
            </div>
        </div>
    </div>

    <!-- Revenue by Month Chart -->
    <?php if ( ! empty( $revenue_by_month ) ) : ?>
        <div class="pfob-admin-section">
            <h2>Revenue by Month (<?php echo date( 'Y' ); ?>)</h2>
            <div class="pfob-revenue-chart">
                <?php
                $max_revenue = 0;
                foreach ( $revenue_by_month as $month_data ) {
                    $max_revenue = max( $max_revenue, $month_data->revenue );
                }
                ?>
                <div class="chart-bars">
                    <?php foreach ( $revenue_by_month as $month_data ) : ?>
                        <?php
                        $height = $max_revenue > 0 ? ( $month_data->revenue / $max_revenue ) * 100 : 0;
                        $month_name = date( 'M', strtotime( $month_data->period . '-01' ) );
                        ?>
                        <div class="chart-bar-container">
                            <div class="chart-bar" style="height: <?php echo $height; ?>%;" title="$<?php echo number_format( $month_data->revenue, 2 ); ?>"></div>
                            <div class="chart-label"><?php echo $month_name; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Recent Transactions -->
    <div class="pfob-admin-section">
        <h2>Recent Transactions</h2>
        <?php if ( ! empty( $recent_transactions ) ) : ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>User</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Method</th>
                        <th>Transaction ID</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $recent_transactions as $transaction ) : ?>
                        <tr>
                            <td><?php echo date( 'M j, Y g:i a', strtotime( $transaction->transaction_date ) ); ?></td>
                            <td>
                                <?php
                                $user = get_userdata( $transaction->user_id );
                                if ( $user ) {
                                    echo '<a href="' . admin_url( 'user-edit.php?user_id=' . $transaction->user_id ) . '">';
                                    echo esc_html( $user->display_name );
                                    echo '</a>';
                                } else {
                                    echo 'Unknown';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="transaction-type">
                                    <?php echo esc_html( ucfirst( str_replace( '_', ' ', $transaction->transaction_type ) ) ); ?>
                                </span>
                            </td>
                            <td>
                                <strong style="color: <?php echo $transaction->amount < 0 ? '#dc3545' : '#28a745'; ?>;">
                                    <?php echo $transaction->amount < 0 ? '-' : ''; ?>$<?php echo number_format( abs( $transaction->amount ), 2 ); ?>
                                </strong>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo esc_attr( $transaction->status ); ?>">
                                    <?php echo esc_html( ucfirst( $transaction->status ) ); ?>
                                </span>
                            </td>
                            <td><?php echo esc_html( ucfirst( $transaction->payment_method ?? 'N/A' ) ); ?></td>
                            <td>
                                <?php if ( $transaction->transaction_id ) : ?>
                                    <code style="font-size: 11px;"><?php echo esc_html( $transaction->transaction_id ); ?></code>
                                <?php else : ?>
                                    <span style="color: #999;">N/A</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="notice notice-info">
                <p>No transactions yet. Revenue will appear here once users subscribe.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.pfob-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.pfob-stat-card {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    background: #2271b1;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.stat-icon .dashicons {
    color: white;
    font-size: 30px;
    width: 30px;
    height: 30px;
}

.stat-content {
    flex: 1;
}

.stat-label {
    font-size: 13px;
    color: #646970;
    margin-bottom: 5px;
}

.stat-value {
    font-size: 32px;
    font-weight: 700;
    color: #1d2327;
}

.pfob-admin-section {
    background: white;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.pfob-admin-section h2 {
    margin-top: 0;
}

.pfob-revenue-chart {
    padding: 20px;
}

.chart-bars {
    display: flex;
    align-items: flex-end;
    justify-content: space-around;
    height: 200px;
    border-bottom: 2px solid #ddd;
    gap: 10px;
}

.chart-bar-container {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    height: 100%;
}

.chart-bar {
    width: 100%;
    background: linear-gradient(to top, #2271b1, #5fa3d0);
    border-radius: 4px 4px 0 0;
    transition: all 0.3s ease;
    cursor: pointer;
}

.chart-bar:hover {
    background: linear-gradient(to top, #135e96, #2271b1);
}

.chart-label {
    margin-top: 10px;
    font-size: 12px;
    color: #646970;
    font-weight: 600;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-completed {
    background: #d4edda;
    color: #155724;
}

.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.transaction-type {
    font-size: 13px;
    color: #646970;
}
</style>
