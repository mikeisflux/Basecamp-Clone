<?php
/**
 * ProjectFOB Subscription Plans Configuration
 *
 * @package    ProjectFOB
 * @subpackage ProjectFOB/includes/config
 */

return array(
    'starter' => array(
        'id' => 'starter',
        'name' => 'Starter',
        'monthly_price' => 9.00,
        'yearly_price' => 90.00, // $7.50/month when billed yearly (17% discount)
        'currency' => 'USD',
        'features' => array(
            'projects' => 5,
            'users' => 10,
            'storage_gb' => 10,
            'websocket' => true,
            'analytics' => true,
            'email_digests' => true,
            'support' => 'standard',
        ),
        'description' => 'Perfect for small teams getting started',
        'popular' => false,
    ),

    'professional' => array(
        'id' => 'professional',
        'name' => 'Professional',
        'monthly_price' => 29.00,
        'yearly_price' => 290.00, // $24.17/month when billed yearly (17% discount)
        'currency' => 'USD',
        'features' => array(
            'projects' => 25,
            'users' => 50,
            'storage_gb' => 100,
            'websocket' => true,
            'analytics' => true,
            'email_digests' => true,
            'google_calendar' => true,
            'support' => 'priority',
        ),
        'description' => 'For growing teams that need more power',
        'popular' => true,
    ),

    'business' => array(
        'id' => 'business',
        'name' => 'Business',
        'monthly_price' => 79.00,
        'yearly_price' => 790.00, // $65.83/month when billed yearly (17% discount)
        'currency' => 'USD',
        'features' => array(
            'projects' => 999999, // Unlimited
            'users' => 250,
            'storage_gb' => 500,
            'websocket' => true,
            'analytics' => true,
            'email_digests' => true,
            'google_calendar' => true,
            'advanced_analytics' => true,
            'custom_branding' => true,
            'support' => 'priority',
        ),
        'description' => 'For established businesses with large teams',
        'popular' => false,
    ),

    'enterprise' => array(
        'id' => 'enterprise',
        'name' => 'Enterprise',
        'monthly_price' => 289.00,
        'yearly_price' => 2890.00, // $240.83/month when billed yearly (17% discount)
        'currency' => 'USD',
        'features' => array(
            'projects' => 999999, // Unlimited
            'users' => 999999, // Unlimited
            'storage_gb' => 999999, // Unlimited
            'websocket' => true,
            'analytics' => true,
            'email_digests' => true,
            'google_calendar' => true,
            'advanced_analytics' => true,
            'custom_branding' => true,
            'white_label' => true,
            'api_access' => true,
            'custom_integrations' => true,
            'dedicated_support' => true,
            'support' => 'dedicated',
        ),
        'description' => 'Unlimited everything for large organizations',
        'popular' => false,
    ),
);
