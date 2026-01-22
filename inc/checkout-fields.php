<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Custom Checkout Fields: Business Type & VAT Number
 * 
 * - Business Type: dropdown (Individual | Company)
 * - VAT Number: text input, shown only when Country is selected, required when Business Type = Company
 */

/**
 * Register custom checkout fields via filter
 */
add_filter( 'woocommerce_checkout_fields', 'woocomproduct_add_custom_checkout_fields' );
function woocomproduct_add_custom_checkout_fields( $fields ) {
    // Add Business Type field (after billing company)
    $fields['billing']['billing_business_type'] = array(
        'type'        => 'select',
        'label'       => __( 'Business Type', 'woocomproduct' ),
        'placeholder' => __( 'Select business type', 'woocomproduct' ),
        'required'    => true,
        'options'     => array(
            ''           => __( '— Select —', 'woocomproduct' ),
            'individual' => __( 'Individual', 'woocomproduct' ),
            'company'    => __( 'Company', 'woocomproduct' ),
        ),
        'priority'    => 45, // After company
    );

    // Add VAT Number field (after Business Type)
    $fields['billing']['billing_vat_number'] = array(
        'type'        => 'text',
        'label'       => __( 'VAT Number', 'woocomproduct' ),
        'placeholder' => __( 'Enter VAT number', 'woocomproduct' ),
        'required'    => false, // Made conditional by JS and validation hook
        'priority'    => 46,
        'class'       => array( 'form-row-full', 'woocomproduct-vat-field' ),
    );

    return $fields;
}

/**
 * Enqueue frontend JS to handle VAT field visibility and validation
 */
add_action( 'wp_enqueue_scripts', 'woocomproduct_enqueue_checkout_js' );
function woocomproduct_enqueue_checkout_js() {
    if ( is_checkout() ) {
        wp_enqueue_script(
            'woocomproduct-checkout-fields',
            get_template_directory_uri() . '/assets/js/checkout-fields.js',
            array( 'jquery' ),
            wp_get_theme()->get( 'Version' ),
            true
        );
    }
}

/**
 * Server-side validation for checkout fields
 */
add_action( 'woocommerce_checkout_process', 'woocomproduct_validate_checkout_fields' );
function woocomproduct_validate_checkout_fields() {
    // Validate Business Type is selected
    if ( empty( $_POST['post_data'] ) ) {
        return;
    }

    parse_str( $_POST['post_data'], $post_data );

    // Business Type is required
    if ( empty( $post_data['billing_business_type'] ) ) {
        wc_add_notice(
            __( 'Business Type is required.', 'woocomproduct' ),
            'error'
        );
    }

    // VAT Number validation: required when Business Type = Company AND Country is selected
    $business_type = ! empty( $post_data['billing_business_type'] ) ? sanitize_text_field( $post_data['billing_business_type'] ) : '';
    $country = ! empty( $post_data['billing_country'] ) ? sanitize_text_field( $post_data['billing_country'] ) : '';
    $vat_number = ! empty( $post_data['billing_vat_number'] ) ? sanitize_text_field( $post_data['billing_vat_number'] ) : '';

    if ( 'company' === $business_type && ! empty( $country ) && empty( $vat_number ) ) {
        wc_add_notice(
            __( 'VAT Number is required when Business Type is Company and country is selected.', 'woocomproduct' ),
            'error'
        );
    }

    // Optional: basic VAT format validation (simple pattern)
    if ( ! empty( $vat_number ) ) {
        if ( ! preg_match( '/^[A-Z0-9]{5,}$/', $vat_number ) ) {
            wc_add_notice(
                __( 'VAT Number format is invalid. Must be at least 5 alphanumeric characters.', 'woocomproduct' ),
                'error'
            );
        }
    }
}

/**
 * Save custom checkout fields to order meta
 */
add_action( 'woocommerce_checkout_update_order_meta', 'woocomproduct_save_checkout_fields' );
function woocomproduct_save_checkout_fields( $order_id ) {
    if ( ! empty( $_POST['post_data'] ) ) {
        parse_str( $_POST['post_data'], $post_data );

        // Save Business Type
        if ( ! empty( $post_data['billing_business_type'] ) ) {
            $business_type = sanitize_text_field( $post_data['billing_business_type'] );
            update_post_meta( $order_id, '_billing_business_type', $business_type );
        }

        // Save VAT Number
        if ( ! empty( $post_data['billing_vat_number'] ) ) {
            $vat_number = sanitize_text_field( $post_data['billing_vat_number'] );
            update_post_meta( $order_id, '_billing_vat_number', $vat_number );
        }
    }
}

/**
 * Display custom fields in WooCommerce admin order view
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocomproduct_display_admin_order_meta', 10, 1 );
function woocomproduct_display_admin_order_meta( $order ) {
    $order_id = $order->get_id();
    $business_type = get_post_meta( $order_id, '_billing_business_type', true );
    $vat_number = get_post_meta( $order_id, '_billing_vat_number', true );

    if ( $business_type || $vat_number ) {
        echo '<div style="clear:both; margin-top: 1rem; border-top: 1px solid #ccc; padding-top: 1rem;">';
        
        if ( $business_type ) {
            echo '<p>';
            echo '<strong>' . esc_html__( 'Business Type:', 'woocomproduct' ) . '</strong> ';
            echo esc_html( ucfirst( $business_type ) );
            echo '</p>';
        }

        if ( $vat_number ) {
            echo '<p>';
            echo '<strong>' . esc_html__( 'VAT Number:', 'woocomproduct' ) . '</strong> ';
            echo esc_html( $vat_number );
            echo '</p>';
        }

        echo '</div>';
    }
}

/**
 * Display custom fields in order emails
 */
add_filter( 'woocommerce_email_order_meta_fields', 'woocomproduct_email_order_meta_fields', 10, 3 );
function woocomproduct_email_order_meta_fields( $fields, $sent_to_admin, $order ) {
    $order_id = $order->get_id();
    $business_type = get_post_meta( $order_id, '_billing_business_type', true );
    $vat_number = get_post_meta( $order_id, '_billing_vat_number', true );

    if ( $business_type ) {
        $fields['business_type'] = array(
            'label' => __( 'Business Type', 'woocomproduct' ),
            'value' => ucfirst( $business_type ),
        );
    }

    if ( $vat_number ) {
        $fields['vat_number'] = array(
            'label' => __( 'VAT Number', 'woocomproduct' ),
            'value' => $vat_number,
        );
    }

    return $fields;
}

/**
 * Optionally: display custom fields on order view page
 */
add_action( 'woocommerce_order_details_after_order_table', 'woocomproduct_display_order_details_custom_fields', 10, 1 );
function woocomproduct_display_order_details_custom_fields( $order ) {
    if ( is_user_logged_in() && get_current_user_id() === $order->get_user_id() ) {
        $order_id = $order->get_id();
        $business_type = get_post_meta( $order_id, '_billing_business_type', true );
        $vat_number = get_post_meta( $order_id, '_billing_vat_number', true );

        if ( $business_type || $vat_number ) {
            echo '<h2>' . esc_html__( 'Business Information', 'woocomproduct' ) . '</h2>';
            echo '<dl>';
            
            if ( $business_type ) {
                echo '<dt>' . esc_html__( 'Business Type:', 'woocomproduct' ) . '</dt>';
                echo '<dd>' . esc_html( ucfirst( $business_type ) ) . '</dd>';
            }

            if ( $vat_number ) {
                echo '<dt>' . esc_html__( 'VAT Number:', 'woocomproduct' ) . '</dt>';
                echo '<dd>' . esc_html( $vat_number ) . '</dd>';
            }

            echo '</dl>';
        }
    }
}
