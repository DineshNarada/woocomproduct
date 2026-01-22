<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Multi-currency display based on user location
 * Show prices in USD for non-Sri Lankan users, LKR for Sri Lankans
 */

/**
 * Get user's country code
 * Uses billing country if logged in, checkout session, or geolocation
 */
function woocomproduct_get_user_country() {
    static $country = null;

    if ( $country !== null ) {
        return $country;
    }

    // Check checkout session first (for dynamic updates during checkout)
    if ( function_exists( 'WC' ) && WC()->session ) {
        $customer = WC()->session->get( 'customer' );
        if ( ! empty( $customer['country'] ) ) {
            $country = $customer['country'];
            return $country;
        }
    }

    // Check if user is logged in and has billing country set
    if ( is_user_logged_in() && WC()->customer ) {
        $billing_country = WC()->customer->get_billing_country();
        if ( ! empty( $billing_country ) ) {
            $country = $billing_country;
            return $country;
        }
    }

    // Use WooCommerce geolocation
    if ( class_exists( 'WC_Geolocation' ) ) {
        $ip = WC_Geolocation::get_ip_address();
        $location = WC_Geolocation::geolocate_ip( $ip );
        $country = $location['country'];
    } else {
        // Fallback to default
        $country = 'LK';
    }

    return $country;
}

/**
 * Exchange rate: 1 USD = 300 LKR (adjust as needed)
 */
define( 'WOOCOMPRODUCT_EXCHANGE_RATE', 300 );

/**
 * Modify product price HTML for display
 */
add_filter( 'woocommerce_get_price_html', 'woocomproduct_modify_price_html', 10, 2 );
function woocomproduct_modify_price_html( $price_html, $product ) {
    $country = woocomproduct_get_user_country();
    if ( $country === 'LK' ) {
        return $price_html;
    }

    $price = $product->get_price();
    if ( $price > 0 ) {
        $usd_price = $price / WOOCOMPRODUCT_EXCHANGE_RATE;
        $price_html = wc_price( $usd_price, array( 'currency' => 'USD' ) );
    }

    return $price_html;
}

/**
 * Modify cart item price display
 */
add_filter( 'woocommerce_cart_item_price', 'woocomproduct_cart_item_price', 10, 3 );
function woocomproduct_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
    $country = woocomproduct_get_user_country();
    if ( $country === 'LK' ) {
        return $price_html;
    }

    $price = $cart_item['data']->get_price();
    if ( $price > 0 ) {
        $usd_price = $price / WOOCOMPRODUCT_EXCHANGE_RATE;
        $price_html = wc_price( $usd_price, array( 'currency' => 'USD' ) );
    }

    return $price_html;
}

/**
 * Modify cart subtotal display
 */
add_filter( 'woocommerce_cart_subtotal', 'woocomproduct_cart_subtotal', 10, 3 );
function woocomproduct_cart_subtotal( $subtotal_html, $compound, $cart ) {
    $country = woocomproduct_get_user_country();
    if ( $country === 'LK' ) {
        return $subtotal_html;
    }

    $subtotal = $cart->get_subtotal();
    $usd_subtotal = $subtotal / WOOCOMPRODUCT_EXCHANGE_RATE;
    return wc_price( $usd_subtotal, array( 'currency' => 'USD' ) );
}

/**
 * Modify cart total display
 */
add_filter( 'woocommerce_cart_total', 'woocomproduct_cart_total' );
function woocomproduct_cart_total( $total_html ) {
    $country = woocomproduct_get_user_country();
    if ( $country === 'LK' ) {
        return $total_html;
    }

    $total = WC()->cart->get_total( 'edit' );
    $usd_total = $total / WOOCOMPRODUCT_EXCHANGE_RATE;
    return wc_price( $usd_total, array( 'currency' => 'USD' ) );
}

/**
 * Modify checkout totals display
 */
add_filter( 'woocommerce_cart_totals_order_total_html', 'woocomproduct_checkout_total' );
function woocomproduct_checkout_total( $total_html ) {
    $country = woocomproduct_get_user_country();
    if ( $country === 'LK' ) {
        return $total_html;
    }

    $total = WC()->cart->get_total( 'edit' );
    $usd_total = $total / WOOCOMPRODUCT_EXCHANGE_RATE;
    return wc_price( $usd_total, array( 'currency' => 'USD' ) );
}