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

    // Add support for custom logo
    add_theme_support( 'custom-logo' );

    // Add support for custom background
    add_theme_support( 'custom-background' );

    // Add theme support for selective refresh for widgets
    add_theme_support( 'customize-selective-refresh-widgets' );

    // Additional WooCommerce support features can be added here
}

// Customizer Setup
add_action( 'customize_register', 'woocomproduct_customize_register' );
function woocomproduct_customize_register( $wp_customize ) {
    // Hero Section Panel
    $wp_customize->add_panel( 'hero_panel', array(
        'title'       => __( 'Hero Section', 'woocomproduct' ),
        'description' => __( 'Customize the hero section on the home page', 'woocomproduct' ),
        'priority'    => 30,
    ) );

    // Hero Background Image Section
    $wp_customize->add_section( 'hero_background_section', array(
        'title'    => __( 'Background Image', 'woocomproduct' ),
        'panel'    => 'hero_panel',
        'priority' => 10,
    ) );

    // Hero Background Image Setting
    $wp_customize->add_setting( 'hero_background_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );

    // Hero Background Image Control
    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image', array(
        'label'    => __( 'Hero Background Image', 'woocomproduct' ),
        'section'  => 'hero_background_section',
        'settings' => 'hero_background_image',
        'description' => __( 'Upload an image for the hero section background. If no image is selected, the gradient will be used.', 'woocomproduct' ),
    ) ) );

    // Hero Overlay Opacity Setting
    $wp_customize->add_setting( 'hero_overlay_opacity', array(
        'default'           => 0.7,
        'sanitize_callback' => 'woocomproduct_sanitize_overlay_opacity',
        'transport'         => 'refresh',
    ) );

    // Hero Overlay Opacity Control
    $wp_customize->add_control( 'hero_overlay_opacity', array(
        'type'        => 'range',
        'section'     => 'hero_background_section',
        'label'       => __( 'Background Overlay Opacity', 'woocomproduct' ),
        'description' => __( 'Adjust the opacity of the overlay on the background image (0 = transparent, 1 = fully opaque)', 'woocomproduct' ),
        'input_attrs' => array(
            'min'  => 0,
            'max'  => 1,
            'step' => 0.1,
        ),
    ) );
}

// Sanitize overlay opacity
function woocomproduct_sanitize_overlay_opacity( $input ) {
    $input = floatval( $input );
    return ( $input >= 0 && $input <= 1 ) ? $input : 0.7;
}

// Dynamic Hero Banner Hook
add_action( 'woocomproduct_hero_banner', 'woocomproduct_display_hero_banner' );
function woocomproduct_display_hero_banner() {
    // Get customizer settings
    $hero_image = get_theme_mod( 'hero_background_image', '' );
    $overlay_opacity = get_theme_mod( 'hero_overlay_opacity', 0.7 );

    // Detect device type for dynamic content
    $is_mobile = wp_is_mobile();
    $hero_title = $is_mobile ? 'Welcome to Our Store' : 'Welcome to Our Amazing Store';
    $hero_subtitle = $is_mobile ? 'Shop now and discover great deals!' : 'Discover amazing products at unbeatable prices. Shop now and experience the difference.';
    $hero_class = $is_mobile ? 'hero-section hero-mobile' : 'hero-section hero-desktop';

    // Build inline styles
    $style = '';
    if ( ! empty( $hero_image ) ) {
        $style = 'background-image: linear-gradient(rgba(0,0,0,' . esc_attr( $overlay_opacity ) . '), rgba(0,0,0,' . esc_attr( $overlay_opacity ) . ')), url(\'' . esc_url( $hero_image ) . '\'); background-size: cover; background-position: center; background-attachment: fixed;';
    } else {
        $style = 'background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);';
    }
    ?>
    <section class="<?php echo esc_attr( $hero_class ); ?>" style="<?php echo $style; ?>">
        <div class="hero-content">
            <h1 class="hero-title"><?php echo esc_html( $hero_title ); ?></h1>
            <p class="hero-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="hero-cta">Shop Now</a>
        </div>
    </section>
    <?php
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

        // Home page styles (only on front page)
        if ( is_front_page() ) {
            wp_enqueue_style( 'woocomproduct-home', get_template_directory_uri() . '/assets/css/home.css', array( 'woocomproduct-account' ), wp_get_theme()->get( 'Version' ) );
            wp_enqueue_script( 'woocomproduct-home-js', get_template_directory_uri() . '/assets/js/main.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
        }

        wp_enqueue_script( 'woocomproduct-mini-cart', get_template_directory_uri() . '/assets/js/mini-cart.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
        wp_localize_script( 'woocomproduct-mini-cart', 'woocomproduct_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'woocomproduct-mini-cart' ),
        ) );
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
    require_once get_template_directory() . '/inc/discounts-settings.php';
    // Shipping methods (location-based rates)
    require_once get_template_directory() . '/inc/shipping.php';
    // Currency display based on location
    require_once get_template_directory() . '/inc/currency.php';
    // Checkout fields customizations
    //4.require_once get_template_directory() . '/inc/checkout-fields.php';

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

