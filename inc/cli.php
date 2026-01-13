<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Simple WP-CLI helpers for this theme
if ( defined( 'WP_CLI' ) && WP_CLI ) {
    class WooComProduct_CLI {
        /**
         * Get a product's _local_imported meta
         *
         * Usage: wp woocomproduct meta get <product_id>
         */
        public function meta( $args, $assoc ) {
            if ( empty( $args ) ) {
                WP_CLI::error( 'Product ID is required.' );
            }

            $subcommand = isset( $args[0] ) ? $args[0] : '';

            if ( 'get' === $subcommand ) {
                $product_id = isset( $args[1] ) ? (int) $args[1] : 0;
                if ( ! $product_id ) {
                    WP_CLI::error( 'Invalid product ID.' );
                }
                $value = get_post_meta( $product_id, '_local_imported', true );
                if ( $value ) {
                    WP_CLI::success( "_local_imported: $value" );
                } else {
                    WP_CLI::success( '_local_imported: (not set)' );
                }
            } else {
                WP_CLI::error( 'Unknown subcommand. Use: wp woocomproduct meta get <product_id>' );
            }
        }
    }

    WP_CLI::add_command( 'woocomproduct', 'WooComProduct_CLI' );
}
