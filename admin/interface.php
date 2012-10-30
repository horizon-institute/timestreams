<?php
/**
 * Functions to provide admin functionality
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	add_action('admin_menu', 'hn_ts_add_admin_menus');
	require_once( HN_TS_ADMIN_DIR . '/settings.php' );
	
	/**
	 * Timestreams Admin menu structure
	 */
	function hn_ts_add_admin_menus(){
		global $hn_ts_admin_timestreams;
		$hn_ts_admin_timestreams = add_menu_page('Timestreams', __('Timestreams',HN_TS_NAME),
				'administrator', __FILE__, 'hn_ts_main_admin_page');
		add_submenu_page(__FILE__, 'Measurements', __('Measurements',HN_TS_NAME), 'manage_options',
				__FILE__.'datasources','hn_ts_datasources_admin_page');
		add_submenu_page(__FILE__, 'Visualisations', __('Visualisations',HN_TS_NAME), 'manage_options',
				__FILE__.'visualisations','hn_ts_visualisations_admin_page');				
		add_submenu_page(__FILE__, 'Context', __('Context',HN_TS_NAME), 'manage_options',
				__FILE__.'context','hn_ts_context_admin_page');
		global $hn_ts_admin_page_repl;
		$hn_ts_admin_page_repl = add_submenu_page(__FILE__, 'Replication', __('Replication',HN_TS_NAME), 'manage_options',
				__FILE__.'replication','hn_ts_replication_admin_page');
	}
	
	/**
	 * Displays top level timestreams admin page
	 */
	function hn_ts_main_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-index" class="icon32"></div>
			<h2 style="padding-bottom: 1em;"><?php _e('Timestreams',HN_TS_NAME); ?></h2>
		<?php
			hn_ts_describeTimestreams();
			hn_ts_addTimestream();
			hn_ts_showTimestreams();
			
		?>
		</div>
		<?php
	}	
	
	/**
	 * Displays timestreams data sources admin page
	 * This can display either the available datasource records
	 * or an individual datasource record
	 */
	function hn_ts_datasources_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e('Timestreams - Metadata',HN_TS_NAME); ?></h2>
			<h3><?php _e('Description',HN_TS_NAME); ?></h3>
			
			<p class="ts_description"><?php _e('Data sources describe the sensor data brought into Timestreams. A device such as a weateher station may collect data using multiple sensors (thermometer, barometer, etc.). Please use one datasource entry per type of sensor.',HN_TS_NAME);?></p>
			<p class="ts_description"><?php _e('To add a new data source fill in the form below so that the platform knows what type of data you are adding into the system. Please note that the values for the unit of measurement field should be entered in <a href="http://en.wikipedia.org/wiki/Internet_media_type" title="Wikipedia entry on Internet media types">internet media type</a> format. For sensor data follow a protocol of: text/x-data-Unit, where Unit would be the unit of measurement (such as Celsius or Decibels). For example: text/x-data-celsius or image/png. Data Types are used to store your data in the correct format. For instance, if you are storing image files you\'d want to use a textual type (VARCHR(255)), but if you\'re storing temperature readings between 0 and 100 then you\'d want to use a numeric type (DECIMAL(4,1)). You may use any of the standard <a href="https://dev.mysql.com/doc/refman/5.5/en/data-types.html" title="mysql data types">MySQL ones</a>.',HN_TS_NAME);?></p>
			<hr />
			
			<?php
				
				if(isset($_GET['table'])){				
					hn_ts_showDataRecord($_GET['table']);
				}else{
					hn_ts_addMetadataRecord();
					hn_ts_showMetadataTable();
				}
			?>
		</div>
		<?php
	}	
	
	/**
	 * Displays timestreams context admin page
	 */
	function hn_ts_context_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-edit-pages" class="icon32"></div>
			<h2><?php _e('Timestreams - Context',HN_TS_NAME); ?></h2>
			<h3><?php _e('Description',HN_TS_NAME); ?></h3>
			<p><?php _e('Context records let you describe the context of the data you collect. These can be any type such as location, activity or session id.',HN_TS_NAME); ?></p>
			<hr />
		<?php
			hn_ts_addContextRecord();
			hn_ts_showContextTable();
		?>
		</div>
		<?php
	}
	
	/**
	 * Displays timestreams replication admin page
	 */
	function hn_ts_replication_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-edit-pages" class="icon32"></div>
			<h2><?php _e('Timestreams - Replication',HN_TS_NAME); ?></h2>
			<h3><?php _e('Description',HN_TS_NAME); ?></h3>
			<p><?php _e('You can replicate your data sources from one timestreams blog to another. This is useful if you are collecting data locally and want to provide it publicly as well.',HN_TS_NAME); ?></p>
			<p><?php _e('Timestreams supports two types of replication -- discrete and continuous. Discrete replication means doing a single data transfer. Continuous means that your data will be tranfered all the time as new data come in from a data source.',HN_TS_NAME); ?></p>
			<p><?php _e('To use replication complete the form below and click add replication record.',HN_TS_NAME); ?></p>
			<hr />
		<?php
			hn_ts_addReplicationRecord();
			hn_ts_showReplicationTable();
		?>
		</div>
		<?php
	}
	
	function hn_ts_visualisations_admin_page()
	{
		?>
		<div class="wrap">
			<div id="icon-edit-pages" class="icon32"></div>
			<h2><?php _e('Timestreams - Visualisations',HN_TS_NAME); ?></h2>
			<p><?php _e('Here is a list and descriptions of the different timestreams visualisations that have been designed by Horizon and Active Ingredient for you to embed in your post or webpage.',HN_TS_NAME); ?>
			 </p>
			<hr />
		<?php
			hn_ts_showVisualisations();
		?>
		</div>
		<?php		
	}
	
?>