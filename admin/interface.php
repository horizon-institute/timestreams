<?php
/**
 * Functions to provide admin functionality
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

	add_action('admin_menu', 'hn_ts_add_admin_menus');
	require_once( HN_TS_ADMIN_DIR . '/settings.php' );
	
	/**
	 * Timestreams Admin menu structure
	 */
	function hn_ts_add_admin_menus(){
		global $hn_ts_admin_timestreams;
		$hn_ts_admin_timestreams = add_menu_page('Timestreams', __('Timestreams'),
				'administrator', __FILE__, 'hn_ts_main_admin_page');
		add_submenu_page(__FILE__, 'Data Sources', __('Data Sources'), 'manage_options',
				__FILE__.'datasources','hn_ts_datasources_admin_page');
		add_submenu_page(__FILE__, 'Visualisations', __('Visualisations'), 'manage_options',
				__FILE__.'visualisations','hn_ts_visualisations_admin_page');				
		add_submenu_page(__FILE__, 'Context', __('Context'), 'manage_options',
				__FILE__.'context','hn_ts_context_admin_page');
		global $hn_ts_admin_page_repl;
		$hn_ts_admin_page_repl = add_submenu_page(__FILE__, 'Replication', __('Replication'), 'manage_options',
				__FILE__.'replication','hn_ts_replication_admin_page');
	}
	
	/**
	 * Displays top level timestreams admin page
	 */
	function hn_ts_main_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-index" class="icon32"></div>
			<h2 style="padding-bottom: 1em;"><?php _e('Timestreams'); ?></h2>
			<button id="hide_ts_description" class="button-primary"><?php _e('Hide Description');?></button>
			<button id="show_ts_description" class="button-primary"><?php _e(' Show Description');?></button>			
			<h3 class="ts_description"><?php _e('Description'); ?></h3>
			<p class="ts_description"><?php _e('Welcome to the Timestreams Wordpress plugin from the <a href="http://horizab1.miniserver.com/relate/" title="Relate Project website">Relate project</a>. Timestreams is developed in collaboration between <a href="http://www.horizon.ac.uk" title="Horizon website">Horizon Digital Economy Research Institute</a> of the University of Nottingham, <a href="http://www.i-am-ai.net" title="Active Ingredient website">Active Ingredient</a>, The Met Office Hadley Centre, Brazilian curator Silvia Leal, University of Exeter, and a number of communities in Brazil and the UK. The project is funded by <a href="http://www.rcuk.ac.uk/" title="RCUK website">RCUK</a>.');?></p>
			<p class="ts_description"><?php _e('Timestreams is an online platform built in Wordpress for capturing, interpreting and visualising time series data. The platform has been developed for artists to capture and conceptualise environmental data, to look at the relationship between climate change and energy.  Timestreams will be released after this research phase to the public, designed for artists, developers, hackers, amateur scientists, schools and communities to set up their own ‘Timestream’ projects.');?></p>
			<p class="ts_description"><?php _e('Once you have set up your timestreams you can choose to output the data as a blog post or a separate webpage, there is also a library of visualisations that are available to embed in your post or webpage. <a href="https://github.com/pszjmb1/Timestreams/wiki/Timestreams-API-Documentation" title="Timestreams api">Click here</a> to learn how to send Timestreams data to a non-web based visualisation or Arduino using the API.');?></p>
			<hr class="ts_description" />
			<button id="hide_ts_instructions" class="button-primary">Hide Instructions</button>
			<button id="show_ts_instructions" class="button-primary">Show Instructions</button>
			<h3 class="ts_instructions">Using Timestreams</h3>
			<p class="ts_instructions"><?php _e('Timestreams can be used with existing data (basic technical skills) on the site and with new data you collect yourself (moderate technical skills).'.
			' You can collect data manually using our upload app or Timesense tool kit.'.
			' Go to <a href="http://192.168.56.101/wordpress/wp-admin/admin.php?page=timestreams/admin/interface.phpdatasources" title="datasources">the datasources page</a> for more information on collecting your own data.'); ?></p>
			<h3 class="ts_instructions"><?php _e('To create a Timestream:'); ?></h3>
			<ol class="ts_instructions"><li><?php _e('Type in a name for your timestream'); ?></li>
			<li><?php _e('Choose the data source that you would like to include in your post, page or output application.'); ?></li>
			<li><?php _e('Choose the range of data that you would like to appear in your post, blog or by used by the API.'); ?></li>
			<li><?php _e('You can set up as many timestreams as you want to output to your post, webpage or API.'); ?></li></ol>
			<p class="ts_instructions"><?php _e('Once you have set up your timestream the data will appear on a timeline below as a graph with an x and y axis. The way it is displayed depends on what type of data it is:'); ?></p>
			<ul class="ts_instructions" style="list-style-type:circle; padding-left:2em;"><li><?php _e('Numerical data will appear as a bar graph with the units of measurement on the x axis and the measurement of the data on the y axis.'); ?></li>
			<li><?php _e('Media and text files will appear as a timeline of the items that you can scroll through.'); ?></li></ul>
			<p class="ts_instructions"><?php _e('Use the scroll bar below the timeline to zoom in and out and move along the timeline.'); ?></p>
			<p class="ts_instructions"><?php _e('You can change timestream being shown by selecting an alternate datasource from the dropdown box above the timestream, and then clicking the update button.'); ?></p>
			<p class="ts_instructions"><?php _e('Delete a timestream by clickign its delete button. Please note that this does not delete the datasource.'); ?></p>
			<h3 class="ts_instructions"><?php _e('To author data'); ?></h3><p class="ts_instructions"><?php _e('You can author your data using your timestream.'); ?></p>
			<ol class="ts_instructions"><li><?php _e('Click start'); ?></li>
			<li><?php _e('Click on the timeline where you want the data in your timestream to begin.'); ?></li>
			<li><?php _e('Click end.'); ?></li>
			<li><?php _e('Click on the timeline where you want the data in your timestream to end.'.
					' A green overlay should appear over the data range that you have chosen.'); ?></li>
			<li><?php _e('Click head and then click the area in the timestream to begin playing from.'); ?></li>
			<li><?php _e('Set the speed that the data plays out using the rate field (1=realtime, 2=double time, 0.5= half time, etc..)'); ?></li>
			<li><?php _e('Click save'); ?></li></ol>
			<p class="ts_instructions"><?php _e('Note that if your toggle on the "start / end time disabled:" checkbox then the timesream will continue to play data as new values come in. If this is toggled off and there is an endtime set, the the data will loop to the start once the end is reached.'); ?></p>
			<hr />
		<?php
		
			hn_ts_addTimestream();
			hn_ts_showTimestreams();
			
		?>
		</div>
		<?php
	}	
	
	/**
	 * Displays timestreams data sources admin page
	 * This can display either the available datasource records
	 * or an individual datasource record
	 */
	function hn_ts_datasources_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-themes" class="icon32"></div>
			<h2><?php _e('Timestreams - Metadata'); ?></h2>
			<h3><?php _e('Description'); ?></h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
			<hr />
		<?php
			
			if(isset($_GET['table'])){				
				hn_ts_showDataRecord($_GET['table']);
			}else{
				hn_ts_addMetadataRecord();
				hn_ts_showMetadataTable();
			}
		?>
		</div>
		<?php
	}	
	
	/**
	 * Displays timestreams context admin page
	 */
	function hn_ts_context_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-edit-pages" class="icon32"></div>
			<h2>Timestreams - Context</h2>
			<h3>Description</h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
			<hr />
		<?php
			hn_ts_addContextRecord();
			hn_ts_showContextTable();
		?>
		</div>
		<?php
	}
	
	/**
	 * Displays timestreams replication admin page
	 */
	function hn_ts_replication_admin_page(){
		?>
		<div class="wrap">
			<div id="icon-edit-pages" class="icon32"></div>
			<h2>Timestreams - Replication</h2>
			<h3>Description</h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
			<hr />
		<?php
			hn_ts_addReplicationRecord();
			hn_ts_showReplicationTable();
		?>
		</div>
		<?php
	}
	
	function hn_ts_visualisations_admin_page()
	{
		?>
		<div class="wrap">
			<div id="icon-edit-pages" class="icon32"></div>
			<h2>Timestreams - Visualisations</h2>
			<h3>Visualisations</h3>
			<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed bibendum elementum sapien, et porttitor enim faucibus at. Sed ut nulla sed turpis dapibus luctus vel non ante. Nunc adipiscing venenatis dui. Morbi vehicula volutpat ornare. Sed non magna id lectus pretium aliquam vitae at velit. Vestibulum posuere pharetra ornare. Pellentesque quis tortor enim, ac molestie urna. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent dignissim augue et urna egestas a sagittis est bibendum. Donec sagittis congue consectetur. Morbi lacinia erat vitae nisl auctor commodo. Donec ut magna id est pretium laoreet. Aenean vitae auctor ligula. Nulla facilisi. Cras ac lorem lacinia justo molestie aliquam varius id ligula. </p>
			<hr />
		<?php
			hn_ts_showVisualisations();
		?>
		</div>
		<?php		
	}
	
?>