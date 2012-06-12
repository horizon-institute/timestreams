<?php

/**
 * Functions to display metadata table content
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add search and edit functionality
 */

	/**
	 * Displays metadata in a table. 
	 * To do: Complete pagination functionality.
	 */
	function hn_ts_showMetadataTable(){
		?>
		<h3>Metadata Table</h3>
		<table class="widefat">
			<thead>
				<tr>
				<th>id</th>
				<th>table name</th>
				<th>measurement type</th>
				<th>first_record</th>
				<th>min value</th>
				<th>max value</th>
				<th>unit</th>
				<th>unit symbol</th>
				<th>device details</th>
				<th>other info</th>
				<th>Data Type</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
				<th>id</th>
				<th>table name</th>
				<th>measurement type</th>
				<th>first_record</th>
				<th>min value</th>
				<th>max value</th>
				<th>unit</th>
				<th>unit symbol</th>
				<th>device details</th>
				<th>other info</th>
				<th>Data Type</th>
				</tr>
			</tfoot>
			<tbody>
			<?php 
			$db = new Hn_TS_Database();
			$rows = $db->hn_ts_select('wp_ts_metadata');
			if($rows){
				foreach ( $rows as $row )
				echo "<tr>
				<td>$row->metadata_id</td>
				<td>$row->tablename</td>
				<td>$row->measurement_type</td>
				<td>$row->first_record</td>
				<td>$row->min_value</td>
				<td>$row->max_value</td>
				<td>$row->unit</td>
				<td>$row->unit_symbol</td>
				<td>$row->device_details</td>
				<td>$row->other_info</td>
				<td>$row->data_type</td>
				</tr>";
			}?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<span class="displaying-num">Displaying <?php echo count($rows);?> of <?php echo count($rows);?></span>
				<span class="page-numbers current">1</span>
				<?php //<a href="#" class="page-numbers">2</a> ?>
				<?php //<a href="#" class="next page-numbers">&raquo;</a> ?>
			</div>
		</div>
		<hr />
		<?php
		
			
	}
?>