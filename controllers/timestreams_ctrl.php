<?php

	function hn_ts_addTimestream()
	{
		$db = new Hn_TS_Database();
		$datasources = $db->hn_ts_select('wp_ts_metadata');
		
		?>
		<h3>Create a new Timestream</h3>			
			<form id="timestreamform" method="post" action="">
				<table class="form-table">
					<tr valign="top">
			        <th scope="row">Timestream Name *</th>
			        <td><input type="text" name="timestream_name" />
			        </td>
			        <th scope="row">Data Source *</th>
					<td>
						<select name="timestream_data"/>
						<?php
						foreach($datasources as $meta)
						{
							echo "<option value=\"" . $meta->metadata_id . "\">" . $meta->tablename . " " . $meta->measurement_type . " " . $meta->device_details . "</option>";
						}
						?>
						</select>
			        </td>
			        </tr>
			    </table>
			    
			    <p class="submit">
			    <input type="hidden" name='command' value='add'/>
			    <input type="submit" class="button-primary" value="<?php _e('Create Timestream') ?>" />
			    </p>
			
			</form>
			<hr />
		<?php
				
		if(isset($_POST["command"]))
		{
			$db = new Hn_TS_Database();
					
			if(!strcmp($_POST["command"], "add"))
			{
				if(isset($_POST["timestream_name"]) && isset($_POST["timestream_data"]))
				{
					$db->hn_ts_addTimestream($_POST["timestream_name"], $_POST["timestream_data"]);
				}
			}
			else if(!strcmp($_POST["command"], "update"))
			{
				if(isset($_POST["timestream_id"]) && isset($_POST["timestream_data"]))
				{
					$db->hn_ts_updateTimestream($_POST["timestream_id"], $_POST["timestream_data"]);
				}		
			}
			else if(!strcmp($_POST["command"], "delete"))
			{
				if(isset($_POST["timestream_id"]))
				{
					$db->hn_ts_deleteTimestream($_POST["timestream_id"]);
				}		
			}
		}
	}
?>