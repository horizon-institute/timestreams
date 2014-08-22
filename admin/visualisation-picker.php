<?php

	require_once(  '../../../../wp-load.php');
	
	$db = new Hn_TS_Database();

	$timestreams = $db->hn_ts_getTimestreams();
	
	?>
	<form name="timestreampicker" id="timestreamForm" action="#">
	<table class="form-table">
		
		
		<tr>
			<th>Select a Timestream</th>
			<td>
				<select id="timestream" name="timestream">
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
				<select id="viz" name="viz">
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

		</table>
	</form>

		
		<p class="submit">
			<input type="button" class="button-primary" value="Insert Visualisation" onclick="insert()" />
		</p>

<script type="text/javascript">

function insert()
{
	tsSelect = document.getElementById("timestreamForm").elements["timestream"];
	tsId = tsSelect.options[tsSelect.selectedIndex].value;

	vizSelect = timestreamForm.elements["viz"];
	vizName = vizSelect.options[vizSelect.selectedIndex].value;

	shortcode = "[timestream viz=\"" + vizName + "\" tsid=" + tsId + "]";

	tinyMCE.activeEditor.execCommand('mceInsertContent', 0, shortcode);
	tb_remove();
}

</script>
