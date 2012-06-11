<?php
	add_action('admin_menu', 'hn_ts_add_admin_menus');
	
	function hn_ts_add_admin_menus(){
	
		// To do replace administrator with a custom capability
		add_menu_page('Timestreams', 'Timestreams',
				'administrator', __FILE__, 'hn_ts_main_admin_page');
		/*add_submenu_page(__FILE__, 'About Plugin', 'About', 'manage_options',
				__FILE__.'about','jmb_about_page');*/
	}
	
	function hn_ts_main_admin_page(){
		echo "Admin Page Test";
	}
?>