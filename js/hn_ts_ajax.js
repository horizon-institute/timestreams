/**
 * For AJAX calls for replication
 */

jQuery(document).ready(function($) {
	// Handle replication button clicks
	$('.button-secondary').click(function() {
		$elem = $(this);
		var btn_id = $(this).attr('name').split('.')[1];
		if(btn_id == "ignore"){
			return;
		}

		$('#hn_ts_rpl_loading-'.concat(btn_id)).show()
		$(this).attr('disabled', true);

		data = {
			action : 'hn_ts_get_replication_results',
			hn_ts_ajax_repl_nonce : hn_ts_ajax_repl_vars.hn_ts_ajax_repl_nonce,
			hn_ts_ajax_repl_id : btn_id
		};

		$.post(ajaxurl, data, function(response) {
			$('#hn_ts_last_repl-'.concat(btn_id)).html(response);
			$('#hn_ts_rpl_loading-'.concat(btn_id)).hide()
			$elem.attr('disabled', false);
		});

		return false;
	});

	// Hide or show Timestreams usage description and instructions

	$("#hide_ts_description").hide();
	$("#show_ts_description").show();
	$(".ts_description").hide();

	$("#hide_ts_description").click(function() {
		$(".ts_description").hide();
		$("#hide_ts_description").hide();
		$("#show_ts_description").show();
	});
	$("#show_ts_description").click(function() {
		$(".ts_description").show();
		$("#hide_ts_description").show();
		$("#show_ts_description").hide();
	});

	$("#hide_ts_instructions").hide();
	$("#show_ts_instructions").show();
	$(".ts_instructions").hide();

	$("#hide_ts_instructions").click(function() {
		$(".ts_instructions").hide();
		$("#hide_ts_instructions").hide();
		$("#show_ts_instructions").show();
	});
	$("#show_ts_instructions").click(function() {
		$(".ts_instructions").show();
		$("#hide_ts_instructions").show();
		$("#show_ts_instructions").hide();
	});

	// Hide or show Datsources form

	$("#hide_ts_ds_form").hide();
	$("#show_ts_ds_form").show();
	$("#ts_ds_form").hide();

	$("#hide_ts_ds_form").click(function() {
		$("#ts_ds_form").hide();
		$("#hide_ts_ds_form").hide();
		$("#show_ts_ds_form").show();
	});
	$("#show_ts_ds_form").click(function() {
		$("#ts_ds_form").show();
		$("#hide_ts_ds_form").show();
		$("#show_ts_ds_form").hide();
	});
	
	// For dropdown fill in of textfield 
	$('#hn_ts_measurementDD').change(function(){
	 	$('#hn_ts_measurementTB').val($(this).val());
	 	
	 	if($(this).val() == ''){
	 		$('#hn_ts_unittb').val('');	  
	 		$('#hn_ts_unitsymboltb').val('');	  
	 		$('#hn_ts_datatypetb').val('');	  
	 		$('#hn_ts_lowval').val('');	  	 
	 		$('#hn_ts_highval').val('');	  	 		
	 	}
	 	
	 	if($(this).val() == 'CO2'){
	 		$('#hn_ts_unittb').val('text/x-data-CO2');	  
	 		$('#hn_ts_unitsymboltb').val('ppm');	  
	 		$('#hn_ts_datatypetb').val('DECIMAL(6,2)');	  
	 		$('#hn_ts_lowval').val('0');	  	 
	 		$('#hn_ts_highval').val('5000');	
	 	}
	 	
	 	if($(this).val() == 'humidity'){
	 		$('#hn_ts_unittb').val('text/x-data-messages');	  
	 		$('#hn_ts_unitsymboltb').val('%');	  
	 		$('#hn_ts_datatypetb').val('SMALLINT');	  
	 		$('#hn_ts_lowval').val('0');	  	 
	 		$('#hn_ts_highval').val('100');	
	 	}
	 	
	 	if($(this).val() == 'temperature'){
	 		$('#hn_ts_unittb').val('text/x-data-messages');	  
	 		$('#hn_ts_unitsymboltb').val('C');	  
	 		$('#hn_ts_datatypetb').val('messages');	  
	 		$('#hn_ts_lowval').val('-40');	  	 
	 		$('#hn_ts_highval').val('125');	
	 	}
	 	
	 	if($(this).val() == 'noise'){
	 		$('#hn_ts_unittb').val('text/x-data-decibels');	  
	 		$('#hn_ts_unitsymboltb').val('db');	  
	 		$('#hn_ts_datatypetb').val('SMALLINT');	  
	 		$('#hn_ts_lowval').val('30');	  	 
	 		$('#hn_ts_highval').val('140');	
	 	}
	 	
	 	if($(this).val() == 'images'){
	 		$('#hn_ts_unittb').val('image/png');	  
	 		$('#hn_ts_unitsymboltb').val('PNG');	  
	 		$('#hn_ts_datatypetb').val('VARCHAR(255)');	
	 		$('#hn_ts_lowval').val('');	  	 
	 		$('#hn_ts_highval').val('');	  
	 	}
	 	
	 	if($(this).val() == 'messages'){
	 		$('#hn_ts_unittb').val('text/plain');	  
	 		$('#hn_ts_unitsymboltb').val('TXT');	  
	 		$('#hn_ts_datatypetb').val('VARCHAR(200)');	
	 		$('#hn_ts_lowval').val('');	  	 
	 		$('#hn_ts_highval').val('');	  
	 	}
	});
});

