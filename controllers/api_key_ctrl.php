<?php
/**
 * Functions to control api key generation, storage and revocation
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add key generation
 * To do: Add key storage
 * To do: Add revocation
 */

/**
 * A form to add context records
 * Todo: Associate the contexts with data
 */
function hn_ts_generateKeys(){
	?>
		<div>		
			<h3><?php _e('API Key Generation'); ?></h3>			
			<form id="apikeyform" method="post" action="">
				<input type="submit" name='hn_ts_new_api_key' class="button-primary" value="<?php _e('Create New API Keys',HN_TS_NAME) ?>" />
			</form>
		</div>
	<?php
	
	if(isset($_POST['hn_ts_new_api_key'])) {
		$db = new Hn_TS_Database();
		$db->hn_ts_addNewAPIKeys();
	}
	echo '<hr />';		
}

?>