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

        // Helper: read query param from URL
        function getParamByName(name, url) {
            if (!url) return null;
            name = name.replace(/[\[\]]/g, "\\$&");
            var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"), results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, " "));
        }

        // Utility: show notice in the mini cart (auto-hide)
        function showNotice(message, isError) {
            var $notice = $('.mini-cart-notice');
            if ( ! $notice.length ) return;
            $notice.text(message || '');
            if ( isError ) {
                $notice.addClass('is-error');
            } else {
                $notice.removeClass('is-error');
            }
            $notice.removeAttr('hidden');
            clearTimeout($notice.data('hideTimeout'));
            $notice.data('hideTimeout', setTimeout(function(){
                $notice.attr('hidden','true');
            }, 4000));
        }

        // Helper: disable/enable interactive elements in mini cart while request is active
        function setPanelBusy($panel, busy) {
            $panel.attr('aria-busy', busy ? 'true' : 'false');
            $panel.find('.remove, .qty input, .quantity input, .button').prop('disabled', !!busy);
        }

        // Handle remove-by-click in mini cart via AJAX with error handling
        $panel.off('click.miniCartRemove', '.remove').on('click.miniCartRemove', '.remove', function(e){
            e.preventDefault();
            var $this = $(this);
            var cartItemKey = $this.data('cart_item_key') || getParamByName('remove_item', $this.attr('href'));
            if ( ! cartItemKey ) return;

            setPanelBusy($panel, true);
            $.post(woocomproduct_ajax.ajax_url, {
                action: 'woocomproduct_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: woocomproduct_ajax.nonce
            }).done(function(resp){
                if ( resp && resp.success ) {
                    if ( resp.data && resp.data.fragments ) {
                        $.each(resp.data.fragments, function(selector, html){
                            try { $(selector).replaceWith(html); } catch (e) { $(selector).html(html); }
                        });
                        // Trigger both namespaced and un-namespaced refresh events for compatibility
                        $(document.body).trigger('wc_fragments_refreshed');
                        $(document.body).trigger('wc_fragments_refreshed.miniCart');
                        $(document.body).trigger('woocom_mini_cart_updated');
                        showNotice( woocomproduct_ajax.remove_success || 'Cart updated' );
                    } else {
                        // No fragments returned; force a full reload to ensure consistency
                        window.location.reload();
                    }
                } else {
                    showNotice( (resp && resp.data && resp.data.message) ? resp.data.message : 'Could not remove item', true );
                }
            }).fail(function(){
                showNotice('Network error. Please try again.', true);
            }).always(function(){
                setPanelBusy($panel, false);
            });
        });

        // Debounced quantity change handler
        $panel.off('input.miniCartQty input change.miniCartQtyChange', '.qty input, .quantity input').on('input.miniCartQty input change.miniCartQtyChange', '.qty input, .quantity input', function(e){
            var $input = $(this);
            var qty = $input.val();
            var $item = $input.closest('.mini_cart_item');
            var cartItemKey = $item.find('.remove').data('cart_item_key');
            if ( ! cartItemKey ) return;

            clearTimeout($input.data('qtyTimeout'));
            $input.data('qtyTimeout', setTimeout(function(){
                setPanelBusy($panel, true);
                $.post(woocomproduct_ajax.ajax_url, {
                    action: 'woocomproduct_update_cart_item',
                    cart_item_key: cartItemKey,
                    quantity: qty,
                    nonce: woocomproduct_ajax.nonce
                }).done(function(resp){
                    if ( resp && resp.success ) {
                        if ( resp.data && resp.data.fragments ) {
                            $.each(resp.data.fragments, function(selector, html){
                                try { $(selector).replaceWith(html); } catch (e) { $(selector).html(html); }
                            });
                            $(document.body).trigger('wc_fragments_refreshed');
                            $(document.body).trigger('wc_fragments_refreshed.miniCart');
                            $(document.body).trigger('woocom_mini_cart_updated');
                            showNotice( woocomproduct_ajax.update_success || 'Cart updated' );
                        } else {
                            window.location.reload();
                        }
                    } else {
                        showNotice( (resp && resp.data && resp.data.message) ? resp.data.message : 'Could not update quantity', true );
                    }
                }).fail(function(){
                    showNotice('Network error. Please try again.', true);
                }).always(function(){
                    setPanelBusy($panel, false);
                });
            }, 600));
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
