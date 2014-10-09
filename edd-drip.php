<?php

/**
 * Plugin Name:     Easy Digital Downloads - Drip
 * Plugin URI:      http://fatcatapps.com/edd-drip/
 * Description:     Integrates Easy Digital Downloads with the Drip Email Marketing Automation tool.
 * Version:         1.2
 * Author:          Fatcat Apps
 * Author URI:      http://fatcatapps.com/
 *
 */
// Exit if accessed directly
if (!defined( 'ABSPATH' ))
    exit;

if (!class_exists( 'EDD_Drip' )) {

    /**
     * Main EDD_Drip class
     *
     * @since       1.0.0
     */
    class EDD_Drip {

        /**
         * @var         EDD_Drip $instance The one true EDD_Drip
         * @since       1.0.0
         */
        private static $instance;

        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_Drip
         */
        public static function instance() {
            if (!self::$instance) {
                self::$instance = new EDD_Drip();
                self::$instance->setup_constants();
                self::$instance->includes();
                //We don't have the textdomain yet, so comment this function
                //self::$instance->load_textdomain ();
                self::$instance->hooks();
            }

            return self::$instance;
        }

        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {
            // Plugin version
            define( 'EDD_DRIP_VER',
                    '1.0.0' );

            // Plugin path
            define( 'EDD_DRIP_DIR',
                    plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_DRIP_URL',
                    plugin_dir_url( __FILE__ ) );
        }

        /**
         * Include necessary files such as scripts , functions, shortcodes , widgets
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {
            if (!class_exists( 'EDDDripApi' ))
                require_once(EDD_DRIP_DIR . 'includes/drip/drip.php');
        }

        /**
         * Run action and filter hooks
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         *
         * @todo        The hooks listed in this section are a guideline, and
         *              may or may not be relevant to your particular extension.
         *              Please remove any unnecessary lines, and refer to the
         *              WordPress codex and EDD documentation for additional
         *              information on the included hooks.
         *
         *              This method should be used to add any filters or actions
         *              that are necessary to the core of your extension only.
         *              Hooks that are relevant to meta boxes, widgets and
         *              the like can be placed in their respective files.
         *
         *              IMPORTANT! If you are releasing your extension as a
         *              commercial extension in the EDD store, DO NOT remove
         *              the license check!
         */
        private function hooks() {
            // Register settings
            add_filter( 'edd_settings_extensions', array( $this, 'eddcp_add_drip_settings' ), 1 );

            add_action( 'edd_complete_purchase', array( $this, 'eddcp_fire_event_drip_after_complete_purchase' ), 10 , 1 );

            add_action( 'edd_update_payment_status', array( $this, 'eddcp_trigger_change_payment_action' ), 10 , 3 );

            // Handle licensing
            // @todo        Replace the Plugin Name and Your Name with your data
            /* if( class_exists( 'EDD_License' ) ) {
              $license = new EDD_License( __FILE__, 'Plugin Name', EDD_PLUGIN_NAME_VER, 'Your Name' );
              } */
        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {

            // Set filter for language directory
            $lang_dir = EDD_DRIP_DIR . '/languages/';
            $lang_dir = apply_filters( 'edd_plugin_name_languages_directory',
                    $lang_dir );

            // Traditional WordPress plugin locale filter
            $locale = apply_filters( 'plugin_locale',
                    get_locale(),
                    'edd-drip' );
            $mofile = sprintf( '%1$s-%2$s.mo',
                    'edd-drip',
                    $locale );

            // Setup paths to current locale file
            $mofile_local = $lang_dir . $mofile;
            $mofile_global = WP_LANG_DIR . '/edd-drip/' . $mofile;

            if (file_exists( $mofile_global )) {
                // Look in global /wp-content/languages/edd-plugin-name/ folder
                load_textdomain( 'edd-drip',
                        $mofile_global );
            } elseif (file_exists( $mofile_local )) {
                // Look in local /wp-content/plugins/edd-plugin-name/languages/ folder
                load_textdomain( 'edd-drip',
                        $mofile_local );
            } else {
                // Load the default language files
                load_plugin_textdomain( 'edd-drip',
                        false,
                        $lang_dir );
            }
        }

        /**
         * Add settings
         *
         * @access      public
         * @since       1.0.0
         * @param       array $settings The existing EDD settings array
         * @return      array The modified EDD settings array
         * 
         * //Add select box of campain list
         *   $eddcp_settings[] = array(
         *                   'id' => 'eddcp_drip_list',
         *                   'name' => __( 'Choose drip list ',
         *                           'eddcp' ),
         *                   'desc' => __( 'Select the list you wish to subscribe buyers to',
         *                           'eddcp' ),
         *                   'type' => 'select',
         *                   'options' => $this->eddcp_drip_get_lists()
         *   );
         * 
         * 
         */
        public function eddcp_add_drip_settings( $settings ) {

            $eddcp_settings = array(
                            array(
                                            'id' => 'eddcp_drip_settings',
                                            'name' => '<strong>' . __( 'Drip Settings',
                                                    'eddcp' ) . '</strong>',
                                            'desc' => __( 'Configure Drip Integration Settings.',
                                                    'eddcp' ),
                                            'type' => 'header'
                            ),
                            array(
                                            'id' => 'eddcp_drip_api',
                                            'name' => __( 'Drip API Key',
                                                    'eddcp' ),
                                            'desc' => __( 'Enter your Drip API Key here.',
                                                    'eddcp' ),
                                            'type' => 'text',
                                            'size' => 'regular'
                            ),
                            array(
                                            'id' => 'eddcp_drip_account_id',
                                            'name' => __( 'Drip Account ID',
                                                    'eddcp' ),
                                            'desc' => __( 'Enter your Drip Account ID here',
                                                    'eddcp' ),
                                            'type' => 'text',
                                            'size' => 'regular'
                            ),
            );


            return array_merge( $settings,
                            $eddcp_settings );
        }

        // get all your mail lists in drip
        function eddcp_drip_get_lists() {

            global $edd_options;

            if (empty( $edd_options['eddcp_drip_api'] ) || empty( $edd_options['eddcp_drip_account_id'] )) {
                return array( );
            }
            $drip_api = EDDDripApi::getInstance();
            $result = json_decode( $drip_api->get_all_lists(),
                    true );
            //var_dump($result);



            $lists = array( );
            foreach ($result['campaigns'] as $list) {
                $lists[$list['id']] = $list['name'];
            }
            return $lists;
        }
        
        /**
          * - adds an email to the drip subscription list
          * - change the lifetime_value
          * - fire event Made a purchase
          * - The plugin also tracks the following properties:
          *
          *          value (Price of the product bought)
          *          product_name (Name of the product bought)
          *          price_name (The price_name [if you're using variable pricing]) 
          * - Lifetime Value (LTV) Tracking This plugin tracks your customer's lifetime value in a custom field called lifetime_value.
          *
          *       + If a customer makes a purchase:
          *             lifetime_value+={price}
          *       + If a customer refunds:
          *             lifetime_value-={price} 
          * 
          * 
         */
        
        function eddcp_fire_event_drip_after_complete_purchase( $payment_id ) {
            
            $meta = get_post_meta( $payment_id,
                        '_edd_payment_meta',
                        true );
            $user_infor = $meta['user_info'];
            $email = $user_infor['email'];
            $name = $user_infor['first_name'] . ' ' . $user_infor['last_name'];
            
            //get all item in the cart
            $cart_items = edd_get_cart_content_details();

            // push subscribe infor to server
            $drip_api = EDDDripApi::getInstance();

            $result = json_decode( $drip_api->get_subscribers( $email ),
                    true );

            $is_not_created = false;

            if (isset( $result['errors'] ) && $result['errors']) {
                $is_not_created = true;
                $current_lifetime_value = 0;
            } else {
                $subscribers_field = $result['subscribers'][0];
                $current_lifetime_value = (isset( $subscribers_field['custom_fields']['lifetime_value'] )) ? $subscribers_field['custom_fields']['lifetime_value'] : 0;
            }

            foreach ($cart_items as $item) {
                if ($is_not_created) {
                    $drip_api->add_subscriber( $email,
                            array(  
                                    'name'        => $name,
                                    'lifetime_value' => $item['price']
                            )
                    );

                    $current_lifetime_value +=$item['price'];

                    $drip_api->fire_event(
                            $email,
                            'Made a purchase',
                            array(
                                    'value' => $item['price'],
                                    'product_name' => $item['name'],
                                    'quantity' => $item['quantity']
                            )
                    );
                    $is_not_created = false;
                } else {
                    $current_lifetime_value +=$item['price'];
                    $drip_api->add_subscriber( $email,
                            array(
                                    'name'        => $name,         
                                    'lifetime_value' => $current_lifetime_value
                            )
                    );
                    $drip_api->fire_event(
                            $email,
                            'Made a purchase',
                            array(
                                    'value' => $item['price'],
                                    'product_name' => $item['name'],
                                    'price_name' => edd_get_cart_item_price_name( $item )
                            )
                    );
                }
            }
        }
        
        /**
         * 
         *  - checks whether the order status changed to refund. If so, call Drip API with "Refunded" event
         *  - checks whether the order status changed to abandoned. If so, call Drip API with "Abandoned cart" event
         *  - The plugin also tracks the following properties:
         *
         *           value (Price of the product bought)
         *           product_name (Name of the product bought)
         *           price_name (The price_name [if you're using variable pricing]) 
         * 
         *  -Lifetime Value (LTV) Tracking This plugin tracks your customer's lifetime value in a custom field called lifetime_value.
         *
         *       + If a customer makes a purchase:
         *             lifetime_value+={price}
         *       + If a customer refunds:
         *             lifetime_value-={price}  
         * 
         * 
         */        
        function eddcp_trigger_change_payment_action( $payment_id, $new_status, $old_status ) {

            if ($new_status == 'refunded') {
                // push subscribe infor to server
                $drip_api = EDDDripApi::getInstance();

                $meta = get_post_meta( $payment_id,
                        '_edd_payment_meta',
                        true );
                $user_infor = $meta['user_info'];
                $email = $user_infor['email'];
                //get all item in the cart
                $cart_items = $meta['cart_details'];
                //$name = $user_infor['first_name'] . ' ' . $user_infor['last_name'];

                $result = json_decode( $drip_api->get_subscribers( $email ),
                        true );

                $subscribers_field = $result['subscribers'][0];
                $current_lifetime_value = (isset( $subscribers_field['custom_fields']['lifetime_value'] )) ? $subscribers_field['custom_fields']['lifetime_value'] : 0;


                foreach ($cart_items as $item) {
                    // push subscribe infor to server

                    $current_lifetime_value -=$item['price'];
                    $drip_api->add_subscriber( $email,
                            array(
                                    'lifetime_value' => $current_lifetime_value
                            )
                    );

                    $drip_api->fire_event(
                            $email,
                            'Refunded',
                            array(
                                    'value' => $item['price'],
                                    'product_name' => $item['name'],
                                    'price_name' => edd_get_cart_item_price_name( $item )
                            )
                    );
                }
            } elseif ($new_status == 'abandoned') {
                
                // push subscribe infor to server
                $drip_api = EDDDripApi::getInstance();

                $meta = get_post_meta( $payment_id,
                        '_edd_payment_meta',
                        true );
                $user_infor = $meta['user_info'];
                $email = $user_infor['email'];
                //get all item in the cart
                $cart_items = $meta['cart_details'];

                foreach ($cart_items as $item) {
                   
                    $drip_api->fire_event(
                            $email,
                            'Abandoned cart',
                            array(
                                    'value' => $item['price'],
                                    'product_name' => $item['name'],
                                    'price_name' => edd_get_cart_item_price_name( $item )
                            )
                    );
                }                  
            } 
            
        }

    }

}

/**
 * The main function responsible for returning the one true EDD_Drip
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      EDD_Drip The one true EDD_Drip
 */
function EDD_Drip_load() {
    if (!class_exists( 'Easy_Digital_Downloads' )) {
        if (!class_exists( 'EDD_Extension_Activation' )) {
            require_once 'includes/class.extension-activation.php';
        }

        $activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation->run();
    } else {
        return EDD_Drip::instance();
    }
}

add_action( 'plugins_loaded', 'EDD_Drip_load' );
