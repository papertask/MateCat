<?php
/**
 * Created by PhpStorm.
 * Date: 27/01/14
 * Time: 18.57
 *
 */

/**
 * Abstract Class controller
 */
abstract class controller {

    protected $userRole = TmKeyManagement_Filter::ROLE_TRANSLATOR;

    /**
     * Controllers Factory
     *
     * Initialize the Controller Instance and route the
     * API Calls to the right Controller
     *
     * @return mixed
     */
    public static function getInstance() {

        if( isset( $_REQUEST['api'] ) && filter_input( INPUT_GET, 'api', FILTER_VALIDATE_BOOLEAN ) ){

            if( !isset( $_REQUEST['action'] ) || empty( $_REQUEST['action'] ) ){
                header( "Location: " . INIT::$HTTPHOST . INIT::$BASEURL . "api/docs", true, 303 ); //Redirect 303 See Other
                die();
            }

            $_REQUEST[ 'action' ][0] = strtoupper( $_REQUEST[ 'action' ][ 0 ] );

            //PHP 5.2 compatibility, don't use a lambda function
            $func                 = create_function( '$c', 'return strtoupper($c[1]);' );

            $_REQUEST[ 'action' ] = preg_replace_callback( '/_([a-z])/', $func, $_REQUEST[ 'action' ] );
            $_POST[ 'action' ]    = $_REQUEST[ 'action' ];

            //set the log to the API Log
            Log::$fileName = 'API.log';

        }

        //Default :  catController
        $action = ( isset( $_POST[ 'action' ] ) ) ? $_POST[ 'action' ] : ( isset( $_GET[ 'action' ] ) ? $_GET[ 'action' ] : 'cat' );
        $className = $action . "Controller";

        //Put here all actions we want to be performed by ALL controllers
        require_once INIT::$MODEL_ROOT . '/queries.php';

        return new $className();

    }

	/**
	 *
	 * @return bool true if version is up to date, false otherwise
	 */

	public static function isRightVersion(){
		$version_config = parse_ini_file(INIT::$ROOT."/inc/version.ini");
		$version = $version_config['version'];

//		Log::doLog("Same version number? ".($version == INIT::$BUILD_NUMBER));

		return $version == INIT::$BUILD_NUMBER;

	}

    /**
     * When Called it perform the controller action to retrieve/manipulate data
     *
     * @return mixed
     */
    abstract function doAction();

    /**
     * Called to get the result values in the right format
     *
     * @return mixed
     */
    abstract function finalize();

    /**
     * Set No Cache headers
     *
     */
    protected function nocache() {
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Cache-Control: post-check=0, pre-check=0", false);
        header("Pragma: no-cache");
    }

    /**
     * Get the values from GET OR POST global Vars
     *
     * @deprecated
     *
     * @param $varname
     *
     * @return null
     */
    protected function get_from_get_post($varname) {
        $ret = null;
        $ret = isset($_GET[$varname]) ? $_GET[$varname] : (isset($_POST[$varname]) ? $_POST[$varname] : null);
        return $ret;
    }

    public function sessionStart(){
        INIT::sessionStart();
    }

    /**
     * Explicitly disable sessions for ajax call
     *
     * Sessions enabled on INIT Class
     *
     */
    public function disableSessions(){
        INIT::sessionClose();
    }

}