<?php

/**
 * Functions to display replication table content
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add search and edit functionality
 */

	/**
	 * Displays replication table.
	 * To do: Complete pagination functionality. 
	 */
	function hn_ts_showReplicationTable(){
		?>
		<h3>Replication Table</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>id</th>
					<th>local table</th>
					<th>remote user</th>
					<th>remote url</th>
					<th>remote table</th>
					<th>continuous</th>
					<th>last_replication</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>id</th>
					<th>local table</th>
					<th>remote user</th>
					<th>remote url</th>
					<th>remote table</th>
					<th>continuous</th>
					<th>last_replication</th>
				</tr>
			</tfoot>
			<tbody>
			<?php 
			$db = new Hn_TS_Database();			
			global $wpdb;
			$rows = $db->hn_ts_select($wpdb->prefix.'ts_replication');
			if($rows){
				global $pagenow;
				$screen = get_current_screen();
				foreach ( $rows as $row )
					echo "<tr>
						<td>$row->replication_id</td>
						<td><a href=\"".$pagenow.
							"?page=timestreams/admin/interface.phpdatasources&table=
							$row->local_table\">$row->local_table</a></td>
						<td>$row->remote_user_login</td>
						<td>$row->remote_url</td>
						<td>$row->remote_table</td>
						<td>$row->continuous</td>
						<td>$row->last_replication</td>
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