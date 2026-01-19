<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Theme Customizer settings for automatic discounts
 */
function woocomproduct_customize_register_discounts( $wp_customize ) {
    $wp_customize->add_section( 'woocomproduct_discounts', array(
        'title'       => __( 'Discounts', 'woocomproduct' ),
        'priority'    => 160,
        'description' => __( 'Automatic discount settings', 'woocomproduct' ),
    ) );

    $wp_customize->add_setting( 'woocomproduct_discount_threshold', array(
        'default'           => 20000,
        'sanitize_callback' => 'woocomproduct_sanitize_number',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'woocomproduct_discount_threshold', array(
        'label'       => __( 'Discount threshold (subtotal)', 'woocomproduct' ),
        'section'     => 'woocomproduct_discounts',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 0, 'step' => 0.01 ),
    ) );

    $wp_customize->add_setting( 'woocomproduct_discount_percentage', array(
        'default'           => 20,
        'sanitize_callback' => 'woocomproduct_sanitize_number',
        'transport'         => 'refresh',
    ) );

    $wp_customize->add_control( 'woocomproduct_discount_percentage', array(
        'label'       => __( 'Discount percentage (%)', 'woocomproduct' ),
        'section'     => 'woocomproduct_discounts',
        'type'        => 'number',
        'input_attrs' => array( 'min' => 0, 'max' => 100, 'step' => 0.1 ),
    ) );
}
add_action( 'customize_register', 'woocomproduct_customize_register_discounts' );

function woocomproduct_sanitize_number( $val ) {
    if ( $val === '' || $val === null ) return '';
    return floatval( $val );
}
