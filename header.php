<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<header class="site-header">
    <div class="container header-inner">
        <div class="site-branding">
            <?php if ( function_exists( 'the_custom_logo' ) ) the_custom_logo(); ?>
            <a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
        </div>

        <nav class="site-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'woocomproduct' ); ?>">
            <?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false ) ); ?>
        </nav>

        <div class="header-tools">
            <button class="mini-cart-toggle" aria-controls="mini-cart-panel" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open cart', 'woocomproduct' ); ?>">
                <svg class="mini-cart-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 12.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span class="mini-cart-count" aria-live="polite"><?php echo ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0; ?></span>
            </button>

            <div id="mini-cart-panel" class="mini-cart-panel" hidden aria-hidden="true" role="region" aria-label="<?php esc_attr_e( 'Mini cart', 'woocomproduct' ); ?>" tabindex="-1">
                <?php
                // Include dynamic mini cart content (uses `inc/mini-cart.php`)
                if ( locate_template( 'inc/mini-cart.php' ) ) {
                    include_once get_template_directory() . '/inc/mini-cart.php';
                } else {
                    if ( function_exists( 'woocommerce_mini_cart' ) ) {
                        echo '<div class="widget_shopping_cart_content">';
                        woocommerce_mini_cart();
                        echo '</div>';
                    } else {
                        echo '<p class="mini-cart-empty">' . esc_html__( 'No cart content available', 'woocomproduct' ) . '</p>';
                    }
                }
                ?>
            </div>
        </div>
    </div>
</header>