

function Timestream(remoteUrl, timestreamId, dataSource, serverTs, start, end, rate, minY, maxY, unit)
{
	this.remote_pollingRate = 5000;
	this.remote_username = "username";
	this.remote_password = "password";
	this.remote_url = remoteUrl;
	
	this.timestreamId = timestreamId;
	
	this.head = 0;
	this.newHead = 0;
	
	this.rate = rate;
	
	now = new Date().getTime() / 1000;
	this.offset = now - serverTs;

	this.end = (end+this.offset)*1000;	
	this.start = (start+this.offset)*1000;
	this.startEndEnabled = true;
	
	this.data = [];
	this.annotations = [];
	
	this.minY = minY;
	this.maxY = maxY;
	this.unit = unit;
	this.isMedia = false;
	
	this.interactionMode = 0;
	
	this.dataSource = dataSource;
	
	this.dataResetOnData = false;
	this.dataOffset = 0;
	this.dataLimit = 500;
	this.dataLastTs = 0;
	this.dataLatest = true;
	this.initialised = false;
	
	this.remote_service = new rpc.ServiceProxy(this.remote_url, {
		asynchronous: true,
		sanitize: true,
		methods: ['timestreams.int_get_timestream_head',
			'timestreams.int_get_timestream_data',
			'timestreams.int_update_timestream_head'],
		protocol: 'XML-RPC',
	});


	this.init = function()
	{	
		if(this.unit.indexOf("image")!=-1)
		{
			this.isMedia = true;
			this.dataLimit = 20;
		}
		
		if(this.end > 0)
		{
			this.startEndEnabled = true;
		}
	}
	
	this.initDygraph = function()
	{
		var _this = this;
		
		if(this.isMedia == true)
		{
			this.dygraph = new Dygraph(document.getElementById("timestream_"+this.timestreamId),
					this.data,
					{
						interactionModel:
						{
							click: function(e, x, points) {	_this.onClickCallback.call(_this, e, x, points); }
						},
						xValueParser: function(x) { return x; }, // annotations are knackered without this
						plotter: function(e) { _this.onDrawCallback.call(_this, e); },
						valueRange: [0,1],
						showRangeSelector: true,
						labels: ["date", "data"],
						drawYAxis: true,
						drawXGrid: true,
						drawYGrid: false,
						annotationDblClickHandler: function(ann, point, dg, event) { _this.onAnnotationDblClick.call(_this, ann, point, dg, event); },
					}
			);
			
			this.dygraph.setAnnotations(this.annotations);			
		}
		else
		{
			this.dygraph = new Dygraph(document.getElementById("timestream_"+this.timestreamId),
					this.data,
					{
						interactionModel:
						{
							click: function(e, x, points) {	_this.onClickCallback.call(_this, e, x, points); }
						},
						xValueParser: function(x) { return x; }, // annotations are knackered without this
						plotter: function(e) { _this.onDrawCallback.call(_this, e); },
						valueRange: [_this.minY, _this.maxY],
						showRangeSelector: true,
						labels: ["date", "data"],
					}
			);		
		}
		
		this.initialised = true;
	}
	
	this.latest = function()
	{
		this.dataLatest = true;
		this.dataOffset = 0;
		this.dataResetOnData = true;
		this.dataLastTs = 0;
		this.doDataRpc();
	}
	
	this.prev = function()
	{
		this.dataLatest = false;
		this.dataOffset+=this.dataLimit;
		this.dataResetOnData = true;
		this.dataLastTs = 0;
		this.doDataRpc();
	}
	
	this.next = function()
	{
		this.dataLatest = false;
		this.dataOffset-=this.dataLimit;	
		this.dataResetOnData = true;	
		this.dataLastTs = 0;
		this.doDataRpc();
	}
	
	this.save = function()
	{		
		var _this = this;
		
		var _head = (this.newHead/1000) - this.offset;
		
		var _start = 0;
		var _end = 0;
		
		if(this.startEndEnabled)
		{
			_start = (this.start/1000) - this.offset;
			_end = (this.end/1000) - this.offset;
		}
		
		var _rate = document.getElementById("timestream_"+this.timestreamId+"_rate").value;
		
		_this.remote_service.timestreams.int_update_timestream_head({
			params:  [_this.remote_username, _this.remote_password, _this.timestreamId, _head, _start, _end, _rate],
				onSuccess:function(successObj){ _this.saveRpcSuccess.call(_this, successObj) },
				onException:function(errorObj){ _this.saveRpcError.call(_this, errorObj) },
				onComplete:function(){ _this.saveRpcComplete.call(_this) }
		});
	}
	
	this.saveRpcComplete = function()
	{
		
	}
	
	this.saveRpcError = function(errorObj)
	{
		
	}
	
	this.saveRpcSuccess = function(message)
	{
		
	}
	
	this.setInteractionMode = function(mode)
	{
		this.interactionMode = mode;		
	}
	
	this.toggleStartEnd = function()
	{
		this.startEndEnabled = !this.startEndEnabled;
	}
	
	this.redraw = function()
	{
		// force redraw
		this.dygraph.updateOptions( { 'file': this.data } );
		this.dygraph.setAnnotations(this.annotations);
	}
	
	this.onDrawCallback = function(e)
	{
		var ctx = e.drawingContext;
		var points = e.points;
		var y_bottom = e.dygraph.toDomYCoord(0); 
		 
		var bar_width = 5; 
		ctx.fillStyle = e.color;
		 
		for (var i = 0; i < points.length; i++)
		{
			var p = points[i];
			var center_x = p.canvasx;
		 
			ctx.fillRect(center_x - bar_width / 2, p.canvasy, bar_width, y_bottom - p.canvasy);
		    ctx.strokeRect(center_x - bar_width / 2, p.canvasy, bar_width, y_bottom - p.canvasy);
		}
		
		ctx.fillStyle = "rgba(0, 255, 0, 0.2)";
		var xs = e.dygraph.toDomXCoord(this.start);
		var xe = e.dygraph.toDomXCoord(this.end);
		ctx.fillRect(xs, 0, xe-xs, 200);

		ctx.fillStyle = "rgba(255, 0, 0, 1.0)";
		ctx.fillRect(e.dygraph.toDomXCoord(this.head), 0, 5, 200);
		
		ctx.fillStyle = "rgba(255, 150, 150, 1.0)";
		ctx.fillRect(e.dygraph.toDomXCoord(this.newHead), 0, 5, 200);

	}
	
	this.onAnnotationDblClick = function(ann, point, dg, event)
	{
		var newDiv = jQuery(document.createElement('div')); 
		newDiv.html("<img src="+ann.text+">");
		newDiv.dialog();
	}
	
	
	this.onClickCallback = function(e, x, points)
	{
		var clicked = this.dygraph.eventToDomCoords(e);
		var x = this.dygraph.toDataXCoord(clicked[0]);
	
		switch(this.interactionMode)
		{
		case 1: // head
			this.newHead = x;
			document.getElementById("timestream_" + timestreamId + "_head").value = new Date(this.newHead);
			break;
		case 2: // start
			this.start = x;
			document.getElementById("timestream_" + timestreamId + "_start").value = new Date(this.start);
			break;
		case 3: // end
			this.end = x;
			document.getElementById("timestream_" + timestreamId + "_end").value = new Date(this.end);
			break;
		default:
			break;	
		}
		
		this.redraw();
	}
	
	this.onZoomCallback = function(minDate, maxDate, yRanges)
	{
	
	}

	this.onUnderlayCallback = function(canvas, area, g)
	{

	}
	
	this.headRpcSuccess = function(message)
	{
		if(this.initialised == false)
		{
			return;
		}
		
		var newhead = (message["currenttime"]+this.offset)*1000;
		
		this.head = newhead;
		this.redraw();
	}

	this.headRpcComplete = function()
	{
		
	}

	this.headRpcError = function(errorObj)
	{
		
	}
	
	this.dataRpcSuccess = function(message)
	{	
		if(this.dataResetOnData == true)
		{
			this.dataResetOnData = false;
			this.data = [];
		}
		
		for(property in message)
		{
			var reading = message[property];
			_ts = (reading.timestamp+this.offset)*1000;
			
			if(this.isMedia == true)
			{
				this.data.push([new Date(_ts), 0]);				
								
				this.annotations.push(
				{
					series: "data",
					x: _ts,
					shortText: ""+reading.value,
					icon: reading.value,
					width: 160,
					height: 120,
					text: reading.value,
			        cssClass: 'annotation',
					attachAtBottom: "true",
				});
			}
			else
			{
				this.data.push([new Date(_ts), reading.value]);					
			}
						
			this.dataLastTs = reading.timestamp;
		}

		if(this.initialised == false)
		{
			this.initDygraph();
		}
	}
	
	this.dataRpcComplete = function()
	{
		
	}
	
	this.dataRpcError = function(errorObj)
	{
		
	}
	
	this.doHeadRpc = function()
	{
		var _this = this;
		
		_this.remote_service.timestreams.int_get_timestream_head({
			params:  [_this.remote_username, _this.remote_password, _this.timestreamId],
				onSuccess:function(successObj){ _this.headRpcSuccess.call(_this, successObj) },
				onException:function(errorObj){ _this.headRpcError.call(_this, errorObj) },
				onComplete:function(){ _this.headRpcComplete.call(_this) }
		});
		
		setTimeout(function(){ _this.doHeadRpc.call(_this)}, _this.remote_pollingRate);
	}
	
	this.doDataRpc = function()
	{	
		var _this = this;

		_this.remote_service.timestreams.int_get_timestream_data({
			params:  [_this.remote_username, _this.remote_password, _this.dataSource, _this.dataLimit, _this.dataOffset, _this.dataLastTs],
				onSuccess:function(successObj){ _this.dataRpcSuccess.call(_this, successObj) },
				onException:function(errorObj){ _this.dataRpcError.call(_this, errorObj) },
				onComplete:function(){ _this.dataRpcComplete.call(_this) }
		});
		
		// poll for latest
		if(_this.dataLatest==true)
		{
			setTimeout(function(){ _this.doDataRpc.call(_this)}, _this.remote_pollingRate);
		}
	}
	
	this.init();
	this.doHeadRpc();
	this.doDataRpc();
}
