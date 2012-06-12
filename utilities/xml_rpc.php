<?php

/* Provides HN_TS_SensorDB_XML_RPC which provides access to db through XML-RPC.
    Copyright (C) 2012  Jesse Blum (JMB)

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
	// Ensure that HORZ_SP_UTILITES_DIR was defined
	if ( !defined( 'HN_TS_UTILITES_DIR' ) ) exit;
	
	require_once( HN_TS_UTILITES_DIR . '/db.php'     );
	
	/**
	 * Uses the shortcode method to output weather data from the DB to a WP page  
	 * @author pszjmb
	 *
	 */
	class HN_TS_SensorDB_XML_RPC{
		private $tsdb;
		
		function hn_ts_SensorDB_XML_RPC(){
			$this->tsdb = new Hn_TS_Database();
		}

		/**
		 * Associates XML-RPC method names with functions of this class 
		 * @param $methods is a key/value paired array
		 * @return $methods
		 */
		function add_new_xmlrpc_methods( $methods ) {
			$methods['timestreams.insert_reading'] =  array(&$this->tsdb, 'hn_ts_insert_reading');
			
			return $methods;
		}
	}

?>