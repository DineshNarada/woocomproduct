/**
 * Navigation JavaScript - Handles mobile menu and dropdowns
 * Accessible and keyboard-friendly
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Mobile menu toggle
        $('.mobile-menu-toggle').on('click', function() {
            const $menu = $('#primary-menu');
            const $toggle = $(this);
            const isExpanded = $toggle.attr('aria-expanded') === 'true';

            $toggle.attr('aria-expanded', !isExpanded);
            $menu.toggleClass('is-open');

            // Close dropdowns when mobile menu closes
            if (isExpanded) {
                $('.sub-menu').attr('aria-hidden', 'true');
                $('.menu-item-has-children').attr('aria-expanded', 'false');
            }
        });

        // Dropdown menu functionality for desktop
        $('.menu-item-has-children').on('mouseenter focusin', function() {
            if (window.innerWidth > 768) {
                $(this).find('.sub-menu').first().attr('aria-hidden', 'false');
                $(this).attr('aria-expanded', 'true');
            }
        }).on('mouseleave focusout', function() {
            if (window.innerWidth > 768) {
                // Delay hiding to allow moving to submenu
                setTimeout(() => {
                    if (!$(this).is(':hover') && !$(this).find('.sub-menu').is(':hover') && !$(this).find('a').is(':focus')) {
                        $(this).find('.sub-menu').attr('aria-hidden', 'true');
                        $(this).attr('aria-expanded', 'false');
                    }
                }, 100);
            }
        });

        // Mobile dropdown toggle
        $('.menu-item-has-children > a').on('click', function(e) {
            if (window.innerWidth <= 768) {
                const $parent = $(this).parent();
                const $submenu = $parent.find('.sub-menu').first();
                const isExpanded = $parent.attr('aria-expanded') === 'true';

                e.preventDefault();

                // Close other open submenus
                $('.menu-item-has-children').not($parent).attr('aria-expanded', 'false').find('.sub-menu').attr('aria-hidden', 'true');

                $parent.attr('aria-expanded', !isExpanded);
                $submenu.attr('aria-hidden', isExpanded ? 'true' : 'false');
            }
        });

        // Close mobile menu when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.site-navigation, .mobile-menu-toggle').length) {
                $('.mobile-menu-toggle').attr('aria-expanded', 'false');
                $('#primary-menu').removeClass('is-open');
                $('.sub-menu').attr('aria-hidden', 'true');
                $('.menu-item-has-children').attr('aria-expanded', 'false');
            }
        });

        // Keyboard navigation
        $(document).on('keydown', function(e) {
            // Escape key closes menus
            if (e.key === 'Escape') {
                $('.mobile-menu-toggle').attr('aria-expanded', 'false');
                $('#primary-menu').removeClass('is-open');
                $('.sub-menu').attr('aria-hidden', 'true');
                $('.menu-item-has-children').attr('aria-expanded', 'false');
            }
        });

        // Handle window resize
        $(window).on('resize', function() {
            if (window.innerWidth > 768) {
                // Reset mobile menu state on desktop
                $('.mobile-menu-toggle').attr('aria-expanded', 'false');
                $('#primary-menu').removeClass('is-open');
                $('.sub-menu').attr('aria-hidden', 'true');
                $('.menu-item-has-children').attr('aria-expanded', 'false');
            }
        });
    });

})(jQuery);