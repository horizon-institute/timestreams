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
	class Hn_TS_Database {
		private $wpserver;
		protected $missingcontainername;
		protected $missingParameters;
		
		function Hn_TS_Database(){
			$this->wpserver = new wp_xmlrpc_server();
			$this->missingcontainername = new IXR_Error(403, __('Missing container name parameter.'));//"Missing container name parameter.";
			$this->missingParameters= new IXR_Error(403, __('Incorrect number of parameters.'));
		}
		
		/**
		 * Creates the initial timestreams db tables. This is expected only to 
		 * run at plugin install.
		 */
		function hn_ts_createMultisiteTables(){
			global $wpdb;
			$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_context (
				context_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				context_type varchar(100) COLLATE utf8_unicode_ci NOT NULL,
				value varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			    start_time TIMESTAMP NULL DEFAULT 0,
			    end_time TIMESTAMP NULL DEFAULT 0,
				PRIMARY KEY  (context_id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';
			$wpdb->query($sql);
						
			$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_metadata (
				metadata_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				tablename varchar(45) COLLATE utf8_unicode_ci NOT NULL,
  				measurement_type varchar(45) COLLATE utf8_unicode_ci NOT NULL,
			    min_value varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
			    max_value varchar(45) COLLATE utf8_unicode_ci DEFAULT NULL,
			    unit varchar(45) COLLATE utf8_unicode_ci NOT NULL,
			    unit_symbol varchar(5) COLLATE utf8_unicode_ci DEFAULT NULL,
			    device_details varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			    other_info varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
			    data_type varchar(45) COLLATE utf8_unicode_ci NOT NULL,
				PRIMARY KEY  (metadata_id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;';
			$wpdb->query($sql);
						
			$sql = 'CREATE TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_timestreams (
				  timestream_id bigint(20) unsigned NOT NULL,
				  name varchar(55) COLLATE utf8_unicode_ci NOT NULL,
				  head_id bigint(20) NOT NULL,
				  metadata_id bigint(20) unsigned NOT NULL,
				  starttime timestamp,
				  endtime timestamp,
				  PRIMARY KEY  (timestream_id)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;';
			$wpdb->query($sql);
						
			$sql = 'CREATE  TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_timestream_has_context (
				 wp_ts_timestream_id BIGINT(20) UNSIGNED NOT NULL,
				 wp_ts_context_id BIGINT(20) UNSIGNED NOT NULL,
				 PRIMARY KEY  (wp_ts_timestream_id, wp_ts_context_id)
				) ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;';
			$wpdb->query($sql);
						
			$sql = 'CREATE  TABLE IF NOT EXISTS '.$wpdb->prefix.'ts_head (
				  head_id BIGINT(20) NOT NULL,
				  currenttime TIMESTAMP,
				  lasttime TIMESTAMP,
				  rate INT(11) NOT NULL,
				  PRIMARY KEY  (head_id) 
				) ENGINE = MyISAM DEFAULT CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;';			
			
			$wpdb->query($sql);
			//For some reason dbDelta wasn't working for all of the tables :(
		}
		/**
		 * Creates a sensor measurement table for a site.
		 * @param $blogId is the id for the site that the sensor belongs to
		 * @param $type is the type of measurement taken (such as temperature)
		 * @param $deviceId is the id for the device that took the readings
		 * @param $dataType is the type of value to use. Any MySQL type (such as decimal(4,1) ) is a legal value.
		 * To do: Sanitise inputs 
		 */
		function hn_ts_createMeasurementTable($blogId, $type, $deviceId,$dataType){
			global $wpdb;
			$tablename = $wpdb->prefix.$blogId.'_ts_'.$type.'_'.$deviceId;
			$idName = 'id';//$type.'_'.$blogId.'_'.$deviceId.'_id';
			$sql =
			'CREATE TABLE IF NOT EXISTS '.$tablename.' (
				'.$idName.' bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				value '.$dataType.' DEFAULT NULL,
				timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  ('.$idName.')
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
			dbDelta($sql);
			
			return $tablename;
		}
		
		/**
		 * Adds records to the wp_ts_metadata table
		 * @param String $blog_id 
		 * @param String $measurementType 
		 * @param String $minimumvalue
		 * @param String $maximumvalue
		 * @param String $unit
		 * @param String $unitSymbol
		 * @param String $deviceDetails
		 * @param String $otherInformation
		 * @param $dataType is the type of value to use. Any MySQL type (such as decimal(4,1) ) is a legal value.
		 * To do: Sanitise inputs
		 */
		function hn_ts_addMetadataRecord($blog_id='', $measurementType, $minimumvalue, $maximumvalue,
				$unit, $unitSymbol, $deviceDetails, $otherInformation, $dataType){
			global $wpdb;
			if($blog_id==''){
				global $blog_id;				
			}
			//$nextdevice= $this->getCount('wp_ts_metadata')+1;
			/*$nextdevice=$this->getRecord(
					'wp_ts_metadata', 'metadata_id', 
					'1=1 ORDER BY metadata_id DESC Limit 1')+1;*/
			$nextdevice=$wpdb->get_row($wpdb->prepare( 
					"SHOW TABLE STATUS LIKE 'wp_ts_metadata';" )
			);
			$nextdevice=$nextdevice->Auto_increment;
			$tablename = $wpdb->prefix.$blog_id.'_ts_'.$measurementType.'_'.$nextdevice;
			$wpdb->insert(  
			    'wp_ts_metadata', 
			    array( 	'tablename' => $tablename,
			    		'measurement_type' => $measurementType, 
			    		'min_value' => $minimumvalue, 
			    		'max_value' => $maximumvalue, 
			    		'unit' => $unit, 
			    		'unit_symbol' => $unitSymbol,
			    		'device_details' => $deviceDetails,			    		
			    		'other_info' => $otherInformation,			    		
			    		'data_type' => $dataType), 
			    array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' , '%s' )  
			);  
			
			return $this->hn_ts_createMeasurementTable($blog_id, $measurementType, $nextdevice, $dataType);
		}
		
		/**
		 * Retrieves records from a readings table of the form wp_[blog-id]_ts_[measurement-type]_[device-id]
		 * @param integer $blogId is the id of the blog to select from
		 * @param String $measurementType is the type of measurement such as temperatureto select from
		 * @param integer $deviceId is the id of the device to select from
		 * @param timestamp $minimumTime is the lowest timestamp to select from (can be null)
		 * @param timestamp $maximumTime is the maximum timestamp to select from (can be null)
		 * To do: Sanitise parameters
		 * @return the result of the select
		 */
		function hn_ts_get_readings($blogId, $measurementType, $deviceId,
				$minimumTime, $maximumTime){
			global $wpdb;
			$table="wp_$blogId_ts_$measurementType_$deviceId";
			$where="WHERE ";
			if($minimum){
				$where=$where."timestamp > $minimumTime ";
				
				if($maximumTime){
					$where=$where."AND timestamp < $maximumTime";
				}
			}else if($maximumTime){
				$where=$where."timestamp < $maximumTime";
			}
			if(0==strcmp($where,"WHERE ")){
				$where="";
			}
			return $wpdb->get_var( 	$wpdb->prepare("SELECT * FROM $table $where;" )	);
		}
		
		/**
		 * Retrieves records from a readings table of the form wp_[blog-id]_ts_[measurement-type]_[device-id]
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * [3]minimum timestamp
		 * [4]maximum timestamp
		 * To do: Sanitise parameters
		 * @return the result of the select
		 */
		function hn_ts_get_readings_from_name($args){
			global $wpdb;
			if(count($args) < 3){
				return $this->missingcontainername;
			}
			$table=$args[2];
			$minimumTime=$args[3];
			$maximumTime=$args[4];
			$where="WHERE ";
			if($minimumTime){
				$where=$where."timestamp >= '$minimumTime' ";
				
				if($maximumTime){
					$where=$where."AND timestamp < '$maximumTime'";
				}
			}else if($maximumTime){
				$where=$where."timestamp < '$maximumTime'";
			}
			
			if(0==strcmp($where,"WHERE ")){
				$where="";
			}
			return $wpdb->get_results( 	$wpdb->prepare("SELECT * FROM $table $where;" )	);
		}
		
		/**
		 * Retrieves the first record from a readings table of the form wp_[blog-id]_ts_[measurement-type]_[device-id]
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * To do: Sanitise parameters
		 * @return the result of the select
		 */
		function hn_ts_get_first_reading($args){
			global $wpdb;
			if(count($args) != 3){
				return $this->missingcontainername;
			}
				
			$table=$args[2];
			
			return $wpdb->get_row( 	$wpdb->prepare("SELECT * FROM $table LIMIT 1" )	);
		}
		
		/**
		 * Retrieves the first record from a readings table of the form wp_[blog-id]_ts_[measurement-type]_[device-id]
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * To do: Sanitise parameters
		 * @return the result of the select
		 */
		function hn_ts_get_metadata_by_name($args){
			global $wpdb;
			if(count($args) != 3){
				return $this->missingcontainername;
			}
			
			$table=$args[2];
			
			return $wpdb->get_results( 	$wpdb->prepare(
					"SELECT * FROM wp_ts_metadata WHERE tablename='$table'" )	);			
		}
		
		/**
		 * Retrieves the latest record from a readings table of the form wp_[blog-id]_ts_[measurement-type]_[device-id]
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * To do: Sanitise parameters
		 * @return the result of the select
		 */
		function hn_ts_get_latest_reading($args){
			global $wpdb;
			if(count($args) != 3){
				return $this->missingcontainername;
			}
			$table=$args[2];
			
			return $wpdb->get_row( 	$wpdb->prepare("SELECT * FROM 
					$table WHERE id = ( SELECT MAX( id ) FROM $table ) " )	);
		}
		
		/**
		 * Retrieves the count from a readings table of the form wp_[blog-id]_ts_[measurement-type]_[device-id]
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * To do: Sanitise parameters
		 * @return the result of the select
		 */
		function hn_ts_count_readings($args){
			global $wpdb;if(count($args) != 3){
				return $this->missingcontainername;
			}
			
			$table=$args[2];
			
			return $this->getCount($table);
		}
		
		/**
		 * Retrieves a count from a table
		 * @param $table is the table to count
		 */
		function getCount($table){
			global $wpdb;
			$sql="SELECT COUNT(*) FROM $table;";
			return $wpdb->get_var($wpdb->prepare($sql));
		}
		
		/**
		 * Selects all from a table
		 * @param  $table is the table to select from
		 */
		function hn_ts_select($table){
			global $wpdb;
			$sql="SELECT * FROM $table;";
			return $wpdb->get_results($wpdb->prepare($sql));
		}
		
		/**
		 * Retrieves context information
		 * @return the selection
		 */
		function hn_ts_select_context(){
			global $wpdb;
			$sql="SELECT * FROM wp_ts_context";
			return $wpdb->get_results($wpdb->prepare($sql));
		}
		
		/**
		 * Retrieves context information
		 * @return the selection
		 */
		function hn_ts_get_context_by_type($args){
			global $wpdb;
			if(count($args) < 3){
				return $this->missingParameters;
			}
			$sql="SELECT *  FROM wp_ts_context WHERE context_type='$args[2]'";
			return $wpdb->get_results($wpdb->prepare($sql));
		}
		
		/**
		 * Retrieves context information
		 * @return the selection
		 */
		function hn_ts_get_context_by_value($args){
			global $wpdb;
			
			if(count($args) < 3){
				return $this->missingParameters;
			}
			$sql="SELECT *  FROM wp_ts_context WHERE value='$args[2]'";
			return $wpdb->get_results($wpdb->prepare($sql));
		}
		
		/**
		 * Retrieves context information
		 * @return the selection
		 */
		function hn_ts_get_context_by_type_and_value($args){
			global $wpdb;
			
			if(count($args) < 4){
				return $this->missingParameters;
			}
			
			$sql="SELECT *  FROM wp_ts_context WHERE context_type='$args[2]' AND value='$args[3]'";
			return $wpdb->get_results($wpdb->prepare($sql));
		}
		
		/**
		 * Retrieves context information
		 * @return the selection
		 */
		function hn_ts_get_context_within_time_range($args){
			global $wpdb;
			//$sql="SELECT *  FROM wp_ts_context WHERE context_type='$args[2]' AND value='$args[3]'";
			//return $wpdb->get_results($wpdb->prepare($sql));	
			
			if(count($args) < 4){
				return $this->missingParameters;
			}		
			
			$startTime=$args[2];
			$endTime=$args[3];
			$where="WHERE ";
			if(0!=strcmp(strtoupper($startTime),"NULL")){
				$where=$where."start_time >= '$startTime' ";
			
				if(0!=strcmp(strtoupper($endTime),"NULL")){
					$where=$where."AND 	end_time < '$endTime'";
				}
			}else if(0!=strcmp(strtoupper($endTime),"NULL")){
				$where=$where."	end_time < '$endTime'";
			}
			if(0==strcmp($where,"WHERE ")){
				$where="";
			}
			return $wpdb->get_results( 	$wpdb->prepare("SELECT * FROM wp_ts_context $where;" )	);
		}
	
		/**
		 * Inserts a reading into a data table
		 * Todo: handle write permissions from username and password
		 * 		Or better yet, implement OAuth
		 * 		Also, handle the format param for $wpdb->insert.
		 * 		And also make it more robust!
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * [3]value
		 * [4]timestamp 
		 */
		function hn_ts_insert_reading($args){
			global $wpdb;
			if(count($args)> 4){
				return $wpdb->insert( $args[2],
						 array('value' => $args[3],'timestamp' => $args[4]) );				
			}else if(count($args) == 4){
				return $wpdb->insert( $args[2], array('value' => $args[3]));
			}else{
				return $this->missingParameters;
			}			
		}
		
		/**
		 * Inserts multiple readings into a data table
		 * Todo: handle write permissions from username and password
		 * 		Or better yet, implement OAuth
		 * 		Also, handle the format param for $wpdb->insert.
		 * 		And also make it more robust!
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * [odd]value
		 * [even]timestamp 
		 */
		function hn_ts_insert_readings($args){
			global $wpdb;
			$retval = 0;
			$cnt = count($args);
			if(count($args) < 4){
				return "Number of insertions: $retval";  
			}
			for($i=3; $i+1 < $cnt; $i+=2){
				if(count($args) > $i+1){
					if($wpdb->insert( $args[2],
							array('value' => $args[$i],'timestamp' => $args[$i+1]) )){
						$retval++;
					}
				}
			}
			return "Number of insertions: $retval";
			
		}
		
		/**
		 * Adds records to the wp_ts_context table. 
		 * @param $args should have 6 parameters:
		 * $username, $password, context_type, context_value, start time (optional), end time(optional)
		 * optional values should be "NULL"
		 * Todo: Sanitise inputs
		 */
		function hn_ts_addContextRecordTimestamped($args){
			global $wpdb;			
			if(count($args) != 6){
				return $this->missingParameters;
			}
			$baseVals = array( 	'context_type' => $args[2], 'value' => $args[3]);
			$baseTypes=array( '%s', '%s');  
			
			if(!(0 == strcmp($args[4], "") || 0 == strcmp($args[4], "NULL"))){
				array_push($baseVals, $args[4]);
				array_push($baseTypes, '%s');
			}
				
			if(!(0 == strcmp($args[5], "") || 0 == strcmp($args[5], "NULL"))){
				array_push($baseVals, $args[5]);
				array_push($baseTypes, '%s');
			}
			
			return $wpdb->insert( 'wp_ts_context',  $baseVals, $baseTypes  );
		}
		
		/**
		 * Adds records to the wp_ts_context table. 
		 * @param String $context_type
		 * @param String $context_value
		 * Todo: Sanitise inputs
		 */
		function hn_ts_addContextRecord($context_type, $context_value){
			global $wpdb;
			
			return $wpdb->insert(  
			    'wp_ts_context', 
			    array( 	'context_type' => $context_type,
			    		'value' => $context_value), 
			    array( '%s', '%s' )  
			);  
		}
		
		/**
		 * Updates wp_ts_context records. 
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]Context type
		 * [3]Value
		 * [4]Start time (optional -- use 'NULL' to exclude)
		 * [5]End time
		 * Todo: Sanitise inputs
		 */
		function hn_ts_updateContextRecord($args){
			global $wpdb;	
			if(count($args) != 6){
				return $this->missingParameters;
			}
			$where="";
			if(0!=strcmp(strtoupper($args[4]),"NULL")){
				$where = array( 	'context_type' => $args[2],
						'value' => $args[3],
						'start_time' => $args[4]);
			}else{
				$where = array( 	'context_type' => $args[2],
						'value' => $args[3]);				
			}
			
			return $wpdb->update(  
			    'wp_ts_context',  array( 'end_time' => $args[5]), $where,'%s','%s'
			);
		}
		/**
		 * Records that a file was uploaded. The timestamp is the time the file was last modified prior to upload
		 * Todo: handle write permissions from username and password
		 * 		Or better yet, implement OAuth
		 * 		Also, handle the format param for $wpdb->insert.
		 * 		And also make it more robust!
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * [3]File details: [0] Name, [1] Type [3] Bits
		 * [4]timestamp 
		 */
		function hn_ts_upload_reading_file($args){
			global $wpdb, $blog_id;
			
			if(count($args) < 4){
				return $this->missingParameters;
			}
			$args[3]['name']=$args[2].'_'.$args[3]['name'];
			$fileArgs = array($blog_id, $args[0],$args[1],$args[3]);			
			$uploadedFile = $this->wpserver->mw_newMediaObject($fileArgs);
			if(is_array($uploadedFile)){
				if(count($args)>4){
					$wpdb->insert( $args[2],
						 array('value' => $uploadedFile['url'],'timestamp' => $args[4]) );
				}else{
					$wpdb->insert( $args[2],
						array('value' => $uploadedFile['url']) );
				}
				return $uploadedFile['url'];
			}else{
				return $uploadedFile;
			}
			
		}
		/**
		 * Records that a file was uploaded. The timestamp is the time the file was last modified prior to upload
		 * Todo: handle write permissions from username and password
		 * 		Or better yet, implement OAuth
		 * 		Also, handle the format param for $wpdb->insert.
		 * 		And also make it more robust!
		 * @param $args is an array in the expected format of:
		 * [0]username
		 * [1]password
		 * [2]table name
		 * [3]Array of File details: [0] Name, [1] Type [3] Bits [4]timestamp 
		 */
		function hn_ts_upload_reading_files($args){
			global $wpdb, $blog_id;
			
			if(count($args) < 4){
				return $this->missingParameters;
			}
			
			$fileCount = 0;
			foreach($args[3] as $aFile){
				$aFile['name']=$args[2].'_'.$aFile['name'];
				$fileArgs = array($blog_id, $args[0],$args[1],$aFile);			
				$uploadedFile = $this->wpserver->mw_newMediaObject($fileArgs);
				if(is_array($uploadedFile)){
					$wpdb->insert( $args[2],
						 array('value' => $uploadedFile['url'],'timestamp' => $aFile['timestamp']) );
					$fileCount++;
				}
			}
			return $fileCount;			
		}
	}	
?>