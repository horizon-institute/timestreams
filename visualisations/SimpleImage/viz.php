<?php

	class SimpleImage extends Visualisation
	{
		public function describe()
		{
			echo "Simple view of image data from a Timestream";	
		}
		
		public function write()
		{
			wp_enqueue_script('timestreams-api', '/wp-content/plugins/timestreams/js/timestreams-api.js');
			wp_enqueue_script('rpc', '/wp-content/plugins/timestreams/js/rpc.js');
			
			wp_enqueue_script('timestream-image', '/wp-content/plugins/timestreams/visualisations/SimpleImage/timestream-image.js');
			
			return "
			
			<script type='text/javascript'>

			if(window.addEventListener)
			{
		        window.addEventListener('load', function() {  
		    		window['" . $this->vizId."'] = new SimpleImage(
							'" . site_url() . "/xmlrpc.php',
							" . $this->timestreamId . ",
							'viz_" . $this->vizId . "',
							'viz_" . $this->vizId . "_meta');
		        }, false);
		    }    
			else if (window.attachEvent)
			{
				window.attachEvent('onload', function() {
		    		window['" . $this->vizId . "'] = new SimpleImage(
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