<?php
add_action('admin_init', 'hn_ts_init' );
add_action('admin_menu', 'hn_ts_add_page');

// Init plugin options to white list our options
function hn_ts_init(){
	register_setting( 'hn_ts_options', 'hn_ts', 'hn_ts_options_validate' );
}

// Add menu page
function hn_ts_add_page() {
	add_options_page('Timestreams', 'Timestreams', 'manage_options', 'hn_ts_options', 'hn_ts_options_do_page');
}

// Draw the menu page itself
function hn_ts_options_do_page() {
	?>
	<div class="wrap">
		<h2>Timestreams Options</h2>
		<form method="post" action="options.php">
			<?php settings_fields('hn_ts_options'); ?>
			<?php $options = get_option('hn_ts'); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row">Proxy Address</th>
					<td><input type="text" name="hn_ts[proxyAddr]" value="<?php echo $options['proxyAddr']; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row">Proxy Port</th>
					<td><input type="text" name="hn_ts[proxyPort]" value="<?php echo $options['proxyPort']; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
	<?php	
}

// Sanitize and validate input. Accepts an array, return a sanitized array.
function hn_ts_options_validate($input) {
	
	// Must be safe text with no HTML tags
	$input['proxyAddr'] =  wp_filter_nohtml_kses($input['proxyAddr']);
	
	// Must be safe text with no HTML tags
	$input['proxyPort'] =  wp_filter_nohtml_kses($input['proxyPort']);
	
	return $input;
}