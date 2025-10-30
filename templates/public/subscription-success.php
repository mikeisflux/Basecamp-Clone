<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to ProjectFOB!</title>
    <style>
        body {
            margin: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            background: #52c41a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
        }

        h1 {
            font-size: 36px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 20px 0;
        }

        .subtitle {
            font-size: 18px;
            color: #666;
            margin: 0 0 40px 0;
            line-height: 1.6;
        }

        .info-box {
            background: #f7f9fc;
            border-radius: 12px;
            padding: 30px;
            margin: 30px 0;
            text-align: left;
        }

        .info-box h3 {
            font-size: 18px;
            color: #333;
            margin: 0 0 20px 0;
        }

        .info-box ul {
            margin: 0;
            padding-left: 20px;
        }

        .info-box li {
            margin-bottom: 12px;
            color: #666;
            line-height: 1.6;
        }

        .cta-button {
            display: inline-block;
            padding: 16px 40px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background 0.3s ease;
            margin-top: 20px;
        }

        .cta-button:hover {
            background: #5568d3;
        }

        .loading-spinner {
            text-align: center;
            padding: 20px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="success-container">
        <div class="loading-spinner" id="loading">
            <div class="spinner"></div>
            <p>Finalizing your subscription...</p>
        </div>

        <div id="success-content" style="display: none;">
            <div class="success-icon">✓</div>
            <h1>Welcome to ProjectFOB!</h1>
            <p class="subtitle">
                Your subscription has been activated successfully.<br>
                You're all set to start collaborating with your team.
            </p>

            <div class="info-box">
                <h3>What's Next?</h3>
                <ul>
                    <li>Create your first project</li>
                    <li>Invite team members to collaborate</li>
                    <li>Explore all the powerful features</li>
                    <li>Set up integrations like Google Calendar</li>
                </ul>
            </div>

            <a href="<?php echo site_url( '/projectfob/' ); ?>" class="cta-button">
                Go to Dashboard
            </a>
        </div>
    </div>

    <script>
        async function completeSubscription() {
            // Get subscription ID from URL
            const urlParams = new URLSearchParams(window.location.search);
            const subscriptionId = urlParams.get('subscription_id') || urlParams.get('ba_token');

            if (!subscriptionId) {
                // No subscription ID, just redirect to dashboard
                window.location.href = '<?php echo site_url( '/projectfob/' ); ?>';
                return;
            }

            try {
                const response = await fetch('/wp-json/projectfob/v1/subscription/complete', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    credentials: 'include',
                    body: JSON.stringify({
                        subscription_id: subscriptionId
                    })
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Show success message
                    document.getElementById('loading').style.display = 'none';
                    document.getElementById('success-content').style.display = 'block';
                } else {
                    // Redirect to dashboard anyway (subscription may have already been activated by webhook)
                    window.location.href = '<?php echo site_url( '/projectfob/' ); ?>';
                }
            } catch (error) {
                console.error('Error completing subscription:', error);
                // Redirect to dashboard anyway
                setTimeout(() => {
                    window.location.href = '<?php echo site_url( '/projectfob/' ); ?>';
                }, 2000);
            }
        }

        document.addEventListener('DOMContentLoaded', completeSubscription);
    </script>
</body>
</html>
