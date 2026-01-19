/* checkout-fields.js
   Handles conditional visibility of VAT field based on country and business type
*/
(function($){
    function toggleVat(){
        var business = $('#billing_business_type').val();
        var country  = $('#billing_country').val();

        // WooCommerce wraps fields with an element id like #billing_vat_number_field
        var vatField = $('#billing_vat_number_field');

        if (business === 'company' && country && country.length > 0) {
            vatField.show();
        } else {
            vatField.hide();
            $('#billing_vat_number').val('');
        }
    }

    $(function(){
        // initialize on load (checkout may be updated via fragments/ajax)
        toggleVat();

        // bind to changes — billing country and business type
        $(document.body).on('change', 'select#billing_business_type, select#billing_country', toggleVat);

        // Also re-run after checkout updated (e.g., when shipping changes update fragments)
        $(document.body).on('updated_checkout', toggleVat);
    });
})(jQuery);
