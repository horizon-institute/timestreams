<?php
/**
 * Class to interact with the wp_ts database tables
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');	// provides dbDelta
require_once(ABSPATH . WPINC . '/class-IXR.php');
require_once(ABSPATH . WPINC . '/class-wp-xmlrpc-server.php');

/**
 * Controls calls to the database for timestreams
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
		return $wpdb->get_results( 	$wpdb->prepare(
				"SELECT site_id, blog_id FROM wp_blogs ORDER BY site_id ASC, blog_id ASC;" )	);
	}

	/**
	 * Returns db rows with all users
	 */
	function hn_bu_getUserlist(){
		global $wpdb;
		return $wpdb->get_results( 	$wpdb->prepare(
				"SELECT ID FROM wp_users;" )	);
	}

	/**
	 * Adds items from the given array to a truncated version of bu_blogusers
	 */
	function hn_bu_setUserBloglist($userblogarray){
		global $wpdb;
		//$wpdb->query( $wpdb->prepare( "TRUNCATE TABLE wp_bu_blogusers;" )	);
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
	
	function hn_bu_addAllUsersBlogs(){
		$user_list = $this->hn_bu_getUserlist();
		$userblogarray = array();
		$counter=0;
		foreach ($user_list AS $user) {
			$user_blogs = get_blogs_of_user( $user->ID );
			foreach ($user_blogs AS $user_blog) {
				$userblogarray[$counter]=array($user->ID, $user_blog->site_id, $user_blog->userblog_id);
				$counter+=1;
				$this->hn_bu_setUserBloglist($userblogarray);
			}
		}
	}
}
?>