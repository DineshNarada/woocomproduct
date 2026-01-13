<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Display product "Local / Imported" badge on shop & category (archive) pages
 * using WooCommerce hooks (no template overrides).
 */

add_action( 'woocommerce_before_shop_loop_item_title', 'woocomproduct_output_product_badge', 10 );
function woocomproduct_output_product_badge() {
    if ( ! function_exists( 'wc_get_product' ) ) {
        return;
    }

    global $product;

    if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
        $product = wc_get_product( get_the_ID() );
    }

    if ( ! $product ) {
        return;
    }

    $badge = get_post_meta( $product->get_id(), '_local_imported', true );
    if ( ! $badge ) {
        return;
    }

    $label = '';
    $class = 'woocomproduct-badge';

    switch ( $badge ) {
        case 'local':
            $label = __( 'Local', 'woocomproduct' );
            $class .= ' woocomproduct-badge--local';
            break;
        case 'imported':
            $label = __( 'Imported', 'woocomproduct' );
            $class .= ' woocomproduct-badge--imported';
            break;
        default:
            return;
    }

    // Output badge (kept simple & accessible)
    echo sprintf( '<span class="%s" aria-hidden="true">%s</span>', esc_attr( $class ), esc_html( $label ) );
}
