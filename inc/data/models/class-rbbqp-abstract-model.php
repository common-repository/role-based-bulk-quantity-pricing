<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

abstract class Rbbqp_Abstract_Model {

    // Only works from PHP 7.4 up
    // public int $ID = 0;
    // protected Rbbqp_Abstract_Repository $repository;

    public $ID = 0;
    protected $repository;


    public function __construct( $data ) {
        $name_arr = explode( '\\', get_called_class() );
        $result = implode( '\\', array_merge( array_slice( $name_arr, 0, count( $name_arr ) - 1 ), array( 'repositories' ), array_slice( $name_arr, count( $name_arr ) - 1 ) ) ) . '_Repository';

        $this->repository = new $result;
        $this->init( $data );
    }

    protected function init( $data ) {

    }

    public function getID() {
        return $this->ID;
    }

    public function setID() {
        $this->ID = (int) $ID;
    }

    public function getRepository() {
        return $this->repository;
    }
}