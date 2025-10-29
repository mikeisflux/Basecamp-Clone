/**
 * ProjectFOB Theme JavaScript
 */

(function($) {
    'use strict';

    // Mobile menu toggle
    $('.mobile-menu-toggle').on('click', function() {
        $('.main-navigation').slideToggle();
        $('.header-actions').slideToggle();
        $(this).toggleClass('active');
    });

    // Smooth scroll for anchor links
    $('a[href^="#"]').on('click', function(e) {
        var target = $(this.hash);
        if (target.length) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top - 80
            }, 800);
        }
    });

    // Add animation classes on scroll
    function checkScroll() {
        $('.feature-card, .post-card').each(function() {
            var elementTop = $(this).offset().top;
            var viewportBottom = $(window).scrollTop() + $(window).height();

            if (elementTop < viewportBottom - 100) {
                $(this).addClass('animate-in');
            }
        });
    }

    $(window).on('scroll', checkScroll);
    checkScroll(); // Run on page load

})(jQuery);
