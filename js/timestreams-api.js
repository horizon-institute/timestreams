

function TimestreamAPI(remoteUrl, timestreamId, pollRate, count, dataCallback, metaCallback)
{
	this.remote_pollingRate = pollRate;
	this.remote_username = "username";
	this.remote_password = "password";
	this.remote_url = remoteUrl;
	
	this.timestreamId = timestreamId;
	
	this.count = count;

	this.clockOffset = 0;
	this.lastAsk = 0;
	
	this.dataCallback = dataCallback;
	this.metaCallback = metaCallback;
	
	this.init = function()
	{
		this.syncClock();
		this.metaData();
	}
	
	this.syncClock = function()
	{
		var _this = this;
		
		jQuery.ajax({
		    url: this.remote_url + "/time",
		    type: 'GET',
		    success: function(data, textStatus, jqXHR){_this.syncClockSuccess.call(_this, data, textStatus, jqXHR)},
		    complete: function(jqXHR, textStatus){ _this.syncClockComplete.call(_this, jqXHR, textStatus) },
		    error: function(jqXHR, textStatus, errorThrown){ _this.syncClockError.call(_this, jqXHR, textStatus, errorThrown) },
		});	
	}
	
	this.syncClockSuccess = function(data, textStatus, jqXHR)
	{
		var obj = jQuery.parseJSON(data);
		var timestamp = obj['timestamp'][0]['CURRENT_TIMESTAMP'];
		now = new Date().getTime() / 1000;
		this.clockOffset = now - timestamp;
	}

	this.syncClockError = function(jqXHR, textStatus, errorThrown)
	{
		console.log("syncClockError: " + jqXHR + " " + textStatus + " " + errorThrown);
	}	

	this.syncClockComplete = function(jqXHR, textStatus)
	{
		console.log("syncClockComplete: " + jqXHR + " "  + textStatus);
		this.getData();
	}
	
	this.metaData = function()
	{
		var _this = this;
		
		jQuery.ajax({
		    url: this.remote_url + "/measurementContainerMetadata?tsid="+this.timestreamId,
		    type: 'GET',
		    success: function(data, textStatus, jqXHR){_this.metaDataSuccess.call(_this, data, textStatus, jqXHR)},
		    complete: function(jqXHR, textStatus){ _this.metaDataComplete.call(_this, jqXHR, textStatus) },
		    error: function(jqXHR, textStatus, errorThrown){ _this.metaDataError.call(_this, jqXHR, textStatus, errorThrown) },
		});	
	}
	
	this.metaDataSuccess = function(data, textStatus, jqXHR)
	{
		if(this.metaCallback!=null)
		{
			var obj = jQuery.parseJSON(data);
			this.metaCallback(obj.metadata);
		}
	}

	this.metaDataError = function(jqXHR, textStatus, errorThrown)
	{
		console.log("metaDataError: " + jqXHR + " " + textStatus + " " + errorThrown);
	}	

	this.metaDataComplete = function(jqXHR, textStatus)
	{
		console.log("metaDataComplete: " + jqXHR + " "  + textStatus);
	}
	
	this.getData = function()
	{
		var _this = this;
		
		setTimeout(function(){ _this.getData.call(_this) }, _this.remote_pollingRate);
		
		jQuery.ajax({
		    url: this.remote_url + "/timestream/id/"+this.timestreamId+"?last="+_this.lastAsk+"&limit="+_this.count,
		    type: 'GET',
		    success: function(data, textStatus, jqXHR){_this.getDataSuccess.call(_this, data, textStatus, jqXHR)},
		    complete: function(jqXHR, textStatus){ _this.getDataComplete.call(_this, jqXHR, textStatus) },
		    error: function(jqXHR, textStatus, errorThrown){ _this.getDataError.call(_this, jqXHR, textStatus, errorThrown) },
		});	
			
		_this.lastAsk = (new Date().getTime() / 1000) - _this.clockOffset;
	}
	
	this.getDataSuccess = function(data, textStatus, jqXHR)
	{
		var obj = jQuery.parseJSON(data);
		readings = obj.timestream;

		for(property in readings)
		{
			var reading = readings[property];
			this.dataCallback(reading);
		}
	}

	this.getDataError = function(jqXHR, textStatus, errorThrown)
	{
		console.log("getDataError: " + jqXHR + " " + textStatus + " " + errorThrown);
	}	

	this.getDataComplete = function(jqXHR, textStatus)
	{
		//console.log("getDataComplete: " + jqXHR + " "  + textStatus);
	}	
	
	this.init();
}