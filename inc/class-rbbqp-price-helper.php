<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc;
use Role_Based_Bulk_Quantity_Pricing\Inc\Helpers;
use Automattic\WooCommerce\Utilities\NumberUtil;

class Rbbqp_Price_Helper {

    private $data_accessor;
    private $user_helpers;
    private $roles;
    private $number_utils;

    public function __construct() {
        $this->data_accessor = new Inc\Rbbqp_Data_Accessor();
        $this->user_helpers = new Helpers\Rbbqp_User_Helpers();
        $this->roles = $this->user_helpers->get_user_roles();
        $this->number_utils = new NumberUtil();
    }

    public function get_item_price( $product, $quantity ) {

        if ( is_admin() || defined('REST_REQUEST') ) 
            return $product->get_price();

        $pricing_line = $this->data_accessor->getPricingLine( $product->get_id(), $quantity, $this->roles );

        $line_price = $product->get_price();

        if ( ! is_null( $pricing_line ) && $pricing_line != '' ) {
            if ( $quantity >= (float)$pricing_line->threshold_min_qty ) {
                $line_price = (float) $pricing_line->threshold_unit_price;
            }
        }

        $display_context = 'shop'; // TODO: Allow for 'cart' context.
        $tax_display = get_option(
            'cart' === $display_context ? 'woocommerce_tax_display_cart' : 'woocommerce_tax_display_shop'
        );

        $return_price = 'incl' === $tax_display ?
            wc_get_price_including_tax(
                $product,
                array(
                    'qty'   => $quantity,
                    'price' => $line_price,
                )
            ) :
            wc_get_price_excluding_tax(
                $product,
                array(
                    'qty'   => $quantity,
                    'price' => $line_price,
                )
            );

        if ( $quantity == 0 ) {
            return $this->number_utils->round( $return_price, wc_get_price_decimals() );
        }

        return $this->number_utils->round( $return_price / $quantity, wc_get_price_decimals() );
    }

    public function get_all_pricings_for_item( $item_id, $product ) {

        $pricing_lines = $this->data_accessor->getPricingLines( $item_id, $this->roles );

        if ( empty( $pricing_lines ) || $pricing_lines === '' )
            return array();

        foreach ( $pricing_lines as $pricing_line_key => $pricing_line_item ) {
             $pricing_line_item->threshold_unit_price = $this->get_item_price( $product, $pricing_line_item->threshold_min_qty );
             $pricing_line_item->base_unit_price      = $this->get_item_price( $product, 1 );
        }

        return $pricing_lines;
    }
}