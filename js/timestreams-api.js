

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
	
	this.remote_service = new rpc.ServiceProxy(this.remote_url, {
		asynchronous: true,
		sanitize: true,
		methods: ['timestreams.ext_get_timestream_data',
			'timestreams.ext_get_time',
			'timestreams.ext_get_timestreams',
			'timestreams.ext_get_timestream_metadata'],
		protocol: 'XML-RPC',
	});
	
	this.init = function()
	{
		this.syncClock();
		this.metaData();
	}
	
	this.syncClock = function()
	{
		var _this = this;
			
		_this.remote_service.timestreams.ext_get_time({
			params:  [_this.remote_username, _this.remote_password],
				onSuccess:function(successObj){ _this.syncClockSuccess.call(_this, successObj) },
				onException:function(errorObj){ _this.syncClockError.call(_this, errorObj) },
				onComplete:function(){ _this.syncClockComplete.call(_this) }
		});		
	}
	
	this.syncClockSuccess = function(message)
	{
		now = new Date().getTime() / 1000;
		this.clockOffset = now - message;
	}

	this.syncClockError = function(message)
	{
		console.log("syncClockError: " + message);
	}	

	this.syncClockComplete = function()
	{
		this.getData();
	}
	
	this.metaData = function()
	{
		var _this = this;
			
		_this.remote_service.timestreams.ext_get_timestream_metadata({
			params:  [_this.remote_username, _this.remote_password, _this.timestreamId],
				onSuccess:function(successObj){ _this.metaDataSuccess.call(_this, successObj) },
				onException:function(errorObj){ _this.metaDataError.call(_this, errorObj) },
				onComplete:function(){ _this.metaDataComplete.call(_this) }
		});		
	}
	
	this.metaDataSuccess = function(message)
	{
		if(this.metaCallback!=null)
		{
			this.metaCallback(message);
		}
	}

	this.metaDataError = function(message)
	{
		console.log("metaDataError: " + message);
	}	

	this.metaDataComplete = function()
	{

	}
	
	this.getData = function()
	{
		var _this = this;
		
		setTimeout(function(){ _this.getData.call(_this) }, _this.remote_pollingRate);
		
		_this.remote_service.timestreams.ext_get_timestream_data({
			params:  [_this.remote_username, _this.remote_password, _this.timestreamId, _this.lastAsk, _this.count],
				onSuccess:function(successObj){ _this.getDataSuccess.call(_this, successObj) },
				onException:function(errorObj){ _this.getDataError.call(_this, errorObj) },
				onComplete:function(){ _this.getDataComplete.call(_this) }
		});
			
		_this.lastAsk = (new Date().getTime() / 1000) - _this.clockOffset;
	}
	
	this.getDataSuccess = function(message)
	{
		for(property in message)
		{
			var reading = message[property];
			this.dataCallback(reading);
		}
	}

	this.getDataError = function(message)
	{
		console.log("getDataError: " + message);
	}	

	this.getDataComplete = function()
	{
	
	}	
	
	this.init();
}