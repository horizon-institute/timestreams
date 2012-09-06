<?php
/**
 * Loads admin functionality
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_TS_ADMIN_DIR' ) )
		define( 'HN_TS_ADMIN_DIR', HN_TS_PLUGIN_DIR . '/admin' );
	
	// Require utility files
	require_once( HN_TS_ADMIN_DIR . '/interface.php' );
?>