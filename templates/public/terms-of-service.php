<?php
/**
 * Terms of Service Page
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

PFOB_Template::header( 'Terms of Service', false );
?>

<div class="pfob-public-page">
    <div class="pfob-public-container">
        <div class="pfob-legal-content">
            <h1>Terms of Service</h1>

            <p class="last-updated">Last Updated: <?php echo date( 'F j, Y' ); ?></p>

            <section>
                <h2>1. Agreement to Terms</h2>
                <p>By accessing or using ProjectFOB ("Service," "Platform," or "we"), you agree to be bound by these Terms of Service ("Terms"). If you do not agree to these Terms, do not use our Service.</p>
                <p>We reserve the right to modify these Terms at any time. Continued use of the Service after changes constitutes acceptance of the modified Terms.</p>
            </section>

            <section>
                <h2>2. Description of Service</h2>
                <p>ProjectFOB is a web-based project management and team collaboration platform. We provide tools for:</p>
                <ul>
                    <li>Project planning and task management</li>
                    <li>Team communication and messaging</li>
                    <li>File storage and sharing</li>
                    <li>Schedule and calendar management</li>
                    <li>Time tracking and analytics</li>
                    <li>Third-party integrations (Google Drive, Dropbox, Google Calendar)</li>
                </ul>
            </section>

            <section>
                <h2>3. Account Registration and Eligibility</h2>
                <h3>3.1 Eligibility</h3>
                <p>You must be at least 18 years old and have the legal capacity to enter into contracts to use our Service. By creating an account, you represent that you meet these requirements.</p>

                <h3>3.2 Account Security</h3>
                <p>You are responsible for:</p>
                <ul>
                    <li>Maintaining the confidentiality of your account credentials</li>
                    <li>All activities that occur under your account</li>
                    <li>Notifying us immediately of any unauthorized access</li>
                    <li>Ensuring your account information is accurate and up-to-date</li>
                </ul>
            </section>

            <section>
                <h2>4. Subscription Plans and Billing</h2>
                <h3>4.1 Subscription Plans</h3>
                <p>We offer multiple subscription tiers: Starter, Professional, Business, Business+, and Enterprise. Each plan includes different features and usage limits as described on our pricing page.</p>

                <h3>4.2 Payment Terms</h3>
                <ul>
                    <li>Subscriptions are billed monthly or yearly in advance</li>
                    <li>All fees are in U.S. Dollars (USD)</li>
                    <li>Payment is processed securely through PayPal</li>
                    <li>You authorize us to charge your payment method for all subscription fees</li>
                    <li>Add-ons (Timesheet, Admin Pro Pack) are billed monthly regardless of base subscription interval</li>
                </ul>

                <h3>4.3 Price Changes</h3>
                <p>We reserve the right to change pricing with 30 days' notice. Price changes will not affect your current billing cycle.</p>

                <h3>4.4 Refunds</h3>
                <p>Subscription fees are non-refundable except as required by law or at our sole discretion. You may cancel your subscription at any time, and cancellation will take effect at the end of your current billing period.</p>
            </section>

            <section>
                <h2>5. Cancellation and Termination</h2>
                <h3>5.1 Your Right to Cancel</h3>
                <p>You may cancel your subscription at any time through your account settings. Upon cancellation:</p>
                <ul>
                    <li>Your subscription remains active until the end of the current billing period</li>
                    <li>You will not be charged for subsequent billing periods</li>
                    <li>Your data will be retained for 30 days after cancellation</li>
                    <li>After 30 days, your account and data may be permanently deleted</li>
                </ul>

                <h3>5.2 Our Right to Terminate</h3>
                <p>We may suspend or terminate your account immediately if:</p>
                <ul>
                    <li>You violate these Terms</li>
                    <li>Your payment fails or account is past due</li>
                    <li>You engage in fraudulent or illegal activities</li>
                    <li>You abuse the Service or harm other users</li>
                    <li>We determine your use poses a security risk</li>
                </ul>
            </section>

            <section>
                <h2>6. Acceptable Use Policy</h2>
                <p>You agree NOT to:</p>
                <ul>
                    <li>Use the Service for any illegal purpose or to violate any laws</li>
                    <li>Upload or transmit viruses, malware, or harmful code</li>
                    <li>Attempt to gain unauthorized access to our systems</li>
                    <li>Interfere with or disrupt the Service or servers</li>
                    <li>Use automated systems (bots, scrapers) without permission</li>
                    <li>Reverse engineer, decompile, or disassemble the Service</li>
                    <li>Resell or redistribute the Service without authorization</li>
                    <li>Upload content that infringes intellectual property rights</li>
                    <li>Harass, abuse, or harm other users</li>
                    <li>Share your account credentials with unauthorized users</li>
                </ul>
            </section>

            <section>
                <h2>7. User Content and Data</h2>
                <h3>7.1 Your Content</h3>
                <p>You retain all rights to content you create, upload, or share through the Service ("Your Content"). By using the Service, you grant us a license to:</p>
                <ul>
                    <li>Store, process, and display Your Content</li>
                    <li>Make backups for data protection</li>
                    <li>Enable collaboration features (sharing with team members)</li>
                    <li>Provide technical support</li>
                </ul>

                <h3>7.2 Content Responsibility</h3>
                <p>You are solely responsible for Your Content. You represent that:</p>
                <ul>
                    <li>You own or have rights to all content you upload</li>
                    <li>Your Content does not violate any laws or third-party rights</li>
                    <li>Your Content does not contain harmful or malicious code</li>
                </ul>

                <h3>7.3 Data Storage and Backups</h3>
                <p>We store your data securely using Cloudflare R2. We perform regular backups but recommend you maintain your own backups of critical data. We are not liable for data loss.</p>
            </section>

            <section>
                <h2>8. Intellectual Property</h2>
                <h3>8.1 Our Property</h3>
                <p>The Service, including all software, designs, text, graphics, and trademarks, is owned by ProjectFOB and protected by copyright, trademark, and other laws. You may not copy, modify, or distribute our intellectual property without permission.</p>

                <h3>8.2 Feedback</h3>
                <p>If you provide feedback, suggestions, or ideas about the Service, we may use them without any obligation to compensate you.</p>
            </section>

            <section>
                <h2>9. Third-Party Integrations</h2>
                <p>Our Service integrates with third-party services (Google Drive, Dropbox, Google Calendar, PayPal). These integrations are subject to the third party's terms and privacy policies. We are not responsible for third-party services or their actions.</p>
                <p>When you authorize an integration:</p>
                <ul>
                    <li>You grant us permission to access data through the integration</li>
                    <li>You understand we may store authentication tokens</li>
                    <li>You can revoke access at any time through your settings</li>
                </ul>
            </section>

            <section>
                <h2>10. Warranties and Disclaimers</h2>
                <p><strong>THE SERVICE IS PROVIDED "AS IS" WITHOUT WARRANTIES OF ANY KIND, EXPRESS OR IMPLIED.</strong></p>
                <p>We disclaim all warranties, including:</p>
                <ul>
                    <li>Merchantability and fitness for a particular purpose</li>
                    <li>Non-infringement of third-party rights</li>
                    <li>Uninterrupted or error-free operation</li>
                    <li>Accuracy, reliability, or completeness of content</li>
                    <li>Security against unauthorized access or data loss</li>
                </ul>
                <p>We do not guarantee that the Service will meet your requirements or be available at all times.</p>
            </section>

            <section>
                <h2>11. Limitation of Liability</h2>
                <p><strong>TO THE MAXIMUM EXTENT PERMITTED BY LAW, PROJECTFOB SHALL NOT BE LIABLE FOR:</strong></p>
                <ul>
                    <li>Indirect, incidental, special, or consequential damages</li>
                    <li>Lost profits, revenue, data, or business opportunities</li>
                    <li>Service interruptions or data loss</li>
                    <li>Third-party actions or content</li>
                    <li>Unauthorized access to your account or data</li>
                </ul>
                <p><strong>OUR TOTAL LIABILITY SHALL NOT EXCEED THE AMOUNT YOU PAID US IN THE 12 MONTHS PRIOR TO THE CLAIM.</strong></p>
            </section>

            <section>
                <h2>12. Indemnification</h2>
                <p>You agree to indemnify and hold harmless ProjectFOB, its officers, employees, and agents from any claims, damages, losses, or expenses (including legal fees) arising from:</p>
                <ul>
                    <li>Your use of the Service</li>
                    <li>Your violation of these Terms</li>
                    <li>Your violation of any laws or third-party rights</li>
                    <li>Your Content or data</li>
                </ul>
            </section>

            <section>
                <h2>13. Data Export and Portability</h2>
                <p>You may export your data at any time through our data export feature. Upon request, we will provide your data in a commonly used format. We are not obligated to retain your data after account termination.</p>
            </section>

            <section>
                <h2>14. Governing Law and Disputes</h2>
                <h3>14.1 Governing Law</h3>
                <p>These Terms are governed by the laws of the United States and the State of [Your State], without regard to conflict of law principles.</p>

                <h3>14.2 Dispute Resolution</h3>
                <p>Any disputes shall be resolved through binding arbitration in accordance with the rules of the American Arbitration Association. You waive your right to participate in class actions or class arbitrations.</p>

                <h3>14.3 Exceptions</h3>
                <p>Either party may seek injunctive relief in court to prevent unauthorized use of intellectual property or confidential information.</p>
            </section>

            <section>
                <h2>15. General Provisions</h2>
                <h3>15.1 Entire Agreement</h3>
                <p>These Terms constitute the entire agreement between you and ProjectFOB regarding the Service.</p>

                <h3>15.2 Severability</h3>
                <p>If any provision of these Terms is found unenforceable, the remaining provisions will remain in effect.</p>

                <h3>15.3 No Waiver</h3>
                <p>Our failure to enforce any right or provision does not constitute a waiver of that right.</p>

                <h3>15.4 Assignment</h3>
                <p>You may not assign these Terms without our written consent. We may assign these Terms at any time.</p>

                <h3>15.5 Force Majeure</h3>
                <p>We are not liable for delays or failures due to circumstances beyond our reasonable control.</p>
            </section>

            <section>
                <h2>16. Contact Information</h2>
                <p>For questions about these Terms, please contact us:</p>
                <p>
                    <strong>ProjectFOB</strong><br>
                    Email: <a href="mailto:legal@projectfob.com">legal@projectfob.com</a><br>
                    Support: <a href="mailto:support@projectfob.com">support@projectfob.com</a><br>
                    Website: <a href="https://projectfob.com">https://projectfob.com</a>
                </p>
            </section>

            <div class="pfob-legal-footer">
                <a href="<?php echo home_url( '/projectfob/privacy-policy' ); ?>">Privacy Policy</a>
                <span>|</span>
                <a href="<?php echo home_url( '/projectfob/pricing' ); ?>">Pricing</a>
                <span>|</span>
                <a href="<?php echo home_url( '/projectfob' ); ?>">Back to Dashboard</a>
            </div>
        </div>
    </div>
</div>

<style>
.pfob-public-page {
    min-height: 100vh;
    background: #f8f9fa;
    padding: 40px 20px;
}

.pfob-public-container {
    max-width: 900px;
    margin: 0 auto;
}

.pfob-legal-content {
    background: white;
    padding: 60px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.pfob-legal-content h1 {
    font-size: 36px;
    color: #1a1a1a;
    margin: 0 0 10px 0;
    font-weight: 700;
}

.last-updated {
    color: #666;
    font-size: 14px;
    margin: 0 0 40px 0;
    font-style: italic;
}

.pfob-legal-content section {
    margin-bottom: 40px;
}

.pfob-legal-content h2 {
    font-size: 24px;
    color: #2d9061;
    margin: 0 0 20px 0;
    font-weight: 600;
}

.pfob-legal-content h3 {
    font-size: 18px;
    color: #333;
    margin: 20px 0 12px 0;
    font-weight: 600;
}

.pfob-legal-content p {
    line-height: 1.8;
    color: #444;
    margin: 0 0 16px 0;
}

.pfob-legal-content ul {
    margin: 12px 0;
    padding-left: 30px;
}

.pfob-legal-content li {
    line-height: 1.8;
    color: #444;
    margin-bottom: 8px;
}

.pfob-legal-content a {
    color: #2d9061;
    text-decoration: none;
}

.pfob-legal-content a:hover {
    text-decoration: underline;
}

.pfob-legal-footer {
    margin-top: 60px;
    padding-top: 30px;
    border-top: 1px solid #e0e0e0;
    text-align: center;
    font-size: 14px;
}

.pfob-legal-footer a {
    color: #2d9061;
    text-decoration: none;
    margin: 0 10px;
}

.pfob-legal-footer span {
    color: #ccc;
}

@media (max-width: 768px) {
    .pfob-legal-content {
        padding: 30px 20px;
    }

    .pfob-legal-content h1 {
        font-size: 28px;
    }

    .pfob-legal-content h2 {
        font-size: 20px;
    }
}
</style>

</body>
</html>
