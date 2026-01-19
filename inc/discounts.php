<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Automatic discount: 20% off when cart subtotal exceeds threshold (default: 20,000).
 *
 * - Applied as a negative fee using `woocommerce_cart_calculate_fees` so it is visible in cart & checkout.
 * - Filters:
 *   - `woocomproduct_discount_threshold` (default 20000)
 *   - `woocomproduct_discount_percentage` (default 20)
 *   - `woocomproduct_discount_taxable` (default true)
 *   - `woocomproduct_discount_tax_class` (default '')
 */
// Run earlier so shipping methods and tax calculators receive the discounted totals
add_action( 'woocommerce_cart_calculate_fees', 'woocomproduct_apply_automatic_discount', 10, 1 );
function woocomproduct_apply_automatic_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    if ( ! is_object( $cart ) || $cart->is_empty() ) {
        return;
    }

    // Read values from the Theme Customizer (so admins can change them), then allow filters
    $threshold = floatval( get_theme_mod( 'woocomproduct_discount_threshold', 20000 ) );
    $threshold = floatval( apply_filters( 'woocomproduct_discount_threshold', $threshold ) );

    $percentage = floatval( get_theme_mod( 'woocomproduct_discount_percentage', 20 ) );
    $percentage = floatval( apply_filters( 'woocomproduct_discount_percentage', $percentage ) );

    // Subtotal before fees and coupons, excluding taxes.
    $subtotal = floatval( $cart->get_subtotal() );

    if ( $subtotal > $threshold ) {
        $discount = round( ( $subtotal * $percentage ) / 100, wc_get_price_decimals() );

        $label = sprintf( __( 'Discount (%s%%)', 'woocomproduct' ), $percentage );

        $taxable = (bool) apply_filters( 'woocomproduct_discount_taxable', true );
        $tax_class = apply_filters( 'woocomproduct_discount_tax_class', '' );

        // Avoid duplicate fee lines if totals are recalculated multiple times during the request.
        foreach ( $cart->get_fees() as $fee ) {
            if ( $fee->name === $label ) {
                return;
            }
        }

        // Negative fee to show as a discount line. $taxable=true ensures taxes are recalculated against the discounted total.
        $cart->add_fee( $label, -$discount, $taxable, $tax_class );
    }
}
