(function($){
    'use strict';

    // Initialize or re-initialize mini cart interactions safely
    function initMiniCart(){
        var $toggle = $('.mini-cart-toggle');
        var $panel = $('#mini-cart-panel');

        if ( !$toggle.length || !$panel.length ) {
            return;
        }

        // Unbind namespaced events before binding to avoid duplicates
        $toggle.off('.miniCart').on('click.miniCart', function(e){
            e.preventDefault();
            var expanded = $toggle.attr('aria-expanded') === 'true';
            if ( expanded ) {
                closePanel($toggle, $panel);
            } else {
                openPanel($toggle, $panel);
            }
        });

        // Close on outside click (namespaced)
        $(document).off('.miniCartOutside').on('click.miniCartOutside', function(e){
            if ( $panel.is(':visible') && !$(e.target).closest('#mini-cart-panel, .mini-cart-toggle').length ) {
                closePanel($toggle, $panel);
            }
        });

        // Close on ESC (namespaced)
        $(document).off('.miniCartKey').on('keydown.miniCartKey', function(e){
            if ( e.key === 'Escape' || e.keyCode === 27 ) {
                closePanel($toggle, $panel);
            }
        });

        // Ensure aria attributes exist
        if ( typeof $toggle.attr('aria-expanded') === 'undefined' ) {
            $toggle.attr('aria-expanded','false');
        }
        if ( typeof $panel.attr('aria-hidden') === 'undefined' ) {
            $panel.attr('aria-hidden','true');
            $panel.attr('hidden','true');
        }

        // Handle WooCommerce fragment refreshes
        $(document.body).off('wc_fragments_refreshed.miniCart').on('wc_fragments_refreshed.miniCart', function(){
            // Update cart count if present inside the refreshed fragments
            var $newCount = $('.widget_shopping_cart_content .count, .mini-cart-count-ref');
            if ( $newCount.length ) {
                var text = $newCount.text().trim();
                if ( text ) {
                    $('.mini-cart-count').text(text);
                }
            }

            // Re-init to rebind events for any replaced markup
            initMiniCart();

            // Allow other scripts to hook into the update
            $(document.body).trigger('woocom_mini_cart_updated');
        });
    }

    function openPanel($toggle,$panel){
        $toggle.attr('aria-expanded','true');
        $panel.removeAttr('hidden');
        $panel.attr('aria-hidden','false');
        $panel.show();
        $panel.focus();
    }
    function closePanel($toggle,$panel){
        $toggle.attr('aria-expanded','false');
        $panel.attr('hidden','true');
        $panel.attr('aria-hidden','true');
        $panel.hide();
        $toggle.focus();
    }

    $(document).ready(function(){
        initMiniCart();
    });

})(jQuery);
