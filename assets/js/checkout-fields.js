(function($){
    'use strict';

    /**
     * Handle VAT field visibility based on Business Type and Country selection
     */
    function toggleVatField() {
        var $businessTypeField = $('[name="billing_business_type"]');
        var $countryField = $('[name="billing_country"]');
        var $vatField = $('#billing_vat_number_field');
        var $vatLabel = $vatField.find('label');

        if ( !$businessTypeField.length || !$vatField.length ) {
            return;
        }

        var businessType = $businessTypeField.val();
        var country = $countryField.val();

        // Show VAT field only when Country is selected
        if ( country ) {
            $vatField.removeClass('hidden').slideDown(200);

            // Make VAT field required when Business Type = Company
            if ( 'company' === businessType ) {
                $vatField.addClass('required');
                $vatLabel.append( '<abbr class="required" title="required">*</abbr>' );
            } else {
                $vatField.removeClass('required');
                $vatLabel.find('abbr.required').remove();
            }
        } else {
            $vatField.addClass('hidden').slideUp(200);
            $vatField.removeClass('required');
            $vatLabel.find('abbr.required').remove();
            $vatField.find('input').val('');
        }
    }

    /**
     * Initialize field listeners on checkout load
     */
    function initCheckoutFieldListeners() {
        var $businessTypeField = $('[name="billing_business_type"]');
        var $countryField = $('[name="billing_country"]');

        if ( !$businessTypeField.length || !$countryField.length ) {
            return;
        }

        // Trigger on load
        toggleVatField();

        // Unbind to avoid duplicate handlers
        $businessTypeField.off('change.woocomProduct').on('change.woocomProduct', function() {
            toggleVatField();
        });

        $countryField.off('change.woocomProduct').on('change.woocomProduct', function() {
            toggleVatField();
        });

        // Also hook into WooCommerce update_checkout event (for address form updates via AJAX)
        $('body').off('updated_checkout.woocomProduct').on('updated_checkout.woocomProduct', function() {
            initCheckoutFieldListeners();
        });
    }

    $(document).ready(function(){
        initCheckoutFieldListeners();
    });

})(jQuery);
