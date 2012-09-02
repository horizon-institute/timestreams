<?php

	class Visualisation
	{
		public $name;
		public $timestreamId;
		public $vizId;
		public $description;
		
		public function Visualisation($vizId, $name, $timestreamId)
		{
			$this->vizId = $vizId;
			$this->name = $name;
			$this->timestreamId = $timestreamId;
		}
		
		public function write()
		{
			echo $this->vizId . " " . $this->name . " " . $this->timestreamId;
		}
		
		public function describe()
		{
			echo "This visualisation does not have a description";
		}
	}
		
	add_shortcode( 'timestream', 'hn_ts_shortcode_handler' );
	
	function hn_ts_shortcode_handler($atts)
	{
		global $post;
		
		extract( shortcode_atts( array(
			'tsid' => -1,
			'viz' => 'none',
		), $atts ) );
		
		if(strcmp($viz, 'none') != 0 && $tsid != -1)
		{
			require_once( HN_TS_PLUGIN_DIR . '/visualisations/' . $viz . '/viz.php'     );
		
			$vizId = $post->ID . "_" . $tsid . "_" . rand();
			$vizInstance = new $viz($vizId, $viz, $tsid);
			$vizInstance->write();
		}
		else
		{
			echo "Timestream or visualisation not found";
		}
		
		return "";
	}
	
	function hn_ts_vizbuttons()
	{
		if(!current_user_can("edit_posts") && !current_user_can("edit_pages"))
		{
			return;
		}

		add_filter("mce_external_plugins", "hn_ts_vizbuttons_plugin");
		add_filter("mce_buttons", "hn_ts_vizbuttons_register");
	}
	
	function hn_ts_vizbuttons_register($buttons)
	{
		array_push($buttons, "|", "timestreams_button");
		return $buttons;
	}
	
	function hn_ts_vizbuttons_plugin($plugin_array)
	{
		$plugin_array['timestreams'] = HN_TS_PLUGIN_URL . '/js/timestreams-mce.js';
		return $plugin_array;
	}
	
	add_action('init', 'hn_ts_vizbuttons');
	
	function hn_ts_create_post_type()
	{
		register_post_type(
			'timestream',
			array(
				'labels' => array(
					'name' => __( 'Timestream Pages' ),
					'singular_name' => __( 'Timestream Page' ),
					'add_new' => __( 'Add A New Timestream Page' ),
					'add_new_item' => __( 'Add A New Timestream Page' ),
					'edit' => __( 'Edit Timestream Pages' ),
					'edit_item' => __( 'Edit Timestream Pages' ),
					'new_item' => __( 'New Timestream Page' ),
					'view' => __( 'View This Timestream Page' ),
					'view_item' => __( 'View This Timestream Page' ),
					'search_items' => __( 'Search Timestream Page' ),
					'not_found' => __( 'No Timestream Pages Found' ),
					'not_found_in_trash' => __( 'No Timestream Pages Found In Trash' ),
					),
				'public' => true,
				'has_archive' => true,
				'menu_position' => 5,
				'capability_type' => 'post',
			)
		);
	}
	
	add_action( 'init', 'hn_ts_create_post_type' );
	
	function hn_ts_add_custom_singletemplate($single_template)
	{
		global $post;
		
		if ($post->post_type == 'timestream')
		{
			$single_template = HN_TS_PLUGIN_DIR . '/views/single-visualisation.php';
		}
		return $single_template;
	}

	add_filter( "single_template", "hn_ts_add_custom_singletemplate" ) ;
	
?>