<?php

/**
 * Plugin Name:     Easy Digital Downloads - Drip
 * Plugin URI:      http://fatcatapps.com/edd-drip/
 * Description:     Integrates Easy Digital Downloads with the Drip Email Marketing Automation tool.
 * Version:         1.4.6
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

	        require_once(EDD_DRIP_DIR . 'vendor/autoload.php');

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
            
            // define  once_half_hour custom time for cron
            add_filter( 'cron_schedules', array( $this, 'edd_drip_filter_cron_schedules' ) );
          
            // Register settings
	        add_filter( 'edd_settings_sections_extensions', array( $this, 'eddcp_settings_section' ), 1 );

            add_filter( 'edd_settings_extensions', array( $this, 'eddcp_add_drip_settings' ), 1 );

            add_action( 'edd_complete_purchase', array( $this, 'eddcp_fire_event_drip_after_complete_purchase' ), 10 , 1 );

	        //Runs 30 seconds after event to offload processing - 3 parms
	        add_action( 'edd_after_payment_actions', array( $this, 'eddcp_after_payment_actions' ), 10 , 3 );

            add_action( 'edd_update_payment_status', array( $this, 'eddcp_trigger_change_payment_action' ), 10 , 3 );
            
            add_action('edd_drip_cron_half_hourly', array($this, 'edd_drip_cron_half_hourly_func'));

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
	     * Register the settings section
	     *
	     * @return array
	     */
	    function eddcp_settings_section( $sections ) {
		    $sections['ck-settings'] = __( 'Drip Settings', 'eddcp' );
		    return $sections;
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
			//EDD_Drip_Logging::info(__METHOD__,'Drip Settings.');

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
                            array(
				                            'id'    => 'eddcp_drip_account_logging',
				                            'name'  => __( 'Logging', 'eddcp' ),
				                            'desc'  => __( 'Turn on logging', 'eddcp' ),
				                            'type'  => 'checkbox'
                            ),
            );

	        // If EDD is at version 2.5 or later...
	        if ( version_compare( EDD_VERSION, 2.5, '>=' ) ) {
		        $eddcp_settings = array( 'ck-settings' => $eddcp_settings );
	        }

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


        // This method is no longer being used.
	    function eddcp_fire_event_drip_after_complete_purchase($payment_id){

	    }

	    /**
	     *    After Payment Action - Event is called 30 seconds after purchase is complete
	     *      -  uses cron
	     *
	     *      - adds an email to the drip subscription list
	     *      - change the lifetime_value
	     *      - fire event Made a purchase
	     *      - The plugin also tracks the following properties:         *
	     *          value (Price of the product bought)
	     *          product_name (Name of the product bought)
	     *          price_name (The price_name [if you're using variable pricing])
	     *
	     *      - Lifetime Value (LTV) Tracking This plugin tracks your customer's lifetime value in a custom field called lifetime_value.
	     *       + If a customer makes a purchase:
	     *             lifetime_value+={price}
	     *       + If a customer refunds:
	     *             lifetime_value-={price}
	     *
	     * @param int  $payment_id
	     * @param bool $payment
	     * @param bool $customer
	     */
        function eddcp_after_payment_actions( $payment_id = 0, $payment = false, $customer = false  ) {
	        EDD_Drip_Logging::debug(__METHOD__,'EDD after payment event fired for payment ID:'. $payment_id);

            $payment_info = $this->get_payment_info_by_payment_id($payment_id);

            $email = $payment_info['email'];
            $first_name = $payment_info['first_name'];
            //get all item in the cart
            $cart_items = $payment_info['cart_items'];
            $name = $payment_info['name'];
            $is_renewal = $payment_info['is_renewal'];
	        EDD_Drip_Logging::debug(__METHOD__,'Payment Info:' .var_export($payment_info,true));

            // push subscribe info to server
            $drip_api = EDDDripApi::getInstance();

            $result = json_decode( $drip_api->get_subscribers( $email ), true );

	        $current_lifetime_value = 0;
            if (isset( $result['errors'] ) && $result['errors']) {
	            //If no items in cart then add the subscriber anyway
	            if (empty($cart_items)) {
		            $drip_response = $drip_api->add_subscriber( $email, array(
			            'first_name' => $first_name,
			            'name'       => $name,
			            'lifetime_value' => $current_lifetime_value
		            ) );

		            EDD_Drip_Logging::debug(__METHOD__,'Drip Add Subscriber(1) response:' .var_export($drip_response,true));
	            }
            } else {
                $subscribers_field = $result['subscribers'][0];
                $current_lifetime_value = (isset( $subscribers_field['custom_fields']['lifetime_value'] )) ? $subscribers_field['custom_fields']['lifetime_value'] : 0;
            }

            //Iterate over cart updating subscriber and firing made purchase events to drip
            foreach ($cart_items as $item) {
	            $current_lifetime_value +=$item['price'];

	            //update subscriber lifetime value
	            $drip_response = $drip_api->add_subscriber( $email,
                        array(
                                'first_name'  => $first_name,
                                'name'        => $name,
                                'lifetime_value' => $current_lifetime_value
                        )
                );
	            EDD_Drip_Logging::debug(__METHOD__,'Drip Add Subscriber(2) response:' .var_export($drip_response,true));

	            $drip_response = $drip_api->fire_event(
                        $email,
                        'Made a purchase',
                        array(
                                'value'         => (int) $item['price'],
                                'product_name'  => $item['name'],
                                'price_id'      => edd_get_cart_item_price_id($item),
                                'price_name'    => edd_get_cart_item_price_name( $item ),
                                'quantity'      => $item['quantity'],
                                'is_renewal'    => $is_renewal
                        )
                );
	            EDD_Drip_Logging::debug(__METHOD__,'Drip Made a Purchase Event Response:' .var_export($drip_response,true));
            }
        }
        
        /**
         *  Payment status changed -  used for Refunds  & Abandoned Carts
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
         */        
        function eddcp_trigger_change_payment_action( $payment_id, $new_status, $old_status ) {
	        EDD_Drip_Logging::debug(__METHOD__,'EDD Change Payment Event Fired for payment id:'. var_export($payment_id,true));
			//Dont think this is firing

            // push subscribe infor to server
            $drip_api = EDDDripApi::getInstance();

	        switch ($new_status) {
		        case "refunded":

			        $infor = $this->get_payment_info_by_payment_id($payment_id);
			        $email = $infor['email'];
			        //get all item in the cart
			        $cart_items = $infor['cart_items'];

			        $result = json_decode( $drip_api->get_subscribers( $email ), true );
			        $subscribers_field = $result['subscribers'][0];
			        $current_lifetime_value = (isset( $subscribers_field['custom_fields']['lifetime_value'] )) ? $subscribers_field['custom_fields']['lifetime_value'] : 0;

			        foreach ($cart_items as $item) {
				        // push subscribe infor to server

				        $current_lifetime_value -= $item['price'];
				        $drip_api->add_subscriber( $email, array(
						        'lifetime_value' => $current_lifetime_value
					        ) );


				        $drip_response = $drip_api->fire_event( $email, 'Refunded', array(
						        'value'        => (int) $item['price'],
						        'product_name' => $item['name'],
						        'price_name'   => edd_get_cart_item_price_name( $item )
					        ) );
				        EDD_Drip_Logging::debug( __METHOD__, 'Drip refunded event response:' . var_export( $drip_response, true ) );
			        }

		        	break;

		        case "abandoned":
			        $payment = get_post( $payment_id );
			        $time_make_payment = strtotime($payment->post_date) ;
			        // if the payment was existed over 30mins , no need to do any thing
			        if( time() - $time_make_payment > 1800 ) {
				        return;
			        }

			        $infor = $this->get_payment_info_by_payment_id($payment_id);
			        $email = $infor['email'];
			        //get all item in the cart
			        $cart_items = $infor['cart_items'];

			        foreach ($cart_items as $item) {

				        $drip_response = $drip_api->fire_event(
					        $email,
					        'Abandoned cart',
					        array(
						        'value' => (int) $item['price'],
						        'product_name' => $item['name'],
						        'price_name' => edd_get_cart_item_price_name( $item )
					        )
				        );

				        EDD_Drip_Logging::debug(__METHOD__,'Drip Abandoned Cart Event Response:'. var_export($drip_response,true));
			        }

		        	break;

		        case "renewal payment":

		        	$infor = $this->get_payment_info_by_payment_id($payment_id);
			        EDD_Drip_Logging::debug(__METHOD__,'Renewal Payment:'. var_export($infor,true));

		        	break;
	        }

            
//            if ($new_status == 'refunded') {
//
//                $infor = $this->get_infor_by_payment_id($payment_id);
//                $email = $infor['email'];
//                //get all item in the cart
//                $cart_items = $infor['cart_items'];
//
//                $result = json_decode( $drip_api->get_subscribers( $email ),
//                        true );
//                $subscribers_field = $result['subscribers'][0];
//                $current_lifetime_value = (isset( $subscribers_field['custom_fields']['lifetime_value'] )) ? $subscribers_field['custom_fields']['lifetime_value'] : 0;
//
//
//                foreach ($cart_items as $item) {
//                    // push subscribe infor to server
//
//                    $current_lifetime_value -=$item['price'];
//                    $drip_api->add_subscriber( $email,
//                            array(
//                                    'lifetime_value' => $current_lifetime_value
//                            )
//                    );
//
//
//                    $drip_response = $drip_api->fire_event(
//                            $email,
//                            'Refunded',
//                            array(
//                                    'value' => (int) $item['price'],
//                                    'product_name' => $item['name'],
//                                    'price_name' => edd_get_cart_item_price_name( $item )
//                            )
//                    );
//	                EDD_Drip_Logging::debug(__METHOD__,'Drip refunded event response:'. var_export($drip_response,true));
//                }
//            } elseif ($new_status == 'abandoned') {
//
//                $payment = get_post( $payment_id );
//                $time_make_payment = strtotime($payment->post_date) ;
//                // if the payment was existed over 30mins , no need to do any thing
//                if( time() - $time_make_payment > 1800 ) {
//                    return;
//                }
//
//                $infor = $this->get_infor_by_payment_id($payment_id);
//                $email = $infor['email'];
//                //get all item in the cart
//                $cart_items = $infor['cart_items'];
//
//                foreach ($cart_items as $item) {
//
//                    $drip_response = $drip_api->fire_event(
//                            $email,
//                            'Abandoned cart',
//                            array(
//                                    'value' => (int) $item['price'],
//                                    'product_name' => $item['name'],
//                                    'price_name' => edd_get_cart_item_price_name( $item )
//                            )
//                    );
//
//	                EDD_Drip_Logging::debug(__METHOD__,'Drip Abandoned Cart Event Response:'. var_export($drip_response,true));
//                }
//            }
            
        }

	    /**
	     *  Get Payment Information by Payment ID
	     *
	     * @param $payment_id
	     *
	     * @return mixed
	     */
       function get_payment_info_by_payment_id ( $payment_id ) {

			$meta = get_post_meta( $payment_id,
			        '_edd_payment_meta',
			        true );
			EDD_Drip_Logging::debug(__METHOD__,'Payment Info:' .var_export($meta,true));

			$user_infor = $meta['user_info'];
			$infor['email'] = $user_infor['email'];
			$infor['first_name'] = $user_infor['first_name'];
			$infor['name'] = $user_infor['first_name'] . ' ' . $user_infor['last_name'];
			//get all item in the cart
			$infor['cart_items'] = $meta['cart_details'];
			$infor['is_renewal'] = (bool) get_post_meta( $payment_id, '_edd_sl_is_renewal', true );

			return $infor;
       } 
        
      /**
       *  On activation, set up scheduled action hook.
       */ 
      function edd_drip_activation() {

             wp_schedule_event( time(), 'edd_once_half_hour', 'edd_drip_cron_half_hourly'  );
      }

      /**
       *  On deactivation, remove all functions from the scheduled action hook.
       */ 
      function edd_drip_deactivation() {

            wp_clear_scheduled_hook(  'edd_drip_cron_half_hourly'  );
      }

      // add custom time to cron
      function edd_drip_filter_cron_schedules( $schedules ) {

             $schedules['edd_once_half_hour'] = array( 
                         'interval' => 1800, // seconds
                         'display'  => __( 'Once Half an Hour' ) 
                      );            
             return $schedules;
      }
        
        
      function edd_drip_cron_half_hourly_func() {
           
           $now = time();
           $thirty_mins_before = $now - (30*60);
           
           $args = array(
			'status'     => 'pending',
			'start_date' => $thirty_mins_before,
			'end_date'   => $now,
		);

           $p_query  = new EDD_Payments_Query( $args );

           $payments =  $p_query->get_payments();           
           
           foreach ($payments as $payment) {
                // push subscribe infor to server
                $drip_api = EDDDripApi::getInstance();
                $infor = $this->get_payment_info_by_payment_id($payment->ID);
                $email = $infor['email'];
                //get all item in the cart
                $cart_items = $infor['cart_items'];

                foreach ($cart_items as $item) {
                   
                    $drip_api->fire_event(
                            $email,
                            'Abandoned cart',
                            array(
                                    'value' => (int) $item['price'],
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

// Check if EDD plugin is activated
if (in_array('easy-digital-downloads/easy-digital-downloads.php', get_option('active_plugins'))) {
    
   /**
    * On activation, set up scheduled action hook.
    */
    register_activation_hook( __FILE__, array( EDD_Drip::instance(), 'edd_drip_activation' ) );

    /**
     * On deactivation, remove all functions from the scheduled action hook.
     */
   register_deactivation_hook( __FILE__, array( EDD_Drip::instance(), 'edd_drip_deactivation' ) );
}
