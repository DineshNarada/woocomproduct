# WooComProduct Theme - Assignment V3

Custom WordPress theme extending WooCommerce with advanced customizations using only hooks and filters.

## Deliverables

### Completed Features
- **Theme Setup & WooCommerce Compatibility**: Full WooCommerce support with proper asset enqueuing and theme setup.
- **Product Listing Customization**: Custom "Local / Imported" badge displayed on shop and category pages using hooks only.
- **Mini Cart in Header**: AJAX-powered mini cart with dynamic updates for add/remove/quantity changes.
- **Order Data Management**: Custom fields saved to order meta, displayed in admin and emails.
- **Automatic Discount Logic**: 20% discount applied when subtotal exceeds 20,000 LKR.
- **Location-Based Shipping**: Custom shipping method with rates based on customer location (LK: 500, Asia: 1500, Other: 3000 LKR).

### Files Modified/Created
- `functions.php`: Theme setup, asset enqueuing, WooCommerce support.
- `inc/product-meta.php`: Product meta field registration and admin UI.
- `inc/product-badges.php`: Badge display logic using WooCommerce hooks.
- `inc/mini-cart.php`: Mini cart HTML and AJAX handlers.
- `inc/discounts.php`: Automatic discount calculation.
- `inc/shipping.php`: Custom shipping method implementation.
- `header.php`: Mini cart placement.
- `assets/css/main.css`: Styling for badges and mini cart.
- `assets/js/mini-cart.js`: JavaScript for mini cart interactions.

### Hooks and Filters Used

#### Theme Setup
- `add_action( 'after_setup_theme', 'woocomproduct_theme_setup' )`: Registers WooCommerce support.
- `add_action( 'wp_enqueue_scripts', 'woocomproduct_enqueue_assets' )`: Enqueues styles and scripts.

#### Product Badges
- `add_action( 'woocommerce_before_shop_loop_item_title', 'woocomproduct_output_product_badge', 10 )`: Displays badge on product listings.
- `apply_filters( 'woocomproduct_show_product_badge', true, $html )`: Allows disabling badge display.
- `apply_filters( 'woocomproduct_product_badge_html', $html, $product )`: Filters final badge HTML.

#### Product Meta
- `add_action( 'init', 'woocomproduct_register_product_meta' )`: Registers product meta.
- `add_action( 'woocommerce_product_options_general_product_data', 'woocomproduct_add_local_imported_field' )`: Adds admin field.
- `add_action( 'woocommerce_process_product_meta', 'woocomproduct_save_local_imported_field' )`: Saves meta field.
- `add_filter( 'manage_edit-product_columns', 'woocomproduct_add_local_imported_column' )`: Adds admin column.
- `add_action( 'manage_product_posts_custom_column', 'woocomproduct_render_local_imported_column', 10, 2 )`: Renders admin column.

#### Mini Cart
- `add_filter( 'woocommerce_add_to_cart_fragments', 'woocomproduct_mini_cart_fragments' )`: Updates mini cart via AJAX.
- `add_filter( 'woocommerce_add_to_cart_fragments', 'woocomproduct_cart_count_fragments' )`: Updates cart count.
- `add_action( 'wp_ajax_woocomproduct_remove_cart_item', 'woocomproduct_ajax_remove_cart_item' )`: AJAX remove item.
- `add_action( 'wp_ajax_nopriv_woocomproduct_remove_cart_item', 'woocomproduct_ajax_remove_cart_item' )`: Non-logged remove.
- `add_action( 'wp_ajax_woocomproduct_update_cart_item', 'woocomproduct_ajax_update_cart_item' )`: AJAX update quantity.
- `add_action( 'wp_ajax_nopriv_woocomproduct_update_cart_item', 'woocomproduct_ajax_update_cart_item' )`: Non-logged update.

#### Discounts
- `add_action( 'woocommerce_cart_calculate_fees', 'woocomproduct_apply_automatic_discount', 10, 1 )`: Applies discount.
- `apply_filters( 'woocomproduct_discount_threshold', 20000 )`: Configurable threshold.
- `apply_filters( 'woocomproduct_discount_percentage', 20 )`: Configurable percentage.
- `apply_filters( 'woocomproduct_discount_taxable', true )`: Taxable setting.
- `apply_filters( 'woocomproduct_discount_tax_class', '' )`: Tax class.

#### Shipping
- `add_action( 'woocommerce_shipping_init', 'woocomproduct_location_shipping_init' )`: Initializes shipping method.
- `add_filter( 'woocommerce_shipping_methods', 'woocomproduct_register_location_shipping_method' )`: Registers method.
- `apply_filters( 'woocomproduct_location_shipping_cost', $cost, $package )`: Filters shipping cost.

#### Additional
- `add_action( 'woocommerce_thankyou', 'woocomproduct_add_download_receipt_button', 10, 1 )`: Adds receipt button.
- `add_action( 'template_redirect', 'woocomproduct_redirect_home_to_shop' )`: Redirects home to shop.

## Quick Testing Steps

1. **Theme Setup**: Activate theme, verify WooCommerce support and assets load.
2. **Product Badges**: Create products, set Local/Imported meta, view on shop/category pages.
3. **Mini Cart**: Add items, change quantities, remove items - verify AJAX updates without page reload.
4. **Order Data**: Place order, check admin order view and emails for custom fields.
5. **Discount**: Add items totaling >20,000 LKR, verify 20% discount in cart/checkout.
6. **Shipping**: Change billing country, verify shipping rates update (LK: 500, Asia: 1500, Other: 3000).

## Commit History
- Day-by-day commits with descriptive messages for traceability.
- Small, focused commits for each feature implementation.