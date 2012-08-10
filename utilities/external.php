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
		function Hn_TS_External($proxyHost=NULL, $proxyPort=NULL,
				$proxyUser=NULL,$proxyPassword=NULL){		
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
		}
		
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