<?php
/**
 * Adminland - Minimal Diagnostic Version
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

error_log( '[Adminland-Minimal] START - Page load initiated' );

try {
    $user_id = get_current_user_id();
    error_log( '[Adminland-Minimal] User ID: ' . $user_id );

    $user = wp_get_current_user();
    error_log( '[Adminland-Minimal] User name: ' . $user->display_name );

    $subscription = PFOB_Subscription::get_by_user_id( $user_id );
    error_log( '[Adminland-Minimal] Subscription: ' . ( $subscription ? 'Found' : 'Not found' ) );

    if ( $subscription ) {
        error_log( '[Adminland-Minimal] Plan ID: ' . $subscription->plan_id );
        error_log( '[Adminland-Minimal] Status: ' . $subscription->status );
    }

    echo '<h1>Adminland Diagnostic</h1>';
    echo '<p>User ID: ' . $user_id . '</p>';
    echo '<p>User: ' . esc_html( $user->display_name ) . '</p>';
    echo '<p>Subscription: ' . ( $subscription ? 'Active' : 'None' ) . '</p>';

    if ( $subscription ) {
        echo '<p>Plan: ' . esc_html( $subscription->plan_id ) . '</p>';
        echo '<p>Status: ' . esc_html( $subscription->status ) . '</p>';
    }

    error_log( '[Adminland-Minimal] SUCCESS - Page rendered' );

} catch ( Exception $e ) {
    error_log( '[Adminland-Minimal] ERROR: ' . $e->getMessage() );
    error_log( '[Adminland-Minimal] Stack trace: ' . $e->getTraceAsString() );
    echo '<h1>Error</h1>';
    echo '<p>' . esc_html( $e->getMessage() ) . '</p>';
}
