<?php

/**
 * Functions to display replication table content
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add search and edit functionality
 */

	/**
	 * Enqueue the javascript
	 */
	function hn_ts_doReplicationJSSccript($hook){
		global $hn_ts_admin_page_repl;
	
		if($hook != $hn_ts_admin_page_repl){
			return;
		}
	
		wp_enqueue_script('hn_ts_ajax_repl', plugin_dir_url(HN_TS_VIEWS_DIR).'js/hn_ts_ajax.js', array('jquery'));
		wp_localize_script('hn_ts_ajax_repl', 'hn_ts_ajax_repl_vars', array(
			'hn_ts_ajax_repl_nonce' => wp_create_nonce('hn_ts_ajax_repl-nonce')
		));
	}
	add_action('admin_enqueue_scripts', 'hn_ts_doReplicationJSSccript');
	
	/**
	 * Displays the success or failure of an attempt at table replication
	 */
	function hn_ts_ajax_repl_get_replication_results(){
		if(!isset($_POST["hn_ts_ajax_repl_nonce"]) || !wp_verify_nonce($_POST["hn_ts_ajax_repl_nonce"], 'hn_ts_ajax_repl-nonce')){
			die('Failed permissions check.');
		}
		if(!isset($_POST["hn_ts_ajax_repl_id"])){
			die('');
		}else{
			echo hn_ts_replicate_full($_POST["hn_ts_ajax_repl_id"]);
		}
		die();	
	}
	add_action('wp_ajax_hn_ts_get_replication_results','hn_ts_ajax_repl_get_replication_results');

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
					<th>Replicate Now</th>
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
					<th>Replicate Now</th>
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
				foreach ( $rows as $row ){
					$RowString = "<tr>
						<td>$row->replication_id</td>
						<td><a href=\"".$pagenow.
							"?page=timestreams/admin/interface.phpdatasources&table=
							$row->local_table\">$row->local_table</a></td>
						<td>$row->remote_user_login</td>
						<td>$row->remote_url</td>
						<td>$row->remote_table</td>
						<td>$row->continuous</td>
						<td><div id=\"hn_ts_last_repl-$row->replication_id\">$row->last_replication</td></div>
						<td>	
							<form id=\"doReplicationform\" name=\"doReplicationForm\" method=\"POST\" action=\"\">
									<input id=\"hn_ts_rpl_submit\"
									type=\"submit\" 
									name=\"rpl.$row->replication_id\" 
									class=\"button-secondary\" 
									value=\"Replicate\" />
							</form>
							<img id=\"hn_ts_rpl_loading-$row->replication_id\" src=\"".admin_url('/images/wpspin_light.gif')."\" 
								class=\"waiting\" style=\"display:none;\" />
						</td>
					</tr>";
					echo $RowString;
				}
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

