<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '<div class="mini-cart-content">';
echo '<div class="mini-cart-notice" role="status" aria-live="polite" hidden></div>'; 

// Custom mini cart implementation (no WooCommerce widget usage)
$cart_items = WC()->cart->get_cart();
if ( ! empty( $cart_items ) ) {
    echo '<ul class="woocommerce-mini-cart cart_list product_list_widget">';
    foreach ( $cart_items as $cart_item_key => $cart_item ) {
        $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
        $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

        if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
            $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
            $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
            $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
            ?>
            <li class="woocommerce-mini-cart-item mini_cart_item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
                <?php if ( empty( $product_permalink ) ) : ?>
                    <div class="product-thumbnail">
                        <?php echo $thumbnail; ?>
                    </div>
                    <div class="product-details">
                        <span class="product-name"><?php echo wp_kses_post( $product_name ); ?></span>
                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                        <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity"><input type="number" class="qty" min="0" max="99" value="' . $cart_item['quantity'] . '" data-cart_item_key="' . $cart_item_key . '" /> &times; ' . $product_price . '</span>', $cart_item, $cart_item_key ); ?>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url( $product_permalink ); ?>" class="product-thumbnail">
                        <?php echo $thumbnail; ?>
                    </a>
                    <div class="product-details">
                        <a href="<?php echo esc_url( $product_permalink ); ?>" class="product-name">
                            <?php echo wp_kses_post( $product_name ); ?>
                        </a>
                        <?php echo wc_get_formatted_cart_item_data( $cart_item ); ?>
                        <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity"><input type="number" class="qty" min="0" max="99" value="' . $cart_item['quantity'] . '" data-cart_item_key="' . $cart_item_key . '" /> &times; ' . $product_price . '</span>', $cart_item, $cart_item_key ); ?>
                    </div>
                <?php endif; ?>
                <?php
                $remove_link = sprintf(
                    '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                    esc_attr__( 'Remove this item', 'woocommerce' ),
                    esc_attr( $product_id ),
                    esc_attr( $cart_item_key ),
                    esc_attr( $_product->get_sku() )
                );
                echo $remove_link;
                ?>
            </li>
            <?php
        }
    }
    echo '</ul>';

    echo '<p class="woocommerce-mini-cart__total total">';
    echo '<strong>' . esc_html__( 'Subtotal:', 'woocommerce' ) . '</strong> ' . WC()->cart->get_cart_subtotal();
    echo '</p>';

    echo '<div class="mini-cart-actions">';
    echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="button wc-forward">' . esc_html__( 'View Cart', 'woocommerce' ) . '</a>';
    echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="button checkout wc-forward alt">' . esc_html__( 'Checkout', 'woocommerce' ) . '</a>';
    echo '</div>';
} else {
    echo '<p class="woocommerce-mini-cart__empty-message">' . esc_html__( 'No products in the cart.', 'woocommerce' ) . '</p>';
}

echo '</div>'; // .mini-cart-content

/**
 * Ensure mini cart HTML is returned as a fragment so WooCommerce AJAX updates refresh it.
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'woocomproduct_mini_cart_fragments' );
function woocomproduct_mini_cart_fragments( $fragments ) {
    ob_start();
    echo '<div class="mini-cart-notice" role="status" aria-live="polite" hidden></div>'; 
    // Custom mini cart implementation for fragments
    $cart_items = WC()->cart->get_cart();
    if ( ! empty( $cart_items ) ) {
        echo '<ul class="woocommerce-mini-cart cart_list product_list_widget">';
        foreach ( $cart_items as $cart_item_key => $cart_item ) {
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_name = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                echo '<li class="woocommerce-mini-cart-item mini_cart_item ' . esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ) . '">';
                if ( empty( $product_permalink ) ) {
                    echo '<div class="product-thumbnail">' . $thumbnail . '</div>';
                    echo '<div class="product-details">';
                    echo '<span class="product-name">' . wp_kses_post( $product_name ) . '</span>';
                    echo wc_get_formatted_cart_item_data( $cart_item );
                    echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity"><input type="number" class="qty" min="0" max="99" value="' . $cart_item['quantity'] . '" data-cart_item_key="' . $cart_item_key . '" /> &times; ' . $product_price . '</span>', $cart_item, $cart_item_key );
                    echo '</div>';
                } else {
                    echo '<a href="' . esc_url( $product_permalink ) . '" class="product-thumbnail">' . $thumbnail . '</a>';
                    echo '<div class="product-details">';
                    echo '<a href="' . esc_url( $product_permalink ) . '" class="product-name">' . wp_kses_post( $product_name ) . '</a>';
                    echo wc_get_formatted_cart_item_data( $cart_item );
                    echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="quantity"><input type="number" class="qty" min="0" max="99" value="' . $cart_item['quantity'] . '" data-cart_item_key="' . $cart_item_key . '" /> &times; ' . $product_price . '</span>', $cart_item, $cart_item_key );
                    echo '</div>';
                }
                $remove_link = sprintf(
                    '<a href="%s" class="remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s">&times;</a>',
                    esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                    esc_attr__( 'Remove this item', 'woocommerce' ),
                    esc_attr( $product_id ),
                    esc_attr( $cart_item_key ),
                    esc_attr( $_product->get_sku() )
                );
                echo $remove_link;
                echo '</li>';
            }
        }
        echo '</ul>';

        echo '<p class="woocommerce-mini-cart__total total">';
        echo '<strong>' . esc_html__( 'Subtotal:', 'woocommerce' ) . '</strong> ' . WC()->cart->get_cart_subtotal();
        echo '</p>';

        echo '<div class="mini-cart-actions">';
        echo '<a href="' . esc_url( wc_get_cart_url() ) . '" class="button wc-forward">' . esc_html__( 'View Cart', 'woocommerce' ) . '</a>';
        echo '<a href="' . esc_url( wc_get_checkout_url() ) . '" class="button checkout wc-forward alt">' . esc_html__( 'Checkout', 'woocommerce' ) . '</a>';
        echo '</div>';
    } else {
        echo '<p class="woocommerce-mini-cart__empty-message">' . esc_html__( 'No products in the cart.', 'woocommerce' ) . '</p>';
    }
    $fragments['.mini-cart-content'] = ob_get_clean();
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
    try {
        check_ajax_referer( 'woocomproduct-mini-cart', 'nonce' );

        if ( empty( $_POST['cart_item_key'] ) ) {
            wp_send_json_error( array( 'message' => __( 'Missing cart item', 'woocomproduct' ) ) );
        }

        $cart_item_key = sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) );
        error_log('Attempting to remove cart item: ' . $cart_item_key);
        $removed = WC()->cart->remove_cart_item( $cart_item_key );

        if ( ! $removed ) {
            wp_send_json_error( array( 'message' => __( 'Could not remove item', 'woocomproduct' ) ) );
        }

        // Ensure totals are recalculated
        WC()->cart->calculate_totals();

        // Return refreshed fragments (uses the theme's fragment filter)
        $fragments = woocomproduct_mini_cart_fragments(array());

        wp_send_json_success( array( 'fragments' => $fragments ) );
    } catch (Exception $e) {
        error_log('Exception in remove handler: ' . $e->getMessage());
        wp_send_json_error( array( 'message' => 'Server error occurred' ) );
    }
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