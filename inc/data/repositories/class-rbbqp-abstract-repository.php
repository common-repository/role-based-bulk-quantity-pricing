<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc\Data;

abstract class Rbbqp_Abstract_Repository {

    // Only works from PHP 7.4 up
    // protected static string $table = '';
    protected static $table = '';

    public static function get_all( $page = 1, $order_by = null, $direction = 'ASC', $per_page = 10 ) {
        $query = new Data\Rbbqp_Query( static::$table );
        $query->select();
        $query->limit( $per_page, ( $page - 1 ) * $per_page );

        $results = $query->fetch_all();

        return $results;
    }

    public static function get_count( ) {
        $query = new Data\Rbbqp_Query( static::$table );
        $query->select( ['COUNT(1)'] );

        $results = $query->fetch_single_var();

        return $results;
    }

}