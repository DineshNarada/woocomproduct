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
 */
add_action( 'woocommerce_cart_calculate_fees', 'woocomproduct_apply_automatic_discount', 20, 1 );
function woocomproduct_apply_automatic_discount( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    if ( ! is_object( $cart ) || $cart->is_empty() ) {
        return;
    }

    $threshold = floatval( apply_filters( 'woocomproduct_discount_threshold', 20000 ) );
    $percentage = floatval( apply_filters( 'woocomproduct_discount_percentage', 20 ) );

    // Subtotal before fees and coupons, excluding taxes.
    $subtotal = floatval( $cart->get_subtotal() );

    if ( $subtotal > $threshold ) {
        $discount = round( ( $subtotal * $percentage ) / 100, wc_get_price_decimals() );

        $label = sprintf( __( 'Automatic discount (%s%%)', 'woocomproduct' ), $percentage );

        $taxable = (bool) apply_filters( 'woocomproduct_discount_taxable', true );

        // Negative fee to show as a discount line. $taxable=true ensures taxes are calculated against discounted totals.
        $cart->add_fee( $label, -$discount, $taxable );
    }
}
