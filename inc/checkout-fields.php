<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Register additional checkout fields for WooCommerce block-based checkout */
add_action( 'init', 'register_business_vat_block_fields' );

function register_business_vat_block_fields() {
    if ( ! function_exists( 'woocommerce_register_additional_checkout_field' ) ) return;

    // Registering Business Type field
    woocommerce_register_additional_checkout_field( array(
        'id'          => 'woocomproduct/business_type',
        'label'       => __( 'Business Type', 'woocomproduct' ),
        'location'    => 'address', // This tells the Block to place it within the address card
        'type'        => 'select',
        'required'    => true,
        'options'     => array(
            array( 'value' => '',           'label' => __( 'Select…', 'woocomproduct' ) ),
            array( 'value' => 'individual', 'label' => __( 'Individual', 'woocomproduct' ) ),
            array( 'value' => 'company',    'label' => __( 'Company', 'woocomproduct' ) ),
        ),
        'priority' => 105,
    ) );

    // Registering VAT Number field
    woocommerce_register_additional_checkout_field( array(
        'id'          => 'woocomproduct/vat_number',
        'label'       => __( 'VAT Number', 'woocomproduct' ),
        'location'    => 'address',
        'type'        => 'text',
        'required'    => true,
        'placeholder' => '',
        'priority'    => 106,
    ) );
}

/* Server-side validation using WooCommerce hooks */
add_action( 'woocommerce_checkout_process', 'woocomproduct_validate_vat' );
function woocomproduct_validate_vat() {
    $business = isset( $_POST['woocomproduct/business_type'] ) ? wc_clean( wp_unslash( $_POST['woocomproduct/business_type'] ) ) : '';
    $country  = isset( $_POST['billing_country'] ) ? wc_clean( wp_unslash( $_POST['billing_country'] ) ) : '';
    $vat      = isset( $_POST['woocomproduct/vat_number'] ) ? wc_clean( wp_unslash( $_POST['woocomproduct/vat_number'] ) ) : '';

    // VAT Number is required only when: Business Type = Company AND Country is selected
    if ( 'company' === $business && ! empty( $country ) && empty( $vat ) ) {
        wc_add_notice( __( 'Please enter a VAT Number for company billing addresses.', 'woocomproduct' ), 'error' );
    }
}

/* Ensure values are saved to order meta (safe guard) */
add_action( 'woocommerce_checkout_update_order_meta', 'woocomproduct_save_checkout_fields' );
function woocomproduct_save_checkout_fields( $order_id ) {
    if ( isset( $_POST['woocomproduct/business_type'] ) ) {
        update_post_meta( $order_id, '_billing_business_type', sanitize_text_field( wp_unslash( $_POST['woocomproduct/business_type'] ) ) );
    }
    if ( isset( $_POST['woocomproduct/vat_number'] ) ) {
        update_post_meta( $order_id, '_billing_vat_number', sanitize_text_field( wp_unslash( $_POST['woocomproduct/vat_number'] ) ) );
    }
}

/* Display saved fields in admin order billing panel */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocomproduct_display_admin_order_meta', 10, 1 );
function woocomproduct_display_admin_order_meta( $order ) {
    $business = get_post_meta( $order->get_id(), '_billing_business_type', true );
    $vat      = get_post_meta( $order->get_id(), '_billing_vat_number', true );

    if ( $business ) {
        echo '<p><strong>' . esc_html__( 'Business Type:', 'woocomproduct' ) . '</strong> ' . esc_html( $business ) . '</p>';
    }
    if ( $vat ) {
        echo '<p><strong>' . esc_html__( 'VAT Number:', 'woocomproduct' ) . '</strong> ' . esc_html( $vat ) . '</p>';
    }
}