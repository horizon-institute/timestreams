<?php
/**
 * Class to interact with extrernal timestreams instances such as getting and posting data
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */
	/**
	 * Controls calls to external timestreams
	 * @author pszjmb
	 *
	 */
	class Hn_TS_External {
		private $db;
		private $username;
		private $password;
		
		/**
		 * Constructor
		 * @param $db -- an initialised database object to query
		 * @param $usernameIn
		 * @param $passwordIn
		 * @param $proxyHost -- leave Null if not using proxy for HTTP requests
		 * @param $proxyPort -- leave Null if not using proxy for HTTP requests
		 * @param $proxyUser -- leave Null if not using proxy for HTTP requests or no user required
		 * @param $proxyPassword -- leave Null if not using proxy for HTTP requests or no user required
		 */
		function Hn_TS_External($dbIn, $usernameIn, $passwordIn, $proxyHost=NULL, $proxyPort=NULL,
				$proxyUser=NULL,$proxyPassword=NULL){
			$db = $dbIn;
			$username = $usernameIn;
			$password = $passwordIn;
			if(NULL !=$proxyHost){	
				define('WP_PROXY_HOST', $proxyHost);				
				if(NULL !=$proxyPort){
					define('WP_PROXY_PORT', $proxyPort);
					if(NULL != $proxyUser){
						define('WP_PROXY_USERNAME', $proxyUser);						
						if(NULL != $proxyPassword){
							define('WP_PROXY_PASSWORD', $proxyPassword);
						}
					}
				}
			}	
			add_action('hn_ts_replicate_reading', array($this,'hn_ts_replicate_reading_func'),
					10, 3);
			add_action('hn_ts_replicate_readings', array($this,'hn_ts_replicate_readings_func'),
					10, 2);
		}
		
		/**
		 * Posts xml-rpc to the timestreams API
		 * @param $url is the URL to send the request to
		 * @param $method is the XML-RPC procedure to call
		 * @param $params are parameters to pass to the procedure
		 */
		function hn_ts_post($url, $method, $params){
			$data = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>
				<methodCall><methodName>$method</methodName>";
			if(count($params)){
				$data = $data."<params>";
				foreach( $params as $param){
					$data = $data."<param><value><string>$param</string></value></param>";
				} 
				$data = $data."</params>";
			}
			$data=$data."</methodCall>";
			
			return wp_remote_post($url, array( 'headers' => array(
					'Content-Type' => 'text/xml'), 'body' => $data, 'timeout' => 20));
		}
		
		/**
		 * Sends data to interested subscribers
		 * @param $table is the table being subscribed to
		 * @param $value is the data to send to subscribers
		 * @param $timestamp is the timestamp for the data to send to the subscribers
		 */
		function hn_ts_replicate_reading_func($table, $value, $timestamp ){
			//lookup subscribers for given table
			$replications = $db->hn_ts_get_replication_by_local_table(
					array($username,$password,$table));
			//post value/time to subscribers
			foreach($replications as $replication){
				$params = array($replication['remote_user_login'],
						$replication['remote_user_pass'],
						$replication['remote_table'], $value, $timestamp);
				hn_ts_post($replication['remote_url'], 'timestreams.add_measurement', $params);
			}
		}
		
		/**
		 * Sends data to interested subscribers
		 * @param $table is the table being subscribed to
		 * @param $readings is an array of values and timestamps
		 */
		function hn_ts_replicate_readings_func($table, $readings ){
			//lookup subscribers for given table
			$replications = $db->hn_ts_get_replication_by_local_table(
					array($username,$password,$table));
			//post value/time to subscribers
			foreach($replications as $replication){
				$params = array($replication['remote_user_login'],
						$replication['remote_user_pass'],
						$replication['remote_table']);
				$params = $params + $readings;
				// $value, $timestamp);
				hn_ts_post($replication['remote_url'], 'timestreams.add_measurements', $params);
			}
		}
		
		
		/*
		 //The following tests the external posts
			$external = new Hn_TS_External('wwwcache-20.cs.nott.ac.uk','3128');
			$resp = $external->hn_ts_post('http://timestreams.wp.horizon.ac.uk/xmlrpc.php',
					'timestreams.select_measurements', array('admin', 'Time349', 'wp_1_ts_C02_66', '2000-03-02 00:34:00'));
			//var_dump($resp);
			$xml = wp_remote_retrieve_body($resp);
			//ensure successful response
			if(empty($xml)){
				return false;
			} else{
				echo $xml;
			}
		*/
	}
?>