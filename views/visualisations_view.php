<?php

	function hn_ts_showVisualisations()
	{
		$visDir = HN_TS_PLUGIN_DIR . '/visualisations/';
		$visDirs = glob($visDir . "*", GLOB_ONLYDIR);
	
		?>
		
		<table class="form-table">
		<?php
		
		foreach($visDirs as $viz)
		{
			$vizName = basename($viz);
			require_once( HN_TS_PLUGIN_DIR . '/visualisations/' . $vizName . '/viz.php'     );
		
			$vizInstance = new $vizName(null, null, null);
			
			echo "<tr>";
			echo "<td>$vizName</td>";
			echo "<td>";
			$vizInstance->describe();
			echo "</td>";
			echo "</tr>";
		}
		
		?>
		</table>
		<?php
	}
?>