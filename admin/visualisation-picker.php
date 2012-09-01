<?php

	require_once(  '../../../../wp-load.php');
	
	$db = new Hn_TS_Database();

	$timestreams = $db->hn_ts_getTimestreams();
	
	?>
	
	<table class="form-table">
		<form name="timestreampicker" id="timestreamForm" action="#">
		
		<tr>
			<th>Select a Timestream</th>
			<td>
				<select name="timestream">
					<option value=""> - Select - </option>
	<?php
		
	foreach($timestreams as $timestream)
	{
		echo "<option value=\"$timestream->timestream_id\">$timestream->name</option>";
	}
	
	?>
				</select>
			</td>
		</tr>
		
		<tr>
			<th>Select a Visualisation</th>
			<td>
				<select name="viz">
					<option value=""> - Select - </option>
	<?php
	
	$visDir = "../visualisations/";
	$visDirs = glob($visDir . "*", GLOB_ONLYDIR);
	
	foreach($visDirs as $viz)
	{
		$vizName = basename($viz);
		echo "<option value=\"$vizName\">$vizName</option>";
	}

	?>
				</select>
			</td>
		</tr>

		</form>
		</table>
		
		<p class="submit">
			<input type="button" class="button-primary" value="Insert Visualisation" onclick="insert()" />
		</p>

<script type="text/javascript">

function insert()
{
	tsSelect = timestreamForm.elements["timestream"];
	tsId = tsSelect.options[tsSelect.selectedIndex].value;

	vizSelect = timestreamForm.elements["viz"];
	vizName = vizSelect.options[vizSelect.selectedIndex].value;

	shortcode = "[timestream viz=\"" + vizName + "\" tsid=" + tsId + "]";

	tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
	tb_remove();
}

</script>
