<?php
			
	wp_enqueue_script('dygraph', '/wp-content/plugins/timestreams/js/dygraph-combined.js');
	wp_enqueue_script('rpc', '/wp-content/plugins/timestreams/js/rpc.js');
	wp_enqueue_script('timestreams-interface', '/wp-content/plugins/timestreams/js/timestreams-interface.js');

	function hn_ts_showTimestreams()
	{
		?>
		<h3>Timestreams</h3>
		<script type="text/javascript">
			var timestreams = [];
		</script>
		
		<?php
		
		$db = new Hn_TS_Database();
		
		// data sources
		$metarows = $db->hn_ts_select('wp_ts_metadata');
		// TODO limit / ownership / group
		$timestreams = $db->hn_ts_getTimestreams();
		
		foreach($timestreams as $timestream)
		{
			echo "<table><tr>";
			echo "<td><h4>$timestream->name</h4></td>";
			echo "<form id=\"timestream_" . $timestream->timestream_id . "_frm\" method=\"post\" action=\"\">";
			echo "<td><select name=\"timestream_data\">";
			
			$head = $db->hn_ts_getReadHead($timestream->head_id);
			$metadata = $db->hn_ts_getMetadata($timestream->metadata_id);
			
			foreach($metarows as $meta)
			{
				$selected = "";
				
				if(!strcmp($meta->metadata_id, $timestream->metadata_id))
				{
					$selected = "selected=\"selected\"";
				}
			
				echo "<option " . $selected . " value=\"" . $meta->metadata_id . "\">" . $meta->tablename . " " . $meta->measurement_type . " " . $meta->device_details . "</option>";					
			}
			
			echo "</select></td>";
			echo "<input type=\"hidden\" name=\"timestream_id\" value=\"" . $timestream->timestream_id . "\">";
			echo "<td><input type=\"submit\" name=\"command\" class=\"button-primary\" value=\"update\"></td>";
			echo "<td><input type=\"submit\" name=\"command\" class=\"button-primary\" value=\"delete\"></td>";
			echo "</form></tr></table>";
			
			echo "<table>";
			echo "<tr>";
			
			echo "<td>";
			echo "<div id=\"timestream_" . $timestream->timestream_id . "\" style=\"width:800px; height:200px;\"></div>";
			echo "</td>";
			
			echo "<td>";
			echo "<div style=\"width:200px; height:200px\">";
			
			echo "head time: <br><input type=\"text\" name=\"head\" id=\"timestream_" . $timestream->timestream_id . "_head\"></input><br>";
			echo "start time: <br><input type=\"text\" name=\"start\" id=\"timestream_" . $timestream->timestream_id . "_start\"></input><br>";
			echo "end time: <br><input type=\"text\" name=\"end\" id=\"timestream_" . $timestream->timestream_id . "_end\"></input><br>";
			echo "start / end time disabled: <input type=\"checkbox\" name=\"endEnable\" value=\"true\" onclick=timestreams[" . $timestream->timestream_id . "].toggleStartEnd() /><br>";
			echo "rate: <br><input type=\"text\" name=\"rate\" id=\"timestream_" . $timestream->timestream_id . "_rate\" value=\"" . $head->rate . "\"></input><br>";
			
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(1)\">head</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(2)\">start</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(3)\">end </a>";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].save()\">save </a>";
			
			echo "</div>";
			echo "</td>";

			echo "</tr>";
			echo "</table>";

			if($metadata!=null && $head!=nulll)
			{

			?>

			<script type="text/javascript">

				timestreams[<?php echo $timestream->timestream_id; ?>] = new Timestream(
						"<?php echo site_url(); ?>/xmlrpc.php",
						<?php echo $timestream->timestream_id; ?>,
						"<?php echo $metadata->tablename; ?>",
						<?php echo $db->hn_ts_ext_get_time(); ?>,
						<?php echo strtotime($timestream->starttime) ?>,
						<?php echo strtotime($timestream->endtime) ?>,
						<?php echo $head->rate ?>,
						<?php echo $metadata->min_value; ?>,
						<?php echo $metadata->max_value; ?>);

			</script>

			<?php
			}
			else
			{
				// no datasource yet
			}
		}
	}
?>