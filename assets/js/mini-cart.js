(function($){
    'use strict';

    // Initialize or re-initialize mini cart interactions safely
    function initMiniCart(){
        var $toggle = $('.mini-cart-toggle');
        var $panel = $('#mini-cart-panel');

        if ( !$toggle.length || !$panel.length ) {
            console.log('Mini cart elements not found');
            return;
        }

        console.log('Initializing mini cart interactions');

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

        // Close button handler (using event delegation)
        $(document).off('click.miniCartClose', '.mini-cart-close').on('click.miniCartClose', '.mini-cart-close', function(e){
            // Check if this close button is inside the mini cart panel
            if (!$(this).closest('#mini-cart-panel').length) {
                return;
            }
            e.preventDefault();
            closePanel($toggle, $panel);
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

        // Handle remove-by-click in mini cart via AJAX with error handling (using event delegation)
        $(document).off('click.miniCartRemove', '.remove_from_cart_button').on('click.miniCartRemove', '.remove_from_cart_button', function(e){
            console.log('Remove button clicked - handler triggered');
            // Check if this remove button is inside the mini cart panel
            if (!$(this).closest('#mini-cart-panel').length) {
                return; // Not in mini cart, let WooCommerce handle it normally
            }
            e.preventDefault();
            var $this = $(this);
            var cartItemKey = $this.data('cart_item_key') || $this.attr('data-cart_item_key');
            
            // If no data attribute, try to extract from URL
            if ( ! cartItemKey ) {
                var href = $this.attr('href');
                console.log('Extracting from href:', href);
                if ( href ) {
                    cartItemKey = getParamByName('remove_item', href);
                    console.log('Extracted cart item key from URL:', cartItemKey);
                }
            }
            
            console.log('Final cart item key:', cartItemKey);
            if ( ! cartItemKey ) {
                console.error('No cart item key found');
                showNotice('Could not identify item to remove', true);
                return;
            }

            setPanelBusy($panel, true);
            console.log('AJAX params available:', typeof woocomproduct_ajax !== 'undefined');
            if (typeof woocomproduct_ajax === 'undefined') {
                console.error('woocomproduct_ajax not defined');
                showNotice('JavaScript error. Please refresh the page.', true);
                setPanelBusy($panel, false);
                return;
            }
            console.log('AJAX params:', woocomproduct_ajax);
            $.post(woocomproduct_ajax.ajax_url, {
                action: 'woocomproduct_remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: woocomproduct_ajax.nonce
            }).done(function(resp){
                console.log('AJAX response received:', resp);
                console.log('Response success:', resp && resp.success);
                console.log('Response data:', resp && resp.data);
                if ( resp && resp.success ) {
                    if ( resp.data && resp.data.fragments ) {
                        console.log('Processing fragments:', Object.keys(resp.data.fragments));
                        $.each(resp.data.fragments, function(selector, html){
                            console.log('Replacing selector:', selector, 'with HTML length:', html.length);
                            try { 
                                if (selector === '.mini-cart-content') {
                                    $(selector).html(html);
                                } else {
                                    $(selector).replaceWith(html);
                                }
                                console.log('Successfully replaced:', selector);
                            } catch (e) { 
                                console.error('Error replacing selector:', selector, e);
                                $(selector).html(html); 
                            }
                        });
                        // Trigger both namespaced and un-namespaced refresh events for compatibility
                        $(document.body).trigger('wc_fragments_refreshed');
                        $(document.body).trigger('wc_fragments_refreshed.miniCart');
                        $(document.body).trigger('woocom_mini_cart_updated');
                        showNotice( woocomproduct_ajax.remove_success || 'Item removed from cart' );
                    } else {
                        console.warn('No fragments in response');
                        // No fragments returned; force a full reload to ensure consistency
                        window.location.reload();
                    }
                } else {
                    console.error('Response not successful:', resp);
                    showNotice( (resp && resp.data && resp.data.message) ? resp.data.message : 'Could not remove item', true );
                }
            }).fail(function(xhr, status, error){
                console.error('AJAX failed - Status:', status, 'Error:', error);
                console.error('XHR response:', xhr.responseText);
                console.error('XHR status:', xhr.status);
                showNotice('Network error. Please try again.', true);
            }).always(function(){
                setPanelBusy($panel, false);
            });
        });

        // Debounced quantity change handler (using event delegation)
        $(document).off('input.miniCartQty change.miniCartQtyChange', '.qty input, .quantity input').on('input.miniCartQty change.miniCartQtyChange', '.qty input, .quantity input', function(e){
            // Check if this input is inside the mini cart panel
            if (!$(this).closest('#mini-cart-panel').length) {
                return; // Not in mini cart, let WooCommerce handle it normally
            }
            var $input = $(this);
            var qty = $input.val();
            var $item = $input.closest('.mini_cart_item');
            var cartItemKey = $item.find('.remove').data('cart_item_key') || $item.find('.remove').attr('data-cart_item_key');
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
