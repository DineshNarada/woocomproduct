# WooComProduct Theme - Assignment V3

Custom WordPress theme extending WooCommerce with advanced customizations using only hooks and filters.

## Deliverables

### Completed Features ✅

1. **Theme Setup & WooCommerce Compatibility**: Full WooCommerce support with proper asset enqueuing and theme setup.
2. **Product Listing Customization**: Custom "Local / Imported" badge displayed on shop and category pages using hooks only.
3. **Mini Cart in Header**: AJAX-powered mini cart with dynamic updates for add/remove/quantity changes.
4. **Custom Checkout Fields** (NEW): Business Type (dropdown) & VAT Number (conditional required field).
5. **Order Data Management**: Custom fields saved to order meta, displayed in admin and emails.
6. **Automatic Discount Logic**: 20% discount applied when subtotal exceeds 20,000 LKR.
7. **Location-Based Shipping**: Custom shipping method with rates based on customer location (LK: 500, Asia: 1500, Other: 3000 LKR).

### Files Modified/Created

**Core Theme Files:**
- `functions.php`: Theme setup, asset enqueuing, WooCommerce support.
- `header.php`: Mini cart placement and header structure.
- `README.md`: This file.

**Include Modules** (`inc/`):
- `product-meta.php`: Product meta field registration and admin UI for Local/Imported badge.
- `product-badges.php`: Badge display logic using WooCommerce hooks.
- `mini-cart.php`: Mini cart HTML and AJAX handlers.
- `checkout-fields.php` **(NEW)**: Custom checkout fields (Business Type, VAT Number).
- `discounts.php`: Automatic discount calculation logic.
- `discounts-settings.php`: Theme Customizer settings for discount configuration.
- `shipping.php`: Custom shipping method implementation.

**Assets:**
- `assets/css/main.css`: Styling for badges, mini cart, and checkout fields.
- `assets/js/mini-cart.js`: JavaScript for mini cart interactions and AJAX.
- `assets/js/checkout-fields.js` **(NEW)**: JavaScript for conditional VAT field visibility.

---

## Implementation Details

### 1. Checkout Fields (Business Type & VAT Number) - NEW

**Features:**
- Business Type dropdown: Individual | Company (required)
- VAT Number text field: Shown only when country is selected, required when Business Type = Company
- Server-side validation with meaningful error messages
- Client-side conditional display with jQuery
- Saved to order meta and displayed in admin order view
- Visible in both admin and customer emails

**Files:**
- `inc/checkout-fields.php`: Backend logic (fields, validation, saving, display)
- `assets/js/checkout-fields.js`: Frontend field visibility toggle
- `assets/css/main.css`: Field styling

**Key Hooks:**
- `woocommerce_checkout_fields`: Add custom fields
- `woocommerce_checkout_process`: Server-side validation
- `woocommerce_checkout_update_order_meta`: Save to order meta
- `woocommerce_admin_order_data_after_billing_address`: Display in admin
- `woocommerce_email_order_meta_fields`: Display in emails
- `woocommerce_order_details_after_order_table`: Display on order view page

---

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

#### Checkout Fields (NEW)
- `add_filter( 'woocommerce_checkout_fields', 'woocomproduct_add_custom_checkout_fields' )`: Adds Business Type & VAT fields.
- `add_action( 'woocommerce_checkout_process', 'woocomproduct_validate_checkout_fields' )`: Validates fields server-side.
- `add_action( 'woocommerce_checkout_update_order_meta', 'woocomproduct_save_checkout_fields' )`: Saves to order meta.
- `add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocomproduct_display_admin_order_meta' )`: Shows in admin.
- `add_filter( 'woocommerce_email_order_meta_fields', 'woocomproduct_email_order_meta_fields' )`: Shows in emails.
- `add_action( 'woocommerce_order_details_after_order_table', 'woocomproduct_display_order_details_custom_fields' )`: Shows on order page.
- `add_action( 'wp_enqueue_scripts', 'woocomproduct_enqueue_checkout_js' )`: Enqueues checkout field JS.

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

---

## Testing Instructions

### 1. Product Badges
1. Go to **Products** → **Add New** (or edit existing)
2. In the **Product Data** panel, find **Local / Imported** dropdown
3. Select "Local" or "Imported"
4. Publish/Update
5. Visit Shop or Category page → badge should appear on product card
6. Go to **Products** list → column shows badge value ✅

### 2. Mini Cart - AJAX Updates
1. Go to shop page
2. Click **Add to Cart** on a product
3. Click mini cart icon (top right) → cart should open showing item
4. **Remove item**: Click `×` button → item removed, count updated (no page reload)
5. **Update quantity**: Change quantity input → cart updated dynamically
6. **View Cart / Checkout**: Buttons work and navigate correctly ✅

### 3. Checkout Fields - Business Type & VAT
1. Add products to cart
2. Go to checkout page
3. Fill billing address, **select a country**
4. **Business Type** field is required dropdown
   - Select "Individual" → VAT field **hidden**
   - Select "Company" → VAT field **visible** and **required**
5. Try checkout with:
   - Company selected but no country → VAT field hidden ✅
   - Company selected with country but no VAT → error: "VAT Number is required" ✅
   - Company with valid VAT (e.g., "VAT123456") → checkout succeeds ✅
6. After order placed, check:
   - **Admin Order View** → Business Type & VAT displayed ✅
   - **Order Email** (Customer & Admin) → Fields visible in metadata ✅
   - **My Orders** (Account page) → Fields shown under "Business Information" ✅

### 4. Automatic 20% Discount
1. Add products totaling **exactly 20,000 LKR** → no discount visible
2. Add products totaling **> 20,000 LKR** (e.g., 21,000)
3. Go to cart → "Discount (20%)" line appears
4. Discount amount = 20% of subtotal
5. In checkout → discount persists in order totals ✅

### 5. Location-Based Shipping
1. Create a test product with a price
2. Add to cart, go to checkout
3. In **Billing Address**:
   - Select **Country: Sri Lanka (LK)** → Shipping cost = **500 LKR** ✅
   - Select **Country: India** → Shipping cost = **1,500 LKR** ✅ (Asia)
   - Select **Country: USA** → Shipping cost = **3,000 LKR** ✅ (Other)
4. Verify shipping method is "Location-based Shipping"
5. Try multiple countries to confirm rates match regions

---

## Customization

### Change Discount Settings
Visit **Appearance** → **Customize** → **Discounts**:
- **Discount threshold (subtotal)**: Default 20,000
- **Discount percentage (%)**: Default 20

### Modify Shipping Rates
Edit `inc/shipping.php`:
- `$cost = 500;` for LK
- `$cost = 1500;` for Asia
- `$cost = 3000;` for Other

### Override Field Validation
Use filters in your child theme:
```php
add_filter( 'woocomproduct_validate_vat_number', function( $is_valid, $vat, $business_type, $country ) {
    // Custom validation logic
    return $is_valid;
}, 10, 4 );
```

---

## Notes

- **No Template Overrides**: All customizations use hooks/filters only
- **No Additional Plugins**: Pure theme code
- **No WooCommerce Core Edits**: Extensible and safe for updates
- **AJAX Fragments**: Mini cart and discounts update dynamically without page reload
- **Accessibility**: Proper ARIA labels and keyboard navigation

---

## Acceptance Criteria Checklist ✅

- [x] Theme declares `woocommerce` support and loads assets correctly.
- [x] Product badge saved as product meta and visible on Shop/Category via hooks.
- [x] Mini cart in header updates via AJAX on add/remove/quantity changes and displays required info.
- [x] **Checkout fields added, validated, saved to order meta, visible in admin and emails.**
- [x] 20% discount applied automatically when subtotal exceeds 20,000 LKR and visible in cart/checkout.
- [x] Shipping rates match rules by customer location and appear at checkout.
- [x] No template copy-paste overrides, no additional plugins, no WooCommerce core edits.

