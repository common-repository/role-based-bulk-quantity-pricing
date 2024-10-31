<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc;
use Role_Based_Bulk_Quantity_Pricing\Inc\Helpers;
use Role_Based_Bulk_Quantity_Pricing\Inc\Helpers\Models;

class Rbbqp_Product_Helper {

    private $options_manager;
    private $rbbqp_cart_helper;
    private $user_helpers;
    private $price_helpers;    
    private $roles;

    /**
     * Options
     */
    private $debug_mode;
    private $display_product_total;

    public function __construct() {

        add_action( 'woocommerce_single_product_summary', array( $this, 'total_product_price' ), 31 );
        add_action( 'woocommerce_get_price_html', array( $this, 'total_product_price_html' ), 99, 2 );

        add_filter('safe_style_css', array( $this, 'add_display_as_safe_style_css' ), 9999, 1 );

        $this->rbbqp_cart_helper = new Rbbqp_Cart_Helper();
        $this->options_manager = Inc\Rbbqp_Options_Manager::get_instance();
        $this->user_helpers = new Helpers\Rbbqp_User_Helpers();
        $this->price_helpers = new Inc\Rbbqp_Price_Helper();

        $this->roles = $this->user_helpers->get_user_roles();

        $this->debug_mode = $this->options_manager->get_debug_mode_option();

        $this->display_product_total = $this->options_manager->get_display_product_total_option();
    }

    public function add_display_as_safe_style_css( $styles ) {
        $styles[] = 'display';
        return $styles;
    }

    /**
     * Prices displayed on product page.
     */
    public function total_product_price() {

        if ( is_admin() || defined('REST_REQUEST') ) 
            return;

        global $woocommerce, $product;
        $product_type = $product->get_type();

        $json_ob = array();

        if ( $product->is_type( 'variable' ) ) {

            $variations = $product->get_available_variations('objects');

            foreach ( $variations as $key => $value ) {
                $pricing_lines = $this->price_helpers->get_all_pricings_for_item( $value->get_id(), $value );

                if ( empty( $pricing_lines ) )
                    continue;

                $json_ob = $this->add_pricing_to_json_obj( $json_ob, $pricing_lines, $value->get_id(), $value );
            }

            if ( empty( $json_ob ) )
                return;

            ?> 
            <script>
                rbbqp_updateCurrentPrice( '<?php echo esc_js( $product_type ); ?>', '<?php echo esc_js( $product->get_price_suffix() ); ?>', -1 );            
            </script>
            <?php
        } else {

            $pricing_lines = $this->price_helpers->get_all_pricings_for_item( $product->get_id(), $product );

            if ( empty( $pricing_lines ) )
                return;
            
            $json_ob = $this->add_pricing_to_json_obj( $json_ob, $pricing_lines, $product->get_id(), $product );
            ?> 
            <script>        
                rbbqp_updateCurrentPrice( '<?php echo esc_js( $product_type ); ?>', '<?php echo esc_js( $product->get_price_suffix() ); ?>', '<?php echo esc_js( $product->get_id() ); ?>' );    
            </script>
            <?php

        }

        $customPricingTable_html = sprintf( '<div style="display:none;" id="customPricingTable" data-json="' . htmlspecialchars( json_encode( $json_ob ), ENT_QUOTES, 'UTF-8' ) . '"></div>' );
        echo wp_kses( 
            $customPricingTable_html, 
            array( 
                'div' => array(
                    'id' => array(),
                    'data-json' => array(),
                    'style' => array(),
                ) ) );

        if ( $this->display_product_total ) {

            $price_suffix_span = '<span class="rbbqp_price_suffix">' . $product->get_price_suffix() . '</span>';

            echo wp_kses( 
                sprintf( '<div id="product_total_price">%s %s</div>', __( 'Order Total:', 'role-based-bulk-quantity-pricing' ), '<span class="price">--</span>' . $price_suffix_span ),
                array( 
                    'div' => array(
                        'id' => array(),
                        'style' => array(),
                    ), 
                    'span' => array(
                        'class' => array()
                    ) 
                ) );
        }
        
        $this->maybe_print_debug_data();
    }

    /**
     * If debug mode is set to TRUE then some useful information will be displayed on the product page.
     */
    private function maybe_print_debug_data() {

        if ( $this->debug_mode != '1' )
            return;
            
        echo wp_kses( 
            sprintf('<div id="user_assigned_roles_debug" class="rbbqp_debug-message">%s %s %s</div>', __('Assigned Roles','role-based-bulk-quantity-pricing'), ':', print_r( $this->roles, true ) ),
            array( 
                'div' => array(
                    'id' => array(),
                    'class' => array(),
                    'data-json' => array(),
                    'style' => array(),
                ) )
        );
    }

    /**
     * Override the price HTML for products.
     */
    public function total_product_price_html( $price, $product_obj ) {

        if ( is_admin() || defined('REST_REQUEST') ) 
            return $price;

        global $product;

        $product_type = $product->get_type();

        // TODO: Loop through pricing lines for simple product, so we can also show a "From..." if necessary.
        if ( 'variable' !== $product->get_type() || 
            'product_variation' === $product_obj->post_type ) {

            $calculated_price = $this->price_helpers->get_item_price( $product_obj, 1 );
            $price_suffix_span = '<span class="rbbqp_price_suffix">' . $product->get_price_suffix() . '</span>';
            $price = wc_price( $calculated_price ) . ' ' . $price_suffix_span;

            return $price;
        }

        $variations = $product->get_available_variations('objects');

        $best_threshold = 1;
        $best_variation = $variations[0];
        $min_price = 99999;
        $max_price = 0;

        foreach ( $variations as $key => $value ) {
            $variation_price = $value->get_price();
            $pricing_lines = $this->price_helpers->get_all_pricings_for_item( $value->get_id(), $value );

            if ( is_array( $pricing_lines) && count ( $pricing_lines ) > 0 ) {

                for ( $x = 0; $x < count( $pricing_lines ); $x++ ) {
                
                    if ( $pricing_lines[$x]->threshold_unit_price < $min_price ) {
                        $min_price = $pricing_lines[$x]->threshold_unit_price;
                        $best_threshold = $pricing_lines[$x]->threshold_min_qty;
                        $best_variation = $value;
                    }
                }
            } else {
                /** In case this variation is not defined on the custom pricing lines. */
                if ( $variation_price < $min_price ) {
                    $min_price      = $variation_price;
                    $best_variation = $value;
                    $best_threshold = 1;
                }
            }
        }

        $price_suffix_span = '<span class="rbbqp_price_suffix">' . $product->get_price_suffix() . '</span>';

        $price = sprintf( 
            __( 'From: %s %s', 'role-based-bulk-quantity-pricing' ), 
            wc_price( $this->price_helpers->get_item_price( $best_variation, $best_threshold ) ),
            $price_suffix_span );

        return apply_filters( 'rbbqp_get_product_price_html', $price );
    }

    private function add_pricing_to_json_obj( $json_ob, $pricing_lines, $item_id, $item ) {

        if ( is_array( $item ) ) {
            $price = $item['display_price'];
        } else {
            $price = $item->get_price();
        }

        $initial_quantity = $this->rbbqp_cart_helper->get_item_quantity( $item_id, 0 );

        if ( is_null( $pricing_lines ) || empty( $pricing_lines ) ) {
            $json_ob[$item_id] = array( array( 
                'threshold_min_qty' => 0, 
                'base_unit_price' => $price, 
                'threshold_unit_price' => $price,
                'initial_quantity' => $initial_quantity ) );
        } else {
            
            foreach ( $pricing_lines as $key => $pricing_line ) {
                $pricing_line->initial_quantity = $initial_quantity;
            }

            $json_ob[$item_id] = $pricing_lines;
        }

        return $json_ob;
    }
}