<?php
/**
 * Loads admin functionality
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_BU_ADMIN_DIR' ) )
		define( 'HN_BU_ADMIN_DIR', HN_BU_PLUGIN_DIR . '/admin' );
	
	// Require utility files
	require_once( HN_BU_ADMIN_DIR . '/hn_bu_interface.php' );
?>