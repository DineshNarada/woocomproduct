<?php
/**
 * WooCommerce Product Theme Functions
 *
 * Main theme functionality file for the WooCommerce Product theme.
 * Handles theme setup, customization, assets, and WooCommerce integration.
 *
 * @package WooComProduct
 * @version 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * ============================================================================
 * THEME SETUP & SUPPORT
 * ============================================================================
 */

/**
 * Setup theme support and register navigation menus
 *
 * @hook after_setup_theme
 * @return void
 */
add_action( 'after_setup_theme', 'woocomproduct_theme_setup' );
function woocomproduct_theme_setup() {
    add_theme_support( 'post-thumbnails' );

    add_theme_support( 'woocommerce', array(
        'thumbnail_image_width' => 300,
        'single_image_width'    => 600,
    ) );

    add_theme_support( 'custom-logo' );
    add_theme_support( 'custom-background' );
    add_theme_support( 'customize-selective-refresh-widgets' );

    register_nav_menus( array(
        'primary' => __( 'Primary Menu', 'woocomproduct' ),
    ) );
}

/**
 * ============================================================================
 * NAVIGATION & WALKER
 * ============================================================================
 */

/**
 * Custom Navigation Walker for dropdown menu support
 *
 * @package WooComProduct
 */
class Woocomproduct_Walker_Nav_Menu extends Walker_Nav_Menu {
    /**
     * Start level
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of the item.
     * @param array  $args   Arguments.
     */
    public function start_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "\n{$indent}<ul class=\"sub-menu\" role=\"menu\" aria-hidden=\"true\">\n";
    }

    /**
     * End level
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param int    $depth  Depth of the item.
     * @param array  $args   Arguments.
     */
    public function end_lvl( &$output, $depth = 0, $args = null ) {
        $indent = str_repeat( "\t", $depth );
        $output .= "{$indent}</ul>\n";
    }

    /**
     * Start element
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Menu item data object.
     * @param int    $depth  Depth of the item.
     * @param array  $args   Arguments.
     * @param int    $id     ID.
     */
    public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        $current_classes = array_intersect( $classes, array( 'current-menu-item', 'current-menu-parent', 'current-menu-ancestor' ) );
        if ( ! empty( $current_classes ) ) {
            $classes[] = 'current';
        }

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args ) );
        $class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

        $id = apply_filters( 'nav_menu_item_id', 'menu-item-' . $item->ID, $item, $args );
        $id = $id ? ' id="' . esc_attr( $id ) . '"' : '';

        $output .= $indent . '<li' . $id . $class_names . '>';

        $attributes = ! empty( $item->attr_title ) ? ' title="' . esc_attr( $item->attr_title ) . '"' : '';
        $attributes .= ! empty( $item->target ) ? ' target="' . esc_attr( $item->target ) . '"' : '';
        $attributes .= ! empty( $item->xfn ) ? ' rel="' . esc_attr( $item->xfn ) . '"' : '';
        $attributes .= ! empty( $item->url ) ? ' href="' . esc_attr( $item->url ) . '"' : '';

        $attributes .= ' role="menuitem"';
        if ( in_array( 'menu-item-has-children', $classes ) ) {
            $attributes .= ' aria-haspopup="true" aria-expanded="false"';
        }

        $item_output = isset( $args->before ) ? $args->before : '';
        $item_output .= '<a' . $attributes . '>';
        $item_output .= ( isset( $args->link_before ) ? $args->link_before : '' ) . apply_filters( 'the_title', $item->title, $item->ID ) . ( isset( $args->link_after ) ? $args->link_after : '' );
        $item_output .= '</a>';
        $item_output .= isset( $args->after ) ? $args->after : '';

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }

    /**
     * End element
     *
     * @param string $output Passed by reference. Used to append additional content.
     * @param object $item   Menu item data object.
     * @param int    $depth  Depth of the item.
     * @param array  $args   Arguments.
     */
    public function end_el( &$output, $item, $depth = 0, $args = null ) {
        $output .= "</li>\n";
    }
}

/**
 * ============================================================================
 * CUSTOMIZER
 * ============================================================================
 */

/**
 * Register customizer settings and controls
 *
 * @hook customize_register
 * @param object $wp_customize Customizer object.
 * @return void
 */
add_action( 'customize_register', 'woocomproduct_customize_register' );
function woocomproduct_customize_register( $wp_customize ) {
    $wp_customize->add_panel( 'hero_panel', array(
        'title'       => __( 'Hero Section', 'woocomproduct' ),
        'description' => __( 'Customize the hero section on the home page', 'woocomproduct' ),
        'priority'    => 30,
    ) );

    $wp_customize->add_section( 'hero_background_section', array(
        'title'    => __( 'Background Image', 'woocomproduct' ),
        'panel'    => 'hero_panel',
        'priority' => 10,
    ) );

    $wp_customize->add_setting( 'hero_background_image', array(
        'default'           => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'hero_background_image', array(
        'label'       => __( 'Hero Background Image', 'woocomproduct' ),
        'section'     => 'hero_background_section',
        'settings'    => 'hero_background_image',
        'description' => __( 'Upload an image for the hero section background. If no image is selected, the gradient will be used.', 'woocomproduct' ),
    ) ) );

    $wp_customize->add_setting( 'hero_overlay_opacity', array(
        'default'           => 0.7,
        'sanitize_callback' => 'woocomproduct_sanitize_overlay_opacity',
        'transport'         => 'refresh',
    ) );

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

/**
 * Sanitize overlay opacity value
 *
 * @param mixed $input Input value.
 * @return float Sanitized opacity value.
 */
function woocomproduct_sanitize_overlay_opacity( $input ) {
    $input = floatval( $input );
    return ( $input >= 0 && $input <= 1 ) ? $input : 0.7;
}

/**
 * ============================================================================
 * HERO BANNER
 * ============================================================================
 */

/**
 * Display hero banner with customizable background
 *
 * @hook woocomproduct_hero_banner
 * @return void
 */
add_action( 'woocomproduct_hero_banner', 'woocomproduct_display_hero_banner' );
function woocomproduct_display_hero_banner() {
    $hero_image       = get_theme_mod( 'hero_background_image', '' );
    $overlay_opacity  = get_theme_mod( 'hero_overlay_opacity', 0.7 );
    $hero_title       = 'FrockMEE – Made for Every Me';
    $hero_subtitle    = 'Discover casual, party, office & summer frocks';
    $hero_class       = 'hero-section frockmee-hero';

    if ( ! empty( $hero_image ) ) {
        $style = sprintf(
            'background-image: linear-gradient(rgba(0,0,0,%s), rgba(0,0,0,%s)), url(\'%s\'); background-size: cover; background-position: center; background-attachment: fixed;',
            esc_attr( $overlay_opacity ),
            esc_attr( $overlay_opacity ),
            esc_url( $hero_image )
        );
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

/**
 * ============================================================================
 * ASSETS & ENQUEUE
 * ============================================================================
 */

/**
 * Enqueue theme styles and scripts
 *
 * @hook wp_enqueue_scripts
 * @return void
 */
add_action( 'wp_enqueue_scripts', 'woocomproduct_enqueue_assets' );
function woocomproduct_enqueue_assets() {
    $theme_version = wp_get_theme()->get( 'Version' );
    $template_uri  = get_template_directory_uri();

    wp_enqueue_style( 'woocomproduct-style', get_stylesheet_uri(), array(), $theme_version );
    wp_enqueue_style( 'woocomproduct-main', $template_uri . '/assets/css/main.css', array( 'woocomproduct-style' ), $theme_version );
    wp_enqueue_style( 'woocomproduct-breadcrumb', $template_uri . '/assets/css/breadcrumb.css', array( 'woocomproduct-main' ), $theme_version );
    wp_enqueue_style( 'woocomproduct-thank-you', $template_uri . '/assets/css/tnq.css', array( 'woocomproduct-breadcrumb' ), $theme_version );
    wp_enqueue_style( 'woocomproduct-account', $template_uri . '/assets/css/account.css', array( 'woocomproduct-thank-you' ), $theme_version );
    wp_enqueue_style( 'woocomproduct-nav', $template_uri . '/assets/css/nav.css', array( 'woocomproduct-account' ), $theme_version );

    if ( is_front_page() ) {
        wp_enqueue_style( 'woocomproduct-home', $template_uri . '/assets/css/home.css', array( 'woocomproduct-account' ), $theme_version );
        wp_enqueue_script( 'woocomproduct-home-js', $template_uri . '/assets/js/main.js', array( 'jquery' ), $theme_version, true );
    }

    wp_enqueue_script( 'woocomproduct-mini-cart', $template_uri . '/assets/js/mini-cart.js', array( 'jquery' ), $theme_version, true );
    wp_localize_script( 'woocomproduct-mini-cart', 'woocomproduct_ajax', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'woocomproduct-mini-cart' ),
    ) );

    wp_enqueue_script( 'woocomproduct-nav', $template_uri . '/assets/js/nav.js', array( 'jquery' ), $theme_version, true );
}

/**
 * ============================================================================
 * INCLUDES & REQUIRED FILES
 * ============================================================================
 */

// Load product meta handlers
require_once get_template_directory() . '/inc/product-meta.php';

// Load WooCommerce specific functionality
if ( class_exists( 'WooCommerce' ) ) {
    require_once get_template_directory() . '/inc/product-badges.php';
    require_once get_template_directory() . '/inc/discounts.php';
    require_once get_template_directory() . '/inc/discounts-settings.php';
    require_once get_template_directory() . '/inc/shipping.php';
    require_once get_template_directory() . '/inc/currency.php';
    require_once get_template_directory() . '/inc/checkout-fields.php';
}

/**
 * ============================================================================
 * SHOP PAGE
 * ============================================================================
 */

/**
 * Remove add to cart button from shop page
 *
 * This functionality was previously in child theme - now integrated into main theme.
 *
 * @hook init
 * @return void
 */
add_action( 'init', 'woocom_remove_add_to_cart_from_shop' );
function woocom_remove_add_to_cart_from_shop() {
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
}

/**
 * ============================================================================
 * WOOCOMMERCE HOOKS & FILTERS
 * ============================================================================
 */

/**
 * Add mini-cart count to WooCommerce AJAX cart fragments
 *
 * @hook woocommerce_add_to_cart_fragments
 * @param array $fragments Cart fragments.
 * @return array Modified fragments.
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'woocomproduct_cart_count_fragments' );
function woocomproduct_cart_count_fragments( $fragments ) {
    $cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
    $fragments['.mini-cart-count'] = '<span class="mini-cart-count" aria-live="polite">' . intval( $cart_count ) . '</span>';
    return $fragments;
}

/**
 * Add download receipt button to thank you page
 *
 * @hook woocommerce_thankyou
 * @param int $order_id Order ID.
 * @return void
 */
add_action( 'woocommerce_thankyou', 'woocomproduct_add_download_receipt_button', 10, 1 );
function woocomproduct_add_download_receipt_button( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    if ( ! $order ) {
        return;
    }

    ?>
    <div class="receipt-actions" style="text-align: center; margin: 2rem 0;">
        <button onclick="window.print()" class="button receipt-print-btn" style="margin-right: 1rem;">
            🖨️ <?php esc_html_e( 'Print Receipt', 'woocomproduct' ); ?>
        </button>
        <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>" class="button receipt-view-btn">
            📄 <?php esc_html_e( 'View Order Details', 'woocomproduct' ); ?>
        </a>
    </div>
    <?php
}
