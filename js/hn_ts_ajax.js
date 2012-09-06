/**
 * For AJAX calls for replication
 */

jQuery(document).ready(function($){
	$('#doReplicationform').submit(function() {
		$("#hn_ts_rpl_loading").show()
		$("#hn_ts_rpl_submit").attr('disabled',true);
		
		data={
				action: 'hn_ts_get_replication_results',
				hn_ts_ajax_repl_nonce: hn_ts_ajax_repl_vars.hn_ts_ajax_repl_nonce
		};
		
		$.post(ajaxurl, data, function (response){
			$('#hn_ts_last_repl').html(response);		
			$("#hn_ts_rpl_loading").hide()	
			$("#hn_ts_rpl_submit").attr('disabled',false);
		});
		
		return false;
	});
});