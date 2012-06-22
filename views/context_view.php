<?php
/**
 * Functions to display context table content
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add search and edit functionality
 */

/**
 * Displays context information in a table.
 * To do: Complete pagination functionality.
 */
	function hn_ts_showContextTable(){
		?>
		<h3>Context Table</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>id</th>
					<th>Context Type</th>
					<th>Value</th>
					<th>Start Time</th>
					<th>End Time</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>id</th>
					<th>Context Type</th>
					<th>Value</th>
					<th>Start Time</th>
					<th>End Time</th>
				</tr>
			</tfoot>
			<tbody>
				
			<?php 
			$db = new Hn_TS_Database();
			$rows = $db->hn_ts_select_context();
			if($rows){
				foreach ( $rows as $row )
				echo "<tr>
				<td>$row->context_id</td>
				<td>$row->context_type 	</td>
				<td>$row->value</td>
				<td>$row->start_time</td>
				<td>$row->end_time </td>
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