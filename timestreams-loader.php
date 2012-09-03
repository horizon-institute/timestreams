<?php
	/*
		Plugin Name: Timestreams
		Plugin URI: https://github.com/pszjmb1/Timestreams/
		Description: Sensor data I/O for WordPress. Connect information from your community or school with your blog and the rest of the world.
		Version: 0.8
		Author: Horizon Digital Economy Research Institute Jesse Blum (JMB) & Martin Flintham (MDF)
		Author URI: http://www.horizon.ac.uk
		License: AGPLv3
	*/
	
	/*  Copyright (C) 2012  Jesse Blum (JMB)
	
	    This program is free software: you can redistribute it and/or modify
	    it under the terms of the GNU Affero General Public License as
	    published by the Free Software Foundation, either version 3 of the
	    License, or (at your option) any later version.
	
	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU Affero General Public License for more details.
	
	    You should have received a copy of the GNU Affero General Public License
	    along with this program.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	/**
	 * Sets up common variables and required files
	 */
	function setup(){		
		// Define the Timestreams version
		if ( !defined( 'HN_TS_VERSION' ) )
			define( 'HN_TS_VERSION', '0.8' );
		
		// Define the database version
		if ( !defined( 'HN_TS_DB_VERSION' ) )
			define( 'HN_TS_DB_VERSION', 0.1 );
		
		// Define the plugin name
		if ( !defined( 'HN_TS_NAME' ) )
			define( 'HN_TS_NAME', 'Timestreams' );
		
		// Define the Timestreams blog id -- idea courtesy of Buddypress
		if ( !defined( 'HN_TS_ROOT_BLOG' ) ) {
			if( !is_multisite() ) {
				$_id = 1;
			}else if ( !defined( 'HN_TS_ENABLE_MULTIBLOG' ) ) {
				$current_site = get_current_site();
				$_id = $current_site->blog_id;
			} else {
				$_id = get_current_blog_id();
			}
			define( 'HN_TS_ROOT_BLOG', $_id );
		}
		
		// Path and URL
		if ( !defined( 'HN_TS_PLUGIN_DIR' ) )
			define( 'HN_TS_PLUGIN_DIR', WP_PLUGIN_DIR . '/timestreams' );
		
		if ( !defined( 'HN_TS_PLUGIN_URL' ) )
			define( 'HN_TS_PLUGIN_URL', plugins_url( 'timestreams' ) );
		
		hn_ts_timestreams_checkVersion(3);
		
		load_plugin_textdomain('timestreams',false,'timestreams/languages');
		
		require_once( HN_TS_PLUGIN_DIR . '/utilities/utilitiesloader.php'     );
		require_once( HN_TS_PLUGIN_DIR . '/views/views-loader.php'     );
		require_once( HN_TS_PLUGIN_DIR . '/controllers/controllers-loader.php'     );
		require_once( HN_TS_PLUGIN_DIR . '/admin/admin-loader.php'     );
		
		// Hook into the dashboard action
		register_activation_hook(__FILE__, 'hn_ts_timestreams_activate');
		register_deactivation_hook(__FILE__, 'hn_ts_timestreams_deactivate');
	}

	/**
	 * Plugin activation. This creates the initial multisite tables.
	 */
	function hn_ts_timestreams_activate() {
		// Ensure that ABSPATH was defined
		if ( !defined( 'ABSPATH' ) ) exit;
		
		$hn_ts_db = new Hn_TS_Database();
		$hn_ts_db->hn_ts_createMultisiteTables();
		 
		//require_once( ABSPATH.'wp-content/plugins/sample/app/controllers/xml-rpc.php');
		//require_once( HN_TS_PLUGIN_DIR . '/admin/posttype.php'     );
		 //add_action( 'init', 'hn_ts_blah' );
	}
	
	/**
	 * Plugin deactivation. Currently this does nothing, 
	 * but in the future should clean up the plugin database tables and files.
	 */
	function hn_ts_timestreams_deactivate() {
		//To do: remove database tables
	}
	
	/**
	 * Exits the plugin if the WP version is lower than $minver 
	 * @param $minver is the minimum version of Wordpress supported
	 */
	function hn_ts_timestreams_checkVersion($minver){
		global $wp_version;
		$exit_msg="HN_TS_NAME requires Wordpress version ".$wp_version.' or newer.';
		if(version_compare($wp_version, $minver,"<")){
			exit($exit_msg);
		}
	}
	
	setup();
	
	require_once( HN_TS_PLUGIN_DIR . '/admin/posttype.php'     );

?>