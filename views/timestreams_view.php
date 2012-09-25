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

	function hn_ts_describeTimestreams(){	
		?>	
		<button id="hide_ts_description" class="button-primary"><?php _e('Hide Description',HN_TS_NAME);?></button>
		<button id="show_ts_description" class="button-primary"><?php _e(' Show Description',HN_TS_NAME);?></button>			
		<h3 class="ts_description"><?php _e('Description',HN_TS_NAME); ?></h3>
		<p class="ts_description"><?php _e('Welcome to the Timestreams Wordpress plugin from the <a href="http://horizab1.miniserver.com/relate/" title="Relate Project website">Relate project</a>. Timestreams is developed in collaboration between <a href="http://www.horizon.ac.uk" title="Horizon website">Horizon Digital Economy Research Institute</a> of the University of Nottingham, <a href="http://www.i-am-ai.net" title="Active Ingredient website">Active Ingredient</a>, The Met Office Hadley Centre, Brazilian curator Silvia Leal, University of Exeter, and a number of communities in Brazil and the UK. The project is funded by <a href="http://www.rcuk.ac.uk/" title="RCUK website">RCUK</a>.',HN_TS_NAME);?></p>
		<p class="ts_description"><?php _e('Timestreams is an online platform built in Wordpress for capturing, interpreting and visualising time series data. The platform has been developed for artists to capture and conceptualise environmental data, to look at the relationship between climate change and energy.  Timestreams will be released after this research phase to the public, designed for artists, developers, hackers, amateur scientists, schools and communities to set up their own Timestream projects.',HN_TS_NAME);?></p>
		<p class="ts_description"><?php _e('Once you have set up your timestreams you can choose to output the data as a blog post or a separate webpage, there is also a library of visualisations that are available to embed in your post or webpage. <a href="https://github.com/pszjmb1/Timestreams/wiki/Timestreams-API-Documentation" title="Timestreams api">Click here</a> to learn how to send Timestreams data to a non-web based visualisation or Arduino using the API.',HN_TS_NAME);?></p>
		<hr class="ts_description" />
		<button id="hide_ts_instructions" class="button-primary">Hide Instructions</button>
		<button id="show_ts_instructions" class="button-primary">Show Instructions</button>
		<h3 class="ts_instructions">Using Timestreams</h3>
		<p class="ts_instructions"><?php _e('Timestreams can be used with existing data (basic technical skills) on the site and with new data you collect yourself (moderate technical skills).'.
		' You can collect data manually using our upload app or Timesense tool kit.'.
		' Go to <a href="http://192.168.56.101/wordpress/wp-admin/admin.php?page=timestreams/admin/interface.phpdatasources" title="datasources">the datasources page</a> for more information on collecting your own data.',HN_TS_NAME); ?></p>
		<h3 class="ts_instructions"><?php _e('To create a Timestream:',HN_TS_NAME); ?></h3>
		<ol class="ts_instructions"><li><?php _e('Type in a name for your timestream',HN_TS_NAME); ?></li>
		<li><?php _e('Choose the data source that you would like to include in your post, page or output application.',HN_TS_NAME); ?></li>
		<li><?php _e('Choose the range of data that you would like to appear in your post, blog or by used by the API.',HN_TS_NAME); ?></li>
		<li><?php _e('You can set up as many timestreams as you want to output to your post, webpage or API.',HN_TS_NAME); ?></li></ol>
		<p class="ts_instructions"><?php _e('Once you have set up your timestream the data will appear on a timeline below as a graph with an x and y axis. The way it is displayed depends on what type of data it is:',HN_TS_NAME); ?></p>
		<ul class="ts_instructions" style="list-style-type:circle; padding-left:2em;"><li><?php _e('Numerical data will appear as a bar graph with the units of measurement on the x axis and the measurement of the data on the y axis.',HN_TS_NAME); ?></li>
		<li><?php _e('Media and text files will appear as a timeline of the items that you can scroll through.',HN_TS_NAME); ?></li></ul>
		<p class="ts_instructions"><?php _e('Use the scroll bar below the timeline to zoom in and out and move along the timeline.',HN_TS_NAME); ?></p>
		<p class="ts_instructions"><?php _e('You can change timestream being shown by selecting an alternate datasource from the dropdown box above the timestream, and then clicking the update button.',HN_TS_NAME); ?></p>
		<p class="ts_instructions"><?php _e('Delete a timestream by clickign its delete button. Please note that this does not delete the datasource.',HN_TS_NAME); ?></p>
		<h3 class="ts_instructions"><?php _e('To author data',HN_TS_NAME); ?></h3><p class="ts_instructions"><?php _e('You can author your data using your timestream.',HN_TS_NAME); ?></p>
		<ol class="ts_instructions"><li><?php _e('Click start',HN_TS_NAME); ?></li>
		<li><?php _e('Click on the timeline where you want the data in your timestream to begin.',HN_TS_NAME); ?></li>
		<li><?php _e('Click end.',HN_TS_NAME); ?></li>
		<li><?php _e('Click on the timeline where you want the data in your timestream to end.'.
				' A green overlay should appear over the data range that you have chosen.',HN_TS_NAME); ?></li>
		<li><?php _e('Click head and then click the area in the timestream to begin playing from.',HN_TS_NAME); ?></li>
		<li><?php _e('Set the speed that the data plays out using the rate field (1=realtime, 2=double time, 0.5= half time, etc..)',HN_TS_NAME); ?></li>
		<li><?php _e('Click save'); ?></li></ol>
		<p class="ts_instructions"><?php _e('Note that if your toggle on the "start / end time disabled:" checkbox then the timesream will continue to play data as new values come in. If this is toggled off and there is an endtime set, the the data will loop to the start once the end is reached.',HN_TS_NAME); ?></p>
		<p class="ts_instructions"><?php _e('After you have authored your timestreams you can put them in blog posts, blog pages, or special timestreams pages.',HN_TS_NAME); ?></p>
		<hr />
		<?php
	}
	function hn_ts_showTimestreams()
	{
		?>
		<style>
		div.ts_preview
		{
			width: 640px;
			height: 480px;
			position: absolute;
			display: block;
			right: 0;
			bottom: 0;
			background-color: #dddddd;
			text-align: left;
  			z-index: 1000;
			}
		</style>

        <div id="ts_preview" class="ts_preview"></div>
		<script type="text/javascript">
			jQuery("#ts_preview").hide();
		</script>
		
		<h3><?php _e('Timestreams',HN_TS_NAME); ?></h3>
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
			
			if(strcmp($timestream->endtime, "0000-00-00 00:00:00")==0)
			{
				$timestream->endtime = "1970-01-01 00:00:00";
			}
			
			if(strcmp($timestream->starttime, "0000-00-00 00:00:00")==0)
			{
				$timestream->starttime = "1970-01-01 00:00:00";
			}
			
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
			echo "<td><input type=\"submit\" name=\"command\" class=\"button-primary\" value=\""; _e('update',HN_TS_NAME); echo "\"></td>";
			echo "<td><input type=\"submit\" name=\"command\" class=\"button-primary\" value=\""; _e('delete',HN_TS_NAME); echo "\"></td>";
			echo "</form></tr></table>";
			
			echo "<table>";
			echo "<tr>";
			
			echo "<td>";
			echo "<div id=\"timestream_" . $timestream->timestream_id . "\" style=\"width:800px; height:200px;\"></div>";
			echo "</td>";
			
			echo "<td>";
			echo "<div style=\"width:200px; height:200px; padding-bottom: 1em;\">";
			
			_e('new head time'); echo ": <br><input type=\"text\" name=\"head\" id=\"timestream_" . $timestream->timestream_id . "_head\"></input><br>";
			_e('new start time'); echo ": <br><input type=\"text\" name=\"start\" id=\"timestream_" . $timestream->timestream_id . "_start\"></input><br>";
			_e('new end time'); echo ": <br><input type=\"text\" name=\"end\" id=\"timestream_" . $timestream->timestream_id . "_end\"></input><br>";
			_e('start / end time disabled'); echo ": <input type=\"checkbox\" name=\"endEnable\" value=\"true\" onclick=timestreams[" . $timestream->timestream_id . "].toggleStartEnd() /><br>";
			_e('rate'); echo ": <br><input type=\"text\" name=\"rate\" id=\"timestream_" . $timestream->timestream_id . "_rate\" value=\"" . $head->rate . "\"></input><br>";

			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(2)\">";_e('start',HN_TS_NAME); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(3)\">";_e('end',HN_TS_NAME); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].setInteractionMode(1)\">";_e('head',HN_TS_NAME); echo "</a><br />";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].prev()\">";_e('prev',HN_TS_NAME); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].next()\">";_e('next',HN_TS_NAME); echo "</a> ";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].latest()\">";_e('latest',HN_TS_NAME); echo "</a><br />";
			echo "<a href=\"javascript:onclick=timestreams[" . $timestream->timestream_id . "].save()\">";_e('save',HN_TS_NAME); echo "</a><br />";
			
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