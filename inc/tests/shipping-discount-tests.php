<?php
/**
 * WP-CLI test script: Shipping, Discount and Tax combinations
 *
 * Usage (from WP root or specify --path):
 *  wp eval-file wp-content/themes/woocomproduct/inc/tests/shipping-discount-tests.php
 *
 * Notes:
 *  - This script assumes WooCommerce is active and will create temporary data (products and tax rates).
 *  - It prints human-readable outputs for each scenario and exits with non-zero code on failure.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WooCommerce' ) ) {
    echo "WooCommerce not active.\n";
    exit(1);
}

// Helpers
function wc_test_create_product( $price = '10000' ) {
    $post_id = wp_insert_post( array(
        'post_title'  => 'Test Product ' . wp_generate_password( 6, false ),
        'post_type'   => 'product',
        'post_status' => 'publish',
    ) );

    update_post_meta( $post_id, '_price', $price );
    update_post_meta( $post_id, '_regular_price', $price );

    $product = wc_get_product( $post_id );
    return $product;
}

function wc_test_create_tax_rate( $country, $rate = '15.0000', $tax_id = 'TEST' ) {
    global $wpdb;

    $data = array(
        'tax_rate_country' => $country,
        'tax_rate' => $rate,
        'tax_rate_name' => $tax_id,
        'tax_rate_priority' => 1,
        'tax_rate_class' => '',
    );

    $table = $wpdb->prefix . 'woocommerce_tax_rates';
    $wpdb->insert( $table, $data );
    $rate_id = $wpdb->insert_id;

    // Clear cache
    WC_Tax::get_tax_rates();

    return $rate_id;
}

function wc_test_cleanup_tax_rate( $rate_id ) {
    global $wpdb;
    $table = $wpdb->prefix . 'woocommerce_tax_rates';
    $wpdb->delete( $table, array( 'tax_rate_id' => $rate_id ) );
}

function run_scenario( $scenario ) {
    echo "\n--- Scenario: " . $scenario['label'] . " ---\n";

    // Empty cart
    WC()->cart->empty_cart();

    // Create product with price
    $product = wc_test_create_product( $scenario['price'] );

    // Add to cart (quantity 1)
    WC()->cart->add_to_cart( $product->get_id(), 1 );

    // Set customer location
    WC()->customer->set_billing_country( $scenario['country'] );
    WC()->customer->set_shipping_country( $scenario['country'] );

    // Recalc totals (this triggers our discount and shipping calculations)
    WC()->cart->calculate_totals();

    // Report
    $subtotal = WC()->cart->get_subtotal();
    $fees = WC()->cart->get_fees();
    $shipping_total = WC()->cart->get_shipping_total();
    $tax_total = WC()->cart->get_taxes_total();
    $total = WC()->cart->get_total( 'edit' );

    echo "Subtotal: " . wc_price( $subtotal ) . "\n";
    echo "Shipping: " . wc_price( $shipping_total ) . "\n";
    echo "Tax: " . wc_price( $tax_total ) . "\n";
    echo "Fees: \n";
    foreach ( $fees as $fee ) {
        echo " - " . $fee->name . ": " . wc_price( $fee->amount ) . "\n";
    }
    echo "Total: " . wc_price( $total ) . "\n";

    return array(
        'subtotal' => $subtotal,
        'shipping' => $shipping_total,
        'tax'      => $tax_total,
        'fees'     => $fees,
        'total'    => $total,
    );
}

// Scenarios
$scenarios = array(
    array( 'label' => 'Sri Lanka - Below threshold (no discount)', 'country' => 'LK', 'price' => '10000' ),
    array( 'label' => 'Sri Lanka - Above threshold (discount applied)', 'country' => 'LK', 'price' => '25000' ),
    array( 'label' => 'Asia (IN) - Above threshold', 'country' => 'IN', 'price' => '25000' ),
    array( 'label' => 'Other (US) - Above threshold', 'country' => 'US', 'price' => '25000' ),
);

// Ensure tax rate exists for testing (global 15% for all countries) -- create a fallback rate for blank country which acts as global.
$global_rate_id = wc_test_create_tax_rate( '', '15.0000', 'GLOBAL TEST' );

$results = array();
foreach ( $scenarios as $sc ) {
    $results[] = run_scenario( $sc );
}

// Cleanup test tax rate
wc_test_cleanup_tax_rate( $global_rate_id );

echo "\nAll scenarios executed. Review output above for discount, shipping and tax behavior.\n";

// Final note
exit(0);
