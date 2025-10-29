<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sign Up - ProjectFOB</title>
    <link rel="stylesheet" href="<?php echo PFOB_PLUGIN_URL; ?>assets/css/style.css">
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

        .signup-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            padding: 50px 40px;
        }

        .signup-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .signup-header h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 0 0 10px 0;
        }

        .signup-header p {
            font-size: 16px;
            color: #666;
            margin: 0;
        }

        .plan-badge {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 8px 20px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .form-group input {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.3s ease;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .name-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover:not(:disabled) {
            background: #5568d3;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .error-message {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .error-message.show {
            display: block;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }

        .login-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .loading-spinner {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .loading-spinner.show {
            display: block;
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
    <div class="signup-container">
        <div class="signup-header">
            <h1>Create Your Account</h1>
            <p>Join ProjectFOB and start collaborating</p>
            <div class="plan-badge" id="plan-badge">Loading...</div>
        </div>

        <div class="error-message" id="error-message"></div>

        <form id="signup-form">
            <div class="name-row">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="8" placeholder="Minimum 8 characters">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
            </div>

            <button type="submit" class="submit-btn" id="submit-btn">
                Create Account & Subscribe
            </button>
        </form>

        <div class="loading-spinner" id="loading-spinner">
            <div class="spinner"></div>
            <p>Creating your account...</p>
        </div>

        <div class="login-link">
            Already have an account? <a href="<?php echo wp_login_url( site_url( '/projectfob/' ) ); ?>">Log in</a>
        </div>
    </div>

    <script>
        // Get selected plan from session storage
        const selectedPlan = sessionStorage.getItem('selected_plan');
        const selectedPlanName = sessionStorage.getItem('selected_plan_name');

        if (!selectedPlan) {
            // Redirect back to pricing if no plan selected
            window.location.href = '<?php echo site_url( '/projectfob/pricing' ); ?>';
        } else {
            // Show selected plan
            document.getElementById('plan-badge').textContent = selectedPlanName + ' Plan';
        }

        // Handle form submission
        document.getElementById('signup-form').addEventListener('submit', async (e) => {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const data = Object.fromEntries(formData);

            // Validate password match
            if (data.password !== data.confirm_password) {
                showError('Passwords do not match');
                return;
            }

            // Add plan ID
            data.plan_id = selectedPlan;

            // Show loading state
            document.getElementById('signup-form').style.display = 'none';
            document.getElementById('loading-spinner').classList.add('show');
            document.getElementById('submit-btn').disabled = true;

            try {
                const response = await fetch('/wp-json/projectfob/v1/subscription/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (response.ok && result.success) {
                    // Redirect to PayPal for payment
                    window.location.href = result.data.approval_url;
                } else {
                    // Show error
                    const errorMessage = result.message || 'An error occurred. Please try again.';
                    showError(errorMessage);

                    // Hide loading state
                    document.getElementById('signup-form').style.display = 'block';
                    document.getElementById('loading-spinner').classList.remove('show');
                    document.getElementById('submit-btn').disabled = false;
                }
            } catch (error) {
                console.error('Signup error:', error);
                showError('An unexpected error occurred. Please try again.');

                // Hide loading state
                document.getElementById('signup-form').style.display = 'block';
                document.getElementById('loading-spinner').classList.remove('show');
                document.getElementById('submit-btn').disabled = false;
            }
        });

        function showError(message) {
            const errorDiv = document.getElementById('error-message');
            errorDiv.textContent = message;
            errorDiv.classList.add('show');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                errorDiv.classList.remove('show');
            }, 5000);
        }
    </script>
</body>
</html>
