<?php
	/*
		Plugin Name: Timestreams
		Plugin URI: https://github.com/pszjmb1/Timestreams/
		Description: Sensor data I/O for WordPress. Connect information from your community or school with your blog and the rest of the world.
		Version: 2.0.0-Alpha-0.2
		Author: Horizon Digital Economy Research Institute Jesse Blum (JMB) & Martin Flintham (MDF)
		Author URI: http://www.horizon.ac.uk
		License: AGPLv3
	*/
	
	/*  Copyright (C) 2012  Horizon Digital Economy Research Institute, Jesse Blum (JMB) & Martin Flintham (MDF)
	
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

	// from http://fuelyourcoding.com/simple-debugging-with-wordpress/
	if(!function_exists('_log')){
		function _log( $message ) {
			if( WP_DEBUG === true ){
				if( is_array( $message ) || is_object( $message ) ){
					error_log( print_r( $message, true ) );
				} else {
					error_log( $message );
				}
			}
		}
	}
	
	/**
	 * Sets up common variables and required files
	 */
	function hn_ts_setup(){		
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
		
		//load_plugin_textdomain('timestreams',false,'timestreams/languages');
		
		require_once( HN_TS_PLUGIN_DIR . '/utilities/utilitiesloader.php'     );
		require_once( HN_TS_PLUGIN_DIR . '/controllers/controllers-loader.php'     );
		require_once( HN_TS_PLUGIN_DIR . '/views/views-loader.php'     );
		require_once( HN_TS_PLUGIN_DIR . '/admin/admin-loader.php'     );
	
		require_once( HN_TS_PLUGIN_DIR . '/admin/visualisation.php'     );
		
		
		if(!wp_next_scheduled('hn_ts_cron_replication')){
			wp_schedule_event(
					time(), 'minutely', 'minutely_replication');
		}
		
		// Hook into the dashboard action
		register_activation_hook(__FILE__, 'hn_ts_timestreams_activate');
		register_deactivation_hook(__FILE__, 'hn_ts_timestreams_deactivate');
		
	}
	add_action('minutely_replication', 'hn_ts_continuousReplication');
	function hn_ts_stylesheet(){
		wp_enqueue_style('ts-css', plugins_url('/css/documentation.css', __FILE__));
	}
	add_action( 'wp_enqueue_scripts', 'hn_ts_stylesheet' );
	
	function timestreams_init() {
		$plugin_path = dirname( plugin_basename( __FILE__ ) ) ;
		load_plugin_textdomain(HN_TS_NAME, false, $plugin_path);
	}
	add_action('init', 'timestreams_init');

	add_filter('cron_schedules', 'add_scheduled_interval');
	
	// add once a minute to wp schedules
	function add_scheduled_interval($schedules) {
	
		$schedules['minutely'] = array('interval'=>60, 'display'=>'Once a minute');
		return $schedules;
	}
	
	/**
	 * Plugin activation. This creates the initial multisite tables.
	 */
	function hn_ts_timestreams_activate() {
		// Ensure that ABSPATH was defined
		if ( !defined( 'ABSPATH' ) ) exit;
		
		$hn_ts_db = new Hn_TS_Database();
		$hn_ts_db->hn_ts_createMultisiteTables();

	}
	
	/**
	 * Plugin deactivation. Currently this does nothing, 
	 * but in the future should clean up the plugin database tables and files.
	 */
	function hn_ts_timestreams_deactivate() {
		//To do: remove database tables
		wp_clear_scheduled_hook('my_hourly_event');
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
	
	/**
	 * Allow unity file uploads. This would be better placed in its own plugin. 
	 * Ideally the functionality of the plugin would allow admins to add additional mimetypes.
	 */
	add_filter('upload_mimes', 'custom_upload_mimes');
	
	function custom_upload_mimes ( $existing_mimes=array() ){
		$existing_mimes['unity3d'] = 'application/vnd.unity';
		return $existing_mimes;
	}
	
	hn_ts_setup();
	

?>