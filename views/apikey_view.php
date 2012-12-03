<?php
/**
 * Functions to display API keys
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

/**
 * Displays context information in a table.
 * To do: Complete pagination functionality.
 */
function hn_ts_showApiKeys(){
	?>
		<h3>API Keys</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>Creation Date</th>
					<th>Public Key</th>
					<th>Reveal Private Key</th>
					<th>Revoke Key</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>Creation Date</th>
					<th>Public Key</th>
					<th>Reveal Private Key</th>
					<th>Revoke Key</th>
				</tr>
			</tfoot>
			<tbody>
			<?php 
			$db = new Hn_TS_Database();
			$rows = $db->hn_ts_select_apiKeys();
			global $pagenow;
			if(isset($_REQUEST['hn_ts_l']) && isset($_REQUEST['hn_ts_key'])) {
				echo $db->hn_ts_revealPrivateAPIKey($_REQUEST['hn_ts_key']);
			}
			if(isset($_REQUEST['hn_ts_k']) && isset($_REQUEST['hn_ts_key'])) {
				if($db->hn_ts_revokeAPIKey($_REQUEST['hn_ts_key'])){
					$rows = $db->hn_ts_select_apiKeys();
					echo 'Key revoked.';
				}
			}
			echo '<hr />';
			foreach ( $rows as $row ){
				echo "
				<tr>
					<td>$row->creation_date</td>
					<td>$row->publickey</td>
					<td><a class='button-primary'  href=\"$pagenow
					?page=timestreams/admin/interface.phpapikeys&hn_ts_l=true&hn_ts_key=$row->publickey\">
					Reveal Private Key</a></td>
					<td><a class='button-primary'  href=\"$pagenow
					?page=timestreams/admin/interface.phpapikeys&hn_ts_k=true&hn_ts_key=$row->publickey\">
					Revoke Key</a></td>
				";
				//<td>$showPrivatebtn | $revokebtn</td>
			}
}
?>