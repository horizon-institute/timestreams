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
		?>	<button id="hide_ts_ds_form" class="button-primary"><?php _e('Hide Add Measurement Container Form',HN_TS_NAME);?></button>
			<button id="show_ts_ds_form" class="button-primary"><?php _e(' Show Add Measurement Container Form',HN_TS_NAME);?></button>			
			<div id="ts_ds_form">		
			<h3><?php _e('Add New Measurement Container'); ?></h3>			
			<form id="metadataform" method="post" action="">
				<table class="form-table">
			        <tr valign="top">
				        <th scope="row"><?php _e('What are you measuring',HN_TS_NAME); ?>? *</th>
				        <td>
					        <input type="text" name="measurement_type"  />
					    </td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row"><?php _e('What is the minimum value of measurement you expect to be recorded?',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="minimum" />
			        </td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What is the maximum value of measurement you expect to be recorded?',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="maximum" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What is the unit of measurement (e.g. Celsius, png image file)?',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="unit" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What is the symbol of this measurement (e,g, C)?',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="unit_symbol"  /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What is the unique name of device containing your sensor called (e.g. Rachel\'s Eco Sense)?',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="device"  /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('Any Other Information (this is to help you recognise the data source):',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="other" 			/></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What Data Type do you want to use to store your values?',HN_TS_NAME); ?>
			         *</th>
			        <td><input type="text" name="datatype" 			/></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What value does your device use if it has an error or a missing value?',HN_TS_NAME); ?></th>
			        <td><input type="text" name="missingDataValue" 			/></td>
			        </tr>
			        
			    </table>
			    
			    <p class="submit">
			    <input type="submit" name='submit' class="button-primary" value="<?php _e('Add Metadata Record',HN_TS_NAME) ?>" />
			    </p>
			
			</form>
			</div>
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
/**
 * A button to go to the share interface for a given table
 */
function hn_ts_addShareTableButton($tableName){
	//$share = _e("Share",HN_TS_NAME);  // brakes the button for no apparent reason :(
	$share = "Share";
	global $pagenow;
	return "<a class='button-primary' href=\"$pagenow?page=timestreams/admin/interface.phpdatasources&share_button=$tableName\">Share</a>";
			
}