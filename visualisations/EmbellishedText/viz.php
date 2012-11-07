<?php

	class EmbellishedText extends Visualisation
	{
		public function describe()
		{
			echo "Embellished text view of data from a Timestream";	
		}
		
		public function write()
		{
			wp_enqueue_script('timestreams-api', '/wp-content/plugins/timestreams/js/timestreams-api.js');
			wp_enqueue_script('rpc', '/wp-content/plugins/timestreams/js/rpc.js');
			
			wp_enqueue_script('timestream-embellished', '/wp-content/plugins/timestreams/visualisations/EmbellishedText/timestream-embellished.js');
			//wp_register_style( 'viz-style', plugins_url('timestreams/visualisations/EmbellishedText/viz.css') );
			//wp_enqueue_style( 'viz-style' );
			
					return "
			
			<script type='text/javascript'>

			if(window.addEventListener)
			{
		        window.addEventListener('load', function() {  
		    		window['" . $this->vizId."'] = new EmbellishedText(
							'" . site_url() . "/xmlrpc.php',
							" . $this->timestreamId . ",
							'viz_" . $this->vizId . "',
							'viz_" . $this->vizId . "_meta');
		        }, false);
		    }    
			else if (window.attachEvent)
			{
				window.attachEvent('onload', function() {
		    		window['" . $this->vizId . "'] = new EmbellishedText(
							'" . site_url() . "/xmlrpc.php',
							" . $this->timestreamId . ",
							'viz_" . $this->vizId . "',
							'viz_" . $this->vizId . "_meta');
				});
		    }

			</script>
			<div id='viz_" . $this->vizId . "'></div>
			<div id='viz_" . $this->vizId . "_meta'></div>			
			";
		}
	}
?>