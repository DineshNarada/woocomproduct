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

    return $fields;
}

/**
 * Display Business Type in admin order billing address panel for quick reference
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocomproduct_display_business_type_in_admin', 10, 1 );
function woocomproduct_display_business_type_in_admin( $order ) {
    $business_type = get_post_meta( $order->get_id(), '_billing_business_type', true );
    if ( $business_type ) {
        $label = $business_type === 'company' ? __( 'Company', 'woocomproduct' ) : __( 'Individual', 'woocomproduct' );
        echo '<p><strong>' . esc_html__( 'Business Type', 'woocomproduct' ) . ':</strong> ' . esc_html( $label ) . '</p>';
    }
}
