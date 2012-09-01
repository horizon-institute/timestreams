<?php

	class GaugeViz extends Visualisation
	{
		public function describe()
		{
			echo "Simple gauge visualisation of numeric Timestream data";
		}
		
		public function write()
		{
			wp_enqueue_script('timestreams-api', '/wp-content/plugins/timestreams/js/timestreams-api.js');
			wp_enqueue_script('d3', '/wp-content/plugins/timestreams/js/d3.v2.min.js');
			wp_enqueue_script('rpc', '/wp-content/plugins/timestreams/js/rpc.js');
			
			wp_enqueue_script('gauge', '/wp-content/plugins/timestreams/visualisations/GaugeViz/gauge.js');
			wp_enqueue_script('timestream-gauge', '/wp-content/plugins/timestreams/visualisations/GaugeViz/timestream-gauge.js');
			
			?>
			
			<script type="text/javascript">

			if(window.addEventListener)
			{
		        window.addEventListener('load', function() {  
		    		window["<?php echo $this->vizId;?>"] = new TimestreamGauge(
							"<?php echo site_url(); ?>/xmlrpc.php",
							<?php echo $this->timestreamId;?>,
							"viz_<?php echo $this->vizId;?>");
		        }, false);
		    }    
			else if (window.attachEvent)
			{
				window.attachEvent('onload', function() {
		    		window["<?php echo $this->vizId;?>"] = new TimestreamGauge(
							"<?php echo site_url(); ?>/xmlrpc.php",
							<?php echo $this->timestreamId;?>,
							"viz_<?php echo $this->vizId;?>");
				});
		    }

			</script>
			<div id="viz_<?php echo $this->vizId;?>"></div>
			<?php 
		}
	}
?>