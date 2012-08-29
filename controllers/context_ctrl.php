<?php
/**
 * Functions to control the context tables
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add edit functionality
 */

/**
 * A form to add context records
 * Todo: Associate the contexts with data
 */
	function hn_ts_addContextRecord(){
		?>			
			<h3>Add Context Record</h3>
			<form method="post" action="">
				<table class="form-table">
			        <tr valign="top">
			        <th scope="row">Context Type</th>
			        <td><input type="text" name="context_type" 
			        value="<?php echo get_option('context_type'); ?>" /></td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row">Value</th>
			        <td><input type="text" name="context_value"
			        value="<?php echo get_option('context_value'); ?>" /></td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row">Start Time</th>
			        <td><input type="text" name="start_time"
			        value="<?php echo get_option('start_time'); ?>" /></td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row">End Time</th>
			        <td><input type="text" name="end_time"
			        value="<?php echo get_option('end_time'); ?>" /></td>
			        </tr>
			    </table>
			    
			    <p class="submit">
			    <input type="submit" class="button-primary" value="<?php _e('Add Context Record') ?>" />
			    </p>
			
			</form>
			<hr />
			<?php
				if(isset($_POST['context_type']) && $_POST['context_type'] && 
						isset($_POST['context_value']) && $_POST['context_value'] && 
						isset($_POST['start_time']) && $_POST['start_time'] && 
						isset($_POST['end_time']) && $_POST['end_time']) {
					$db = new Hn_TS_Database();
					global $current_user;
					get_currentuserinfo();
					$db->hn_ts_addContextRecordTimestamped(array(
						$current_user->user_ID,
						$current_user->user_pass,$_POST['context_type'], $_POST['context_value'],$_POST['start_time'], 
							$_POST['end_time'])
					);
					echo 'Record added.';
				}	
	}
?>