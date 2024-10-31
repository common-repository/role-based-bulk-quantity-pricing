<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;
use Role_Based_Bulk_Quantity_Pricing\Inc\Data\Models;

class Rbbqp_Pricing_Repository extends Repositories\Rbbqp_Abstract_Repository {

    // Only works from PHP 7.4 up
    // protected static string $table = 'pricing_table';
    // protected static string $model_class = Models\Rbbqp_Pricing_Model::class;

    protected static $table = 'pricing_table';
    protected static $model_class = Models\Rbbqp_Pricing_Model::class;

}