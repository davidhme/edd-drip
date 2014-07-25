<?php

/*
  Plugin Name: Easy Digital Downloads - Drip
  Plugin URL: http://fatcatapps.com/
  Description: Include a drip signup option with your Easy Digital Downloads checkout
  Version: 1.1.1
  Author: Thuantp
  Author URI: #
 */


/*
  |--------------------------------------------------------------------------
  |  Init data : Number of Mail list , the particular download ID for Plan, Plan Name
  |--------------------------------------------------------------------------
 */

$number_of_mail_list = 1; // Set number of mail list

$download_ids_list = array(
    // give the particular Download ID here'
    //Download ID for Mail list 'Easy Pricing Tables Customers'
    array(429, 9, 430),
    //Download Id for Mail list 'test list'
    array(60, 62, 40)
);

$download_plan_names_list = array(
    // give the particular Plan name here'
    //Plan name for Mail list 'Easy Pricing Tables Customers'
    array('Personal', 'Bussiness', 'Agency'),
    //Plan name for Mail list 'test list'
    array('Personal1', 'Bussiness1', 'Agency1')
);


// adds an email to the drip subscription list
function eddcp_subscribe_email_drip($email, $name) {

    global $download_ids_list;
    global $download_plan_names_list;
    global $number_of_mail_list;

        $number_of_mail_list_in_cart = 0;
        $mail_list_names = array();
        $plan_names = array();
        //get all item in the cart
        $cart_items = edd_get_cart_contents();
        foreach ($cart_items as $key => $item) {

            for ($i = 0; $i < $number_of_mail_list; $i++) {
                $download_ids = $download_ids_list[$i];
                if (in_array($item['id'], $download_ids)) {
                    $number_of_mail_list_in_cart++;
                    $mail_list_names[$i] = 'eddcp_list' . $i;
                    $index = array_search($item['id'], $download_ids);
                    $plan_names[$i] = $download_plan_names_list[$i][$index];
                }
            }
        }

        if (!class_exists('EDDDripApi'))
            require_once(plugin_dir_path(__FILE__) . '/drip/drip.php');

        // push subscribe infor to server
        foreach ($mail_list_names as $key => $mail_list_name) {

            $drip_api = new EDDDripApi();
            $result = $drip_api->add_subscriber(
                    $email, 
                    array(
                        'name' => $name,
                        'event' => 'Purchased EPT ' . $plan_names[$key]
                    )
            );

        }

}


// checks user infor for subscribing drip list
function eddcp_check_for_email_drip($posted, $user_info) {

    $email = $user_info['email'];
    $name = $user_info['first_name'] . ' ' . $user_info['last_name'];
    eddcp_subscribe_email_drip($email, $name);
}

add_action('edd_checkout_before_gateway', 'eddcp_check_for_email_drip', 10, 2);


// checks whether the order status changed to refund. If so, call Drip API with "Refunded EPT Personal/Business/Agency"
function eddcp_refund_subscribe_email($payment_id, $new_status, $old_status) {

    global $download_ids_list;
    global $download_plan_names_list;
    global $number_of_mail_list;
    if ($new_status == 'refunded') {
        if (!class_exists('EDDDripApi'))
            require_once(plugin_dir_path(__FILE__) . '/drip/drip.php');

        $meta = get_post_meta($payment_id, '_edd_payment_meta', true);
        $user_infor = $meta['user_info'];
        $email = $user_infor['email'];
        //get all item in the cart
        $cart_items = $meta['cart_details'];
        $name = $user_infor['first_name'] . ' ' . $user_infor['last_name'];
        $mail_list_names = array();
        $plan_names = array();
        $number_of_mail_list_in_cart = 0;
        foreach ($cart_items as $key => $item) {

            for ($i = 0; $i < $number_of_mail_list; $i++) {
                $download_ids = $download_ids_list[$i];
                if (in_array($item['id'], $download_ids)) {
                    $number_of_mail_list_in_cart++;
                    $mail_list_names[$i] = 'eddcp_list' . $i;
                    $index = array_search($item['id'], $download_ids);
                    $plan_names[$i] = $download_plan_names_list[$i][$index];
                }
            }
        }

        // push subscribe infor to server
        foreach ($mail_list_names as $key => $mail_list_name) {
            $drip_api = new EDDDripApi();
            $drip_api->add_subscriber(
                    $email, 
                    array(
                'name' => $name,
                'event' => 'Refunded EPT ' . $plan_names[$key]
                    )
            );
        }
    }
}
add_action('edd_update_payment_status', 'eddcp_refund_subscribe_email', 10, 3);
