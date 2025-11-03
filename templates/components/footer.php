<footer class="pfob-main-footer">
    <div class="pfob-footer-links">
        <a href="<?php echo home_url( '/projectfob/privacy-policy' ); ?>">Privacy Policy</a>
        <span class="pfob-footer-separator">•</span>
        <a href="<?php echo home_url( '/projectfob/terms-of-service' ); ?>">Terms of Service</a>
        <span class="pfob-footer-separator">•</span>
        <a href="<?php echo home_url( '/projectfob/pricing' ); ?>">Pricing</a>
        <?php if ( PFOB_Subscription::is_active( get_current_user_id() ) ) : ?>
        <span class="pfob-footer-separator">•</span>
        <a href="<?php echo home_url( '/projectfob/adminland' ); ?>">Adminland</a>
        <?php endif; ?>
    </div>
    <div class="pfob-footer-copyright">
        &copy; <?php echo date( 'Y' ); ?> ProjectFOB. All rights reserved.
    </div>
</footer>

<style>
.pfob-main-footer {
    text-align: center;
    padding: 40px 20px;
    margin-top: 60px;
    border-top: 1px solid #e0e0e0;
    background: #f8f9fa;
}

.pfob-footer-links {
    margin-bottom: 15px;
}

.pfob-footer-links a {
    color: #2d9061;
    text-decoration: none;
    font-size: 14px;
    margin: 0 10px;
    transition: color 0.2s;
}

.pfob-footer-links a:hover {
    color: #1f6b48;
    text-decoration: underline;
}

.pfob-footer-separator {
    color: #ccc;
    margin: 0 5px;
}

.pfob-footer-copyright {
    font-size: 13px;
    color: #666;
}

@media (max-width: 768px) {
    .pfob-footer-links {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .pfob-footer-separator {
        display: none;
    }
}
</style>
