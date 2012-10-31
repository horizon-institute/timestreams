<?php

/**
 * Functions to display metadata table content
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add search and edit functionality
 */
	/**
	 * Page header for Measurement Container Base Page
	 */
	function hn_ts_meaurementContainerDescription(){
		?>
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e('Timestreams - Measurement Containers',HN_TS_NAME); ?></h2>
		<h3><?php _e('Description',HN_TS_NAME); ?></h3>
		
		<p class="ts_description"><?php _e('Data sources describe the sensor data brought into Timestreams. A device such as a weateher station may collect data using multiple sensors (thermometer, barometer, etc.). Please use one datasource entry per type of sensor.',HN_TS_NAME);?></p>
		<p class="ts_description"><?php _e('To add a new data source fill in the form below so that the platform knows what type of data you are adding into the system. Please note that the values for the unit of measurement field should be entered in <a href="http://en.wikipedia.org/wiki/Internet_media_type" title="Wikipedia entry on Internet media types">internet media type</a> format. For sensor data follow a protocol of: text/x-data-Unit, where Unit would be the unit of measurement (such as Celsius or Decibels). For example: text/x-data-celsius or image/png. Data Types are used to store your data in the correct format. For instance, if you are storing image files you\'d want to use a textual type (VARCHR(255)), but if you\'re storing temperature readings between 0 and 100 then you\'d want to use a numeric type (DECIMAL(4,1)). You may use any of the standard <a href="https://dev.mysql.com/doc/refman/5.5/en/data-types.html" title="mysql data types">MySQL ones</a>.',HN_TS_NAME);?></p>
		<hr />
		<?php
	}
	/**
	 * Page header for Measurement Container Measurements Page
	 */
	function hn_ts_meaurementsDescription($table){
		?>
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e('Timestreams - Measurements: '.$table,HN_TS_NAME); ?></h2>
		<hr />
		<?php
	}
	/**
	 * Page header for Measurement Container Sharing Page
	 */
	function hn_ts_sharingDescription($table){
		?>
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e('Timestreams - Measurement Sharing - '.$table, HN_TS_NAME); ?></h2>
		<hr />
		<?php
	}

	function hn_ts_showShareInterface(){
		echo 'W00T';
	}

	/**
	 * Displays metadata in a table. 
	 * To do: Complete pagination functionality.
	 */
	function hn_ts_showMetadataTable(){
		?>
		<h3>Measurement Container</h3>
		<table class="widefat">
			<thead>
				<tr>
				<th>id</th>
				<th>share</th>
				<th>table name</th>
				<th>measurement type</th>
				<th>min value</th>
				<th>max value</th>
				<th>unit</th>
				<th>unit symbol</th>
				<th>device details</th>
				<th>other info</th>
				<th>Data Type</th>
				<th>Missing Data Value</th>
				<th>Device IP Address</th>
				<th>Heartbeat</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
				<th>id</th>
				<th>share</th>
				<th>table name</th>
				<th>measurement type</th>
				<th>min value</th>
				<th>max value</th>
				<th>unit</th>
				<th>unit symbol</th>
				<th>device details</th>
				<th>other info</th>
				<th>Data Type</th>
				<th>Missing Data Value</th>
				<th>Device IP Address</th>
				<th>Heartbeat</th>
				</tr>
			</tfoot>
			<tbody>
			<?php 
			$db = new Hn_TS_Database();
			//$rows = $db->hn_ts_select('wp_ts_metadata ORDER BY metadata_id DESC');
			$rows = $db->hn_ts_select_viewable_metadata();
			if($rows){
				global $pagenow;
				$screen = get_current_screen();
				foreach ( $rows as $row ){
					
					if($db->hn_ts_isTableOwnedByBlogOrUser($row->tablename)){
						$btn = hn_ts_addShareTableButton($row->tablename);
					}else{
						$btn = NULL;
					}
					echo "<tr>
					<td>$row->metadata_id</td>
					<td>$btn</td>
					<td><a href=\"".$pagenow.
						"?page=timestreams/admin/interface.phpdatasources&table=
						$row->tablename\">$row->tablename</a></td>
					<td>$row->measurement_type</td>
					<td>$row->min_value</td>
					<td>$row->max_value</td>
					<td>$row->unit</td>
					<td>$row->unit_symbol</td>
					<td>$row->device_details</td>
					<td>$row->other_info</td>
					<td>$row->data_type</td>
					<td>$row->missing_data_value</td>
					<td>$row->last_IP_Addr</td>
					<td>$row->heartbeat_time</td>
					</tr>";
				}
			}?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php _e('Displaying ',HN_TS_NAME); ?><?php echo count($rows);?><?php _e(' of ',HN_TS_NAME); ?><?php echo count($rows);?></span>
				<span class="page-numbers current">1</span>
				<?php //<a href="#" class="page-numbers">2</a> ?>
				<?php //<a href="#" class="next page-numbers">&raquo;</a> ?>
			</div>
		</div>
		<hr />
		<?php
		
			
	}
?>