<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Theme support for WooCommerce
add_action( 'after_setup_theme', 'woocomproduct_theme_setup' );
function woocomproduct_theme_setup() {
    add_theme_support( 'woocommerce' );

    // Additional WooCommerce support features can be added here
}

// Enqueue theme styles and scripts
    add_action( 'wp_enqueue_scripts', 'woocomproduct_enqueue_assets' );
    function woocomproduct_enqueue_assets() {
        // Main theme stylesheet (style.css)
        wp_enqueue_style( 'woocomproduct-style', get_stylesheet_uri(), array(), wp_get_theme()->get( 'Version' ) );
    }

// Load product meta handlers (moved to inc/product-meta.php)
require_once get_template_directory() . '/inc/product-meta.php';

/**
 * Load product badges display handlers
 */
if ( class_exists( 'WooCommerce' ) ) {
    require_once get_template_directory() . '/inc/product-badges.php';
}
