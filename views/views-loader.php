<?php
/**
 * Loads view files. Views are output representations of data.
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_TS_VIEWS_DIR' ) )
		define( 'HN_TS_VIEWS_DIR', HN_TS_PLUGIN_DIR . '/views' );
	
	// Require utility files
	require_once( HN_TS_VIEWS_DIR . '/metadata_view.php'     );
	require_once( HN_TS_VIEWS_DIR . '/ts_data_view.php'     );
	require_once( HN_TS_VIEWS_DIR . '/context_view.php'     );
	require_once( HN_TS_VIEWS_DIR . '/fileView.php'     );
?>