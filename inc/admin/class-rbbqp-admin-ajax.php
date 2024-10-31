<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Admin;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc;
use Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;

class Rbbqp_Admin_Ajax {

    private $data_accessor;
    private $admin_email_wp_option_name;
    private $notification_email_subject_wp_option_name;
    private $notification_email_message_wp_option_name;

    public function __construct() {

        $this->data_accessor = new Inc\Rbbqp_Data_Accessor();

        add_action( 'wp_ajax_call_wp_rbbqp_export_csv', array( $this, 'exportCsv' ) );
        add_action( 'wp_ajax_call_wp_rbbqp_export_products_csv', array ( $this, 'exportProductsCsv' ) );
        add_action( 'wp_ajax_call_wp_rbbqp_export_roles', array ( $this, 'getRoles' ) );
        add_action( 'wp_ajax_call_wp_rbbqp_upload_csv', array ( $this, 'uploadCsv' ) );
        add_action( 'wp_ajax_call_wp_rbbqp_get_pricing_lines', array ( $this, 'get_pricing_lines' ) );
        add_action( 'wp_ajax_call_wp_rbbqp_get_total_pages', array ( $this, 'get_total_pages' ) );
    }

    private function get_csv_folder() {
        $upload = wp_upload_dir();

        if ( !file_exists( $upload['basedir'] . '/rbbqp_files' ) ) {
            mkdir( $upload['basedir'] . '/rbbqp_files', 0777 );
        }

        return array(
            'path' => $upload['basedir'] . '/rbbqp_files',
            'url' => $upload['baseurl'] . '/rbbqp_files'
        );
    }

    private function outputCsv( $assocDataArray ) {

        $upload = $this->get_csv_folder();
        $custom_pricing_csv_file_path = $upload['path'] . '/custom-pricing.csv';

        if ( !file_exists( $custom_pricing_csv_file_path ) ) {
            touch( $custom_pricing_csv_file_path );
        }

        $fp = fopen( $custom_pricing_csv_file_path, 'w' );
        
        $header = array( [ 
            'product_variation_id', 
            'base_unit_price', 
            'threshold_unit_price', 
            'threshold_min_qty', 
            'role'] );
        fputcsv( $fp, $header[0] );

        if ( !empty( $assocDataArray ) ):
            foreach ( $assocDataArray as $fields ) {
                fputcsv( $fp, get_object_vars( $fields ) );
            }

            fclose( $fp );
        endif;
        
        exit( json_encode( array( 'url' => $upload['url'] ) ) );
    }

    public function exportCsv() {
        $results = $this->data_accessor->getPricings();
        return $this->outputCsv( $results );
    }

    private function outputProductsCsv( $products_list ) {

        $upload = $this->get_csv_folder();
        $custom_products_list_file_path = $upload['path'] . '/products-list.csv';

        if ( !empty( $products_list ) ):

            if ( !file_exists( $custom_products_list_file_path ) ) {
                touch( $custom_products_list_file_path );
            }

            $fp = fopen( $custom_products_list_file_path, 'w' );
            
            $header = array( [ 
                'id', 
                'name', 
                'variation', 
                'type' ] );
            fputcsv( $fp, $header[0] );

            foreach ( $products_list as $fields ) {
                fputcsv( $fp, get_object_vars( $fields ) );
            }

            fclose( $fp );
        endif;
        
        exit( json_encode( array( 'url' => $upload['url'] ) ) );

    }

    public function exportProductsCsv() {

        $args = [
            'status' => array( 'publish' ),
            'orderby' => 'id',
            'order' => 'ASC',
            'limit' => -1
        ];

        $available_products = wc_get_products( $args );

        $result = [];

        foreach ( $available_products as $key => $product ) {

            if ( $product->is_type( 'variable' ) ) {

                foreach ( $product->get_available_variations() as $variation ) {

                    $variation_attr_str = '';
                    if ( !is_null( $variation['attributes'] ) &&
                        !empty( $variation['attributes'] ) )  {
                            foreach( $variation["attributes"] as $attribute_key => $attribute ) {
                                $variation_attr_str = $variation_attr_str . $attribute_key . ' => ' . ( empty($attribute) ? 'ANY' : $attribute ) . ' ; ';
                            }
                        }

                    array_push( $result, (object)[
                        'id' => $variation['variation_id'],
                        'name' => $product->get_title(),
                        'variation' => $variation_attr_str,
                        'type' => 'VARIABLE' ] );
                } 

            } else {

                array_push( $result, (object)[ 
                        'id' => $product->get_id(),
                        'name' => $product->get_title(),
                        'variation' => '',
                        'type' => 'SIMPLE' ] );

            }
        } 

        return $this->outputProductsCsv( $result );

    }

    public function uploadCsv( ) {
        // TODO: Create nonces wp_create_nonce
        // Check: https://artisansweb.net/ajax-file-upload-in-wordpress/
        // check_ajax_referer('call_wp_ffcp_upload_csv', 'security');
        // $arr_img_ext = array('image/png', 'image/jpeg', 'image/jpg', 'image/gif');
        // if (in_array($_FILES['file']['type'], $arr_img_ext)) {
        //     // $upload = wp_upload_bits($_FILES["file"]["name"], null, file_get_contents($_FILES["file"]["tmp_name"]));
        //     //$upload['url'] will gives you uploaded file path


        // }
        $row = 0;

        if ( $_FILES['file']['name'] != '' ) {

            $csv = array();

            // check there are no errors
            if ( $_FILES['file']['error'] == 0 ) {
                $name = sanitize_file_name( $_FILES['file']['name'] );
                // Extract file extensions from filename.
                $exploded_results = explode( '.', $name );
                $ext = strtolower( end( $exploded_results ) );
                $type = sanitize_mime_type( $_FILES['file']['type'] );
                $tmpName = sanitize_text_field( $_FILES['file']['tmp_name'] );

                // check the file is a csv
                if( $ext === 'csv' ) {
                    
                    if( ( $handle = fopen( $tmpName, 'r' ) ) !== FALSE) {
                        // necessary if a large csv file
                        set_time_limit(0);

                        while( ( $data = fgetcsv( $handle, 1000, ',' ) ) !== FALSE ) {

                            // Ignore header.
                            if ( $row == 0 ) {
                                $row++;
                                continue;
                            }

                            // number of fields in the csv
                            $col_count = count( $data );

                            if ( $col_count != 5 ) {
                                $error_message = sprintf( 
                                    __( 'The CSV is not correctly formatted. Missing column(s) for row: %1$s', 'role-based-bulk-quantity-pricing' ),
                                    $row );
                                wp_send_json_error( $error_message );
                            }

                            // Validate values.
                            $product_variation_id = sanitize_text_field( $data[0] );
                            $base_unit_price = sanitize_text_field( $data[1] );
                            $threshold_unit_price = sanitize_text_field( $data[2] );
                            $threshold_min_qty = sanitize_text_field( $data[3] );
                            $role = sanitize_text_field( $data[4] );

                            /* TODO: Should also check if role is valid/exists. */

                            if ( !is_numeric( $product_variation_id ) ||
                                !is_numeric( $base_unit_price ) ||
                                !is_numeric( $threshold_unit_price ) ||
                                !is_numeric( $threshold_min_qty ) ) {
                                    $error_message = sprintf( 
                                        __( 'Line: %1$s is invalid. Please fix it and try again. Nothing was uploaded.', 'role-based-bulk-quantity-pricing' ),
                                        $row
                                    );
                                    wp_send_json_error( $error_message );
                                }

                            // get the values from the csv
                            $csv[$row]['col1'] = $product_variation_id;
                            $csv[$row]['col2'] = $base_unit_price;
                            $csv[$row]['col3'] = $threshold_unit_price;
                            $csv[$row]['col4'] = $threshold_min_qty;
                            $csv[$row]['col5'] = $role;

                            $row++;
                        }
                        fclose( $handle );
                    }
                } else {
                    wp_send_json_error( __( 'This is not a CSV file.', 'role-based-bulk-quantity-pricing' ) );
                }
            }

            $this->data_accessor->clearPricings();

            for ( $i = 1; $i <= count( $csv ); $i++ ) {
                $this->data_accessor->addPricing( 
                    $csv[$i]['col1'],
                    $csv[$i]['col2'],
                    $csv[$i]['col3'],
                    $csv[$i]['col4'],
                    $csv[$i]['col5']);
            }
        }

        if ( $row == 0 ) { // Nothing was uploaded.
            wp_send_json_success( 0 );
        } else {
            wp_send_json_success( $row - 1 ); // Ignore header row.
        }
    }
    
    private function outputRoles( $roles ) {
        
        $upload = $this->get_csv_folder();
        $custom_pricing_csv_file_path = $upload['path'] . '/roles.txt';

        if (!file_exists( $custom_pricing_csv_file_path )) {
            touch( $custom_pricing_csv_file_path );
        }

        $fp = fopen( $custom_pricing_csv_file_path, 'w');

        fwrite($fp, print_r($roles, true));
        fclose($fp);
        exit( json_encode( array( 'url' => $upload['url'] ) ) );
    }

    public function getRoles() {
        $editable_roles = get_editable_roles();
        foreach ( $editable_roles as $role => $details ) {
            $sub['role'] = esc_attr( $role );
            $sub['name'] = translate_user_role( $details['name'] );
            $roles[] = $sub;
        }

        return $this->outputRoles( $roles );
    }

    public function get_pricing_lines( ) {

        $page = sanitize_text_field( $_POST['page'] );

        if ( !is_numeric( $page ) ) {
            $page = 1;
        }

        $pricing_lines = Repositories\Rbbqp_Pricing_Repository::get_all( $page );

        wp_send_json_success( $pricing_lines );

    }

    public function get_total_pages() {
        $count = Repositories\Rbbqp_Pricing_Repository::get_count();
        $totalPages =  ceil( $count / 10 );
        wp_send_json_success( $totalPages );
    }
}