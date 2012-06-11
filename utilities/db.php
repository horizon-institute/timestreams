<?php
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');	// provides dbDelta
	
	/**
	 * Controls calls to the database for timestreams
	 * @author pszjmb
	 *
	 */
	class Hn_TS_Database {
		
		/**
		 * Creates the initial timestreams db tables. This is expected only to 
		 * run at plugin install.
		 */
		function hn_ts_createMultisiteTables(){
			global $wpdb;
			$sql = 		
			'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_context_type (
				context_type_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				name varchar(45) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY  (context_type_id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';		
			dbDelta($sql);
			$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_context (
				context_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				context_type_id bigint(20) NOT NULL,
				value varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
				PRIMARY KEY  (context_id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';
			dbDelta($sql);
			
			$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_metadata (
				metadata_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				tablename varchar(45) COLLATE utf8_unicode_ci NOT NULL,
			    first_record datetime DEFAULT NULL,
			    min_value varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
			    max_value varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
			    unit varchar(45) COLLATE utf8_unicode_ci NOT NULL,
			    unit_symbol varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
				PRIMARY KEY  (metadata_id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';
			dbDelta($sql);
		}
	}
?>
