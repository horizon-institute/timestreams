
function EmbellishedText(remoteUrl, timestreamId, dataDiv, metaDiv, style)
{
	this.remoteUrl = remoteUrl;
	this.timestreamId = timestreamId;
	this.dataDiv = dataDiv;
	this.metaDiv = metaDiv;
	this.style = style;

	this.init = function()
	{
		var _this = this;
		new TimestreamAPI(this.remoteUrl, this.timestreamId, 2000, 1,
				function(data){ _this.onData.call(_this, data)},
				function(meta){ _this.onMeta.call(_this, meta)});
	}
		
	this.onData = function(data)
	{
		elem = document.getElementById(this.dataDiv);
		elem.className = "hn_ts_dataViz";
		elem.innerHTML = data.value;
		elem.setAttribute('style',this.style);
	}
	
	this.onMeta = function(meta)
	{
		//elem = document.getElementById(this.metaDiv);
		//elem.innerHTML = meta.device_details + " " + meta.measurement_type + " " + meta.unit;
	}
	
	this.init();
	
}