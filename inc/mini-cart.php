<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '<div class="mini-cart-content">';

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
    return $fragments;
}