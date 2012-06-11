<?php
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
			    </table>
			    
			    <p class="submit">
			    <input type="submit" class="button-primary" value="<?php _e('Add Context Record') ?>" />
			    </p>
			
			</form>
			<hr />
			<?php
				if(isset($_POST['context_type']) && $_POST['context_type'] && 
						isset($_POST['context_value']) && $_POST['context_value']) {
					$db = new Hn_TS_Database();
					$db->hn_ts_addContextRecord(
						$_POST['context_type'], $_POST['context_value']
					);
					echo 'Record added.';
				}	
	}
?>