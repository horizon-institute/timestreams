
function TimestreamGauge(remoteUrl, timestreamId, gaugeDivId)
{
	this.remoteUrl = remoteUrl;
	this.timestreamId = timestreamId;
	this.gaugeDivId = gaugeDivId;

	this.init = function()
	{
		var _this = this;
		new TimestreamAPI(this.remoteUrl, this.timestreamId, 2000, 1,
				function(data){ _this.onData.call(_this, data)},
				function(meta){ _this.onMeta.call(_this, meta)});
	}
		
	this.onData = function(data)
	{
		if(undefined != this.gauge)
		{
			this.gauge.redraw(parseFloat(data.value));
		}
	}
	
	this.onMeta = function(meta)
	{
		var config = 
		{
			size: 240,
			label: meta.measurement_type,
			minorTicks: 5,
			min: parseFloat(meta.min_value),
			max: parseFloat(meta.max_value)
		}
				
		config.redZones = [];
		config.redZones.push({ from: config.max*0.9, to: config.max });

		config.yellowZones = [];
		config.yellowZones.push({ from: config.max*0.75, to: config.max*0.9 });
				
		this.gauge = new Gauge(this.gaugeDivId, config);
		this.gauge.render();
	}
	
	this.init();
	
}