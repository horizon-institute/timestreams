<?php
/**
 * Functions to provide admin functionality
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	add_action('admin_menu', 'hn_bu_add_admin_menus');
	require_once( HN_TS_ADMIN_DIR . '/settings.php' );
	
	/**
	 * Blogusers Admin menu structure
	 */
	function hn_bu_add_admin_menus(){
		global $hn_bu_admin_timestreams;
		$hn_bu_admin_timestreams = add_menu_page('Blogusers', __('Blogusers',HN_TS_NAME),
				'administrator', __FILE__, 'hn_bu_main_admin_page');
	}
	
	/**
	 * Displays top level timestreams admin page
	 */
	function hn_bu_main_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-index" class="icon32"></div>
			<h2 style="padding-bottom: 1em;"><?php _e('Blogusers',HN_TS_NAME); ?></h2>
		<?php
			hn_db_showBlogusers();			
		?>
		</div>
		<?php
	}	
	
?>