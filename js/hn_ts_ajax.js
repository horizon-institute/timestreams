/**
 * For AJAX calls for replication
 */

jQuery(document).ready(function($){
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
});