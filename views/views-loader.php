<?php
	// Exit if accessed directly
	if ( !defined( 'ABSPATH' ) ) exit;
	
	// Utilites directory
	if ( !defined( 'HN_TS_VIEWS_DIR' ) )
		define( 'HN_TS_VIEWS_DIR', HN_TS_PLUGIN_DIR . '/views' );
	
	// Require utility files
	require_once( HN_TS_VIEWS_DIR . '/metadata_view.php'     );
	require_once( HN_TS_VIEWS_DIR . '/context_view.php'     );
?>