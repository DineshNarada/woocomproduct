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

    // Register navigation menus
    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'woocomproduct' ),
    ) );

    // Additional WooCommerce support features can be added here
}

// Custom Navigation Walker for Dropdown Support
class Woocomproduct_Walker_Nav_Menu extends Walker_Nav_Menu {
    function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        $output .= "\n$indent<ul class=\"sub-menu\" role=\"menu\" aria-hidden=\"true\">\n";
    }

    function end_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat("\t", $depth);
        $output .= "$indent</ul>\n";
    }

    function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        // Add current menu item classes
        $current_classes = array_intersect( $classes, array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor' ) );
        if ( ! empty( $current_classes ) ) {
            $classes[] = 'current';
        }

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names .'>';

        $attributes = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

        // Add ARIA attributes for accessibility
        $attributes .= ' role="menuitem"';
        if ( in_array( 'menu-item-has-children', $classes ) ) {
            $attributes .= ' aria-haspopup="true" aria-expanded="false"';
        }

        $item_output = isset( $args->before ) ? $args->before : '';
        $item_output .= '<a'. $attributes .'>';
        $item_output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . apply_filters( 'the_title', $item->title, $item->ID ) . ( isset( $args->link_after ) ? $args->link_after : '' );
        $item_output .= '</a>';
        $item_output .= isset( $args->after ) ? $args->after : '';

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

    function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= "</li>\n";
    }
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

    // FrockMEE specific content
    $hero_title = 'FrockMEE – Made for Every Me';
    $hero_subtitle = 'Discover casual, party, office & summer frocks';
    $hero_class = 'hero-section frockmee-hero';

    // Build inline styles - use feminine gradient if no custom image
    $style = '';
    if ( ! empty( $hero_image ) ) {
        $style = 'background-image: linear-gradient(rgba(0,0,0,' . esc_attr( $overlay_opacity ) . '), rgba(0,0,0,' . esc_attr( $overlay_opacity ) . ')), url(\'' . esc_url( $hero_image ) . '\'); background-size: cover; background-position: center; background-attachment: fixed;';
    } else {
        $style = 'background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 50%, #fecfef 100%);';
    }
    ?>
    <section class="<?php echo esc_attr( $hero_class ); ?>" style="<?php echo $style; ?>">
        <div class="hero-content">
            <h1 class="hero-title"><?php echo esc_html( $hero_title ); ?></h1>
            <p class="hero-subtitle"><?php echo esc_html( $hero_subtitle ); ?></p>
            <div class="hero-ctas">
                <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="hero-cta primary">Shop Now</a>
                <a href="<?php echo esc_url( get_permalink( wc_get_page_id( 'shop' ) ) . '?orderby=date' ); ?>" class="hero-cta secondary">New Arrivals</a>
            </div>
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

        // Navigation styles
        wp_enqueue_style( 'woocomproduct-nav', get_template_directory_uri() . '/assets/css/nav.css', array( 'woocomproduct-account' ), wp_get_theme()->get( 'Version' ) );

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

        // Navigation script
        wp_enqueue_script( 'woocomproduct-nav', get_template_directory_uri() . '/assets/js/nav.js', array( 'jquery' ), wp_get_theme()->get( 'Version' ), true );
    }

// Load product meta handlers (moved to inc/product-meta.php)
require_once get_template_directory() . '/inc/product-meta.php';

/**
 * Remove add to cart button from shop page
 *
 * This functionality was previously in child theme - now integrated into main theme
 */
add_action( 'init', 'woocom_remove_add_to_cart_from_shop' );
function woocom_remove_add_to_cart_from_shop() {
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
}

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
    // Checkout fields customizations (Business Type, VAT Number fields)
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

