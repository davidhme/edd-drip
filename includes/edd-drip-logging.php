<?php if (!defined( 'ABSPATH' ))  exit;

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

if (!class_exists( 'EDD_Drip_Logging' )) {

    class EDD_Drip_Logging {

	    protected static $instance;

	    /**
	     * Configure Monolog to use a rotating files system.
	     *
	     * @return Logger
	     */
	    protected static function configureInstance()
	    {
		    $logger_name = 'Edd-Drip';
		    $log_path = EDD_DRIP_DIR . 'logs';
		    $log_file_name = 'edd-drip.log';

		    if (!file_exists($log_path)){
			    mkdir($log_path, 0700, true);
		    }

		    $logger = new Logger($logger_name);
		    $logger->pushHandler(new RotatingFileHandler($log_path . DIRECTORY_SEPARATOR . $log_file_name, 5));
		    self::$instance = $logger;
	    }

	    /**
	     * Method to return the Monolog instance
	     *
	     * @return \Monolog\Logger
	     */
	    static public function getLogger()
	    {
		    //Check logging turned on
		    $logging= edd_get_option('eddcp_drip_account_logging');  if (!$logging) return false;

		    if (! self::$instance) {
			    self::configureInstance();
		    }

		    return self::$instance;
	    }

	    public static function debug($function, $message, array $context = []){
		    $logger = self::getLogger();
			if ($logger) $logger->addDebug(self::format_message( $function, $message ), $context);
	    }
	    public static function info($function,$message, array $context = []){
		    $logger = self::getLogger();
		    if ($logger) $logger->addInfo(self::format_message( $function, $message ), $context);
	    }
	    public static function notice($function,$message, array $context = []){
		    $logger = self::getLogger();
		    if ($logger) $logger->addNotice(self::format_message( $function, $message ), $context);
	    }
	    public static function warning($function,$message, array $context = []){
		    $logger = self::getLogger();
		    if ($logger) $logger->addWarning(self::format_message( $function, $message ), $context);
	    }
	    public static function error($function,$message, array $context = []){
		    $logger = self::getLogger();
		    if ($logger) $logger->addError(self::format_message( $function, $message ), $context);
	    }
	    public static function critical($function,$message, array $context = []){
		    $logger = self::getLogger();
		    if ($logger) $logger->addCritical(self::format_message( $function, $message ), $context);
	    }
	    public static function alert($function,$message, array $context = []){
		    $logger = self::getLogger();
		    if ($logger) $logger->addAlert(self::format_message( $function, $message ), $context);
	    }
	    public static function emergency($function,$message, array $context = []){
		    $logger = self::getLogger();
		    if ($logger) $logger->addEmergency(self::format_message( $function, $message ), $context);
	    }

	    /**
	     * Format logging message
	     *
	     * @param $function
	     * @param $message
	     *
	     * @return string
	     */
	    private static function format_message( $function, $message ) {
		    $message = sprintf( "[%s] %s", $function, $message );
		    return $message;
	    }
    }

}
