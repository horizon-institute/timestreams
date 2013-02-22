

function Timestream(remoteUrl, timestreamId, dataSource, serverTs, start, end, rate, minY, maxY, unit)
{
	this.remote_pollingRate = 2000;
	this.remote_username = "username";
	this.remote_password = "password";
	this.remote_url = remoteUrl;
	
	this.timestreamId = timestreamId;
	
	this.head = 0;
	this.newHead = 0;
	
	this.rate = rate;
	
	now = new Date().getTime() / 1000;
	this.offset = now - serverTs;

	_start = new Date(start*1000);
	_startoffset = _start.getTimezoneOffset();

	_end = new Date(end*1000);
	_endoffset = _end.getTimezoneOffset();
	
	/*
	console.log(this.offset);
	_n = new Date();
	_s = new Date(serverTs*1000);
	console.log(_n);
	console.log(_s);
	console.log(now);
	console.log(serverTs);
	
	_st = new Date(start*1000);
	o = _st.getTimezoneOffset();
	console.log(o); // minutes
	_n = new Date();
	//o = _n.getTimezoneOffset();
	console.log(o);
	//_en = new Date(end*1000);

	console.log(start);
	console.log(end);
	console.log(new Date(start*1000));
	console.log(new Date(end*1000));*/

	
	
	this.end = (end+this.offset+(_startoffset*60))*1000;	
	this.start = (start+this.offset+(_endoffset*60))*1000;
	this.startEndEnabled = true;
	
	this.data = [];
	this.annotations = [];
	
	this.minY = minY;
	this.maxY = maxY;
	this.unit = unit;
	this.isMedia = 0;
	
	this.interactionMode = 0;
	
	this.dataSource = dataSource;
	
	this.dataResetOnData = false;
	this.dataOffset = 0;
	this.dataLimit = 500;
	this.dataLastTs = 0;
	this.dataLatest = true;
	this.initialised = false;
	
	/*
	this.remote_service = new rpc.ServiceProxy(this.remote_url, {
		asynchronous: true,
		sanitize: true,
		methods: ['timestreams.int_get_timestream_head',
			'timestreams.int_get_timestream_data',
			'timestreams.int_update_timestream_head'],
		protocol: 'XML-RPC',
	});*/


	this.init = function()
	{	
		if(this.unit.indexOf("text/x-data")!=-1)
		{
			this.isMedia = 0;
			console.log("data");
		}
		else if(this.unit.indexOf("image/")!=-1)
		{
			this.isMedia = 1;
			this.dataLimit = 20;
		}
		else
		{
			this.isMedia = 2;
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
		
		if(this.isMedia > 0)
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
						showLabelsOnHighlight: false,
						annotationDblClickHandler: function(ann, point, dg, event) { _this.onAnnotationDblClick.call(_this, ann, point, dg, event); },
				        annotationMouseOverHandler: function(ann, point, dg, event) { _this.annotationMouseOverHandler.call(_this, ann, point, dg, event); },
				        annotationMouseOutHandler: function(ann, point, dg, event) { _this.annotationMouseOutHandler.call(_this, ann, point, dg, event); },
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

		document.getElementById("hn_ts_saved").innerHTML = "Saving...";
		var _this = this;
		
		if(this.newHead==0)
		{
			_head = (this.head/1000) - this.offset;
		}
		else
		{
			_head = (this.newHead/1000) - this.offset;
		}
		
		var _start = 0;
		var _end = 0;
		
		if(this.startEndEnabled)
		{
			_start = (this.start/1000) - this.offset;
			_end = (this.end/1000) - this.offset;
		}
		
		var _rate = document.getElementById("timestream_"+this.timestreamId+"_rate").value;
				
		jQuery.ajax({
		    url: this.remote_url + "/timestream/head/"+this.timestreamId,
		    type: 'PUT',
		    data: 'curtime='+_head+'&start='+_start+'&end='+_end+'&rate='+_rate,
		    success: function(data, textStatus, jqXHR){_this.saveRpcSuccess.call(_this, data, textStatus, jqXHR)},
		    complete: function(jqXHR, textStatus){ _this.saveRpcComplete.call(_this, jqXHR, textStatus) },
		    error: function(jqXHR, textStatus, errorThrown){ _this.saveRpcError.call(_this, jqXHR, textStatus, errorThrown) },
		});	

	}
	

	this.saveRpcSuccess = function(data, textStatus, jqXHR)
	{
		console.log("saveRpcSuccess" + data + " " + textStatus + " " + jqXHR);
	}

	this.saveRpcError = function(jqXHR, textStatus, errorThrown)
	{
		console.log("saveRpcError: " + jqXHR + " " + textStatus + " " + errorThrown);
	}	

	this.saveRpcComplete = function(jqXHR, textStatus)
	{
		document.getElementById("hn_ts_saved").innerHTML = "Saved.";
		console.log("saveRpcComplete: " + jqXHR + " "  + textStatus);
	}	
	
	this.setInteractionMode = function(mode)
	{
		this.interactionMode = mode;		
	}
	
	this.toggleStartEnd = function()
	{
		this.startEndEnabled = !this.startEndEnabled;
		
		document.getElementById("timestream_" + this.timestreamId + "_start").disabled = !this.startEndEnabled;
		document.getElementById("timestream_" + this.timestreamId + "_end").disabled = !this.startEndEnabled;
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


	}
	
	this.annotationMouseOverHandler = function(ann, point, dg, event)
	{	
		switch(this.isMedia)
		{
		case 1:
			jQuery("#ts_preview").animate({height: "480px"}, 0);	
			jQuery("#ts_preview").html("<img width=640 height=480 src="+ann.text+">");
			break;
		case 2:
			jQuery("#ts_preview").animate({height: "100px"}, 0);
			jQuery("#ts_preview").text(ann.text);
			break;
		}
		
		jQuery("#ts_preview").show();
		jQuery("#ts_preview").position(
		{
			of: ann.div,
			at: "right top",
			my: "left top",
			offset: "15 0",
		});
	}
	
	this.annotationMouseOutHandler = function(ann, point, dg, event)
	{
		jQuery("#ts_preview").hide();
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
	
	this.headRpcSuccess = function(data, textStatus, jqXHR)
	{
		var obj = jQuery.parseJSON(data);
		var head = obj.head;
				
		if(this.initialised == false)
		{
			return;
		}
		
		var newhead = (head["currenttime"]+this.offset)*1000;
		
		this.head = newhead;
		this.redraw();
	}

	this.headRpcComplete = function(jqXHR, textStatus)
	{
		console.log("headRpcComplete: " + jqXHR + " "  + textStatus);
	}

	this.headRpcError = function(jqXHR, textStatus, errorThrown)
	{
		console.log("headRpcError: " + jqXHR + " " + textStatus + " " + errorThrown);
	}
	
	this.dataRpcSuccess = function(data, textStatus, jqXHR)
	{
		var obj = jQuery.parseJSON(data);
		var readings = obj.measurements;
		
		if(readings.length==0)
		{
			return;
		}
		
		if(this.dataResetOnData == true)
		{
			this.dataResetOnData = false;
			this.data = [];
		}
		
		for(property in readings)
		{
			var reading = readings[property];
			_ts = (reading.timestamp+this.offset)*1000;
			
			switch(this.isMedia)
			{
			case 0: // numeric
				this.data.push([new Date(_ts), reading.value]);
				break;
			case 1: // image
				this.data.push([new Date(_ts), 0]);				
				
				this.annotations.push(
				{
					series: "data",
					x: _ts,
					//shortText: ""+reading.value,
					icon: reading.value,
					width: 90,
					height: 120,
					text: reading.value,
			        cssClass: 'annotation',
					attachAtBottom: "true",
				});
				break;
			case 2: // other media
				this.data.push([new Date(_ts), 0]);				
				
				this.annotations.push(
				{
					series: "data",
					x: _ts,
					//shortText: ""+reading.value,
					icon: "../wp-includes/images/crystal/document.png",
					width: 46,
					height: 60,
					text: reading.value,
			        cssClass: 'annotation',
					attachAtBottom: "true",
				});
				break;
			}
	
			this.dataLastTs = reading.timestamp;
		}

		if(this.initialised == false)
		{
			this.initDygraph();
		}
	}
	
	this.dataRpcComplete = function(jqXHR, textStatus)
	{
		//console.log("dataRpcComplete: " + jqXHR + " "  + textStatus);
	}
	
	this.dataRpcError = function(jqXHR, textStatus, errorThrown)
	{
		console.log("dataRpcError: " + jqXHR + " " + textStatus + " " + errorThrown);
	}
	
	this.doHeadRpc = function()
	{
		var _this = this;
		
		jQuery.ajax({
		    url: this.remote_url + "/timestream/head/"+this.timestreamId,
		    type: 'GET',
		    success: function(data, textStatus, jqXHR){_this.headRpcSuccess.call(_this, data, textStatus, jqXHR)},
		    complete: function(jqXHR, textStatus){ _this.headRpcComplete.call(_this, jqXHR, textStatus) },
		    error: function(jqXHR, textStatus, errorThrown){ _this.headRpcError.call(_this, jqXHR, textStatus, errorThrown) },
		});	
		
		setTimeout(function(){ _this.doHeadRpc.call(_this)}, _this.remote_pollingRate);
	}
	
	this.doDataRpc = function()
	{	
		var _this = this;
		
		jQuery.ajax({
		    url: this.remote_url + "/timestream/name/"+_this.dataSource+"?limit="+_this.dataLimit+"&offset="+_this.dataOffset+"&last="+_this.dataLastTs,
		    type: 'GET',
		    success: function(data, textStatus, jqXHR){_this.dataRpcSuccess.call(_this, data, textStatus, jqXHR)},
		    complete: function(jqXHR, textStatus){ _this.dataRpcComplete.call(_this, jqXHR, textStatus) },
		    error: function(jqXHR, textStatus, errorThrown){ _this.dataRpcError.call(_this, jqXHR, textStatus, errorThrown) },
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
