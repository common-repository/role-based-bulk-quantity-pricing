<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc;

class Rbbqp_Data_Accessor {

    private $wpdb;
    private $charset_collate;
    private $plugin_prefix;
    private $options_manager;

    private $pricing_table_name;

    public function __construct() {

        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();

        $this->plugin_prefix = 'rbbqp_';
        $this->options_manager = Inc\Rbbqp_Options_Manager::get_instance();

        $this->pricing_table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';
    }

    public function getPricingLine( $product_variation_id, $quantity, $roles ) {

        $allow_for_guest = $this->options_manager->get_allow_for_guest_option();
        if ( $allow_for_guest == "1" && empty( $roles ) ) {
            array_push( $roles, 'customer' );
        }

        $table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';
        $use_roles = $this->options_manager->get_use_roles_option();


        $sql = "SELECT product_variation_id, MIN(base_unit_price) as base_unit_price, MIN(threshold_unit_price) AS threshold_unit_price, threshold_min_qty, role FROM `$table_name` WHERE product_variation_id = '$product_variation_id'";

        if ( $use_roles ) {
            $sql .= " AND role IN ('', '" . implode("', '", $roles ) . "')";
        }
        $sql .= " GROUP BY product_variation_id, threshold_min_qty;";

        $results = $this->wpdb->get_results( $sql );

        $lowestPrice = 999999;

        if ( count( $results ) > 0 ) {

            $pricing = $results[0];

            if ( $quantity >= $pricing->threshold_min_qty && $pricing->base_unit_price > $pricing->threshold_unit_price ) {
                $lowestPrice = $pricing->threshold_unit_price;
            } else {
                $lowestPrice = $pricing->base_unit_price;
            }

            foreach ( $results as $result ) {

                if ( $quantity >= $result->threshold_min_qty && // we get into the new threshold
                    $result->base_unit_price > $result->threshold_unit_price && // edge case where the base unit prices is better than the threshold one
                    $lowestPrice > $result->threshold_unit_price) {

                    $lowestPrice = $result->threshold_unit_price;
                    $pricing = $result;

                } else if ( $quantity >= $result->threshold_min_qty && $result->base_unit_price < $lowestPrice ) {

                    $lowestPrice = $result->base_unit_price;
                    $pricing = $result;

                }
            }
 
            return $pricing;
        }

        return '';
    }

    public function getPricingLines( $product_variation_id, $roles ) {

        $allow_for_guest = $this->options_manager->get_allow_for_guest_option();
        if ( $allow_for_guest == "1" && empty( $roles ) ) {
            array_push( $roles, 'customer' );
        }

        $use_roles = $this->options_manager->get_use_roles_option();
        
        $table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';
        $sql = "SELECT product_variation_id, MIN(base_unit_price) as base_unit_price, MIN(threshold_unit_price) AS threshold_unit_price, threshold_min_qty, role FROM `$table_name` WHERE product_variation_id = '$product_variation_id'";

        if ( $use_roles ) {
            $sql .= " AND role IN ('', '" . implode("', '", $roles ) . "')";
        }
        $sql .= " GROUP BY product_variation_id, threshold_min_qty;";

        $results = $this->wpdb->get_results( $sql );

        if ( count( $results ) > 0 ) {
            return $results;
        }

        return '';
    }

    public function addPricing( $product_variation_id, $base_unit_price, $threshold_unit_price, $threshold_min_qty, $role ) {
        $table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';

        $data = array( 'product_variation_id' => $product_variation_id, 
            'base_unit_price' => $base_unit_price,
            'threshold_unit_price' => $threshold_unit_price,
            'threshold_min_qty' => $threshold_min_qty,
            'role' => $role);
        $format = array( '%f', '%f', '%f', '%f', '%s' );

        $this->wpdb->insert(
            $table_name,
            $data,
            $format
        );
    }

    public function getPricings() {
        $table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';
        $results = $this->wpdb->get_results( "SELECT product_variation_id, base_unit_price, threshold_unit_price, threshold_min_qty, role  FROM $table_name;");

        return $results;
    }

    public function getAllVariationsForRole( $roles ) {

        if ( is_null( $roles ) ) {
            $roles = array();
        }

        if ( count( $roles) === 1 && $roles[0] === 'administrator' ) {
            $roles = array();
        } 

        $allow_for_guest = $this->options_manager->get_allow_for_guest_option();
        if ( $allow_for_guest == "1" && empty( $roles ) ) {
            array_push( $roles, 'customer' );
        }

        $table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';
        $sql = "SELECT DISTINCT product_variation_id FROM `$table_name` WHERE role IN ('', '" . implode("', '", $roles ) . "')";
        $results = $this->wpdb->get_results( $sql );

        return array_column( $results, 'product_variation_id' );
    }

    public function clearPricings( ) {
        $table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';
        $this->wpdb->query( "TRUNCATE $table_name;" );
    }
}