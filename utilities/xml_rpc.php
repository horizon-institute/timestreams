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
		 * Checks user name and password to ensure that this is a valid
		 * system user
		 * @param array args should have the first and second params as user name and password
		 * The rest are ignored
		 * @return boolean true if valid or false otherwise
		 * INCOMPLETE
		 */
		function hn_ts_check_user_pass($args){
			return true;
		}
		
		/**
		 * Checks username password then creates a new measurement container.
		 * @param array $args should have 11 parameters:
		 * $username, $password, $blog_id, $measurementType, $minimumvalue, $maximumvalue,
			$unit, $unitSymbol, $deviceDetails, $otherInformation, $dataType
		 * @return string XML-XPC response with either an error message as a param or the
		 * name of the measurement container
		 */
		function hn_ts_create_measurements($args){
			/*$retString="";
			foreach ($args as $value){
				$retString=$retString.$value;
			}
			return $retString;*/
			if(count($args) < 11){
				return 'Incorrect number of parameters.';
			}
			/*(blog_id='', $$measurementType, $minimumvalue, $maximumvalue,
			$unit, $unitSymbol, $deviceDetails, $otherInformation, $dataType)*/
			if($this->hn_ts_check_user_pass($args)){
				return $this->tsdb->hn_ts_addMetadataRecord($args[2],$args[3],$args[4],$args[5],
					$args[6],$args[7],$args[8],$args[9],$args[10]);
			}else{
				return 'Incorrect username or password.';
			}
		}
		
		/**
		 * Checks username password then adds a measurement to a measurement container.
		 * @param array $args should have 5 parameters:
		 * $username, $password, measurement container name, measurement value, timestamp
		 * @return string XML-XPC response with either an error message as a param or the
		 * value and timestamp of the added measure
		 */
		function hn_ts_add_measurement($args){
			if(count($args) < 5){
				return 'Incorrect number of parameters.';
			}
			if($this->hn_ts_check_user_pass($args)){
				return $this->tsdb->hn_ts_insert_reading($args);
			}else{
				return 'Incorrect username or password.';
			}
		}
		
		/**
		 * Checks username password then adds measurements to a measurement container.
		 * @param array $args should have at least 5 parameters:
		 * $username, $password, measurement container name, array containing [measurement value, timestamp]
		 * @return string XML-XPC response with either an error message as a param or the
		 * value and timestamp of the added measure
		 */
		function hn_ts_add_measurements($args){
			if(count($args) < 5){
				return 'Incorrect number of parameters.';
			}
			if($this->hn_ts_check_user_pass($args)){
				return $this->tsdb->hn_ts_insert_readings($args);
			}else{
				return 'Incorrect username or password.';
			}
		}
		
		/**
		 * Checks username password then selects measurements from a measurement container.
		 * @param array $args should have at least 5 parameters:
		 * $username, $password, measurement container name, minimum time, maximum time
		 * @return string XML-XPC response with either an error message as a param or the
		 * value and timestamp of the added measure
		 */
		function hn_ts_select_measurements($args){
			if(count($args) < 5){
				return 'Incorrect number of parameters.';
			}
			if($this->hn_ts_check_user_pass($args)){
				return $this->tsdb->hn_ts_get_readings_from_name($args);
			}else{
				return 'Incorrect username or password.';
			}
		}

		/**
		 * Associates XML-RPC method names with functions of this class 
		 * @param $methods is a key/value paired array
		 * @return $methods
		 */
		function add_new_xmlrpc_methods( $methods ) {
			//$methods['timestreams.insert_reading'] =  array(&$this, 'hn_ts_insert_reading');
			$methods['timestreams.create_measurements'] =  array(&$this, 'hn_ts_create_measurements');
			$methods['timestreams.add_measurement'] =  array(&$this, 'hn_ts_add_measurement');
			$methods['timestreams.add_measurements'] =  array(&$this, 'hn_ts_add_measurements');
			$methods['timestreams.select_measurements'] =  array(&$this, 'hn_ts_select_measurements');
			
			return $methods;
		}
	}

?>