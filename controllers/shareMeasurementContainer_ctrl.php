<?php
/**
 * Functions to control measurement container sharing
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

/**
 * A form to share measurement containers with blogs.
 * @param $tablename is the name of the measurement container that can be shared
 */
function hn_ts_shareMC($tablename){
	if(!isset($tablename)){
		echo '<p>Sorry the measurement container is missing.</p>';
		return;
	}
	$db = new Hn_TS_Database();
	if(isset($_REQUEST['hn_ts_share_mc'])){
		hn_ts_processShareForm($tablename, $db);
	}
		
	$rows = $db->hn_ts_getBlogList();
	
	if($rows){
		global $pagenow;
		// Output list of blogs with a share button
		echo "<h3>Availabe Blogs:</h3><form id='shareContainerForm' method='post' action=''>";
			$counter=0;
		foreach ( $rows as $row ){
			echo "<input type='hidden' name='hn_ts_share_mc[$counter]' value='no:$row->site_id:$row->blog_id'>";
			$check = $db->hn_ts_isTableSharedWithBlog($tablename, $row->site_id, $row->blog_id);
			echo "<input type='checkbox' ";
			if($check){
				echo 'checked="checked"';
			}
			echo "name='hn_ts_share_mc[$counter]' value='yes:$row->site_id:$row->blog_id' /> <a href='http://$row->domain$row->path' target='_blank'>$row->domain$row->path</a><br />";
			$counter++;
		}
		echo "<input type='hidden' name='hn_ts_share_mc_button' value='$tablename' />";
		echo '<br /><input type="submit" class="button-primary" value="Save"/>';
		echo '</form>';
	}else{
		echo '<p>Sorry, no blogs were found to share this data with.</p>';
	}
}

/**
 * Share form processing
 * @param $tablename is the name of the measurement container that can be shared
 * @param $db is an instantiated Hn_TS_Database
 */
function hn_ts_processShareForm($tablename, $db){
	//print_r($_REQUEST['hn_ts_share_mc']);	
	$db->updateWp_ts_container_shared_with_blog($tablename, $_REQUEST['hn_ts_share_mc']);
	echo '<h3>Share selection saved.</h3>';
}