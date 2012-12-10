<?php
/**
 * Loads utility files
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_BU_UTILITES_DIR' ) )
		define( 'HN_BU_UTILITES_DIR', HN_BU_PLUGIN_DIR . '/utilities' );
	
	// Require utility files
	require_once( HN_BU_UTILITES_DIR . '/hn_bu_db.php'     );
?>