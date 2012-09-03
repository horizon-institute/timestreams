<?php
/**
 * Functions to control the metadata table
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add edit functionality
 */

/**
 * A form to add metadata records
 * Todo: add validation & make messages stand out more
 */
	function hn_ts_addMetadataRecord(){
		?>			
			<h3>Add Metadata Record</h3>			
			<form id="metadataform" method="post" action="">
				<table class="form-table">
			        <tr valign="top">
				        <th scope="row">Measurement Type *</th>
				        <td>
					        <input type="text" name="measurement_type"  />
					    </td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row">Minimum value</th>
			        <td><input type="text" name="minimum" />
			        </td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Maximum value</th>
			        <td><input type="text" name="maximum" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Unit *</th>
			        <td><input type="text" name="unit" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Unit symbol</th>
			        <td><input type="text" name="unit_symbol"  /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Device Details</th>
			        <td><input type="text" name="device"  /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Other Information</th>
			        <td><input type="text" name="other" 			/></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Data Type *</th>
			        <td><input type="text" name="datatype" 			/></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row">Missing Data Value</th>
			        <td><input type="text" name="missingDataValue" 			/></td>
			        </tr>
			        
			    </table>
			    
			    <p class="submit">
			    <input type="submit" name='submit' class="button-primary" value="<?php _e('Add Metadata Record') ?>" />
			    </p>
			
			</form>
			<hr />
			<?php
				if(isset($_POST['measurement_type']) && $_POST['measurement_type'] && 
					isset($_POST['unit']) && $_POST['unit'] &&
					isset($_POST['datatype']) && $_POST['datatype']) {
					$db = new Hn_TS_Database();
					$db->hn_ts_addMetadataRecord("",
						$_POST['measurement_type'], $_POST['minimum'], 
						$_POST['maximum'], $_POST['unit'], $_POST['unit_symbol'], 
						$_POST['device'], $_POST['other'],$_POST['datatype'],
						$_POST['missingDataValue']
					);
					echo 'Record added.';
				}	
			}	