<?php
/**
 * Loads utility files
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_TS_UTILITES_DIR' ) )
		define( 'HN_TS_UTILITES_DIR', HN_TS_PLUGIN_DIR . '/utilities' );
	
	// Require utility files
	require_once( HN_TS_UTILITES_DIR . '/db.php'     );
	require_once( HN_TS_UTILITES_DIR . '/xml_rpc.php'     );
	require_once( HN_TS_UTILITES_DIR . '/external.php'     );
	
	$spdbxml = new HN_TS_SensorDB_XML_RPC();
	if(isset($spdbxml)){
		// Pass the Wordpress xmlrpc methods filter data to the SensorDataView 
		//add_new_xmlrpc_methods function
		add_filter( 'xmlrpc_methods', array(&$spdbxml, 'add_new_xmlrpc_methods'));
	}
?>