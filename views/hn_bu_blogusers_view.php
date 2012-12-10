<?php
/**
 * Functions to display blogs and users
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

/**
 * Displays blog users information in a table.
 * To do: Complete pagination functionality.
 * To do: Complete user sort functionality.
 */
function hn_db_showBlogusers(){
	?>
		<h3>Users and Blogs</h3>
		<table class="widefat">
			<thead>
				<tr>
					<th>User id</th>
					<th>Site id</th>
					<th>Blog id</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>User id</th>
					<th>Site id</th>
					<th>Blog id</th>
				</tr>
			</tfoot>
			<tbody>
			<?php 		
			$hn_bu_db = new HN_BU_Database();
			$user_list = $hn_bu_db->hn_bu_getUserlist();
			$userblogarray = array();
			$counter=0;
			foreach ($user_list AS $user) {
				$user_blogs = get_blogs_of_user( $user->ID );
				foreach ($user_blogs AS $user_blog) {
					echo "<tr><td>$user->ID</td>
					<td>$user_blog->site_id</td>
					<td>$user_blog->userblog_id</td></tr>";
					$userblogarray[$counter]=array($user->ID, $user_blog->site_id, $user_blog->userblog_id);
					$counter+=1;
					$hn_bu_db->hn_bu_setUserBloglist($userblogarray);
				}
			}
			?>
				</tbody></table>
			<?php 		
}
?>