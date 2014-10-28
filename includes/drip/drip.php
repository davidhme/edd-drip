<?php

class EDDDripApi {

    private $api_url;
    private $api_key;
    private $api_account_id;
    private $api_campaign;

    /**
     * Returns the *Singleton* instance of this class.
     *
     * @staticvar Singleton $instance The *Singleton* instances of this class.
     *
     * @return Singleton The *Singleton* instance.
     */
    public static function getInstance( $index_of_maillist = 0 ) {
        static $instance = null;
        if (null === $instance) {
            $instance = new EDDDripApi();
        }

        return $instance;
    }

    /**
     * Protected constructor to prevent creating a new instance of the
     * *Singleton* via the `new` operator from outside of this class.
     */
    protected function __construct() {
        global $edd_options;
        $this->api_url = 'https://api.getdrip.com/v2';
        $this->api_key = $edd_options['eddcp_drip_api'];
        $this->api_account_id = ($edd_options['eddcp_drip_account_id']) ? $edd_options['eddcp_drip_account_id'] : '';
        $this->api_campaign = (isset( $edd_options['eddcp_drip_list'] ) && $edd_options['eddcp_drip_list']) ? $edd_options['eddcp_drip_list'] : '';
    }
    
    // the subscriber to drip'campain
    function add_subscriber_to_campain( $email, $custom_fields = array( ) ) {
        $url = sprintf( '/%s/campaigns/%s/subscribers',
                $this->api_account_id,
                $this->api_campaign );
        $payload = array(
                        'status' => 'active',
                        'subscribers' => array(
                                        array(
                                                        'email' => $email,
                                                        'utc_offset' => 660,
                                                        'double_optin' => false,
                                                        'starting_email_index' => 0,
                                                        'reactivate_if_subscribed' => true,
                                                        'custom_fields' => $custom_fields
                                        )
                        )
        );

        return $this->execute_query( $url,
                        $payload );
    }

    
    // add or update infor of subscriber
    function add_subscriber( $email, $custom_fields = array( ) ) {
        $url = sprintf( '/%s/subscribers',
                $this->api_account_id );
        $payload = array(
                        'subscribers' => array(
                                        array(
                                                        'email' => $email,
                                                        'utc_offset' => 660,
                                                        'custom_fields' => $custom_fields
                                        )
                        )
        );

        return $this->execute_query( $url,
                        $payload );
    }

    function fire_event( $email, $action, $properties = array( ) ) {
        $url = sprintf( '/%s/events',
                $this->api_account_id );
        $payload = array(
                        'events' => array(
                                        array(
                                                        'email' => $email,
                                                        'action' => $action,
                                                        'properties' => $properties
                                        )
                        )
        );

        return $this->execute_query( $url,
                        $payload );
    }

    function get_all_lists() {

        $url = sprintf( '/%s/campaigns',
                $this->api_account_id );

        return $this->execute_query( $url,
                        null,
                        true );
    }

    function get_subscribers( $email ) {

        $url = sprintf( '/%s/subscribers/%s',
                $this->api_account_id,
                $email );

        return $this->execute_query( $url,
                        null,
                        true );
    }

    private function execute_query( $url_path, $payload = array( ), $isGET = false ) {
        $url = $this->api_url . $url_path;
        $data = json_encode( $payload );

        $ch = curl_init();
        curl_setopt( $ch,
                CURLOPT_HTTPAUTH,
                CURLAUTH_BASIC );
        curl_setopt( $ch,
                CURLOPT_USERPWD,
                $this->api_key . ':' );
        curl_setopt( $ch,
                CURLOPT_USERAGENT,
                "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)" );
        curl_setopt( $ch,
                CURLOPT_HEADER,
                false );
        curl_setopt( $ch,
                CURLOPT_RETURNTRANSFER,
                true );
        curl_setopt( $ch,
                CURLOPT_FOLLOWLOCATION,
                true );
        curl_setopt( $ch,
                CURLOPT_HTTPHEADER,
                array( 'Content-Type: application/vnd.api+json' ) );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        
        if (!$isGET) {
            curl_setopt($ch, CURLOPT_POST, true);
            if (count( $payload ) > 0) {
                curl_setopt( $ch,
                        CURLOPT_POSTFIELDS,
                        $data );
            } else {
                curl_setopt( $ch,
                        CURLOPT_POSTFIELDS,
                        array( 'status' => 'active' ) );
            }
        }

        curl_setopt( $ch,
                CURLOPT_URL,
                $url );
        $result = curl_exec( $ch );
        curl_close( $ch );

        return $result;
    }

}

?>