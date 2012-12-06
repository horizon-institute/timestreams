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

class SingleSite{
	public $site_id =1;
	public $blog_id =1;
}

// Draw the menu page itself
function hn_ts_options_do_page() {
	if(is_multisite()){
		global $current_blog;
	}else{
		$current_blog = new SingleSite;
	}
	
	?>
	<div class="wrap">
		<h2><?php _e('Timestreams Options',HN_TS_NAME); ?></h2>
		<p><?php _e('Site Id: ',HN_TS_NAME); echo $current_blog->site_id;?></p>
		<p><?php _e('Blog Id: ',HN_TS_NAME); echo $current_blog->blog_id;?></p>
		<p><?php _e('Please enter the following values if you are replicating your data through a proxy server.',HN_TS_NAME); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields('hn_ts_options'); ?>
			<?php $options = get_option('hn_ts'); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row"><?php _e('Proxy Address',HN_TS_NAME); ?></th>
					<td><input type="text" name="hn_ts[proxyAddr]" value="<?php echo $options['proxyAddr']; ?>" /></td>
				</tr>
				<tr valign="top"><th scope="row"><?php _e('Proxy Port',HN_TS_NAME); ?></th>
					<td><input type="text" name="hn_ts[proxyPort]" value="<?php echo $options['proxyPort']; ?>" /></td>
				</tr>
			</table>
			<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e('Save Changes',HN_TS_NAME) ?>" />
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