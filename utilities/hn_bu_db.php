<?php
/**
 * Class to interact with the wp_ts database tables
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * Copyright (C) 2012  Horizon Digital Economy Research Institute, Jesse Blum (JMB) & Martin Flintham (MDF)

	    This program is free software: you can redistribute it and/or modify
	    it under the terms of the GNU Affero General Public License as
	    published by the Free Software Foundation, either version 3 of the
	    License, or (at your option) any later version.

	    This program is distributed in the hope that it will be useful,
	    but WITHOUT ANY WARRANTY; without even the implied warranty of
	    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	    GNU Affero General Public License for more details.

	    You should have received a copy of the GNU Affero General Public License
	    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');	// provides dbDelta
require_once(ABSPATH . WPINC . '/class-IXR.php');
require_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');

/**
 * Controls calls to the database for blogsuser
 * @author pszjmb
 *
 */
class HN_BU_Database {
	private $wpserver;
	protected $missingcontainername;
	protected $missingParameters;

	function HN_BU_Database(){
		$this->wpserver = new wp_xmlrpc_server();
		$this->missingcontainername = new IXR_Error(403, __('Missing container name parameter.',HN_TS_NAME));//"Missing container name parameter.";
		$this->missingParameters= new IXR_Error(403, __('Incorrect number of parameters.',HN_TS_NAME));
	}

	/**
	 * Creates the initial timestreams db tables. This is expected only to
	 * run at plugin install.
	 */
	function hn_bu_createTables(){
		global $wpdb;
		$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'bu_blogusers (
		user_id bigint(20) unsigned NOT NULL,
		site_id bigint(20) unsigned NOT NULL,
		blog_id bigint(20) unsigned NOT NULL,
		PRIMARY KEY  (user_id,blog_id,site_id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';
		$wpdb->query($sql);
	}

	/**
	 * Returns db rows with all the siteid and blog ids
	 */
	function hn_bu_getBloglist(){
		global $wpdb;
		return $wpdb->get_results(
				"SELECT site_id, blog_id FROM wp_blogs ORDER BY site_id ASC, blog_id ASC;"	);
	}

	/**
	 * Returns db rows with all users
	 */
	function hn_bu_getUserlist(){
		global $wpdb;
		return $wpdb->get_results( "SELECT ID FROM wp_users;" );
	}

	/**
	 * Adds items from the given array to a truncated version of bu_blogusers
	 */
	function hn_bu_setUserBloglist($userblogarray){
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE wp_bu_blogusers;"	);
		foreach ($userblogarray AS $user_blog) {
			$wpdb->insert(
					'wp_bu_blogusers',
					array(
							'user_id' => $user_blog[0],
							'site_id' => $user_blog[1],
							'blog_id' => $user_blog[2]
					)
			);
		}
	}

	/**
	 * Adds all users and blogs to the database.
	 * If they already exist, they will be ignored.
	 */
	function hn_bu_addAllUsersBlogs(){
		$user_list = $this->hn_bu_getUserlist();
		$userblogarray = array();
		$counter=0;
		foreach ($user_list AS $user) {
			if(is_multisite()){
				$user_blogs = get_blogs_of_user( $user->ID );
				foreach ($user_blogs AS $user_blog) {
					$userblogarray[$counter]=array($user->ID,
							$user_blog->site_id, $user_blog->userblog_id);
					$counter+=1;
					$this->hn_bu_setUserBloglist($userblogarray);
				}
			}else{
					$this->hn_bu_setUserBloglist($userblogarray);
					$userblogarray[$counter]=array($user->ID,
							1, 1);
					$counter+=1;
			}
		}
	}
}
?>
