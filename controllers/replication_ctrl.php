<?php
/**
 * Functions to Add/delete replication entries into replication table
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add edit/delete functionality
 */

	/**
	 * A form to add replication records
	 * Todo: add validation & make messages stand out more
	 * Pass in username and password to hn_ts_insert_replication
	 */
	function hn_ts_addReplicationRecord(){
		?>			
			<h3>Add Replication Record</h3>			
			<form id="replicationform" method="post" action="">
				<table class="form-table">
			        <tr valign="top">
				        <th scope="row">Local Table *</th>
				        <td>
					        <input type="text" name="local_table"  />
					    </td>
			        </tr>
			         
			        <tr valign="top">
				        <th scope="row">Remote-User Login Name *</th>
				       	 <td>
				       	 	<input type="text" name="remote_user_login" />
				        </td>
			        </tr>
			        
			        <tr valign="top">
				        <th scope="row">Remote-User Password *</th>
				        <td>
				        	<input type="password" name="pwrd" />
				        </td>
			        </tr>
			        
			        <tr valign="top">
				        <th scope="row">Remote Url *</th>
				        <td>
				        	<input type="text" name="remote_url" />
				        </td>
			        </tr>
			        
			        <tr valign="top">
				        <th scope="row">Remote Table *</th>
				        	<td>
				        		<input type="text" name="remote_table"  />
				        	</td>
			        </tr>
			        
			        <tr valign="top">
				        <th scope="row">Continuous</th>
				        <td>
				        	<input type="checkbox" name="continuous" value="Yes" />
				        </td>
			        </tr>
			        
			    </table>
			    
			    <p class="submit">
			    	<input type="submit" name='submit' class="button-primary" value="<?php _e('Add Replication Record') ?>" />
			    </p>
			
			</form>
			<hr />
			<?php
			$cont = 0;
				if(isset($_POST['continuous']) && $_POST['continuous'] == 'Yes'){
					$cont = 1;
				}
				if(isset($_POST['local_table']) && $_POST['local_table'] && 
					isset($_POST['remote_user_login']) && $_POST['remote_user_login'] &&
					isset($_POST['pwrd']) && $_POST['pwrd'] &&
					isset($_POST['remote_url']) && $_POST['remote_url'] &&
					isset($_POST['remote_table']) && $_POST['remote_table']) {
						$db = new Hn_TS_Database();
						$replication = $db->hn_ts_insert_replication(array("","",
							$_POST['local_table'], $_POST['remote_user_login'], 
							$_POST['pwrd'], $_POST['remote_url'], $_POST['remote_table'], 
							$cont,"")
						);
						echo 'Record added.';
					}	
			}	