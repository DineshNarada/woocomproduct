<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add "Business Type" dropdown to checkout (billing_business_type)
 */
add_filter( 'woocommerce_checkout_fields', 'woocomproduct_add_business_type_field' );
function woocomproduct_add_business_type_field( $fields ) {
    $fields['billing']['billing_business_type'] = array(
        'type'     => 'select',
        'label'    => __( 'Business Type', 'woocomproduct' ),
        'required' => true,
        'class'    => array( 'form-row-wide' ),
        'options'  => array(
            ''           => __( 'Select an option', 'woocomproduct' ),
            'individual' => __( 'Individual', 'woocomproduct' ),
            'company'    => __( 'Company', 'woocomproduct' ),
        ),
        'priority' => 120,
    );

    $fields['billing']['billing_vat_number'] = array(
        'type'        => 'text',
        'label'       => __( 'VAT Number', 'woocomproduct' ),
        'required'    => false,
        'class'       => array( 'form-row-wide', 'vat-number-field' ),
        'placeholder' => __( 'Enter VAT / Tax number (company only)', 'woocomproduct' ),
        'priority'    => 125,
    );

    return $fields;
}

/**
 * Save and display Business Type & VAT for orders
 */
add_action( 'woocommerce_checkout_update_order_meta', 'woocomproduct_save_business_fields' );
function woocomproduct_save_business_fields( $order_id ) {
    if ( isset( $_POST['billing_business_type'] ) ) {
        update_post_meta( $order_id, '_billing_business_type', sanitize_text_field( wp_unslash( $_POST['billing_business_type'] ) ) );
    }

    if ( isset( $_POST['billing_vat_number'] ) && $_POST['billing_vat_number'] !== '' ) {
        update_post_meta( $order_id, '_billing_vat_number', sanitize_text_field( wp_unslash( $_POST['billing_vat_number'] ) ) );
    } else {
        delete_post_meta( $order_id, '_billing_vat_number' );
    }
}

/**
 * Countries that require VAT when Business Type is Company.
 * Default: Sri Lanka (LK). Use the filter `woocomproduct_vat_required_countries` to change.
 */
function woocomproduct_get_vat_required_countries() {
    return apply_filters( 'woocomproduct_vat_required_countries', array( 'LK' ) );
}

/**
 * Validate VAT number on checkout when Company + specific countries
 */
add_action( 'woocommerce_checkout_process', 'woocomproduct_validate_vat_number' );
function woocomproduct_validate_vat_number() {
    if ( isset( $_POST['billing_business_type'] ) && 'company' === sanitize_text_field( wp_unslash( $_POST['billing_business_type'] ) ) ) {
        $country = isset( $_POST['billing_country'] ) ? sanitize_text_field( wp_unslash( $_POST['billing_country'] ) ) : '';
        $required = woocomproduct_get_vat_required_countries();
        if ( in_array( $country, $required, true ) ) {
            if ( empty( $_POST['billing_vat_number'] ) ) {
                wc_add_notice( __( 'VAT Number is required for Company billing addresses in your country.', 'woocomproduct' ), 'error' );
            }
        }
    }
}

add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocomproduct_display_business_type_in_admin', 10, 1 );
function woocomproduct_display_business_type_in_admin( $order ) {
    $business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
    if ( $business_type ) {
        $label = $business_type === 'company' ? __( 'Company', 'woocomproduct' ) : __( 'Individual', 'woocomproduct' );
        echo '<p><strong>' . esc_html__( 'Business Type', 'woocomproduct' ) . ':</strong> ' . esc_html( $label ) . '</p>';
    }

    $vat = get_post_meta( $order->get_id(), '_billing_vat_number', true );
    if ( $vat ) {
        echo '<p><strong>' . esc_html__( 'VAT Number', 'woocomproduct' ) . ':</strong> ' . esc_html( $vat ) . '</p>';
    }
}

/**
 * Include fields on the thank you / order received and view order pages
 */
add_action( 'woocommerce_thankyou', 'woocomproduct_display_business_fields_on_thankyou', 20 );
add_action( 'woocommerce_view_order', 'woocomproduct_display_business_fields_on_thankyou', 20 );
function woocomproduct_display_business_fields_on_thankyou( $order_id ) {
    if ( is_object( $order_id ) && method_exists( $order_id, 'get_id' ) ) {
        $order = $order_id;
        $order_id = $order->get_id();
    }

    $business_type = get_post_meta( $order_id, '_billing_business_type', true );
    $vat = get_post_meta( $order_id, '_billing_vat_number', true );

    echo '<section class="woocomproduct-business-fields">';
    if ( $business_type ) {
        $label = $business_type === 'company' ? __( 'Company', 'woocomproduct' ) : __( 'Individual', 'woocomproduct' );
        echo '<p><strong>' . esc_html__( 'Business Type', 'woocomproduct' ) . ':</strong> ' . esc_html( $label ) . '</p>';
    }

    if ( $vat ) {
        echo '<p><strong>' . esc_html__( 'VAT Number', 'woocomproduct' ) . ':</strong> ' . esc_html( $vat ) . '</p>';
    }
    echo '</section>';
}

/**
 * Add Business Type and VAT Number to order emails
 */
add_filter( 'woocommerce_email_order_meta_fields', 'woocomproduct_add_business_fields_to_emails', 10, 3 );
function woocomproduct_add_business_fields_to_emails( $fields, $sent_to_admin, $order ) {
    $business = get_post_meta( $order->get_id(), '_billing_business_type', true );
    $vat = get_post_meta( $order->get_id(), '_billing_vat_number', true );

    if ( $business ) {
        $fields['billing_business_type'] = array(
            'label' => __( 'Business Type', 'woocomproduct' ),
            'value' => $business === 'company' ? __( 'Company', 'woocomproduct' ) : __( 'Individual', 'woocomproduct' ),
        );
    }

    if ( $vat ) {
        $fields['billing_vat_number'] = array(
            'label' => __( 'VAT Number', 'woocomproduct' ),
            'value' => $vat,
        );
    }

    return $fields;
}
