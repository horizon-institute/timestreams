<?php
/**
 * Functions to provide admin functionality
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	add_action('admin_menu', 'hn_ts_add_admin_menus');
	
	/**
	 * Timestreams Admin menu structure
	 */
	function hn_ts_add_admin_menus(){
		add_menu_page('Timestreams', 'Timestreams',
				'administrator', __FILE__, 'hn_ts_main_admin_page');
		add_submenu_page(__FILE__, 'Data Sources', 'Data Sources', 'manage_options',
				__FILE__.'datasources','hn_ts_datasources_admin_page');
		add_submenu_page(__FILE__, 'Context', 'Context', 'manage_options',
				__FILE__.'context','hn_ts_context_admin_page');
		add_submenu_page(__FILE__, 'Replication', 'Replication', 'manage_options',
				__FILE__.'replication','hn_ts_replication_admin_page');
	}
	
	/**
	 * Displays top level timestreams admin page
	 */
	function hn_ts_main_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-index" class="icon32"></div>
			<h2>Timestreams</h2>
			<h3>Description</h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
			<hr />
		<?php
		
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
			<h2>Timestreams - Metadata</h2>
			<h3>Description</h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
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
			<h2>Timestreams - Context</h2>
			<h3>Description</h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
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
			<h2>Timestreams - Replication</h2>
			<h3>Description</h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
			<hr />
		<?php
			hn_ts_showReplicationTable();
		?>
		</div>
		<?php
	}
	
	
?>