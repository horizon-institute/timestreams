<?php
/**
 * Loads the controllers
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */
	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_BU_CONTROLLERS_DIR' ) )
		define( 'HN_BU_CONTROLLERS_DIR', HN_BU_PLUGIN_DIR . '/controllers' );
	
	// Require utility files
	require_once( HN_BU_CONTROLLERS_DIR . '/hn_bu_users_ctrl.php'     );
?>