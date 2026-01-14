<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '<div class="mini-cart-content">';
echo '<div class="mini-cart-notice" role="status" aria-live="polite" hidden></div>'; 

if ( function_exists( 'woocommerce_mini_cart' ) ) {
    echo '<div class="widget_shopping_cart_content">';
    woocommerce_mini_cart();
    echo '</div>'; // .widget_shopping_cart_content
} else {
    // Fallback sample markup
    ?>
    <div class="mini-cart-sample">
        <ul class="mini-cart-items">
            <li class="mini-cart-item">Sample Product &times; 1 <span class="price">LKR 1,200</span></li>
        </ul>
        <div class="mini-cart-actions">
            <?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
                <a class="button" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View Cart', 'woocomproduct' ); ?></a>
                <a class="button alt" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'woocomproduct' ); ?></a>
            <?php else : ?>
                <a class="button" href="#"><?php esc_html_e( 'View Cart', 'woocomproduct' ); ?></a>
            <?php endif; ?>
        </div>
    </div>
    <?php
}

echo '</div>'; // .mini-cart-content

/**
 * Ensure mini cart HTML is returned as a fragment so WooCommerce AJAX updates refresh it.
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'woocomproduct_mini_cart_fragments' );
function woocomproduct_mini_cart_fragments( $fragments ) {
    ob_start();
    echo '<div class="mini-cart-content">';
    echo '<div class="mini-cart-notice" role="status" aria-live="polite" hidden></div>'; 
    if ( function_exists( 'woocommerce_mini_cart' ) ) {
        echo '<div class="widget_shopping_cart_content">';
        woocommerce_mini_cart();
        echo '</div>';
    } else {
        // Simple fallback markup for fragments
        echo '<div class="mini-cart-sample"><ul class="mini-cart-items"><li class="mini-cart-item">Sample Product &times; 1 <span class="price">LKR 1,200</span></li></ul><div class="mini-cart-actions"><a class="button" href="#">' . esc_html__( 'View Cart', 'woocomproduct' ) . '</a></div></div>';
    }
    echo '</div>';
    $fragments['div.mini-cart-content'] = ob_get_clean();
    // Also provide a small fragment for the header count so it updates reliably
    $fragments['.mini-cart-count'] = '<span class="mini-cart-count" aria-live="polite">' . esc_html( WC()->cart->get_cart_contents_count() ) . '</span>';
    return $fragments;
}

/**
 * AJAX endpoint: remove a cart item and return refreshed fragments
 */
add_action( 'wp_ajax_woocomproduct_remove_cart_item', 'woocomproduct_ajax_remove_cart_item' );
add_action( 'wp_ajax_nopriv_woocomproduct_remove_cart_item', 'woocomproduct_ajax_remove_cart_item' );
function woocomproduct_ajax_remove_cart_item() {
    check_ajax_referer( 'woocomproduct-mini-cart', 'nonce' );

    if ( empty( $_POST['cart_item_key'] ) ) {
        wp_send_json_error( array( 'message' => __( 'Missing cart item', 'woocomproduct' ) ) );
    }

    $cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) );
    $removed = WC()->cart->remove_cart_item( $cart_item_key );

    if ( ! $removed ) {
        wp_send_json_error( array( 'message' => __( 'Could not remove item', 'woocomproduct' ) ) );
    }

    // Ensure totals are recalculated
    WC()->cart->calculate_totals();

    // Return refreshed fragments (uses the theme's fragment filter)
    $fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );

    wp_send_json_success( array( 'fragments' => $fragments ) );
}

/**
 * AJAX endpoint: update cart item quantity and return refreshed fragments
 */
add_action( 'wp_ajax_woocomproduct_update_cart_item', 'woocomproduct_ajax_update_cart_item' );
add_action( 'wp_ajax_nopriv_woocomproduct_update_cart_item', 'woocomproduct_ajax_update_cart_item' );
function woocomproduct_ajax_update_cart_item() {
    check_ajax_referer( 'woocomproduct-mini-cart', 'nonce' );

    if ( empty( $_POST['cart_item_key'] ) || ! isset( $_POST['quantity'] ) ) {
        wp_send_json_error( array( 'message' => __( 'Missing parameters', 'woocomproduct' ) ) );
    }

    $cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) );
    $quantity = wc_stock_amount( wp_unslash( $_POST['quantity'] ) );

    $updated = WC()->cart->set_quantity( $cart_item_key, $quantity, true );

    if ( ! $updated ) {
        wp_send_json_error( array( 'message' => __( 'Could not update quantity', 'woocomproduct' ) ) );
    }

    // Ensure totals are recalculated
    WC()->cart->calculate_totals();

    // Return refreshed fragments
    $fragments = apply_filters( 'woocommerce_add_to_cart_fragments', array() );

    wp_send_json_success( array( 'fragments' => $fragments ) );
}