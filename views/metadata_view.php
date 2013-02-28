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
		<p class="ts_description"><?php _e('Measurement containers store sensor data sent to Timestreams. A device such as a weateher station may collect data using multiple sensors (thermometer, barometer, etc.).
				You have access to any containers that you or others on this blog have added, as well as the ones that have been shared with this blog from other ones. 
				',HN_TS_NAME);?></p>
		
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
		Click "view" on any of the containers to see the data. Click "share" on any of the containers to share the container with other Timestreams blogs. 
				You may only share measurement containers that you have added or have been added to this blog.
		<table class="widefat">
			<thead>
				<tr>
				<th>id</th>
				<th>view</th>
				<th>share</th>
				<th>name</th>
				<th>table name</th>
				<th>device details</th>
				<th>other info</th>
				<th>measurement type</th>
				<th>min value</th>
				<th>max value</th>
				<th>unit</th>
				<th>unit symbol</th>
				<th>Data Type</th>
				<th>Missing Data Value</th>
				<th>Data License</th>
				</tr>
				<?php /*
				<th>Device IP Address</th>
				<th>Heartbeat</th>*/?>
			</thead>
			<tfoot>
				<tr>
				<th>id</th>
				<th>view</th>
				<th>share</th>
				<th>name</th>
				<th>table name</th>
				<th>device details</th>
				<th>other info</th>
				<th>measurement type</th>
				<th>min value</th>
				<th>max value</th>
				<th>unit</th>
				<th>unit symbol</th>
				<th>Data Type</th>
				<th>Missing Data Value</th>
				<th>Data License</th>
				</tr>
				<?php /*
				<th>Device IP Address</th>
				<th>Heartbeat</th>*/?>
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
					<td>$row->friendlyname</td>
					<td>$row->tablename</td>
					<td>$row->device_details</td>
					<td>$row->other_info</td>
					<td>$row->measurement_type</td>
					<td>$row->min_value</td>
					<td>$row->max_value</td>
					<td>$row->unit</td>
					<td>$row->unit_symbol</td>
					<td>$row->data_type</td>
					<td>$row->missing_data_value</td>
					<td><a href=\"$row->licurl\" title=\"$row->licname\">$row->licshortname</a></td>
					</tr>";
					//<td>$row->last_IP_Addr</td>
					//<td>$row->heartbeat_time</td>
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