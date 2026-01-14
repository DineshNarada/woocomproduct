<?php
/**
 * PHPUnit integration test: Shipping, Discount and Tax combinations
 *
 * Run with the WP and WooCommerce PHPUnit harness (e.g., wootests).
 *
 * @group woocommerce
 * @group shipping
 */

class WC_Tests_Shipping_Discount extends WP_UnitTestCase {

    protected $tax_rate_id;
    protected $product_ids = array();

    public function setUp(): void {
        parent::setUp();

        if ( ! class_exists( 'WooCommerce' ) ) {
            $this->markTestSkipped( 'WooCommerce is not active.' );
        }

        // Ensure session and cart exist
        if ( empty( WC()->session ) ) {
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
        }

        if ( empty( WC()->cart ) ) {
            WC()->cart = new WC_Cart();
        }

        // Add a global tax rate for testing (15%)
        global $wpdb;
        $data = array(
            'tax_rate_country' => '',
            'tax_rate'         => '15.0000',
            'tax_rate_name'    => 'GLOBAL TEST',
            'tax_rate_priority'=> 1,
            'tax_rate_class'   => '',
        );
        $table = $wpdb->prefix . 'woocommerce_tax_rates';
        $wpdb->insert( $table, $data );
        $this->tax_rate_id = $wpdb->insert_id;

        // Refresh tax caches
        WC_Tax::get_tax_rates();
    }

    public function tearDown(): void {
        // Cleanup products
        foreach ( $this->product_ids as $pid ) {
            wp_delete_post( $pid, true );
        }
        $this->product_ids = array();

        // Remove test tax rate
        if ( $this->tax_rate_id ) {
            global $wpdb;
            $table = $wpdb->prefix . 'woocommerce_tax_rates';
            $wpdb->delete( $table, array( 'tax_rate_id' => $this->tax_rate_id ) );
            $this->tax_rate_id = null;
        }

        // Empty cart and reset
        if ( ! empty( WC()->cart ) ) {
            WC()->cart->empty_cart();
        }

        parent::tearDown();
    }

    protected function create_product( $price = '10000' ) {
        $post_id = $this->factory->post->create( array(
            'post_title'  => 'Test Product ' . wp_generate_password( 6, false ),
            'post_type'   => 'product',
            'post_status' => 'publish',
        ) );

        update_post_meta( $post_id, '_price', $price );
        update_post_meta( $post_id, '_regular_price', $price );

        $this->product_ids[] = $post_id;

        return wc_get_product( $post_id );
    }

    public function test_shipping_and_discount_combinations() {
        $threshold = apply_filters( 'woocomproduct_discount_threshold', 20000 );

        $scenarios = array(
            array( 'label' => 'Sri Lanka - Below threshold (no discount)', 'country' => 'LK', 'price' => '10000', 'expected_discount' => false, 'expected_shipping' => 500 ),
            array( 'label' => 'Sri Lanka - Above threshold (discount applied)', 'country' => 'LK', 'price' => '25000', 'expected_discount' => true,  'expected_shipping' => 500 ),
            array( 'label' => 'Asia (IN) - Above threshold', 'country' => 'IN', 'price' => '25000', 'expected_discount' => true,  'expected_shipping' => 1500 ),
            array( 'label' => 'Other (US) - Above threshold', 'country' => 'US', 'price' => '25000', 'expected_discount' => true,  'expected_shipping' => 3000 ),
        );

        foreach ( $scenarios as $scenario ) {
            // Reset cart
            WC()->cart->empty_cart();

            $product = $this->create_product( $scenario['price'] );
            $added = WC()->cart->add_to_cart( $product->get_id(), 1 );
            $this->assertNotFalse( $added, 'Failed to add product to cart for scenario: ' . $scenario['label'] );

            // Set customer location
            WC()->customer->set_billing_country( $scenario['country'] );
            WC()->customer->set_shipping_country( $scenario['country'] );

            // Trigger totals calculation (this runs discounts and shipping calculations)
            WC()->cart->calculate_totals();

            // Shipping
            $shipping_total = floatval( WC()->cart->get_shipping_total() );
            $this->assertEquals( (float) $scenario['expected_shipping'], $shipping_total, "Shipping mismatch for: " . $scenario['label'] );

            // Discount fee
            $fees = WC()->cart->get_fees();
            $has_discount = false;
            foreach ( $fees as $fee ) {
                if ( false !== strpos( $fee->name, 'Automatic discount' ) ) {
                    $has_discount = true;
                    $this->assertLessThan( 0, floatval( $fee->amount ), 'Discount fee should be negative' );
                }
            }

            if ( $scenario['expected_discount'] ) {
                $this->assertTrue( $has_discount, 'Expected discount but none found for: ' . $scenario['label'] );
            } else {
                $this->assertFalse( $has_discount, 'Did not expect discount for: ' . $scenario['label'] );
            }

            // Basic sanity: total should equal subtotal + shipping + fees + tax
            $subtotal = floatval( WC()->cart->get_subtotal() );
            $tax_total = floatval( WC()->cart->get_taxes_total() );
            $total = floatval( str_replace( ',', '', WC()->cart->get_total( 'edit' ) ) );

            $fee_total = 0;
            foreach ( $fees as $fee ) {
                $fee_total += floatval( $fee->amount );
            }

            $expected_total = round( $subtotal + $shipping_total + $fee_total + $tax_total, wc_get_price_decimals() );
            $this->assertEquals( $expected_total, $total, 'Total mismatch for: ' . $scenario['label'] );
        }
    }
}
