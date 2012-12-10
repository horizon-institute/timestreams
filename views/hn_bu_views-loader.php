<?php
/**
 * Loads view files. Views are output representations of data.
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_BU_VIEWS_DIR' ) )
		define( 'HN_BU_VIEWS_DIR', HN_BU_PLUGIN_DIR . '/views' );
	
	// Require utility files
	require_once( HN_BU_VIEWS_DIR . '/hn_bu_blogusers_view.php'     );
	
?>