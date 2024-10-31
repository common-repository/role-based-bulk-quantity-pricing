<?php
/*
  Plugin Name: Role Based Bulk Quantity Pricing
  Description: Use a CSV file to set bulk quantity pricing by roles, for WooCommerce.
  Version: 1.2.3
  Author: Kevin Amorim
  Author URI: https://kevamorim.com
  Text Domain: role-based-bulk-quantity-pricing
  Domain Path: /languages
*/

// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    die;
}

if ( !defined( 'RBBQP_VERSION' ) ) {
    define( 'RBBQP_VERSION', '1.2.3' );
}

if ( ! function_exists( 'is_woocommerce_activated' ) ) {
    function is_woocommerce_activated() {
        if ( class_exists( 'woocommerce' ) ) {
            return true;
        } else {
            return false;
        }
    }
}

use Role_Based_Bulk_Quantity_Pricing\Inc\Admin;
use Role_Based_Bulk_Quantity_Pricing\Inc\Data;
use Role_Based_Bulk_Quantity_Pricing\Inc;

require_once( trailingslashit( dirname( __FILE__ ) ) . 'inc/autoloader.php' );

if ( !function_exists( 'rbbqp_log_entry' ) ) {
    function rbbqp_log_entry( $entry, $mode = 'a', $file = 'rbbqp_logs' ) {

        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];

        if ( is_array( $entry ) ) {
            $entry = json_encode( $entry );
        }

        $file = $upload_dir . '/' . $file . '.log';
        $file = fopen( $file, $mode );
        $bytes = fwrite( $file, current_time( 'mysql' ) . '::' . $entry . "\n" );
        fclose( $file );
        return $bytes;
    }
}

function rbbqp_load_plugin_textdomain() {
    load_plugin_textdomain( 'role-based-bulk-quantity-pricing', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
}
add_action( 'plugins_loaded', 'rbbqp_load_plugin_textdomain' );

function rbbqp_enqueue_script() {

    if ( !is_admin() ) {

        wp_enqueue_script( 
            'rbbqp-front-script', 
            plugin_dir_url( __FILE__ ) . 'assets/js/scripts.js', 
            array( 'jquery' ),
            RBBQP_VERSION );

        $translation_array = array(
            "currency_symbol"       => get_woocommerce_currency_symbol(),
            "including_vat_message" => __( '(with VAT)', 'role-based-bulk-quantity-pricing' ),
            "excluding_vat_message" => __( '(without VAT)', 'role-based-bulk-quantity-pricing' )
        );
                
        wp_localize_script( 'rbbqp-front-script', 'rbbqp_localized_strings', $translation_array );  

        wp_enqueue_style( 'rbbqp_admin_styles', plugin_dir_url( __FILE__ ) . 'assets/css/styles.css', array(), RBBQP_VERSION );
    }

}
add_action( 'wp_enqueue_scripts', 'rbbqp_enqueue_script' );

/**
 * Activate the plugin.
 */
function rbbqp_activate() {

    if ( !is_woocommerce_activated( 'woocommerce' ) ) {
        wp_die( 
            rbbqp_build_error_notice(), 
            '', 
            array( 'back_link' => true ) );
    }

    $db_initializer = new Data\Rbbqp_Db_Initializer();
    $db_initializer->addPricingTable();
}
register_activation_hook( __FILE__, 'rbbqp_activate' );

add_action( 'init', 'rbbqp_admin_init' );
function rbbqp_admin_init() {

    if ( !is_woocommerce_activated( 'woocommerce' ) ) {
        return;
    }

    if ( is_admin() ) {
        new Admin\Rbbqp_Admin_Ajax();
        new Admin\Rbbqp_Settings();
    }

    new Inc\Rbbqp_Product_Helper();

    if ( is_cart() ) {
        new Inc\Rbbqp_Cart_Helper();
    }
}


/**
 * Builds the error notice for activation without necessary plugins.
 */
function rbbqp_build_error_notice() {
    return sprintf(
        __( '%1$s requires WooCommerce installed and active. You can download WooCommerce latest version %2$s OR go back to %3$s.', 'role-based-bulk-quantity-pricing' ),
        __( '<strong>Role Based Bulk Quantity Pricing</strong>', 'role-based-bulk-quantity-pricing' ),
        __( '<strong><a href="https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip">from here</a></strong>', 'role-based-bulk-quantity-pricing' ),
        '<strong><a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . __( 'plugins page' , 'role-based-bulk-quantity-pricing' ) . '</a></strong>'
    );
}