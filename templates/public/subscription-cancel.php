<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Subscription Canceled - ProjectFOB</title>
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

        .cancel-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 600px;
            width: 100%;
            padding: 60px 40px;
            text-align: center;
        }

        .cancel-icon {
            width: 80px;
            height: 80px;
            background: #ff4d4f;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 40px;
            color: white;
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
            background: #fff7e6;
            border: 1px solid #ffd591;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
            text-align: left;
        }

        .info-box p {
            margin: 0;
            color: #ad6800;
            line-height: 1.6;
        }

        .cta-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }

        .cta-button {
            display: inline-block;
            padding: 16px 32px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .cta-button:hover {
            background: #5568d3;
        }

        .cta-button.secondary {
            background: white;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .cta-button.secondary:hover {
            background: #f7f9fc;
        }
    </style>
</head>
<body>
    <div class="cancel-container">
        <div class="cancel-icon">✕</div>
        <h1>Subscription Canceled</h1>
        <p class="subtitle">
            You've canceled the subscription process.<br>
            No charges have been made to your account.
        </p>

        <div class="info-box">
            <p>
                <strong>Note:</strong> Your account has been created but is not active yet.
                You'll need to complete the subscription to access ProjectFOB.
            </p>
        </div>

        <div class="cta-buttons">
            <a href="<?php echo site_url( '/projectfob/pricing' ); ?>" class="cta-button">
                Try Again
            </a>
            <a href="<?php echo home_url(); ?>" class="cta-button secondary">
                Go to Homepage
            </a>
        </div>
    </div>
</body>
</html>
