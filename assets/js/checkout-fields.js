(function () {
    'use strict';

    function toggleVat() {
        var select = document.getElementById('billing_business_type');
        var vatWrapper = document.getElementById('billing_vat_number_field');
        var vatInput = document.getElementById('billing_vat_number');
        if (!select || !vatWrapper) return;

        if (select.value === 'company') {
            vatWrapper.style.display = '';
            if (vatInput) vatInput.required = true;
        } else {
            vatWrapper.style.display = 'none';
            if (vatInput) {
                vatInput.required = false;
                // optional: clear value when switching back to Individual
                // vatInput.value = '';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleVat();
        var select = document.getElementById('billing_business_type');
        if (select) select.addEventListener('change', toggleVat);
    });
})();