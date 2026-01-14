<?php
// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Location-based shipping method
 *
 * Rates (LKR):
 *  - Sri Lanka (LK): 500
 *  - Asia (excluding LK): 1,500
 *  - Other: 3,000
 *
 * Filters:
 *  - `woocomproduct_location_shipping_cost` (cost, $package)
 */

add_action( 'woocommerce_shipping_init', 'woocomproduct_location_shipping_init' );
function woocomproduct_location_shipping_init() {
    class WC_Woocomproduct_Location_Shipping extends WC_Shipping_Method {
        public function __construct( $instance_id = 0 ) {
            $this->id                 = 'woocomproduct_location_shipping';
            $this->instance_id        = $instance_id;
            $this->method_title       = __( 'Location-based Shipping', 'woocomproduct' );
            $this->method_description = __( 'Simple flat rates based on destination region (LK, Asia, Other).', 'woocomproduct' );

            $this->supports = array(
                'shipping-zones',
                'instance-settings',
            );

            $this->init();
        }

        public function init() {
            $this->init_form_fields();
            $this->init_settings();

            $this->title   = $this->get_option( 'title', $this->method_title );
            $this->enabled = $this->get_option( 'enabled', 'yes' );

            add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        }

        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title'   => __( 'Enable/Disable', 'woocomproduct' ),
                    'type'    => 'checkbox',
                    'default' => 'yes',
                ),
                'title' => array(
                    'title'   => __( 'Method Title', 'woocomproduct' ),
                    'type'    => 'text',
                    'default' => $this->method_title,
                ),
            );
        }

        public function calculate_shipping( $package = array() ) {
            $country = isset( $package['destination']['country'] ) ? $package['destination']['country'] : '';

            // Default to "Other"
            $cost = 3000;

            if ( 'LK' === $country ) {
                $cost = 500;
            } else {
                // Broad list of Asian ISO country codes (excludes LK above)
                $asia = array( 'AF','AM','AZ','BH','BD','BT','BN','KH','CN','CY','GE','HK','IN','ID','IR','IQ','IL','JP','JO','KZ','KW','KG','LA','LB','MO','MY','MV','MN','MM','NP','KP','OM','PK','PS','PH','QA','SA','SG','KR','SY','TW','TJ','TH','TR','TM','AE','UZ','VN','YE' );
                if ( in_array( $country, $asia, true ) ) {
                    $cost = 1500;
                }
            }

            $cost = apply_filters( 'woocomproduct_location_shipping_cost', $cost, $package );

            $rate = array(
                'id'       => $this->id . ':' . $this->instance_id,
                'label'    => $this->title,
                'cost'     => $cost,
                'calc_tax' => 'per_item',
            );

            $this->add_rate( $rate );
        }
    }
}

add_filter( 'woocommerce_shipping_methods', 'woocomproduct_register_location_shipping_method' );
function woocomproduct_register_location_shipping_method( $methods ) {
    $methods['woocomproduct_location_shipping'] = 'WC_Woocomproduct_Location_Shipping';
    return $methods;
}
