
function SimpleText(remoteUrl, timestreamId, dataDiv, metaDiv)
{
	this.remoteUrl = remoteUrl;
	this.timestreamId = timestreamId;
	this.dataDiv = dataDiv;
	this.metaDiv = metaDiv;

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
		elem.innerHTML = new Date(data.valid_time) + " " + data.value;
	}
	
	this.onMeta = function(meta)
	{
		elem = document.getElementById(this.metaDiv);
		elem.innerHTML = meta.device_details + " " + meta.measurement_type + " " + meta.unit;
	}
	
	this.init();
	
}