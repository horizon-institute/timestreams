<?php
	function hn_ts_addContextRecord(){
		?>			
			<h3>Add Metadata Record</h3>
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
	}
?>