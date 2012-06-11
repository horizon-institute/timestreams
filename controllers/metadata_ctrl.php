<?php
	function hn_ts_addMetadataRecord(){
		?>			
			<h3>Add Metadata Record</h3>
			<form method="post" action="">
				<table class="form-table">
			        <tr valign="top">
			        <th scope="row">Measurement Type</th>
			        <td><input type="text" name="measurement_type" 
			        value="<?php echo get_option('measurement_type'); ?>" /></td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row">Minimum value</th>
			        <td><input type="text" name="minimum"
			        value="<?php echo get_option('minimum'); ?>" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Maximum value</th>
			        <td><input type="text" name="maximum" 
			        value="<?php echo get_option('maximum'); ?>" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Unit</th>
			        <td><input type="text" name="unit" 
			        value="<?php echo get_option('unit'); ?>" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Unit symbol</th>
			        <td><input type="text" name="unit_symbol" 
			        value="<?php echo get_option('unit_symbol'); ?>" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Device Details</th>
			        <td><input type="text" name="device" 
			        value="<?php echo get_option('device'); ?>" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Other Information</th>
			        <td><input type="text" name="other" 
			        value="<?php echo get_option('other'); ?>" /></td>
			        </tr>
			    </table>
			    
			    <p class="submit">
			    <input type="submit" class="button-primary" value="<?php _e('Add Metadata Record') ?>" />
			    </p>
			
			</form>
			<hr />
			<?php
	}
?>