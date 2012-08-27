<?php

	function hn_ts_addposttype()
	{
		register_post_type( 'timestream', 
			array(
			
				'labels' => array(
					'name' => __( 'Timestream Posts' ),
					'singular_name' => __( 'Timestream Post' )
				),
				'public' => true,
				'register_meta_box_cb' => 'hn_ts_metabox',
			)
		);
	}
	
	add_action( 'init', 'hn_ts_addposttype' );
	
	function hn_ts_metabox()
	{
		error_log("metabox");
		add_meta_box( 'hn-ts-metabox-id', 'meta box', 'hn_ts_metabox_cb', 'timestream', 'normal', 'high' );
	}
	
	function hn_ts_metabox_cb()  
	{  
		error_log("metabox cb");
    	echo 'meta box';     
	}  


?>