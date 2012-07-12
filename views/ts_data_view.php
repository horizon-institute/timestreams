<?php
/**
 * Functions to display time series data table records
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add search and edit functionality
 */

	/**
	 * Displays data in a table from the given name of the table
	 * To do: Complete pagination functionality.
	 * @param String $tablename is the name of the table to display. 
	 * The table is expected to be a data table with a name in the format of
	 * wp_<site_id>_ts_<measurement_type>_<device_id>
	 */	
	 function hn_ts_showDataRecord($tablename){
		?>
		<h3>Data Table</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>id</th>
					<th>value</th>
					<th>timestamp</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>id</th>
					<th>value</th>
					<th>timestamp</th>
				</tr>
			</tfoot>
			<tbody>
			<?php 
			$db = new Hn_TS_Database();
			$rows = $db->hn_ts_select($tablename);
			if($rows){
				foreach ( $rows as $row )
				echo "<tr>
					<td>$row->id</td>
					<td>$row->value</td>
					<td>$row->valid_time</td>
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