<?php

namespace Role_Based_Bulk_Quantity_Pricing\Inc\Admin;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.

use Role_Based_Bulk_Quantity_Pricing\Inc;
use Role_Based_Bulk_Quantity_Pricing\Inc\Data\Repositories;

class Rbbqp_Settings {

    private $data_accessor;
    private $options_manager;

    private $page_slug;

    private $pricing_lines;

    public function __construct() {
        
        $this->page_slug = 'role-based-bulk-quantity-pricing-settings';

        $this->data_accessor = new Inc\Rbbqp_Data_Accessor();
        $this->options_manager = Inc\Rbbqp_Options_Manager::get_instance();

        add_action( 'admin_menu', array( $this, 'create_menu_page' ) );
        add_action( 'admin_init', array( $this, 'render_fields' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }
   
    public function enqueue_admin_scripts() {

        $configurations_page_url = plugin_dir_path( __FILE__ ) . '/role-based-bulk-quantity-pricing-settings.php';

        wp_enqueue_script( 
            'twbs_pagination', 
            plugin_dir_url( __FILE__ ) . '../../assets/libs/jquery.twbsPagination.min.js' );
        wp_enqueue_script( 
            'jquery_steps', 
            plugin_dir_url( __FILE__ ) . '../../assets/js/jquery.steps.js',
            array( 'jquery' ) );
        wp_enqueue_script( 
            'rbbqp_admin_scripts', 
            plugin_dir_url( __FILE__ ) . '../../assets/js/admin-scripts.js', 
            array( 'jquery' ), 
            RBBQP_VERSION,
            array(
                'strategy' => 'defer',
                'in_footer' => true
            ) );
        wp_enqueue_style( 
            'bootstrap', 
            plugin_dir_url( __FILE__ ) .  '../../assets/libs/bootstrap.min.css' );

        wp_localize_script( 'rbbqp_admin_scripts', 'rbbqp_localized_strings', array( 
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'dashboard_url' => $configurations_page_url,
                'processing_message' => __( 'Processing... Please wait!', 'role-based-bulk-quantity-pricing' ),
                'upload_success_message' => __( 'A total of: %s lines were uploaded with success!', 'role-based-bulk-quantity-pricing' )
            )
        );

        wp_set_script_translations( 'rbbqp_admin_scripts', 'role-based-bulk-quantity-pricing' );
        wp_enqueue_style( 
            'rbbqp_admin_styles', 
            plugin_dir_url( __FILE__ ) . '../../assets/css/admin-styles.css', 
            array( 'bootstrap' ), 
            RBBQP_VERSION );
    }

    public function create_menu_page() {
        add_menu_page(
            __( 'Role Based Bulk Quantity Pricing', 'role-based-bulk-quantity-pricing'), 
            __( 'Role Based Bulk Quantity Pricing', 'role-based-bulk-quantity-pricing' ), 
            'read',
            $this->page_slug,
            array( $this, 'display_settings_page' )
        );

        add_submenu_page(
            $this->page_slug,
            __( 'Dashboard', 'role-based-bulk-quantity-pricing' ), 
            __( 'Dashboard', 'role-based-bulk-quantity-pricing' ), 
            'read',
            $this->page_slug
        );

        add_submenu_page(
            $this->page_slug,
            __( 'Import Wizard', 'role-based-bulk-quantity-pricing' ), 
            __( 'Import Wizard', 'role-based-bulk-quantity-pricing' ), 
            'read',
            'role-based-bulk-quantity-pricing-import',
            array( $this, 'import_wizard' )
        );

        add_submenu_page(
            $this->page_slug,
            __( 'Configurations', 'role-based-bulk-quantity-pricing' ), 
            __( 'Configurations', 'role-based-bulk-quantity-pricing' ), 
            'read',
            'role-based-bulk-quantity-pricing-configurations',
            array( $this, 'configurations_submenu' )
        );
    }

    public function render_fields() {
        add_settings_section(
            'configurations_section',
            __( 'Configurations', 'role-based-bulk-quantity-pricing' ),
            array( $this, 'options_section' ),
            $this->page_slug   
        );
    }

    public function configurations_submenu() {

        $configurations_saved = false;
        if ( isset( $_POST ) && 
            isset( $_POST['submit'] ) ) {
            $this->submit_configurations( $_POST );
            $configurations_saved = true;
        }

        ?>

        <form method="post" name="rbbqpSettingsForm">

            <?php if( $configurations_saved ): ?>
                <div id="savedSuccessAlert" class="alert alert-success mt-5" role="alert">
                    <?php _e('Configurations saved!', 'role-based-bulk-quantity-pricing' ); ?>
                </div>
            <?php endif; ?>


            <?php do_settings_sections( $this->page_slug ); ?>
            <hr>
            <button class="btn btn-success rbbqp_custom_submit_button" type="submit" class="button" name="submit" value="Submit">
                <img width="17" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../assets/img/save.svg' ) ?>" /> <?php _e( 'Save', 'role-based-bulk-quantity-pricing' ) ?>
            </button>
        </form>

        <div class="rbbqp-brand-logo-container">
            <img class="rbbqp-brand-logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../assets/img/logo.png' ) ?>" />
        </div>


        <?php
    }

    public function import_wizard() {

        ?>
        <div id="wizard">

            <h4>1. Download Template</h4>
            <section>
                <div class="row">
                    <div class="col-md-12">
                        <p>
                            <?php _e( 'The template will include all existing pricing lines.', 'role-based-bulk-quantity-pricing' ); ?>
                        </p>
                        <p>
                            <?php _e( 'Edit the template to add, delete or modify lines and reupload it on the next step.', 'role-based-bulk-quantity-pricing' ); ?>
                        </p>
                    </div>
                    <div class="col-md-12">
                        <button 
                            type="button" 
                            id="exportCsvBtn" 
                            class="btn btn-primary">
                                <?php _e( 'Download Template', 'role-based-bulk-quantity-pricing' ) ?>
                        </button>
                        <br>
                    </div>
                </div>
            </section>

            <hr>

            <h4>2. Import CSV</h4>
            <section>
                <div class="row">
                    <p>
                        <?php _e( 'Upload your edited CSV file. IMPORTANT: Everything will get replaced by your current CSV.', 'role-based-bulk-quantity-pricing' ); ?>
                    </p>
                    <div class="col-md-12">
                    <form id="rbbqpSettingsForm" name="rbbqpSettingsForm" enctype='multipart/form-data'>
                        <hr>
                        <input type='file' name='file' id="csvFileToUploadInput">
                        <button class="btn btn-success rbbqp_custom_submit_button" type="submit" class="button" name="btn_submit" disabled>
                            <img width="17" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../assets/img/save.svg' ); ?>" /> <?php _e( 'Import CSV', 'role-based-bulk-quantity-pricing' ) ?>
                        </button>
                        <div class="row mt-5">
                            <div class="col-md-12">
                                <span id="uploadingCsvMsg" style="display: none;">
                                    <?php _e('The uploaded file is processing... Please, do not leave or refresh this page.', 'role-based-bulk-quantity-pricing' ); ?>
                                </span>
                            </div>
                        </div>

                    </form>
                    </div>
                </div>
            </section>

            <hr />

        </div>
        <?php
    }

    public function options_section() {

        $use_roles = $this->options_manager->get_use_roles_option();
        $use_total_cart_qty = $this->options_manager->get_use_total_cart_qty_option();
        $cartTotalSameItemOnly = $this->options_manager->get_cart_total_same_item_only_option();
        $allow_for_guest = $this->options_manager->get_allow_for_guest_option();
        $debug_mode = $this->options_manager->get_debug_mode_option();
        $display_product_total = $this->options_manager->get_display_product_total_option();

        ?>

        <div class="row mt-5">
            <div class="col-2">
                <label class="rbbqp_custom_label" for="useRoles"><?php _e( 'Use Roles', 'role-based-bulk-quantity-pricing' ) ?>:</label>
            </div>
            <div class="col-10">
                <input class="form-control rbbqp_custom_checkbox_input" type="checkbox" name="useRoles" value="Yes" <?php echo isset( $use_roles ) && $use_roles == "1" ? "checked" : ""; ?> />
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-2">
                <label class="rbbqp_custom_label" for="useTotalCartQty"><?php _e( 'Use Total Cart Quantity', 'role-based-bulk-quantity-pricing' ) ?>:</label>
            </div>
            <div class="col-10">
                <input class="form-control rbbqp_custom_checkbox_input" type="checkbox" name="useTotalCartQty" value="Yes" <?php echo esc_attr( isset( $use_total_cart_qty ) && $use_total_cart_qty == "1" ? "checked" : "" ); ?> />
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-2">
                <label class="rbbqp_custom_label" for="cartTotalSameItemOnly"><?php _e( 'Only allow same item for cart qty', 'role-based-bulk-quantity-pricing' ) ?>:</label>
            </div>
            <div class="col-10">
                <input class="form-control rbbqp_custom_checkbox_input" type="checkbox" name="cartTotalSameItemOnly" value="Yes" <?php echo esc_attr( isset( $cartTotalSameItemOnly ) && $cartTotalSameItemOnly == "1" ? "checked" : "" ); ?> />
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-2">
                <label class="rbbqp_custom_label" for="allowForGuest"><?php _e( 'Allow For Guest', 'role-based-bulk-quantity-pricing' ) ?>:</label>
            </div>
            <div class="col-10">
                <input class="form-control rbbqp_custom_checkbox_input" type="checkbox" name="allowForGuest" value="Yes" <?php echo esc_attr( isset( $allow_for_guest ) && $allow_for_guest == "1" ? "checked" : "" ); ?> />
            </div>
        </div>

        <div class="row mt-2">
            <div class="col-2">
                <label class="rbbqp_custom_label" for="displayProductTotal"><?php _e( 'Display Order Total', 'role-based-bulk-quantity-pricing' ) ?>:</label>
            </div>
            <div class="col-10">
                <input class="form-control rbbqp_custom_checkbox_input" type="checkbox" name="displayProductTotal" value="Yes" <?php echo esc_attr( isset( $display_product_total ) && $display_product_total == "1" ? "checked" : "" ); ?> />
            </div>
        </div>

        <hr />

        <h5><?php _e( 'Advanced', 'role-based-bulk-quantity-pricing' ); ?></h5>

        <div class="row mt-2">
            <div class="col-2">
                <label class="rbbqp_custom_label" for="debugMode"><?php _e( 'Debug Mode', 'role-based-bulk-quantity-pricing' ) ?>:</label>
            </div>
            <div class="col-10">
                <input class="form-control rbbqp_custom_checkbox_input" type="checkbox" name="debugMode" value="Yes" <?php echo esc_attr( isset( $debug_mode ) && $debug_mode == "1" ? "checked" : "" ); ?> />
            </div>
        </div>

        <hr />

        <div class="row mt-2">

            <h2>
                <?php  _e( 'Tools', 'role-based-bulk-quantity-pricing' ); ?>
            </h2>
            
            <div class="col-md-12">
                <button type="button" id="exportCsvBtn" class="btn btn-primary"><?php _e('Download Backup', 'role-based-bulk-quantity-pricing' ) ?></button><br>
            </div>
            <div class="col-md-12 mt-2">
                <button type="button" id="exportProductsListBtn" class="btn btn-primary"><?php _e('Export Products List', 'role-based-bulk-quantity-pricing' ) ?></button><br>
                <span id="exportProductsListDownloadMsg" style="display:none;"><?php _e( 'Processing... Please wait!', 'role-based-bulk-quantity-pricing' ); ?></span>
            </div>
            <div class="col-md-12 mt-2">
                <button type="button" id="exportRolesBtn" class="btn btn-primary"><?php _e('Export Roles List', 'role-based-bulk-quantity-pricing' ) ?></button><br>
                <span id="exportRolesDownloadMsg" style="display:none;"><?php _e( 'Processing... Please wait!', 'role-based-bulk-quantity-pricing' ); ?></span>
            </div>
        </div>
        <?php
    }

    public function submit_configurations( $data ) {
 
        if ( isset( $data["useRoles"] ) && $data["useRoles"] == "Yes" ) {
            $this->options_manager->set_use_roles_option( true ); 
        } else {
            $this->options_manager->set_use_roles_option( false );
        }      

        if ( isset( $data["useTotalCartQty"] ) && $data["useTotalCartQty"] == "Yes" ) {
            $this->options_manager->set_use_total_cart_qty_option( true );
        } else {
            $this->options_manager->set_use_total_cart_qty_option( false );
        }

        if ( isset( $data["cartTotalSameItemOnly"] ) && $data["cartTotalSameItemOnly"] == "Yes" ) {
            $this->options_manager->set_cart_total_same_item_only_option( true );
        } else {
            $this->options_manager->set_cart_total_same_item_only_option( false );
        }
        
        if ( isset( $data["allowForGuest"] ) && $data["allowForGuest"] == "Yes"  ) {
            $this->options_manager->set_allow_for_guest_option( true );
        } else {
            $this->options_manager->set_allow_for_guest_option( false );
        }

        if ( isset( $data["displayProductTotal"] ) && $data["displayProductTotal"] == "Yes"  ) {
            $this->options_manager->set_display_product_total_option( true );
        } else {
            $this->options_manager->set_display_product_total_option( false );
        }
        
        if ( isset( $data["debugMode"] ) && $data["debugMode"] == "Yes"  ) {
            $this->options_manager->set_debug_mode_option( true );
        } else {
            $this->options_manager->set_debug_mode_option( false );
        }
        
    }

    public function display_settings_page() {
        $page = 1;
        $count = Repositories\Rbbqp_Pricing_Repository::get_count();
        $totalPages =  ceil( $count / 10 );
        ?>

        <div class="row">
            <div class="col-12">
                <h2><?php _e( 'Dashboard', 'role-based-bulk-quantity-pricing' ); ?></h2>
            </div>
        </div>

        <div id="pricingLinesTable-Wrapper" class="rbbqp-wrapper">
            <div class="rbbqp-loader" id="rbbqpLoader">
                <div class="lds-dual-ring"></div>
            </div>
            <div class="row mt-5">
                <div class="col-md-12">
                    <table id="pricingLinesTable" class="table" data-current-page="<?php echo esc_attr( $page ); ?>" data-total-pages="<?php echo esc_attr( $totalPages ); ?>">

                        <thead>
                            <tr>
                                <th scope="col"><?php _e( 'ID', 'role-based-bulk-quantity-pricing' ); ?></th>
                                <th scope="col"><?php _e( 'Role', 'role-based-bulk-quantity-pricing' ); ?></th>
                                <th scope="col"><?php _e( 'Base Unit Price', 'role-based-bulk-quantity-pricing' ); ?></th>
                                <th scope="col"><?php _e( 'Threshold Unit Price', 'role-based-bulk-quantity-pricing' ); ?></th>
                                <th scope="col"><?php _e( 'Threshold Quantity', 'role-based-bulk-quantity-pricing' ); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>

                    </table>
                    <!-- Pagination -->
                    <nav id="pricingLinesTableNavigation" aria-label="Page navigation example mt-5">
                        <ul id="pagination-container" class="pagination justify-content-center pagination-sm">
                        </ul>
                    </nav>
                </div>
            </div>
        </div>

        <div class="rbbqp-brand-logo-container">
            <img class="rbbqp-brand-logo" src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . '../../assets/img/logo.png' ); ?>" />
        </div>

        <?php
    }
}