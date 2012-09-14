<?php

	/**
	 * Enqueue javascript scripts
	 */
	function hn_ts_load_TimeStreams_scripts($hook){
		global $hn_ts_admin_timestreams;
	
		if($hook != $hn_ts_admin_timestreams){
			return;
		}
	
		wp_enqueue_script('dygraph', '/wp-content/plugins/timestreams/js/dygraph-combined.js');
		wp_enqueue_script('rpc', '/wp-content/plugins/timestreams/js/rpc.js');
		wp_enqueue_script('timestreams-interface', '/wp-content/plugins/timestreams/js/timestreams-interface.js');
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script('ts-ajax', '/wp-content/plugins/timestreams/js/hn_ts_ajax.js');
	}
	add_action('admin_enqueue_scripts', 'hn_ts_load_TimeStreams_scripts');

	function hn_ts_showTimestreams()
	{
		?>
		<h3><?php _e('Timestreams'); ?></h3>
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
			echo "<td><input type=\"submit\" name=\"command\" class=\"button-primary\" value=\""; _e('update'); echo "\"></td>";
			echo "<td><input type=\"submit\" name=\"command\" class=\"button-primary\" value=\""; _e('delete'); echo "\"></td>";
			echo "</form></tr></table>";
			
			echo "<table>";
			echo "<tr>";
			
			echo "<td>";
			echo "<div id=\"timestream_" . $timestream->timestream_id . "\" style=\"width:800px; height:200px;\"></div>";
			echo "</td>";
			
			echo "<td>";
			echo "<div style=\"width:200px; height:200px; padding-bottom: 1em;\">";
			
			_e('head time'); echo ": <br><input type=\"text\" name=\"head\" id=\"timestream_" . $timestream->timestream_id . "_head\"></input><br>";
			_e('start time'); echo ": <br><input type=\"text\" name=\"start\" id=\"timestream_" . $timestream->timestream_id . "_start\"></input><br>";
			_e('end time'); echo ": <br><input type=\"text\" name=\"end\" id=\"timestream_" . $timestream->timestream_id . "_end\"></input><br>";
			_e('start / end time disabled'); echo ": <input type=\"checkbox\" name=\"endEnable\" value=\"true\" onclick=timestreams[" . $timestream->timestream_id . "].toggleStartEnd() /><br>";
			_e('rate'); echo ": <br><input type=\"text\" name=\"rate\" id=\"timestream_" . $timestream->timestream_id . "_rate\" value=\"" . $head->rate . "\"></input><br>";
			
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(2)\">";_e('start'); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(3)\">";_e('end'); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(1)\">";_e('head'); echo "</a><br />";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].prev()\">";_e('prev'); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].next()\">";_e('next'); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].latest()\">";_e('latest'); echo "</a><br />";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].save()\">";_e('save'); echo "</a><br />";
			
			echo "</div>";
			echo "</td>";

			echo "</tr>";
			echo "</table>";

			if($metadata!=null && $head!=null)
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
						<?php echo $metadata->max_value; ?>,
						"<?php echo $metadata->unit; ?>");

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