/**
 * For AJAX calls for replication
 */

jQuery(document).ready(function($){
	// Handle replication button clicks
	$('.button-secondary').click(function() {
		$elem = $(this);
		var btn_id=$(this).attr('name').split('.')[1];
		
		$('#hn_ts_rpl_loading-'.concat(btn_id)).show()
		$(this).attr('disabled',true);

		data={
				action: 'hn_ts_get_replication_results',
				hn_ts_ajax_repl_nonce: hn_ts_ajax_repl_vars.hn_ts_ajax_repl_nonce,
				hn_ts_ajax_repl_id:btn_id
		};
		
		$.post(ajaxurl, data, function (response){
			$('#hn_ts_last_repl-'.concat(btn_id)).html(response);		
			$('#hn_ts_rpl_loading-'.concat(btn_id)).hide()	
			$elem.attr('disabled',false);
		});
		
		return false;
	});

	// Hide or show Timestreams usage description and instructions
	
    $("#hide_ts_description").show();	   
    $("#show_ts_description").hide();	
    
	$("#hide_ts_description").click(function(){
	    $(".ts_description").hide();
	    $("#hide_ts_description").hide();	   
	    $("#show_ts_description").show();	
	  });
	  $("#show_ts_description").click(function(){
	    $(".ts_description").show();
	    $("#hide_ts_description").show();	   
	    $("#show_ts_description").hide();	   
	  });
	
    $("#hide_ts_instructions").show();	   
    $("#show_ts_instructions").hide();	
    
	$("#hide_ts_instructions").click(function(){
	    $(".ts_instructions").hide();
	    $("#hide_ts_instructions").hide();	   
	    $("#show_ts_instructions").show();	
	  });
	  $("#show_ts_instructions").click(function(){
	    $(".ts_instructions").show();
	    $("#hide_ts_instructions").show();	   
	    $("#show_ts_instructions").hide();	   
	  });
});