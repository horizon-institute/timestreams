<?php
	/*
		Plugin Name: Blog users
		Plugin URI: N/A
		Description: Shows a list of blogs that users belong to
		Version: 0.1.0
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
	
	/**
	 * Sets up common variables and required files
	 */
	function hn_bu_setup(){		
		// Define the Timestreams version
		if ( !defined( 'HN_BU_VERSION' ) )
			define( 'HN_BU_VERSION', '0.1' );
		
		// Define the database version
		if ( !defined( 'HN_BU_DB_VERSION' ) )
			define( 'HN_BU_DB_VERSION', 0.1 );
		
		// Define the plugin name
		if ( !defined( 'HN_BU_NAME' ) )
			define( 'HN_BU_NAME', 'Blogusers' );
		
		// Define the Timestreams blog id -- idea courtesy of Buddypress
		if ( !defined( 'HN_BU_ROOT_BLOG' ) ) {
			if( !is_multisite() ) {
				$_id = 1;
			}else if ( !defined( 'HN_BU_ENABLE_MULTIBLOG' ) ) {
				$current_site = get_current_site();
				$_id = $current_site->blog_id;
			} else {
				$_id = get_current_blog_id();
			}
			define( 'HN_BU_ROOT_BLOG', $_id );
		}
		
		// Path and URL
		if ( !defined( 'HN_BU_PLUGIN_DIR' ) )
			define( 'HN_BU_PLUGIN_DIR', WP_PLUGIN_DIR . '/timestreams' );
		
		if ( !defined( 'HN_BU_PLUGIN_URL' ) )
			define( 'HN_BU_PLUGIN_URL', plugins_url( 'timestreams' ) );
		
		hn_bu_blogusers_checkVersion(3);
		
		//load_plugin_textdomain('blogusers',false,'blogusers/languages');
		
		require_once( HN_BU_PLUGIN_DIR . '/utilities/hn_bu_utilitiesloader.php'     );
		require_once( HN_BU_PLUGIN_DIR . '/controllers/hn_bu_controllers-loader.php'     );
		require_once( HN_BU_PLUGIN_DIR . '/views/hn_bu_views-loader.php'     );
		//require_once( HN_BU_PLUGIN_DIR . '/admin/hn_bu_admin-loader.php'     );
		
		// Hook into the dashboard action
		register_activation_hook(__FILE__, 'hn_bu_blogusers_activate');
		register_deactivation_hook(__FILE__, 'hn_bu_blogusers_deactivate');
	}
	
	function hn_bu_blogusers_init() {
		$plugin_path = dirname( plugin_basename( __FILE__ ) ) ;
		load_plugin_textdomain(HN_BU_NAME, false, $plugin_path);
	}
	add_action('init', 'hn_bu_blogusers_init');

	/**
	 * Plugin activation. This creates the initial multisite tables.
	 */
	function hn_bu_blogusers_activate() {
		// Ensure that ABSPATH was defined
		if ( !defined( 'ABSPATH' ) ) exit;
		
		$hn_bu_db = new HN_BU_Database();
		$hn_bu_db->hn_bu_createTables();		

	}
	
	/**
	 * Plugin deactivation. Currently this does nothing, 
	 * but in the future should clean up the plugin database tables and files.
	 */
	function hn_bu_blogusers_deactivate() {
		wp_clear_scheduled_hook( 'hn_bu_cron_hook' );
	}
	
	/**
	 * Exits the plugin if the WP version is lower than $minver 
	 * @param $minver is the minimum version of Wordpress supported
	 */
	function hn_bu_blogusers_checkVersion($minver){
		global $wp_version;
		$exit_msg="HN_BU_NAME requires Wordpress version ".$wp_version.' or newer.';
		if(version_compare($wp_version, $minver,"<")){
			exit($exit_msg);
		}
	}
	
	/***Schedule database blog/user update every few minutes ***************/
	add_filter( 'cron_schedules', 'my_corn_schedules');
	function my_corn_schedules(){
		return array(
				'minutely' => array(
						'interval' => 60,
						'display' => 'In every Mintue'
				),
				'two_minutely' => array(
						'interval' => 60 * 2,
						'display' => 'In every two Mintues'
				),
				'three_minutely' => array(
						'interval' => 60 * 3,
						'display' => 'In every three Mintues'
				),
				'five_minutely' => array(
						'interval' => 60 * 5,
						'display' => 'In every five Mintues'
				),
		);
	}
	
	add_action('hn_bu_cron_hook', 'hn_bu_cron_bloguserupdate');
	function hn_bu_cron_bloguserupdate(){
		$hn_bu_db = new HN_BU_Database();
		$hn_bu_db->hn_bu_addAllUsersBlogs();			
	}
	
	add_action('admin_menu', 'hn_bu_cron_settings');
	function hn_bu_cron_settings(){
		if(!wp_next_scheduled('hn_bu_cron_hook')){
			wp_schedule_event(time(), 'three_minutely', 'hn_bu_cron_hook');
		}
	}
	
	
	hn_bu_setup();
	

?>