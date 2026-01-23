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

<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'woocomproduct' ); ?></a>

<header class="site-header">
    <div class="container header-inner">
        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo-link">
            <img src="<?php echo get_template_directory_uri(); ?>/assets/logo/logo-without-name.png" alt="<?php bloginfo( 'name' ); ?> Logo" class="site-logo">
        </a>
        <div class="site-branding">
            <?php if ( function_exists( 'the_custom_logo' ) ) the_custom_logo(); ?>
            <a class="site-title" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php bloginfo( 'name' ); ?></a>
        </div>

        <nav class="site-navigation" aria-label="<?php esc_attr_e( 'Primary Menu', 'woocomproduct' ); ?>">
            <button class="mobile-menu-toggle" aria-controls="primary-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'Toggle navigation menu', 'woocomproduct' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </button>
            <?php wp_nav_menu( array(
                'theme_location' => 'primary',
                'container' => false,
                'menu_id' => 'primary-menu',
                'menu_class' => 'primary-menu',
                'walker' => new Woocomproduct_Walker_Nav_Menu()
            ) ); ?>
        </nav>

        <div class="header-search">
            <?php get_product_search_form(); ?>
        </div>

        <div class="header-shop">
            <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="shop-link" aria-label="<?php esc_attr_e( 'Shop', 'woocomproduct' ); ?>">
                <span class="shop-text"><?php esc_html_e( 'Shop', 'woocomproduct' ); ?></span>
            </a>
        </div>

        <div class="header-account">
            <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="account-link" aria-label="<?php esc_attr_e( 'My Account', 'woocomproduct' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                <?php if ( is_user_logged_in() ) : ?>
                    <span class="account-text"><?php esc_html_e( 'My Account', 'woocomproduct' ); ?></span>
                <?php else : ?>
                    <span class="account-text"><?php esc_html_e( 'Login', 'woocomproduct' ); ?></span>
                <?php endif; ?>
            </a>
        </div>

        <div class="header-tools">
            <button class="mini-cart-toggle" aria-controls="mini-cart-panel" aria-expanded="false" aria-label="<?php esc_attr_e( 'Open cart', 'woocomproduct' ); ?>">
                <svg class="mini-cart-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 12.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span class="mini-cart-count" aria-live="polite"><?php echo ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0; ?></span>
            </button>

            <div id="mini-cart-panel" class="mini-cart-panel" hidden aria-hidden="true" role="region" aria-label="<?php esc_attr_e( 'Mini cart', 'woocomproduct' ); ?>" tabindex="-1">
                <button class="mini-cart-close" aria-label="<?php esc_attr_e( 'Close cart', 'woocomproduct' ); ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </button>
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