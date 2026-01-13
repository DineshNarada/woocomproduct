(function($){
    'use strict';

    $(document).ready(function(){
        var $toggle = $('.mini-cart-toggle');
        var $panel = $('#mini-cart-panel');

        function openPanel(){
            $toggle.attr('aria-expanded','true');
            $panel.removeAttr('hidden');
            $panel.attr('aria-hidden','false');
            $panel.show();
        }
        function closePanel(){
            $toggle.attr('aria-expanded','false');
            $panel.attr('hidden','true');
            $panel.attr('aria-hidden','true');
            $panel.hide();
        }

        $toggle.on('click', function(e){
            e.preventDefault();
            var expanded = $toggle.attr('aria-expanded') === 'true';
            if ( expanded ) {
                closePanel();
            } else {
                openPanel();
            }
        });

        // Close on outside click
        $(document).on('click', function(e){
            if ( $panel.is(':visible') && !$(e.target).closest('#mini-cart-panel, .mini-cart-toggle').length ) {
                closePanel();
            }
        });

        // Close on ESC
        $(document).on('keydown', function(e){
            if ( e.key === 'Escape' || e.keyCode === 27 ) {
                closePanel();
            }
        });

        // Optional: update cart count on 'wc_fragments_refreshed' (WooCommerce)
        $(document.body).on('wc_fragments_refreshed', function(){
            var $newCount = $('.widget_shopping_cart_content .count, .mini-cart-count-ref');
            if ( $newCount.length ) {
                // Try to extract count from fragments, fallback to total items
                var text = $newCount.text().trim();
                if ( text ) {
                    $('.mini-cart-count').text(text);
                }
            }
        });
    });
})(jQuery);
