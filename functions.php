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

        // Mini cart JS (initial; file will be added later)
        wp_enqueue_script( 'woocomproduct-mini-cart', get_template_directory_uri() . '/assets/js/mini-cart.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
        wp_localize_script( 'woocomproduct-mini-cart', 'woocomproduct_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'woocomproduct-mini-cart' ),
        ) );
    }

// Load product meta handlers (moved to inc/product-meta.php)
require_once get_template_directory() . '/inc/product-meta.php';

/**
 * Load product badges display handlers
 */
if ( class_exists( 'WooCommerce' ) ) {
    require_once get_template_directory() . '/inc/product-badges.php';
}
