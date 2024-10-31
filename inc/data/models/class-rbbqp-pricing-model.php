<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;

class Rbbqp_Pricing_Model extends Rbbqp_Abstract_Model {

    public $product_variation_id;
    public $base_unit_price;
    public $threshold_unit_price;
    public $threshold_min_qty;
    public $role;
    public $initial_quantity;

    public function init( $data ) {
        $this->initial_quantity = 0;

        foreach ( get_object_vars( $data ) as $key => $value ) {
            $exploded = explode( '_', $key );

            foreach ( $exploded as $ex => $item ) {
                $exploded[ $ex ] = ucwords( $item );
            }

            $method = 'set' . implode( '', $exploded );

            if ( method_exists( $this, $exploded ) ) {
                $this->method( $value );
            }
        }
    }

    public function getProductVariationId() {
        return $this->product_variation_id;
    }

    public function setProductVariationId( $product_variation_id ) {
        $this->product_variation_id = $product_variation_id;
    }

    public function getBaseUnitPrice() {
        return $this->base_unit_price;
    }

    public function setBaseUnitPrice( $base_unit_price ) {
        $this->base_unit_price = $base_unit_price;
    }

    public function getThresholdUnitPrice() {
        return $this->threshold_unit_price;
    }

    public function setThresholdUnitPrice( $threshold_unit_price ) {
        $this->threshold_unit_price = $threshold_unit_price;
    }

    public function getThresholdMinQty() {
        return $this->threshold_min_qty;
    }

    public function setThresholdMinQty( $threshold_min_qty ) {
        $this->threshold_min_qty = $threshold_min_qty;
    }

    public function getRole() {
        return $this->role;
    }

    public function setRole( $role ) {
        $this->role = $role;
    }

    public function getInitialQuantity() {
        return $this->initial_quantity;
    }

    public function setInitialQuantity( $initial_quantity ) {
        $this->initial_quantity = $initial_quantity;
    }
}