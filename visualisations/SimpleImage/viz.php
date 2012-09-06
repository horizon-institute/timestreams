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
			
			?>
			
			<script type="text/javascript">

			if(window.addEventListener)
			{
		        window.addEventListener('load', function() {  
		    		window["<?php echo $this->vizId;?>"] = new SimpleImage(
							"<?php echo site_url(); ?>/xmlrpc.php",
							<?php echo $this->timestreamId;?>,
							"viz_<?php echo $this->vizId;?>",
							"viz_<?php echo $this->vizId;?>_meta");
		        }, false);
		    }    
			else if (window.attachEvent)
			{
				window.attachEvent('onload', function() {
		    		window["<?php echo $this->vizId;?>"] = new SimpleText(
							"<?php echo site_url(); ?>/xmlrpc.php",
							<?php echo $this->timestreamId;?>,
							"viz_<?php echo $this->vizId;?>",
							"viz_<?php echo $this->vizId;?>_meta");
				});
		    }

			</script>
			<p>
			<img id="viz_<?php echo $this->vizId;?>"></div>
			<div id="viz_<?php echo $this->vizId;?>_meta"></div>
			</p>
			<?php 
		}
	}
?>