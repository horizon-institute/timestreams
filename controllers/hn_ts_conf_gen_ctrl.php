<?php/**
 * Functions to generate a config file for Beelab software
 * Author: Jesse Blum (JMB)
 * Date: 2013
 * To do: Complete the functionality
 */

/**
 * Config generation wizard
 */
function hn_ts_addConfGenCtrl(){
		$db = new Hn_TS_Database();
		?>
		<form method="post" action="">
			<table class="form-table">
		        <tr valign="top">
			        <th scope="row"><?php _e('Select API Key',HN_TS_NAME); ?></th>
			        <td>
			        	<select name="api_key" >
						<?php					
						$datasources =  $db->hn_ts_select_apiKeys();	
						foreach($datasources as $meta)	{
							echo "<option value=\"" . $meta->publickey . "\">" . 
							$meta->publickey . "</option>";
						}
						?>
						</select>
			        </td>
		        </tr>
		         
		        <tr valign="top">
			        <th scope="row"><?php _e('Select Temperature Measurement Container',
			        		HN_TS_NAME); ?></th>
			        <td>
			        	<select name="temp_data" >
							<?php						
							$datasources = $db->hn_ts_select_viewable_metadata();
							foreach($datasources as $meta)	{
								echo "<option value=\"" . $meta->tablename . "\">" . 
								$meta->friendlyname . "</option>";
							}
							?>
						</select>
					</td>
		        </tr>
		         
		        <tr valign="top">
			        <th scope="row"><?php _e('Select Humidity Measurement Container',
			        		HN_TS_NAME); ?></th>
			        <td>
			        	<select name="hum_data" >
							<?php						
							$datasources = $db->hn_ts_select_viewable_metadata();
							foreach($datasources as $meta)	{
								echo "<option value=\"" . $meta->tablename . "\">" . 
								$meta->friendlyname . "</option>";
							}
							?>
						</select>
					</td>
		        </tr>
		         
		        <tr valign="top">
			        <th scope="row"><?php _e('Select Hive Weight Measurement Container',
			        		HN_TS_NAME); ?></th>
			        <td>
			        	<select name="hiveWeight_data" >
							<?php						
							$datasources = $db->hn_ts_select_viewable_metadata();
							foreach($datasources as $meta)	{
								echo "<option value=\"" . $meta->tablename . "\">" . 
								$meta->friendlyname . "</option>";
							}
							?>
						</select>
					</td>
		        </tr>
		        
		        <tr valign="top">
			        <th scope="row"><?php _e('Select Feeder Weight Measurement Container',
			        		HN_TS_NAME); ?></th>
			        <td>
			        	<select name="feederWeight_data" >
							<?php						
							$datasources = $db->hn_ts_select_viewable_metadata();
							foreach($datasources as $meta)	{
								echo "<option value=\"" . $meta->tablename . "\">" . 
								$meta->friendlyname . "</option>";
							}
							?>
						</select>
					</td>
		        </tr>
		    </table>
		    
		    <p class="submit">
		    <input type="submit" class="button-primary" value="<?php _e('Generate Config File',HN_TS_NAME) ?>" />
		    </p>
		
		</form>
		<hr />
		<?php
			if(isset($_POST['api_key']) && $_POST['api_key'] && 
					isset($_POST['temp_data']) && $_POST['temp_data'] && 
					isset($_POST['hum_data']) && $_POST['hum_data'] && 
					isset($_POST['hiveWeight_data']) && $_POST['hiveWeight_data'] && 
					isset($_POST['feederWeight_data']) && $_POST['feederWeight_data']) {
				
				
				$prikey =  $db->hn_ts_revealPrivateAPIKey($_POST['api_key']);
				
				$content = $_POST['api_key']. "\n" . 
					$prikey . "\n" .
					$_POST['temp_data']. "\n" .  
					$_POST['hum_data']. "\n" .  
					$_POST['hiveWeight_data']. "\n" . 
					$_POST['feederWeight_data'];
				 
				$uploads = wp_upload_dir();
				$filename = "/beelabapi.config";
				$filepath = $uploads['path'] . $filename;
				$file = fopen($filepath, "wb");
				fwrite ($file,$content);
				fclose($file);
				echo '<a href="' . $uploads['url'] . $filename . 
				'" title="Beelab configuration file">Click here to download configuration file</a>';
			}	
	}?>	