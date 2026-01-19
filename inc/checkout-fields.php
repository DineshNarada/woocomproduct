<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* Add custom checkout fields via hooks (no template overrides) */
add_filter( 'woocommerce_checkout_fields', 'woocomproduct_add_checkout_fields' );
function woocomproduct_add_checkout_fields( $fields ) {
    // Business Type (billing_business_type)
    $fields['billing']['billing_business_type'] = array(
        'type'     => 'select',
        'label'    => __( 'Business Type', 'woocomproduct' ),
        'required' => false,
        'class'    => array( 'form-row-wide' ),
        'options'  => array(
            ''           => __( 'Select…', 'woocomproduct' ),
            'individual' => __( 'Individual', 'woocomproduct' ),
            'company' => __( 'Company', 'woocomproduct' ),
        ),
        'priority' => 105,
    );

    // VAT Number (billing_vat_number) — initially not required; validation handled server-side
    $fields['billing']['billing_vat_number'] = array(
        'type'        => 'text',
        'label'       => __( 'VAT Number', 'woocomproduct' ),
        'required'    => false,
        'placeholder' => '',
        'class'       => array( 'form-row-wide', 'vat-number-field' ),
        'priority'    => 106,
    );

    return $fields;
}

/* Server-side validation using WooCommerce hooks */
add_action( 'woocommerce_checkout_process', 'woocomproduct_validate_vat' );
function woocomproduct_validate_vat() {
    $business = isset( $_POST['billing_business_type'] ) ? wc_clean( wp_unslash( $_POST['billing_business_type'] ) ) : '';
    $country  = isset( $_POST['billing_country'] ) ? wc_clean( wp_unslash( $_POST['billing_country'] ) ) : '';
    $vat      = isset( $_POST['billing_vat_number'] ) ? wc_clean( wp_unslash( $_POST['billing_vat_number'] ) ) : '';

    // VAT Number is required only when: Business Type = Company AND Country is selected
    if ( 'company' === $business && ! empty( $country ) && empty( $vat ) ) {
        wc_add_notice( __( 'Please enter a VAT Number for company billing addresses.', 'woocomproduct' ), 'error' );
    }
}

/* Ensure values are saved to order meta (safe guard) */
add_action( 'woocommerce_checkout_update_order_meta', 'woocomproduct_save_checkout_fields' );
function woocomproduct_save_checkout_fields( $order_id ) {
    if ( isset( $_POST['billing_business_type'] ) ) {
        update_post_meta( $order_id, '_billing_business_type', sanitize_text_field( wp_unslash( $_POST['billing_business_type'] ) ) );
    }
    if ( isset( $_POST['billing_vat_number'] ) ) {
        update_post_meta( $order_id, '_billing_vat_number', sanitize_text_field( wp_unslash( $_POST['billing_vat_number'] ) ) );
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