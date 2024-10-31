<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc;
use Role_Based_Bulk_Quantity_Pricing\Inc\Helpers;

/**
 * Helper functions for the Cart.
 */
class Rbbqp_Cart_Helper {

    private $data_accessor;
    private $options_manager;
    private $user_helpers;
    private $price_helper;
    private $use_total_cart_qty;
    private $same_product_only;
    private $roles;

    public function __construct() {

        $this->data_accessor = new Rbbqp_Data_Accessor();
        $this->options_manager = Inc\Rbbqp_Options_Manager::get_instance();
        $this->user_helpers = new Helpers\Rbbqp_User_Helpers();
        $this->price_helper = new Inc\Rbbqp_Price_Helper();

        $this->use_total_cart_qty = $this->options_manager->get_use_total_cart_qty_option();
        $this->same_product_only = $this->options_manager->get_cart_total_same_item_only_option();
        $this->roles = $this->user_helpers->get_user_roles();

        add_action( 'woocommerce_before_calculate_totals', array( $this, 'change_product_price_cart' ), 9999, 1 );
        add_filter( 'woocommerce_cart_item_price', array( $this, 'cart_item_unit_price' ), 10, 3 );
    }

    public function change_product_price_cart( $cart ) {

        if ( is_admin() && ! defined( 'DOING_AJAX' ) )
            return;

        if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
            return;

        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
            // If simple -> Product ID
            // If variable -> Variation ID
            $item_id = $cart_item['data']->get_id();
            $product = wc_get_product( $item_id );

            $quantity = $this->get_item_quantity( $item_id,  $cart_item['quantity'] );

            $custom_unit_price = $this->price_helper->get_item_price( $cart_item['data'], $quantity);
            $cart_item['data']->set_price( $custom_unit_price );
        }

        do_action( 'rbbqp_cart_before_calculate_totals', $cart );
    }

    public function get_item_quantity( $item_id, $qty ) {

        if ( $this->use_total_cart_qty == "1" ) {
            return $this->get_total_bulk_quantity( $this->roles, $item_id, $this->same_product_only );
        } 

        return $qty;

    }

    /**
     * Gets the cart total quantity. Only items with a bulk definition for the current
     *  role will count towards the cart total quantity.
     * 
     * @param array $roles Roles of the current logged in user.
     * @param int $item_id The ID of the item we're checking. Can be a product or variation ID.
     * @param bool $same_product_only If set to true only similar product will count towards the total bulk qty.
     * 
     * @return int Total cart quantity.
     */
    private function get_total_bulk_quantity( $roles, $item_id, $same_product_only = false ) {

        $cart = WC()->cart;
        
        if ( $same_product_only ) {
            $allowed_variations = array( $item_id );
        } else {
            $allowed_variations = $this->data_accessor->getAllVariationsForRole( $roles );
        }

        $quantity = 0;
    
        foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {

            $cart_item_id = $cart_item['variation_id'] == 0 ? $cart_item['product_id'] : $cart_item['variation_id'];
            if ( in_array( $cart_item_id, $allowed_variations ) ||  $item_id == $cart_item_id ) {
                $quantity += $cart_item['quantity'];
            }
    
        }

        return $quantity;
    }

    /**
     * Used for mini-cart
     */
    public function cart_item_unit_price( $old_display, $cart_item, $cart_item_key ) {
        $quantity = $this->get_item_quantity( $cart_item['data']->get_id(),  $cart_item['quantity'] );
        $price_with_qty = wc_price( $this->price_helper->get_item_price( $cart_item['data'], $quantity ) );
        return apply_filters( 'rbbqp_cart_item_price', $price_with_qty, $cart_item, $cart_item_key );
    }

}