<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Theme support for WooCommerce
add_action( 'after_setup_theme', 'woocomproduct_theme_setup' );
function woocomproduct_theme_setup() {
    // Add support for featured images (post thumbnails)
    add_theme_support( 'post-thumbnails' );

    // Support WooCommerce and set sensible default image widths
    add_theme_support( 'woocommerce', array(
        'thumbnail_image_width' => 300,
        'single_image_width'    => 600,
    ) );

    // Additional WooCommerce support features can be added here
}

// Enqueue theme styles and scripts
    add_action( 'wp_enqueue_scripts', 'woocomproduct_enqueue_assets' );
    function woocomproduct_enqueue_assets() {
        // Main theme stylesheet (style.css)
        wp_enqueue_style( 'woocomproduct-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );

        // Additional theme assets
        wp_enqueue_style( 'woocomproduct-main', get_template_directory_uri() . '/assets/css/main.css', array( 'woocomproduct-style' ), wp_get_theme()->get( 'Version' ) );

        // Breadcrumb styles
        wp_enqueue_style( 'woocomproduct-breadcrumb', get_template_directory_uri() . '/assets/css/breadcrumb.css', array( 'woocomproduct-main' ), wp_get_theme()->get( 'Version' ) );

        // Thank you page styles
        wp_enqueue_style( 'woocomproduct-thank-you', get_template_directory_uri() . '/assets/css/tnq.css', array( 'woocomproduct-breadcrumb' ), wp_get_theme()->get( 'Version' ) );

        // Account page styles
        wp_enqueue_style( 'woocomproduct-account', get_template_directory_uri() . '/assets/css/account.css', array( 'woocomproduct-thank-you' ), wp_get_theme()->get( 'Version' ) );
        wp_enqueue_script( 'woocomproduct-mini-cart', get_template_directory_uri() . '/assets/js/mini-cart.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
        wp_localize_script( 'woocomproduct-mini-cart', 'woocomproduct_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'woocomproduct-mini-cart' ),
        ) );

        // Checkout fields JS (for conditional VAT visibility)
        wp_enqueue_script( 'woocomproduct-checkout', get_template_directory_uri() . '/assets/js/checkout-fields.js', array( 'jquery' ), '1.0.0', true );

        // Additional theme assets can be added here
    }

// Load product meta handlers (moved to inc/product-meta.php)
require_once get_template_directory() . '/inc/product-meta.php';

/**
 * Load checkout fields customizations
 */
if ( class_exists( 'WooCommerce' ) ) {
    require_once get_template_directory() . '/inc/product-badges.php';
    // Discounts (automatic discount rules)
    require_once get_template_directory() . '/inc/discounts.php';
    // Shipping methods (location-based rates)
    require_once get_template_directory() . '/inc/shipping.php';
    // Checkout fields customizations
    require_once get_template_directory() . '/inc/checkout-fields.php';

    // Add mini-cart count to WooCommerce AJAX fragments
    add_filter( 'woocommerce_add_to_cart_fragments', 'woocomproduct_cart_count_fragments' );
    function woocomproduct_cart_count_fragments( $fragments ) {
        $fragments['.mini-cart-count'] = '<span class="mini-cart-count" aria-live="polite">' . ( function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ) . '</span>';
        return $fragments;
    }

    // Add download receipt button to thank you page
    add_action( 'woocommerce_thankyou', 'woocomproduct_add_download_receipt_button', 10, 1 );
    function woocomproduct_add_download_receipt_button( $order_id ) {
        if ( ! $order_id ) return;

        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        echo '<div class="receipt-actions" style="text-align: center; margin: 2rem 0;">';
        echo '<button onclick="window.print()" class="button receipt-print-btn" style="margin-right: 1rem;">🖨️ Print Receipt</button>';
        echo '<a href="' . esc_url( $order->get_view_order_url() ) . '" class="button receipt-view-btn">📄 View Order Details</a>';
        echo '</div>';
    }
}

//Remove when you need to add another page as home page
// Redirect home page to shop page
add_action( 'template_redirect', 'woocomproduct_redirect_home_to_shop' );
function woocomproduct_redirect_home_to_shop() {
    if ( is_front_page() && ! is_page( wc_get_page_id( 'shop' ) ) ) {
        $shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
        if ( $shop_page_url ) {
            wp_redirect( $shop_page_url );
            exit;
        }
    }
}
