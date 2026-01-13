<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Product meta: Local / Imported
 */

/**
 * Register product meta
 */
add_action( 'init', 'woocomproduct_register_product_meta' );
function woocomproduct_register_product_meta() {
    register_post_meta( 'product', '_local_imported', array(
        'single' => true,
        'type' => 'string',
        'show_in_rest' => true,
    ) );
}

/**
 * Add select field to the General tab of the Product data panel
 */
add_action( 'woocommerce_product_options_general_product_data', 'woocomproduct_add_local_imported_field' );
function woocomproduct_add_local_imported_field() {
    woocommerce_wp_select( array(
        'id'          => '_local_imported',
        'label'       => __( 'Local / Imported', 'woocomproduct' ),
        'options'     => array(
            ''         => __( '— Select —', 'woocomproduct' ),
            'local'    => __( 'Local', 'woocomproduct' ),
            'imported' => __( 'Imported', 'woocomproduct' ),
        ),
        'desc_tip'    => true,
        'description' => __( 'Mark whether this product is sourced locally or imported.', 'woocomproduct' ),
    ) );
}

/**
 * Save the value when the product is saved
 */
add_action( 'woocommerce_process_product_meta', 'woocomproduct_save_local_imported_field' );
function woocomproduct_save_local_imported_field( $post_id ) {
    if ( isset( $_POST['_local_imported'] ) ) {
        $value = sanitize_text_field( wp_unslash( $_POST['_local_imported'] ) );
        if ( '' === $value ) {
            delete_post_meta( $post_id, '_local_imported' );
        } else {
            update_post_meta( $post_id, '_local_imported', $value );
        }
    }
}

/**
 * Add a simple admin column to the Products list for quick QA
 */
add_filter( 'manage_edit-product_columns', 'woocomproduct_add_local_imported_column' );
function woocomproduct_add_local_imported_column( $columns ) {
    $columns['local_imported'] = __( 'Local / Imported', 'woocomproduct' );
    return $columns;
}

add_action( 'manage_product_posts_custom_column', 'woocomproduct_render_local_imported_column', 10, 2 );
function woocomproduct_render_local_imported_column( $column, $post_id ) {
    if ( 'local_imported' === $column ) {
        $value = get_post_meta( $post_id, '_local_imported', true );
        if ( $value ) {
            echo esc_html( ucfirst( $value ) );
        } else {
            echo '&mdash;';
        }
    }
}
