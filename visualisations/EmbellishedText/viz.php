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
			
			?>
			
			<script type="text/javascript">

			if(window.addEventListener)
			{
		        window.addEventListener('load', function() {  
		    		window["<?php echo $this->vizId;?>"] = new EmbellishedText(
								"<?php echo site_url(); ?>/xmlrpc.php",
								<?php echo $this->timestreamId;?>,
								"viz_<?php echo $this->vizId;?>",
								"viz_<?php echo $this->vizId;?>_meta",
								"<?php echo $this->style;?>"
							);
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
			<div id="viz_<?php echo $this->vizId;?>"></div>
			<div id="viz_<?php echo $this->vizId;?>_meta"></div>
			</p>
			<?php 
		}
	}
?>