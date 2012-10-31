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
		<p class="ts_description"><?php _e('Data sources describe the sensor data brought into Timestreams. A device such as a weateher station may collect data using multiple sensors (thermometer, barometer, etc.). Below is a form to add new measurement containers followed by a list of existing containers that you have access to. You have access to any containers that you or others on this blog have added, as well as the ones that have been shared with this blog from other ones. Click "view" on any of the containers to see the data. Click "share" on any of the containers to share the container with other Timestreams blogs. You may only share measurement containers that you have added or have been added to this blog.',HN_TS_NAME);?></p>
		<p class="ts_description"><?php _e('To add a new data source fill in the form below so that the platform knows what type of data you are adding into the system. Use one datasource entry per type of sensor. Please note that the values for the unit of measurement field should be entered in <a href="http://en.wikipedia.org/wiki/Internet_media_type" title="Wikipedia entry on Internet media types">internet media type</a> format. For sensor data follow a protocol of: text/x-data-Unit, where Unit would be the unit of measurement (such as Celsius or Decibels). For example: text/x-data-celsius or image/png. Data Types are used to store your data in the correct format. For instance, if you are storing image files you\'d want to use a textual type (VARCHR(255)), but if you\'re storing temperature readings between 0 and 100 then you\'d want to use a numeric type (DECIMAL(4,1)). You may use any of the standard <a href="https://dev.mysql.com/doc/refman/5.5/en/data-types.html" title="mysql data types">MySQL ones</a>.',HN_TS_NAME);?></p>
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
		<h3><?php _e('Description',HN_TS_NAME); ?></h3>		
		<p class="ts_description"><?php _e('Below is a paginated list of the data in this measurement container. Click "FIRST" to go to the first page of records, "PREV" to go to the previous page, "NEXT" to go to the next page, and "LAST" to go to the last page of data.',HN_TS_NAME);?></p>
		<hr />
		
		<?php
	}
	/**
	 * Page header for Measurement Container Sharing Page
	 */
	function hn_ts_sharingDescription($table){
		?>
		<div id="icon-themes" class="icon32"></div>
		<h2><?php _e('Timestreams - Share: '.$table, HN_TS_NAME); ?></h2>
		<h3><?php _e('Description',HN_TS_NAME); ?></h3>
		<p class="ts_description"><?php _e('Share your data with other blogs. Sharing your data allows others to see the data and make their own Timestreams for the data, but the data is immutable so they cannott modify them or pass the data on or share the data with others.',HN_TS_NAME);?></p>
		<p class="ts_description"><?php _e('To share your data, simply select the blogs below and click save. You can share with as many blogs as you want.',HN_TS_NAME);?></p>
		<hr />
		<?php
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
				<th>view</th>
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
				<th>view</th>
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
						$btn = "Can't share.";
					}
					echo "<tr>
					<td>$row->metadata_id</td>
					<td><a class='button-primary'  href=\"".$pagenow.
						"?page=timestreams/admin/interface.phpdatasources&table=
						$row->tablename&limit=100\">View</a></td>
					<td>$btn</td>
					<td>$row->tablename</td>
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