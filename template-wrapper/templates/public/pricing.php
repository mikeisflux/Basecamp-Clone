<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pricing - ProjectFOB</title>
    <link rel="stylesheet" href="<?php echo PFOB_PLUGIN_URL; ?>assets/css/frontend.css?ver=<?php echo PFOB_VERSION; ?>">
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }

        .pricing-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .pricing-header {
            text-align: center;
            color: white;
            margin-bottom: 60px;
        }

        .pricing-header h1 {
            font-size: 48px;
            font-weight: 700;
            margin: 0 0 20px 0;
        }

        .pricing-header p {
            font-size: 20px;
            opacity: 0.9;
            margin: 0;
        }

        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 40px;
            max-width: 1400px;
            margin-left: auto;
            margin-right: auto;
        }

        .pricing-card {
            background: white;
            border-radius: 12px;
            padding: 30px 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            position: relative;
            transition: transform 0.3s ease;
        }

        .pricing-card:hover {
            transform: translateY(-10px);
        }

        .pricing-card.popular {
            border: 3px solid #667eea;
            transform: scale(1.05);
        }

        .pricing-card.popular::before {
            content: 'Most Popular';
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: #667eea;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }

        .plan-name {
            font-size: 24px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 10px 0;
        }

        .plan-description {
            font-size: 14px;
            color: #666;
            margin: 0 0 20px 0;
        }

        .plan-price {
            font-size: 48px;
            font-weight: 700;
            color: #667eea;
            margin: 20px 0;
        }

        .plan-price span {
            font-size: 20px;
            color: #666;
        }

        .plan-features {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }

        .plan-features li {
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            font-size: 15px;
            color: #333;
        }

        .plan-features li::before {
            content: '✓';
            color: #667eea;
            font-weight: bold;
            margin-right: 12px;
            font-size: 18px;
        }

        .plan-cta {
            display: block;
            width: 100%;
            padding: 16px;
            background: #667eea;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            border: none;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .plan-cta:hover {
            background: #5568d3;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin: 60px 0;
            max-width: 1000px;
            margin-left: auto;
            margin-right: auto;
        }

        .feature-box {
            background: rgba(255,255,255,0.1);
            padding: 30px;
            border-radius: 12px;
            color: white;
        }

        .feature-box h3 {
            font-size: 20px;
            margin: 0 0 10px 0;
        }

        .feature-box p {
            margin: 0;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Responsive adjustments */
        @media (max-width: 1400px) {
            .pricing-grid {
                gap: 16px;
            }
        }

        @media (max-width: 1200px) {
            .pricing-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 768px) {
            .pricing-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="pricing-container">
        <div class="pricing-header">
            <h1>Choose Your Plan</h1>
            <p>Start your journey with ProjectFOB today</p>
        </div>

        <div class="pricing-grid" id="pricing-grid">
            <!-- Plans will be loaded dynamically -->
        </div>

        <div class="features-grid">
            <div class="feature-box">
                <h3>🚀 Real-time Collaboration</h3>
                <p>Work together seamlessly with WebSocket-powered real-time updates for chat, activity feeds, and notifications.</p>
            </div>
            <div class="feature-box">
                <h3>☁️ Cloud Storage</h3>
                <p>All your files securely stored in Cloudflare R2 with lightning-fast access from anywhere in the world.</p>
            </div>
            <div class="feature-box">
                <h3>📊 Analytics & Insights</h3>
                <p>Track team performance, project progress, and usage metrics with beautiful charts and reports.</p>
            </div>
            <div class="feature-box">
                <h3>🔗 Integrations</h3>
                <p>Connect with Google Calendar, import/export data, and access powerful APIs for custom workflows.</p>
            </div>
        </div>
    </div>

    <script>
        // Load pricing plans
        async function loadPricing() {
            try {
                const response = await fetch('/wp-json/projectfob/v1/subscription/plans');
                const result = await response.json();

                if (result.success) {
                    renderPricingCards(result.data);
                }
            } catch (error) {
                console.error('Failed to load pricing:', error);
            }
        }

        function renderPricingCards(plans) {
            const grid = document.getElementById('pricing-grid');

            const planOrder = ['starter', 'professional', 'business', 'enterprise'];

            planOrder.forEach(planKey => {
                const plan = plans[planKey];
                if (!plan) return;

                const card = document.createElement('div');
                card.className = 'pricing-card' + (plan.popular ? ' popular' : '');

                const features = [];
                if (plan.features.projects === 999999) {
                    features.push('Unlimited Projects');
                } else {
                    features.push(`${plan.features.projects} Projects`);
                }

                if (plan.features.users === 999999) {
                    features.push('Unlimited Team Members');
                } else {
                    features.push(`Up to ${plan.features.users} Team Members`);
                }

                if (plan.features.storage_gb === 999999) {
                    features.push('Unlimited Storage');
                } else {
                    features.push(`${plan.features.storage_gb}GB Storage`);
                }

                if (plan.features.websocket) features.push('Real-time Collaboration');
                if (plan.features.analytics) features.push('Analytics Dashboard');
                if (plan.features.email_digests) features.push('Email Digests');
                if (plan.features.google_calendar) features.push('Google Calendar Sync');
                if (plan.features.advanced_analytics) features.push('Advanced Analytics');
                if (plan.features.custom_branding) features.push('Custom Branding');
                if (plan.features.white_label) features.push('White Label');
                if (plan.features.api_access) features.push('API Access');
                if (plan.features.custom_integrations) features.push('Custom Integrations');

                const supportLevel = {
                    'standard': 'Standard Support',
                    'priority': 'Priority Support',
                    'dedicated': 'Dedicated Support'
                };
                features.push(supportLevel[plan.features.support] || 'Support Included');

                card.innerHTML = `
                    <h2 class="plan-name">${plan.name}</h2>
                    <p class="plan-description">${plan.description}</p>
                    <div class="plan-price">
                        $${plan.price}<span>/mo</span>
                    </div>
                    <ul class="plan-features">
                        ${features.map(f => `<li>${f}</li>`).join('')}
                    </ul>
                    <button class="plan-cta" onclick="selectPlan('${plan.id}', '${plan.name}')">
                        Get Started
                    </button>
                `;

                grid.appendChild(card);
            });
        }

        function selectPlan(planId, planName) {
            // Store selected plan in session storage
            sessionStorage.setItem('selected_plan', planId);
            sessionStorage.setItem('selected_plan_name', planName);

            // Redirect to signup page
            window.location.href = '<?php echo site_url( '/projectfob/signup' ); ?>';
        }

        // Load pricing on page load
        document.addEventListener('DOMContentLoaded', loadPricing);
    </script>
</body>
</html>
