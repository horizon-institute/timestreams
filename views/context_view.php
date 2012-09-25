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
		<h3><?php _e('Context Table',HN_TS_NAME); ?></h3>
		<table class="widefat">
			<thead>
				<tr>
					<th><?php _e('id',HN_TS_NAME); ?></th>
					<th><?php _e('Context Type',HN_TS_NAME); ?></th>
					<th><?php _e('Value',HN_TS_NAME); ?></th>
					<th><?php _e('Start Time',HN_TS_NAME); ?></th>
					<th><?php _e('End Time',HN_TS_NAME); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e('id',HN_TS_NAME); ?></th>
					<th><?php _e('Context Type',HN_TS_NAME); ?></th>
					<th><?php _e('Value',HN_TS_NAME); ?></th>
					<th><?php _e('Start Time',HN_TS_NAME); ?></th>
					<th><?php _e('End Time',HN_TS_NAME); ?></th>
				</tr>
			</tfoot>
			<tbody>
				
			<?php 
			$db = new Hn_TS_Database();
			$rows = $db->hn_ts_select_context(array(10000,0));
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