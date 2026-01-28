<?php
/**
 * WooCommerce Checkout Fields & Customizations
 *
 * This file handles all custom checkout field functionality including:
 * - Custom field registration (Business Type, VAT Number)
 * - Validation for both block and classic checkout
 * - Field visibility toggling based on business type
 * - Order meta persistence and admin display
 *
 * Migrated from child theme to main theme.
 *
 * @package WooCom
 * @version 1.0.0
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * =====================================
 * Field Definitions & Configuration
 * =====================================
 */

if ( ! defined( 'WOOCOMPRODUCT_TEXT_DOMAIN' ) ) {
    define( 'WOOCOMPRODUCT_TEXT_DOMAIN', 'woocomproduct' );
}

if ( ! defined( 'WOOCOMPRODUCT_BUSINESS_TYPE_FIELD' ) ) {
    define( 'WOOCOMPRODUCT_BUSINESS_TYPE_FIELD', 'business_type' );
}

if ( ! defined( 'WOOCOMPRODUCT_VAT_NUMBER_FIELD' ) ) {
    define( 'WOOCOMPRODUCT_VAT_NUMBER_FIELD', 'vat_number' );
}

/**
 * Get field definitions used in both block and classic checkout
 *
 * @return array Field configuration array for both block and classic checkout formats
 */
function get_woocomproduct_field_definitions() {
    return array(
        WOOCOMPRODUCT_BUSINESS_TYPE_FIELD => array(
            'block'    => array(
                'id'       => 'woocomproduct/' . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD,
                'label'    => __( 'Business Type', WOOCOMPRODUCT_TEXT_DOMAIN ),
                'location' => 'address',
                'type'     => 'select',
                'required' => true,
                'options'  => array(
                    array( 'value' => '',           'label' => __( 'Select…', WOOCOMPRODUCT_TEXT_DOMAIN ) ),
                    array( 'value' => 'individual', 'label' => __( 'Individual', WOOCOMPRODUCT_TEXT_DOMAIN ) ),
                    array( 'value' => 'company',    'label' => __( 'Company', WOOCOMPRODUCT_TEXT_DOMAIN ) ),
                ),
                'priority' => 105,
            ),
            'classic'  => array(
                'type'     => 'select',
                'label'    => __( 'Business Type', WOOCOMPRODUCT_TEXT_DOMAIN ),
                'required' => true,
                'options'  => array(
                    ''           => __( 'Select…', WOOCOMPRODUCT_TEXT_DOMAIN ),
                    'individual' => __( 'Individual', WOOCOMPRODUCT_TEXT_DOMAIN ),
                    'company'    => __( 'Company', WOOCOMPRODUCT_TEXT_DOMAIN ),
                ),
                'priority' => 105,
            ),
        ),
        WOOCOMPRODUCT_VAT_NUMBER_FIELD => array(
            'block'   => array(
                'id'          => 'woocomproduct/' . WOOCOMPRODUCT_VAT_NUMBER_FIELD,
                'label'       => __( 'VAT Number', WOOCOMPRODUCT_TEXT_DOMAIN ),
                'location'    => 'address',
                'type'        => 'text',
                'placeholder' => '',
                'priority'    => 106,
            ),
            'classic' => array(
                'type'     => 'text',
                'label'    => __( 'VAT Number', WOOCOMPRODUCT_TEXT_DOMAIN ),
                'required' => false,
                'priority' => 106,
            ),
        ),
    );
}

/**
 * =====================================
 * Checkout Field Registration (Block Checkout)
 * =====================================
 */

/**
 * Register business type field for block checkout
 */
add_action( 'init', 'register_business_type_field' );
function register_business_type_field() {
    if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
        return;
    }

    $fields = get_woocomproduct_field_definitions();
    woocommerce_register_additional_checkout_field( $fields[ WOOCOMPRODUCT_BUSINESS_TYPE_FIELD ]['block'] );
}

/**
 * Register VAT number field for block checkout
 */
add_action( 'init', 'register_vat_number_field' );
function register_vat_number_field() {
    if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) {
        return;
    }

    $fields = get_woocomproduct_field_definitions();
    woocommerce_register_additional_checkout_field( $fields[ WOOCOMPRODUCT_VAT_NUMBER_FIELD ]['block'] );
}

/**
 * =====================================
 * Checkout Field Registration (Classic Checkout)
 * =====================================
 */

/**
 * Add fields for classic checkout
 *
 * @param array $fields Existing WooCommerce checkout fields
 * @return array Modified checkout fields
 */
add_filter( 'woocommerce_checkout_fields', 'add_classic_checkout_fields' );
function add_classic_checkout_fields( $fields ) {
    $field_defs = get_woocomproduct_field_definitions();

    $fields['billing'][ 'billing_' . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD ] = $field_defs[ WOOCOMPRODUCT_BUSINESS_TYPE_FIELD ]['classic'];
    $fields['billing'][ 'billing_' . WOOCOMPRODUCT_VAT_NUMBER_FIELD ]    = $field_defs[ WOOCOMPRODUCT_VAT_NUMBER_FIELD ]['classic'];

    return $fields;
}

/**
 * =====================================
 * Checkout Validation & Helper Functions
 * =====================================
 */

/**
 * Get checkout field value from posted data
 *
 * Handles both block and classic field names
 *
 * @param array  $posted_data The checkout posted data
 * @param string $field_name  The field name without prefix
 * @return string The field value
 */
function get_woocomproduct_checkout_value( $posted_data, $field_name ) {
    $block_key   = 'woocomproduct/' . $field_name;
    $classic_key = 'billing_' . $field_name;

    return $posted_data[ $block_key ] ?? $posted_data[ $classic_key ] ?? '';
}

/**
 * Validate checkout fields on form submission
 *
 * Handles both classic and block checkout
 * Runs early to prevent order processing
 */
add_action( 'woocommerce_checkout_process', 'woocomproduct_validate_vat', 5 );
function woocomproduct_validate_vat() {
    $posted_data = WC()->checkout->get_posted_data();
    $business    = get_woocomproduct_checkout_value( $posted_data, WOOCOMPRODUCT_BUSINESS_TYPE_FIELD );
    $vat         = get_woocomproduct_checkout_value( $posted_data, WOOCOMPRODUCT_VAT_NUMBER_FIELD );

    if ( 'company' === $business && empty( $vat ) ) {
        wc_add_notice(
            __( 'Please enter a VAT Number for company billing addresses.', WOOCOMPRODUCT_TEXT_DOMAIN ),
            'error'
        );
    }
}

/**
 * Also validate via after checkout validation hook
 * Ensures validation works for all checkout types and AJAX
 */
add_action( 'woocommerce_after_checkout_validation', 'woocomproduct_validate_vat_ajax', 10, 2 );
function woocomproduct_validate_vat_ajax( $posted_data, $errors ) {
    $business = get_woocomproduct_checkout_value( $posted_data, WOOCOMPRODUCT_BUSINESS_TYPE_FIELD );
    $vat      = get_woocomproduct_checkout_value( $posted_data, WOOCOMPRODUCT_VAT_NUMBER_FIELD );

    if ( 'company' === $business && empty( $vat ) ) {
        $errors->add(
            'vat_required',
            __( 'Please enter a VAT Number for company billing addresses.', WOOCOMPRODUCT_TEXT_DOMAIN )
        );
    }
}

/**
 * Register checkout validation for WooCommerce Blocks
 *
 * Handles VAT field requirement based on business type
 */
add_action( 'wp_enqueue_scripts', 'woocomproduct_register_checkout_validation', 99 );
function woocomproduct_register_checkout_validation() {
    if ( ! is_checkout() ) {
        return;
    }

    $validation_script = "
        if ( window.wc && window.wc.blocksCheckout ) {
            window.wc.blocksCheckout.registerCheckoutValidation( 'woocomproduct-vat-validation', function( data ) {
                var business = data['woocomproduct/" . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD . "'] || '';
                var vat      = data['woocomproduct/" . WOOCOMPRODUCT_VAT_NUMBER_FIELD . "'] || '';

                if ( business === 'company' && !vat ) {
                    return {
                        message: '" . esc_js( __( 'Please enter a VAT Number for company billing addresses.', WOOCOMPRODUCT_TEXT_DOMAIN ) ) . "',
                        field: 'woocomproduct/" . WOOCOMPRODUCT_VAT_NUMBER_FIELD . "'
                    };
                }
                return true;
            });
        }
    ";

    wp_add_inline_script( 'woocomproduct-checkout', $validation_script );
}

/**
 * =====================================
 * Checkout Field Visibility
 * =====================================
 */

/**
 * Enqueue client-side field visibility toggle script
 *
 * Toggles VAT field visibility based on selected business type
 * Handles both block and classic checkout formats
 */
add_action( 'wp_enqueue_scripts', 'woocomproduct_enqueue_field_visibility_script', 100 );
function woocomproduct_enqueue_field_visibility_script() {
    if ( ! is_checkout() ) {
        return;
    }

    $visibility_script = "
        (function(\$){
            function getBusinessType() {
                // Try block checkout first
                var blockValue = \$('select[name=\"woocomproduct/" . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD . "\"]').val();
                if (blockValue) return blockValue;
                
                // Fall back to classic checkout
                return \$('select[name=\"billing_" . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD . "\"]').val();
            }

            function getVatInput() {
                // Try block checkout first
                var blockInput = \$('input[name=\"woocomproduct/" . WOOCOMPRODUCT_VAT_NUMBER_FIELD . "\"]');
                if (blockInput.length) return blockInput;
                
                // Fall back to classic checkout
                return \$('input[name=\"billing_" . WOOCOMPRODUCT_VAT_NUMBER_FIELD . "\"]');
            }

            function getVatFieldWrapper() {
                // Try block checkout wrapper
                var blockInput = \$('input[name=\"woocomproduct/" . WOOCOMPRODUCT_VAT_NUMBER_FIELD . "\"]');
                if (blockInput.length) {
                    return blockInput.closest('[class*=\"form\"]').length 
                        ? blockInput.closest('[class*=\"form\"]')
                        : blockInput.closest('div').length 
                            ? blockInput.closest('div')
                            : blockInput.parent();
                }
                
                // Fall back to classic checkout
                var classicInput = \$('input[name=\"billing_" . WOOCOMPRODUCT_VAT_NUMBER_FIELD . "\"]');
                return classicInput.closest('p').length ? classicInput.closest('p') : classicInput.parent();
            }

            function toggleVat() {
                var business   = getBusinessType();
                var vatField   = getVatFieldWrapper();
                var vatInput   = getVatInput();

                if (business === 'company') {
                    vatField.show();
                    vatInput.attr('required', 'required').prop('required', true);
                    vatInput.closest('label').find('.required, [aria-required]').show();
                } else {
                    vatField.hide();
                    vatInput.removeAttr('required').prop('required', false);
                    vatInput.val('').trigger('change');
                }
            }

            \$(function(){
                // Initial toggle
                setTimeout(toggleVat, 100);
                
                // Listen for business type changes
                \$(document).on('change', 'select[name=\"woocomproduct/" . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD . "\"], select[name=\"billing_" . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD . "\"]', function() {
                    toggleVat();
                });
                
                // Listen for checkout updates
                \$(document.body).on('updated_checkout', function(){
                    setTimeout(toggleVat, 100);
                });
            });
        })(jQuery);
    ";

    wp_add_inline_script( 'woocomproduct-checkout', $visibility_script );
}

/**
 * =====================================
 * Order Data Persistence
 * =====================================
 */

/**
 * Save checkout field values to order meta
 *
 * @param int $order_id The order ID
 */
add_action( 'woocommerce_checkout_update_order_meta', 'woocomproduct_save_checkout_fields' );
function woocomproduct_save_checkout_fields( $order_id ) {
    $posted_data = WC()->checkout->get_posted_data();

    $business = get_woocomproduct_checkout_value( $posted_data, WOOCOMPRODUCT_BUSINESS_TYPE_FIELD );
    $vat      = get_woocomproduct_checkout_value( $posted_data, WOOCOMPRODUCT_VAT_NUMBER_FIELD );

    if ( $business ) {
        update_post_meta( $order_id, '_billing_' . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD, sanitize_text_field( $business ) );
    }
    if ( $vat ) {
        update_post_meta( $order_id, '_billing_' . WOOCOMPRODUCT_VAT_NUMBER_FIELD, sanitize_text_field( $vat ) );
    }
}

/**
 * =====================================
 * Admin Order Display
 * =====================================
 */

/**
 * Display saved fields in admin order billing panel
 *
 * @param WC_Order $order The order object
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocomproduct_display_admin_order_meta', 10, 1 );
function woocomproduct_display_admin_order_meta( $order ) {
    $business = get_post_meta( $order->get_id(), '_billing_' . WOOCOMPRODUCT_BUSINESS_TYPE_FIELD, true );
    $vat      = get_post_meta( $order->get_id(), '_billing_' . WOOCOMPRODUCT_VAT_NUMBER_FIELD, true );

    $labels = array(
        'individual' => __( 'Individual', WOOCOMPRODUCT_TEXT_DOMAIN ),
        'company'    => __( 'Company', WOOCOMPRODUCT_TEXT_DOMAIN ),
    );

    if ( $business ) {
        echo '<p><strong>' . esc_html__( 'Business Type:', WOOCOMPRODUCT_TEXT_DOMAIN ) . '</strong> ' . esc_html( $labels[ $business ] ?? $business ) . '</p>';
    }
    if ( $vat ) {
        echo '<p><strong>' . esc_html__( 'VAT Number:', WOOCOMPRODUCT_TEXT_DOMAIN ) . '</strong> ' . esc_html( $vat ) . '</p>';
    }
}
