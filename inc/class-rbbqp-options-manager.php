<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Rbbqp_Options_Manager {

    private static $instance;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private $prefix;

    private $options;

    private $use_roles_wp_option_name;
    private $use_total_cart_qty_wp_option_name;
    private $cart_total_same_item_only_name;
    private $allow_for_guest_wp_option_name;
    private $debug_mode_wp_option_name;

    private $display_product_total;
    
    public function __construct() {

        $this->prefix = 'rbbqp_';

        $this->options = get_option( $this->prefix . 'options' );
        if ( !$this->options )
            $this->options = array();

        $this->use_roles_wp_option_name = 'use_roles';
        $this->use_total_cart_qty_wp_option_name = 'use_total_cart_qty';
        $this->cart_total_same_item_only_name = 'cart_total_same_item_only';
        $this->allow_for_guest_wp_option_name = 'allow_for_guest';
        $this->debug_mode_wp_option_name = 'debug_mode';
        $this->display_product_total = "display_product_total";
    }

    private function get_value_or_default( $key, $default = false ) {
        if ( array_key_exists( $key, $this->options ) ) {
            return $this->options[$key];
        }
        return $default;
    }

    private function set_value( $key, $value ) {
        $this->options[$key] = $value;
        update_option( $this->prefix . 'options', $this->options );        
    }

    public function get_use_roles_option() {
        return $this->get_value_or_default( $this->use_roles_wp_option_name );
    }

    public function set_use_roles_option( $new_value ) {
        $this->set_value( $this->use_roles_wp_option_name, $new_value );
    }

    public function get_use_total_cart_qty_option() {
        return $this->get_value_or_default( $this->use_total_cart_qty_wp_option_name );
    }

    public function set_use_total_cart_qty_option( $new_value ) {
        $this->set_value( $this->use_total_cart_qty_wp_option_name, $new_value );
    }

    public function get_cart_total_same_item_only_option() {
        return $this->get_value_or_default( $this->cart_total_same_item_only_name );
    }

    public function set_cart_total_same_item_only_option( $new_value ) {
        $this->set_value( $this->cart_total_same_item_only_name, $new_value );
    }

    public function get_allow_for_guest_option() {
        return $this->get_value_or_default( $this->allow_for_guest_wp_option_name );
    }

    public function set_allow_for_guest_option( $new_value ) {
        $this->set_value( $this->allow_for_guest_wp_option_name, $new_value );
    }

    public function get_debug_mode_option() {
        return $this->get_value_or_default( $this->debug_mode_wp_option_name );
    }

    public function set_debug_mode_option( $new_value ) {
        $this->set_value( $this->debug_mode_wp_option_name, $new_value );
    }

    public function get_display_product_total_option() {
        return $this->get_value_or_default( $this->display_product_total );
    }

    public function set_display_product_total_option( $new_value ) {
        $this->set_value( $this->display_product_total, $new_value );
    }
}