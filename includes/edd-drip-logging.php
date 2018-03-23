<?php

/**
 * Plugin Name:     Easy Digital Downloads - Drip
 * Plugin URI:      http://fatcatapps.com/edd-drip/
 * Description:     Integrates Easy Digital Downloads with the Drip Email Marketing Automation tool.
 * Version:         1.4.2
 * Author:          Chris Simmons
 *
 */
// Exit if accessed directly
if (!defined( 'ABSPATH' ))
    exit;

if (!class_exists( 'EDD_Drip_Logging' )) {

	//Temporary Logging class
	//replace with wonolog
	//https://torquemag.io/2017/10/logging-wordpress-bugs-wonolog/
    class EDD_Drip_Logging {

    	const DEBUG     ='debug';
	    const WARNING   ='warning';
	    const ERROR     ='error';
	    const INFO      ='info';

    	private static $dateFormat= 'Y-m-d H:i:s';

	    private static function log($function, $short_message, $message,$type) {

	    	try {

	    		//is LOGGING TURNER ON
			    $logging= edd_get_option('eddcp_drip_account_logging');
				if (!$logging) return;

			    //if message isnt string then convert to string
			    if (! is_string($message)) $message = var_export($message,true);

			    //Temporary logging class
			    $message = sprintf("%s(%s) %s: %s %s",self::getTimestamp(),$type, $function, $short_message,$message);
			    error_log($message);

		    } catch (Exception $ex){
	    		//swallow errors here
		    }
	    }

	    public static function log_info($function, $short_message, $message="") {
			self::log($function, $short_message, $message,self::INFO);
	    }

	    public static function log_error($function, $short_message,$message="") {
		    self::log($function, $short_message,$message,self::ERROR);
	    }

	    public static function log_warning($function, $short_message,$message="") {
		    self::log($function, $short_message,$message,self::WARNING);
	    }

	    public static function log_debug($function, $short_message,$message="") {
		    self::log($function, $short_message,$message,self::DEBUG);
	    }

	    private static function getTimestamp()
	    {
		    try {

			    $originalTime = microtime(true);
			    $micro = sprintf("%06d", ($originalTime - floor($originalTime)) * 1000000);
			    $date = new DateTime(date('Y-m-d H:i:s.' . $micro, $originalTime));

		        return $date->format(self::$dateFormat);

		    } catch (Exception $ex){
			    return time();
		    }
	    }
    }

}
