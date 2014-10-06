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
		global $wpdb;

		?>	<button id="hide_ts_ds_form" class="button-primary"><?php _e('Hide Add Measurement Container Form',HN_TS_NAME);?></button>
			<button id="show_ts_ds_form" class="button-primary"><?php _e('Add Measurement Container',HN_TS_NAME);?></button>			
			<div id="ts_ds_form">		
			<h3><?php _e('Add New Measurement Container'); ?></h3><h4>Required fields have * next to them.</h4> 	
			<form id="metadataform" method="post" action="">
				<table class="form-table">	        			        
			        <tr valign="top">
				        <th scope="row"><?php _e('What do you want to call this measurement container?<br/>Make sure to use a unique name. For example \'Horizon outside temperature\'',HN_TS_NAME); ?>
				        </th>
				        <td><input type="text" name="hn_ts_friendlynametb" />*</td>
			        </tr>	
			        
			        <tr valign="top">
				        <th scope="row">
				        <?php _e('What are you measuring?<br/>For example temperature, forest_pictures, CO2',HN_TS_NAME); ?><br/>
				        <input class="button-secondary" type="button" onclick="alert('<?php  hn_ts_exaplinMeasuring()?>')" value="More information">
				        </th>
				        <td>
					        <input id="hn_ts_measurementTB" type="text" name="measurement_type"  />*
					        <select id="hn_ts_measurementDD">
					        	<option value="">Common measurement types</option>
					        	<option value="CO2">CO2</option>
					        	<option value="noise">noise levels</option>
					        	<option value="humidity">humidity</option>
					        	<option value="temperature">temperature</option>
					        	<option value="images">images</option>
					        	<option value="messages">messages</option>
					        	<option value="hive temperature">hive temperature</option>
					        	<option value="hive weight">hive weight</option>
					        	<option value="hive humidity">hive humidity</option>
					        	<option value="hive feeder weight">hive feeder weight</option>
					        </select>
					    </td>
			        </tr>		        
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What unit of measurement do you want to use?<br/>For example: text/x-data-celsius, image/png, text/x-data-ppm',HN_TS_NAME); ?>
			        	<br/><input class="button-secondary" type="button" onclick="alert('<?php hn_ts_explainUnitOfMeasure()?>')" value="More information">
			        </th>
			        <td><input id="hn_ts_unittb" type="text" name="unit" />*</td>
			        </tr>
			       
			        <tr valign="top">
			        <th scope="row"><?php _e('What symbol do you want to use for this unit?<br /> For example C can be used for Celsius or % could be used for percent)?',HN_TS_NAME); ?>
			        </th>
			        <td><input id="hn_ts_unitsymboltb" type="text" name="unit_symbol"  /></td>
			        </tr>
		            
		            <tr valign="top">
			        <th scope="row"><?php _e('What Data Type do you want to use to store your values?',HN_TS_NAME); ?>
			        <br/><input class="button-secondary" type="button" onclick="alert('<?php hn_ts_exaplinDatatypes()?>')" value="More information"></th>
			        <td><input id="hn_ts_datatypetb" type="text" name="datatype" />*</td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What do you want to call the device or person collecting the data?<br /> For example Rachel\'s Eco Sense)?',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="device"  /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What is the lowest measurement value you expect to be recorded?',HN_TS_NAME); ?>
			        <br/><input class="button-secondary" type="button" onclick="alert('<?php hn_ts_whereInfo()?>')" value="More information">
			        </th>
			        <td><input id="hn_ts_lowval" type="text" name="minimum" />
			        </td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What is the highest measurement value you expect to be recorded?',HN_TS_NAME); ?>
			        <br/><input class="button-secondary" type="button" onclick="alert('<?php hn_ts_whereInfo()?>')" value="More information">
			        </th>
			        <td><input id="hn_ts_highval" type="text" name="maximum" /></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('What value does your device use if it has an error or a missing value?',HN_TS_NAME); ?>
			        <br/><input class="button-secondary" type="button" onclick="alert('<?php hn_ts_whereInfo()?>')" value="More information">
			        </th>
			        <td><input type="text" name="missingDataValue" 			/></td>
			        </tr>
			        
			        <tr valign="top">
			        <th scope="row"><?php _e('Any other information?<br/>This can help you recognise the measurement source.):',HN_TS_NAME); ?>
			        </th>
			        <td><input type="text" name="other" 			/></td>
			        </tr>
			        
			        <tr valign="top">
				        <th scope="row">
				        <?php 
				        _e('How do you want to license this data?',HN_TS_NAME);
				        $db = new Hn_TS_Database();
				        $rows = $db->hn_ts_select("wp_ts_datalicenses");
				        ?><br/>
				        <input class="button-secondary" type="button" onclick="alert(
				        '<?php hn_ts_explainLicensing()?>')" value="More information">
				        </th>
				        <td>
					        <select id="hn_ts_licensesDD" name="hn_ts_licensesDD">
					        	<option value="">Standard Licenses</option>
					        	<?php foreach ($rows as $row){
			        				echo "<option value=\"$row->id\">$row->name ($row->shortname)</option>";
								}?>
					        </select>
					    </td>
			        </tr>		
			        
			    </table>
			    
			    <p class="submit">
			    <input type="submit" name='submit' class="button-primary" value="<?php _e('Add Measurement Container',HN_TS_NAME) ?>" />
			    </p>
			
			</form>
			</div>
			<hr />
			<?php
				if(!count($_POST)){
					return;
				}
				if(isset($_POST['measurement_type']) && 
					isset($_POST['unit']) &&
					isset($_POST['datatype'])) {
					$db = new Hn_TS_Database();
					$table = $db->hn_ts_addMetadataRecord("",
						$_POST['measurement_type'], $_POST['minimum'], 
						$_POST['maximum'], $_POST['unit'], $_POST['unit_symbol'], 
						$_POST['device'], $_POST['other'],$_POST['datatype'],
						$_POST['missingDataValue'], $_POST['hn_ts_friendlynametb'], 
							$_POST['hn_ts_licensesDD']
					);
					if(isset($table)){
						
						if(0==strcmp("integer",gettype($table))){
							echo "Record added: $table";
							return;
						} else if(
								0==strcmp("object",gettype($table)) && 
								0==strcmp(get_class($table),"IXR_Error")){
							echo $table->message;
							return;
						}
						
					}
					echo "Unable to add record.";
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

function hn_ts_explainUnitOfMeasure(){
	_e('When measuring it is important to state the unit being measured by. For example Celsius can be used to measure temperature. For this form, the values for the unit of measurement field should be entered in internet media type format (see http://en.wikipedia.org/wiki/Internet_media_type). If sensor data is being stored then follow a protocol of: text/x-data-Unit, where Unit would be the unit of measurement (such as Celsius or Decibels). Alternatively if a media type such as picture is stored then use a media type such as image/png.');
}

function hn_ts_exaplinMeasuring(){
	_e('Please only use basic Latin letters (a-zA-Z), digits (0-9), dollar ($), and underscore (_) characters to describe the type of measurements you are taking.');
}

function hn_ts_exaplinDatatypes(){
	_e('Data Types are used to store your data in the correct format. For instance, if you are storing image files you would want to use a textual type (VARCHR(255)), but if you are storing temperature readings between 0 and 100 then you would want to use a numeric type (DECIMAL(4,1)). You may use any of the standard MySQL ones as described here: https://dev.mysql.com/doc/refman/5.5/en/data-types.html. The following are common choices for different measurement types: CO2 levels (parts per million) - decimal(5,2); noise (decibels) - smallint; humidity (%) - smallint; battery (volts) - decimal(4,2); temperature (Celsius) - decimal(3,1); image files (URL) - varchar(255)');
}

function hn_ts_whereInfo(){
	_e('You might be able to find this out from the packaging your sensor came in or from the person you received the sensor from.');
}

function hn_ts_explainLicensing(){
	_e('Licences are legal instruments that let you permit others to do things with your data that would otherwise infringe on your rights as the data copyright owner. See www.dcc.ac.uk/resources/how-guides/license-research-data for more information about licensing data. If you wish to use use an alternative license, select other and include details about the license (prefereably with a URL to it) in the Any other information? field above.');
}