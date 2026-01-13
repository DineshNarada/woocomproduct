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

/**
 * Echo the product badge HTML (if any). Uses `woocomproduct_get_product_badge_html()` internally.
 */
function woocomproduct_output_product_badge() {
    // Only show on the shop / product archive loops
    if ( ! ( is_shop() || is_post_type_archive( 'product' ) || function_exists( 'is_product_category' ) && is_product_category() || function_exists( 'is_product_tag' ) && is_product_tag() ) ) {
        return;
    }

    $html = woocomproduct_get_product_badge_html();

    // Allow disabling via filter
    $show = apply_filters( 'woocomproduct_show_product_badge', true, $html );
    if ( ! $show || empty( $html ) ) {
        return;
    }

    echo $html;
}

/**
 * Return the product badge HTML for the current product in the loop.
 *
 * @param WC_Product|null $product Product object. If null, the global loop product is used.
 * @return string Badge HTML or empty string
 */
function woocomproduct_get_product_badge_html( $maybe_product = null ) {
    if ( ! function_exists( 'wc_get_product' ) ) {
        return '';
    }

    // Prefer the passed product, otherwise use the global loop product or derive from the current post
    if ( $maybe_product && is_a( $maybe_product, 'WC_Product' ) ) {
        $product = $maybe_product;
    } else {
        global $product;
        $loop_product = ( isset( $product ) && is_a( $product, 'WC_Product' ) ) ? $product : null;
        if ( $loop_product ) {
            $product = $loop_product;
        } else {
            $product = wc_get_product( get_the_ID() );
        }
    }

    if ( ! $product ) {
        return '';
    }

    $badge = get_post_meta( $product->get_id(), '_local_imported', true );

    if ( ! $badge ) {
        return '';
    }

    switch ( $badge ) {
        case 'local':
            $label = __( 'Local', 'woocomproduct' );
            $class = 'woocomproduct-badge woocomproduct-badge--local';
            break;
        case 'imported':
            $label = __( 'Imported', 'woocomproduct' );
            $class = 'woocomproduct-badge woocomproduct-badge--imported';
            break;
        default:
            return '';
    }

    $html = sprintf( '<span class="%s" aria-hidden="true">%s</span>', esc_attr( $class ), esc_html( $label ) );

    /**
     * Filter the final badge HTML.
     *
     * @param string     $html    The badge HTML.
     * @param WC_Product $product The product object.
     */
    return apply_filters( 'woocomproduct_product_badge_html', $html, $product );
}
