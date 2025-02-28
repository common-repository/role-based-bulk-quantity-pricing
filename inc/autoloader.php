<?php

spl_autoload_register( 'role_based_bulk_quantity_pricing_autoload' );

function role_based_bulk_quantity_pricing_autoload( $class_name ) {

    if ( false === strpos( $class_name, 'Role_Based_Bulk_Quantity_Pricing' ) ) {
        return;
    }

    $file_parts = explode( '\\', $class_name );

    $namespace = '';
    for ( $i = count( $file_parts ) - 1; $i > 0; $i-- ) {
        $current = strtolower( $file_parts[ $i ] );
        $current = str_ireplace( '_', '-', $current );

        if ( count( $file_parts ) - 1 === $i ) {

            if ( strpos( strtolower( $file_parts[ count( $file_parts) - 1 ] ), 'interface' ) ) {

                $interface_name = explode( '_', $file_parts[ count( $file_parts ) - 1 ] );
                $interface_name = $interface_name[0];

                $file_name = "interface-$interface_name.php";
            } else {
                $file_name = "class-$current.php";
            }
        } else {
            $namespace = '/' . $current . $namespace;
        }
    }

    $filepath = trailingslashit( dirname( dirname( __FILE__ ) ) . $namespace );
    $filepath .= $file_name;

    if ( file_exists( $filepath ) ) {
        include_once( $filepath );
    } else {
        wp_die(
            esc_html( "The file attempting to be loaded at $filepath does not exist." )
        );
    }
}