<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Data;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Rbbqp_Db_Initializer {

    private $wpdb;
    private $charset_collate;
    private $plugin_prefix;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->charset_collate = $wpdb->get_charset_collate();
        $this->plugin_prefix = 'rbbqp_';

        // To be able to use dbDelta()
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    }

    public function addPricingTable() {
        $table_name = $this->wpdb->prefix . $this->plugin_prefix . 'pricing_table';

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            product_variation_id mediumint(255) NOT NULL,
            base_unit_price decimal(13,2) NOT NULL,
            threshold_unit_price decimal(13,2) NOT NULL,
            threshold_min_qty smallint(9) NOT NULL,
            role varchar(100) NOT NULL,
            PRIMARY KEY  (id)
        ) $this->charset_collate;";
    
        dbDelta( $sql );
    }

}