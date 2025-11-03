<?php
/**
 * The footer template
 *
 * @package ProjectFOB_Theme
 */
?>

</main><!-- #main -->

<footer class="site-footer">
    <div class="footer-widgets">
        <div class="container">
            <div class="footer-widgets-grid">
                <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                    <div class="footer-widget-area">
                        <?php dynamic_sidebar( 'footer-1' ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                    <div class="footer-widget-area">
                        <?php dynamic_sidebar( 'footer-2' ); ?>
                    </div>
                <?php endif; ?>

                <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
                    <div class="footer-widget-area">
                        <?php dynamic_sidebar( 'footer-3' ); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="footer-bottom" style="text-align: center; padding: 40px 20px; margin-top: 60px; border-top: 1px solid #e0e0e0; background: #f8f9fa;">
        <div style="margin-bottom: 15px;">
            <a href="/projectfob/privacy-policy" style="color: #2d9061; text-decoration: none; font-size: 14px; margin: 0 10px;">Privacy Policy</a>
            <span style="color: #ccc;">•</span>
            <a href="/projectfob/terms-of-service" style="color: #2d9061; text-decoration: none; font-size: 14px; margin: 0 10px;">Terms of Service</a>
            <span style="color: #ccc;">•</span>
            <a href="/projectfob/pricing" style="color: #2d9061; text-decoration: none; font-size: 14px; margin: 0 10px;">Pricing</a>
        </div>
        <div style="font-size: 13px; color: #666;">
            &copy; 2025 ProjectFOB. All rights reserved.
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
