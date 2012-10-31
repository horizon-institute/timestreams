<?php
/**
 * Functions to control measurement container sharing
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

/**
 * A form to share measurement containers with blogs.
 */
function hn_ts_shareMC($tablename){
	$db = new Hn_TS_Database();
	$rows = $db->hn_ts_getBlogList();
	if($rows){
		// Output list of blogs with a share button
		echo '<div id="ts_ds_form">';
		echo '<form id="shareContainerForm" method="post" action="">';
		foreach ( $rows as $row ){
			$check = $db->hn_ts_isTableSharedWithBlog($tablename, $row->site_id, $row->blog_id);
			echo "<input type='checkbox' ";
			if($check){
				echo 'checked="checked"';
			}
			echo "name='share' value='$rows->domain/$rows->path'>$rows->domain/$rows->path</input><br />";
		}
		echo '</form></div">';
		addToggleAll();
		addToggleNone();
		echo '<input type="submit" value="Save"/>';
	}else{
		echo '<p>Sorry, no blogs were found to share this data with.</p>';
	}
}

function addToggleAll(){	
	echo '<script language="JavaScript">
	function toggle(source) {
		document.getElementByName("share").checked = true
	}
	</script>';
	echo '<input type="checkbox" onClick="toggle(this)" />Toggle All On<br/>';
}

function addToggleNone(){
	echo '<script language="JavaScript">
	function toggle(source) {
		document.getElementByName("share").checked = false;
	}
	</script>';
	echo '<input type="checkbox" onClick="toggle(this)" />Toggle All Off<br/>';
}