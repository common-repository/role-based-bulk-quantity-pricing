<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Helpers;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

class Rbbqp_User_Helpers {

    public function __construct() {

    }

    public function get_user_roles() {
        $roles = [];
        if( is_user_logged_in() ) {
            $user = wp_get_current_user();
            $roles = ( array ) $user->roles;
        }

        return $roles;
    }

}