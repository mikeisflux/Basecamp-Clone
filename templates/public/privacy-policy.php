<?php
/**
 * Privacy Policy Page
 *
 * @package ProjectFOB
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

PFOB_Template::header( 'Privacy Policy', false );
?>

<div class="pfob-public-page">
    <div class="pfob-public-container">
        <div class="pfob-legal-content">
            <h1>Privacy Policy</h1>

            <p class="last-updated">Last Updated: <?php echo date( 'F j, Y' ); ?></p>

            <section>
                <h2>1. Introduction</h2>
                <p>Welcome to ProjectFOB ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our project management platform.</p>
            </section>

            <section>
                <h2>2. Information We Collect</h2>

                <h3>2.1 Information You Provide</h3>
                <p>We collect information that you voluntarily provide when using our services:</p>
                <ul>
                    <li><strong>Account Information:</strong> Name, email address, company name, and billing information</li>
                    <li><strong>Profile Information:</strong> Profile photos, job titles, and other optional profile details</li>
                    <li><strong>Project Data:</strong> Messages, tasks, files, comments, and other content you create or upload</li>
                    <li><strong>Payment Information:</strong> Credit card details and billing addresses (processed securely through PayPal)</li>
                </ul>

                <h3>2.2 Automatically Collected Information</h3>
                <p>We automatically collect certain information when you use our platform:</p>
                <ul>
                    <li><strong>Usage Data:</strong> Pages visited, features used, time spent, and interaction patterns</li>
                    <li><strong>Device Information:</strong> IP address, browser type, operating system, and device identifiers</li>
                    <li><strong>Log Data:</strong> Access times, error logs, and system activity</li>
                    <li><strong>Cookies:</strong> Session cookies and authentication tokens</li>
                </ul>
            </section>

            <section>
                <h2>3. How We Use Your Information</h2>
                <p>We use collected information for the following purposes:</p>
                <ul>
                    <li>Provide, maintain, and improve our services</li>
                    <li>Process transactions and send billing notifications</li>
                    <li>Send administrative information and service updates</li>
                    <li>Respond to your comments, questions, and support requests</li>
                    <li>Monitor and analyze usage patterns to improve user experience</li>
                    <li>Detect, prevent, and address technical issues and security threats</li>
                    <li>Comply with legal obligations and enforce our terms</li>
                </ul>
            </section>

            <section>
                <h2>4. Information Sharing and Disclosure</h2>
                <p>We do not sell your personal information. We may share information only in the following circumstances:</p>
                <ul>
                    <li><strong>Service Providers:</strong> Third-party vendors who perform services on our behalf (payment processing, hosting, analytics)</li>
                    <li><strong>Team Members:</strong> Other users within your organization who have been granted access</li>
                    <li><strong>Legal Requirements:</strong> When required by law or to protect our rights and safety</li>
                    <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
                    <li><strong>With Your Consent:</strong> When you explicitly authorize us to share your information</li>
                </ul>
            </section>

            <section>
                <h2>5. Third-Party Services</h2>
                <p>Our platform may integrate with third-party services:</p>
                <ul>
                    <li><strong>PayPal:</strong> For payment processing (subject to PayPal's privacy policy)</li>
                    <li><strong>Cloudflare R2:</strong> For file storage and content delivery</li>
                    <li><strong>Google Drive:</strong> For cloud storage integration (optional, with your authorization)</li>
                    <li><strong>Dropbox:</strong> For cloud storage integration (optional, with your authorization)</li>
                    <li><strong>Google Calendar:</strong> For calendar synchronization (optional, with your authorization)</li>
                </ul>
                <p>These services have their own privacy policies. We recommend reviewing them before connecting these integrations.</p>
            </section>

            <section>
                <h2>6. Data Security</h2>
                <p>We implement industry-standard security measures to protect your information:</p>
                <ul>
                    <li>SSL/TLS encryption for data transmission</li>
                    <li>Encrypted storage for sensitive data</li>
                    <li>Regular security audits and monitoring</li>
                    <li>Access controls and authentication mechanisms</li>
                    <li>Secure API endpoints with authentication</li>
                </ul>
                <p>However, no method of transmission over the Internet is 100% secure. While we strive to protect your information, we cannot guarantee absolute security.</p>
            </section>

            <section>
                <h2>7. Data Retention</h2>
                <p>We retain your information for as long as your account is active or as needed to provide services. You may request deletion of your account and associated data at any time. Some information may be retained for legal compliance or legitimate business purposes after account deletion.</p>
            </section>

            <section>
                <h2>8. Your Privacy Rights</h2>
                <p>Depending on your location, you may have the following rights:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal information</li>
                    <li><strong>Correction:</strong> Update or correct inaccurate information</li>
                    <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                    <li><strong>Export:</strong> Request a copy of your data in a portable format</li>
                    <li><strong>Opt-Out:</strong> Unsubscribe from marketing communications</li>
                    <li><strong>Object:</strong> Object to processing of your information</li>
                </ul>
                <p>To exercise these rights, please contact us at the information provided below.</p>
            </section>

            <section>
                <h2>9. Children's Privacy</h2>
                <p>Our services are not intended for individuals under the age of 18. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p>
            </section>

            <section>
                <h2>10. International Data Transfers</h2>
                <p>Your information may be transferred to and processed in countries other than your country of residence. These countries may have different data protection laws. By using our services, you consent to the transfer of your information to our facilities and service providers.</p>
            </section>

            <section>
                <h2>11. Changes to This Privacy Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of any material changes by posting the new policy on this page and updating the "Last Updated" date. Your continued use of our services after changes constitute acceptance of the updated policy.</p>
            </section>

            <section>
                <h2>12. Contact Us</h2>
                <p>If you have questions or concerns about this Privacy Policy or our data practices, please contact us:</p>
                <p>
                    <strong>ProjectFOB</strong><br>
                    Email: <a href="mailto:privacy@projectfob.com">privacy@projectfob.com</a><br>
                    Website: <a href="https://projectfob.com">https://projectfob.com</a>
                </p>
            </section>

            <div class="pfob-legal-footer">
                <a href="<?php echo home_url( '/projectfob/terms-of-service' ); ?>">Terms of Service</a>
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
