<?php

	class SimpleText extends Visualisation
	{
		public function describe()
		{
			echo "Simple text view of data from a Timestream";	
		}
		
		public function write()
		{
			wp_enqueue_script('timestreams-api', '/wp-content/plugins/timestreams/js/timestreams-api.js');
			wp_enqueue_script('rpc', '/wp-content/plugins/timestreams/js/rpc.js');
			
			wp_enqueue_script('timestream-simple', '/wp-content/plugins/timestreams/visualisations/SimpleText/timestream-simple.js');

			return "
			
			<script type='text/javascript'>

			if(window.addEventListener)
			{
		        window.addEventListener('load', function() {  
		    		window['" . $this->vizId."'] = new SimpleText(
							'" . site_url() . "/xmlrpc.php',
							" . $this->timestreamId . ",
							'viz_" . $this->vizId . "',
							'viz_" . $this->vizId . "_meta');
		        }, false);
		    }    
			else if (window.attachEvent)
			{
				window.attachEvent('onload', function() {
		    		window['" . $this->vizId . "'] = new SimpleText(
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