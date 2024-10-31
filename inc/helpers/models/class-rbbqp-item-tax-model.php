<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Helpers\Models;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Rbbqp_Item_Tax_Model {

    // Only works from PHP 7.4 up
    // public string $error;
    // public bool $success;
    // public string $location_country;
    // public float $rate;
    // public float $multiplier;

    public $error;
    public $success;
    public $location_country;
    public $rate;
    public $multiplier;

    public function __construct( $rate, $multiplier, $location_country = 'Ignored', $error = '', $success = true ) {
        $this->rate = $rate;
        $this->multiplier = $multiplier;
        $this->location_country = $location_country;
        $this->error = $error;
        $this->success = $success;
    }

}