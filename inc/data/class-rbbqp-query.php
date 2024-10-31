<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Data;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Rbbqp_Query {

    // Only works from PHP 7.4 up
    // private \wpdb $wpdb;
    // private string $prefix = 'rbbqp_';
    // private string $table;
    // private string $select = '';
    // private string $where = '';
    // private string $order_by = '';
    // private string $limit = '';
    
    private $wpdb;
    private $prefix = 'rbbqp_';
    private $table;
    private $select = '';
    private $where = '';
    private $order_by = '';
    private $limit = '';

    public function __construct( $table ) {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $table;
    }

    public function build_query() {
        if ( empty( $this->select ) ) {
            return $this->select();
        }

        return $this->select . $this->where . $this->order_by . $this->limit;
    }

    public function select( array $fields = [] ) {
        
        if ( empty( $fields ) ) {
            $this->select = 'SELECT * FROM ' . $this->wpdb->prefix . $this->prefix . $this->table; 
        } else {

            $this->select = 'SELECT ';

            foreach ( $fields as $key => $field ) {
                // Last field.
                if ( count( $fields ) === ( $key + 1 ) ) {
                    $this->select .= $field;
                    continue;
                }

                $this->select .= $field . ',';
            }

            $this->select .= ' FROM ' . $this->wpdb->prefix . $this->prefix . $this->table;

        }
    }

    public function limit( int $row_count, int $offset = 0 ) {
        $this->limit = ' LIMIT ' . $offset . ' , ' . $row_count;
    }

    public function fetch_all() {
        return $this->wpdb->get_results( $this->build_query() );
    }

    public function fetch_single() {
        return $this->wpdb->get_row( $this->build_query() );
    }

    public function fetch_single_var() {
        return $this->wpdb->get_var( $this->build_query() );
    }

}