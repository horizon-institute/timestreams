<?php
/* Timestreams API v. 2.0.
 Authors  Jesse Blum (JMB) an Martin Flintham (MDF)
Produced for the Relate Project, Horizon Digital Economy Institute, University of Nottingham

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

require 'Slim/Slim.php';

define('HN_TS_DEBUG', false);
define('HN_TS_VERSION', "v. 2.0.0-Alpha-0.1");
$app = new Slim();
if(HN_TS_DEBUG){
	$app->getLog()->setEnabled(true);
}
else{
	$app->getLog()->setEnabled(false);
}

/** ROUTES (resource URIs to callback function mappings) *********************/
//@todo Make it so that the urls can have / appended to their ends and return correctly.
//At the moment they are 404ing

$app->get('/', 'describeAPI');
$app->get('/metadata', function() use ($app) {
	$paramValue = $app->request()->get('tsid');
	if(!$paramValue){
		hn_ts_metadata();
	}else{
		hn_ts_ext_get_timestream_metadata($paramValue);
	}
});
$app->get('/metadata/:name', function($name) use ($app) {
	$limit = $app->request()->get('limit');
	$offset = $app->request()->get('offset');

	hn_ts_select_metadata_by_name($name, $limit, $offset);
});
$app->get('/measurement_containers', 'hn_ts_list_mc_names');
$app->post('/measurement_container', function() use ($app) {
	$friendlyName = $app->request()->post('name');
	$measuretype = $app->request()->post('measuretype');
	$minimumvalue = $app->request()->post('min');
	$maximumvalue = $app->request()->post('max');
	$unit = $app->request()->post('unit');
	$unitSymbol = $app->request()->post('symbol');
	$device = $app->request()->post('device');
	$otherInformation = $app->request()->post('otherinfo');
	$datatype = $app->request()->post('datatype');
	$missingDataValue = $app->request()->post('missingdatavalue');
	$siteId = $app->request()->post('siteid');
	$blogid = $app->request()->post('blogid');
	$userid = $app->request()->post('userid');
	hn_ts_create_measurement_containerForBlog(
			$measuretype, $minimumvalue, $maximumvalue, $unit,
			$unitSymbol, $device, $otherInformation, $datatype,
			$missingDataValue, $siteId, $blogid, $userid, $friendlyName);
});
$app->get('/measurement_container/:name', function($name) use ($app) {
	if(!isset($name)){
		$app->response()->status(404);
		hn_ts_error_msg("Invalid measurement container: $name");
		return;
	}
	$name = hn_ts_sanitise($name);
	$actionValue = $app->request()->get('action');

	if(NULL == $actionValue){
		$minValue = $app->request()->get('min');
		$maxValue = $app->request()->get('max');
		$limitValue = $app->request()->get('limit');
		$offsetValue = $app->request()->get('offset');
		$sortValue = $app->request()->get('sort');
		$descValue = $app->request()->get('desc');
		hn_ts_select_measurements($name,$minValue, $maxValue, $limitValue,
				$offsetValue,$sortValue,$descValue);
	}else if(!strcasecmp($actionValue, "first")){
		$sql="SELECT * FROM $name LIMIT 1";
		echoJsonQuery($sql, $name);
	}else if(!strcasecmp($actionValue, "latest")){
		$sql = "SELECT * FROM $name WHERE id = ( SELECT MAX( id ) FROM $name ) ";
		echoJsonQuery($sql, $name);
	}else if(!strcasecmp($actionValue, "count")){
		$sql="SELECT COUNT(*) FROM $name;";
		echoJsonQuery($sql, $name);
	}
});
$app->post('/measurement/:id', function($name) use ($app) {
	$value = $app->request()->post('value');
	$timestamp = $app->request()->post('ts');
	hn_ts_add_measurement($name, $value, $timestamp);
});
$app->post('/measurements/:id', function($name) use ($app) {
	$measurements = $app->request()->post('measurements');
	hn_ts_add_measurements($name, $measurements);
});
$app->get('/context', function() use ($app) {
	$typeParam = $app->request()->get('type');
	$valueParam = $app->request()->get('value');
	$startParam = $app->request()->get('start');
	$endParam = $app->request()->get('end');
	$limit = $app->request()->get('limit');
	$offset = $app->request()->get('offset');
	hn_ts_select_contexts($typeParam, $valueParam, $startParam, $endParam, $limit, $offset);
});
$app->post('/context', function() use ($app) {
	$context_type = $app->request()->post('type');
	$value = $app->request()->post('value');
	$start = $app->request()->post('start');
	$end = $app->request()->post('end');
	$user_id = $app->request()->post('user');
	hn_ts_add_context($context_type, $value, $start, $end, $user_id);
});
$app->put('/context', function() use ($app) {
	$context_id = $app->request()->put('id');
	$context_type = $app->request()->put('type');
	$context_value = $app->request()->put('value');
	$start_time = $app->request()->put('start');
	$end_time = $app->request()->put('end');
	hn_ts_update_context($context_id, $context_type, $context_value, $start_time, $end_time);
});
//$app->put('/replicate', 'hn_ts_replicate'); // not really a put or a post -- do we need to define a new verb for activation?
$app->get('/timestream', 'hn_ts_ext_get_timestreams');
$app->get('/timestream/id/:id', function($id) use ($app) {
	$lastAskTime = $app->request()->get('last');
	$limit = $app->request()->get('limit');
	$order = $app->request()->get('order');
	hn_ts_ext_get_timestream_data($id, $lastAskTime, $limit, $order);
});

// name is the timestream table name
$app->get('/timestream/name/:name', function() use ($app) {
	$args=func_get_args();
	$limit = $app->request()->get('limit');
	$offset = $app->request()->get('offset');
	$lastTimestamp = $app->request()->get('lastts');
	hn_ts_int_get_timestream_data($args[0], $limit, $offset, $lastTimestamp);
});
$app->get('/timestream/head/:id', 'hn_ts_int_get_timestream_head');
$app->put('/timestream/head/:id', function() use ($app) {
	$args=func_get_args();
	$newHead = $app->request()->put('curtime');
	$newStart = $app->request()->put('start');
	$newEnd = $app->request()->put('end');
	$newRate = $app->request()->put('rate');
	hn_ts_int_update_timestream_head($args[0], $newHead, $newStart, $newEnd, $newRate);
});
$app->get('/time', 'hn_ts_ext_get_time');

$app->run();

/** Utility functions ********************************************************/

/**
 * Outputs an error message
 * @param $txt is the message to output
 */
function hn_ts_error_msg($txt){
	echo '{"error":{"message":"'.$txt.'"}}';
}

/**
 * Checks if the given parameter is set and not null
 * @param unknown_type $param
 */
function hn_ts_issetRequiredParameter($param, $paramName){
	if(!isset($param)){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing required parameter: $paramName");
		return false;
	}else{
		return true;
	}
}

/**
 * Handles database connection
 * To do get values from wp-config.php
 */
function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="wpuser";
	$dbpass="wordpress";
	$dbname="wp";
	//$dbname="cellar";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

/**
 * Makes a SQL database call given a SQL string -- usually a SELECT or SHOW
 * @param $sql is a SQL string
 * @return The recordset;
 * @todo Error handling
 */
function querySql($sql){
	$db = getConnection();
	$stmt = $db->query($sql);
	$recordset = $stmt->fetchAll(PDO::FETCH_OBJ);
	$db = null;
	return $recordset;
}

/**
 * INSERT SQL
 * @param $sql is the String with the insertion command
 */
function sqlInsert($sql){
	if(!isset($sql)){
		return;
	}
	try {
		$db = getConnection();
		$count0 = $db->exec($sql);
		$db = null;
		echo '{"insertresult": ' . json_encode("$count0 rows inserted") .  '}';
	} catch(PDOException $e) {
		global $app;
		$app->response()->status($error);
		if(HN_TS_DEBUG){
			hn_ts_error_msg($e->getMessage());
		}else{
			hn_ts_error_msg("Error accessing the database.");
		}
	}
}

/**
 * Update SQL
 * @param $sql is the String with the update command
 */
function hn_ts_sqlUpdate($sql){
	try {
		$db = getConnection();
		$count0 = $db->exec($sql);
		$db = null;
		echo $sql;
		echo '{"updateresult": ' . json_encode("Updated $count0 row(s).") . '}';
	} catch(PDOException $e) {
		global $app;
		$app->response()->status($error);
		if(HN_TS_DEBUG){
			hn_ts_error_msg($e->getMessage());
		}else{
			hn_ts_error_msg("Error accessing the database.");
		}
	}
}

/**
 * Runs a SQL query and echos the results as JSON
 * @param $sql is the query to execute
 * @param $root is the root item for the returned JSON
 */
function echoJsonQuery($sql, $root, $error=404){
	try {
		echo '{"'.$root.'": ' . json_encode(querySql($sql)) . '}';
	} catch(PDOException $e) {
		global $app;
		$app->response()->status($error);
		if(HN_TS_DEBUG){
			hn_ts_error_msg($e->getMessage());
		}else{
			hn_ts_error_msg("Error accessing the database.");
		}
	}
}

/**
 * Really simple sanitisation for input parameters.
 * @param is a String to be sanitized $arg
 * @return a String containing only -a-zA-Z0-9_ or NULL
 * @todo Improve this to better sanitise the inputs
 */
function hn_ts_sanitise($arg){
	if(isset($arg)){
		return preg_replace('/[^-a-zA-Z0-9_\s:\/]/', '_', (string)$arg);
	}else{
		return null;
	}
}

/** API Callback functions ***************************************************/

/**
 * Returns wp_ts_metadata entries
 */
function hn_ts_metadata() {
	$sql = "SELECT wp_ts_metadata.*, wp_ts_metadatafriendlynames.friendlyname
	FROM wp_ts_metadata
	LEFT JOIN wp_ts_metadatafriendlynames
	ON wp_ts_metadatafriendlynames.metadata_id=wp_ts_metadata.metadata_id";	;
	echoJsonQuery($sql, "metadata");
}

/**
 * Returns the names and friendly names of the measurement containers.
 */
function hn_ts_list_mc_names(){
	$sql = "SELECT wp_ts_metadata.metadata_id AS id, 
				wp_ts_metadata.tablename AS name, wp_ts_metadatafriendlynames.friendlyname
	FROM  wp_ts_metadatafriendlynames 
	RIGHT JOIN wp_ts_metadata
	ON wp_ts_metadatafriendlynames.metadata_id=wp_ts_metadata.metadata_id";	
	echoJsonQuery($sql, "measurementContainers");
}

/**
 * Creates a new measurement container.
 * @param String $measurementType
 * @param String (optional) $minimumvalue
 * @param String (optional) $maximumvalue
 * @param String MimeType $unit
 * @param String (optional) $unitSymbol
 * @param String $deviceDetails
 * @param String (optional) $otherInformation
 * @param String mySQL data type $dataType is the type of value to use. Any MySQL type (such as decimal(4,1) ) is a legal value.
 * @param String (optional) $missing_data_value is a value of type $dataType which represents rows in the timeseries with unknown values.
 * @param natural number (optional) $siteId site that owns the measurement container
 * @param natural number (optional) $blogId blog that owns the measurement container
 * @param natural number (optional) $userid user that owns the measurement container
 * Outputs the number of rows added (0 on failure or 1 on success)
 * @todo Sanitize inputs
 */
function hn_ts_create_measurement_containerForBlog($measurementType,
$minimumvalue, $maximumvalue, $unit, $unitSymbol, $deviceDetails, $otherInformation,
$dataType,$missingDataValue, $siteId, $blogId, $userid, $friendlyName){
	/* Ensure that the parameters are valid **********/
	
	//  Ensure friendly name is unique
	if(	hn_ts_issetRequiredParameter($friendlyName, "name")){
		$friendlyName = hn_ts_sanitise($friendlyName);
		$sql = "SELECT * FROM wp_ts_metadatafriendlynames WHERE friendlyname = '$friendlyName'";
		$results = querySql($sql);
		if(count($results)){
			global $app;
			$app->response()->status(400);
			hn_ts_error_msg("The name $friendlyName is already used.");
			return;
		}
	}else{
		return;
	}

	//Ensure that there aren't empty or null values going into mandatory fields.
	if(		!hn_ts_issetRequiredParameter($measurementType, "measuretype") ||
			!hn_ts_issetRequiredParameter($unit, "unit") ||
			!hn_ts_issetRequiredParameter($deviceDetails, "device") ||
			!hn_ts_issetRequiredParameter($dataType, "datatype")
	){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing required parameter(s)");
		return;
	}
	
	// Ensure that the site, blog and user ids are valid
	if(!$siteId || $siteId < 1){
		$siteId = 1;
	}
	if(!$blogId || $blogId < 1){
		$blogId = 1;
	}
	if(!$userid || $userid < 1){
		$userid = 1;
	}

	//Ensure that arguments have legal characters.
	$measurementType = hn_ts_sanitise($measurementType);
	$minimumvalue = hn_ts_sanitise($minimumvalue);
	$maximumvalue = hn_ts_sanitise($maximumvalue);
	$unit = hn_ts_sanitise($unit);
	$unitSymbol = hn_ts_sanitise($unitSymbol);
	$deviceDetails = hn_ts_sanitise($deviceDetails);
	$otherInformation = hn_ts_sanitise($otherInformation);
	$dataType = preg_replace('/[^-a-zA-Z0-9_(),]/', '_', $dataType);
	$missingDataValue = hn_ts_sanitise($missingDataValue);
	$siteId = hn_ts_sanitise($siteId);
	$blogId = hn_ts_sanitise($blogId);
	$userid = hn_ts_sanitise($userid);
		
	try {	
		/** Insert record into metadata, create table, and add friendly name record */
	
		$sql = "SHOW TABLE STATUS LIKE 'wp_ts_metadata';";
		$nextdevice=querySql($sql);
		$nextdevice=$nextdevice[0]->Auto_increment;
	
		$tablename = "wp_$blogId"."_ts_$measurementType"."_$nextdevice";
	
		$sql = "INSERT INTO wp_ts_metadata (tablename, measurement_type, min_value,
		max_value, unit, unit_symbol, device_details, other_info,
		data_type, missing_data_value, producer_site_id, producer_blog_id,
		producer_id) VALUES ('$tablename','$measurementType','$minimumvalue',
		'$maximumvalue','$unit','$unitSymbol','$deviceDetails',
		'$otherInformation','$dataType','$missingDataValue','$siteId',
		'$blogId','$userid')";
		$db = getConnection();
		$count0 = $db->exec($sql);
		if($count0 == 1){
			$sql = 'CREATE TABLE IF NOT EXISTS '.$tablename.' (
				id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
				value '.$dataType.' DEFAULT NULL,
				valid_time timestamp NULL,
				transaction_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';
			$count0 = $db->exec($sql);
			$sql = "INSERT INTO  wp_ts_metadatafriendlynames (metadata_id, friendlyname)
						VALUES ('$nextdevice', '$friendlyName');";
			$db->exec($sql);
			$db = null;
			echo '{"measurementcontainer": ' . json_encode($tablename) .  '}';
		}else{
			global $app;
			$app->response()->status(400);
			hn_ts_error_msg("Invalid parameter(s)");
		}
	} catch(PDOException $e) {
		global $app;
		$app->response()->status($error);
		if(HN_TS_DEBUG){
			hn_ts_error_msg($e->getMessage());
		}else{
			hn_ts_error_msg("Error accessing the database.");
		}
	}
}

/**
 * Adds a measurement to a measurement container.
 * @param String of the form  wp_[blog-id]_ts_[measurement-type]_[device-id] $name
 * @param String $value
 * @param String of the form 2012-07-21 00:05:23 $timestamp
 */
function hn_ts_add_measurement($name, $value, $timestamp){
	$name = hn_ts_sanitise($name);
	if(!$name){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing measurement container name.");
		return;
	}

	$value = hn_ts_sanitise($value);
	if(!$value){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing parameter: value");
		return;
	}

	$timestamp = hn_ts_sanitise($timestamp);
	if(!isset($timestamp)){
		$sql = "SELECT CURRENT_TIMESTAMP";
		$_now = querySql($sql);
		$timestamp = $_now[0]->CURRENT_TIMESTAMP;
	}

	$sql = "INSERT INTO $name (value, valid_time) VALUES ('$value', '$timestamp');";
	sqlInsert($sql);
}

/**
 * Adds measurements to a measurement container.
 * @param String $name in the form  wp_[blog-id]_ts_[measurement-type]_[device-id]
 * @param String $measurements in the format: {"measurements":[{"v":1,"t":"2012-11-09 12:10:23"},{"r":2,"t":"2012-07-21 17:10:23"}]}
 */
function hn_ts_add_measurements($name, $measurements){
	$name = hn_ts_sanitise($name);
	if(!$name){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing measurement container name.");
	}
	$sql = "INSERT INTO $name (value, valid_time) VALUES ";
	$sql2 = $sql;
	$measurements = json_decode($measurements, true);
	if(!isset($measurements)){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing required parameter: measurements");
		return;
	}
	$v = NULL;

	foreach($measurements as $m){
		foreach($m as $m1){
			$v = hn_ts_sanitise($m1['v']);
			if(isset($v)){
				$sql=$sql."('".$v."', '".hn_ts_sanitise($m1['t'])."'),";
			}

		}
	}

	if(!strcmp($sql, $sql2)){
		$app->response()->status(400);
		hn_ts_error_msg("Missing required parameter: measurements");
		return;
	}else{
		$sql = rtrim($sql, ",").";";
	}

	sqlInsert($sql);
}

/**
 * Retrieves records from a measurement container
 * @param String of the form  wp_[blog-id]_ts_[measurement-type]_[device-id] $table
 * @param String of the form 2012-07-21 00:05:23 $minimumTime is minimum timestamp
 * @param String of the form 2012-07-21 00:05:23 $maximumTime is maximum timestamp
 * @param Natural number $limit
 * @param Natural number $offset
 * @param String $sortcolumn is the column to sort by
 * @param String $descending is whether to sort descending
 */
function hn_ts_select_measurements($table, $minimumTime, $maximumTime, $limit,
		$offset, $sortcolumn, $descending){
	$table = hn_ts_sanitise($table);
	$minimumTime = hn_ts_sanitise($minimumTime);
	$maximumTime = hn_ts_sanitise($maximumTime);
	$limit = hn_ts_sanitise($limit);
	$offset = hn_ts_sanitise($offset);
	$sortcolumn = hn_ts_sanitise($sortcolumn);
	$descending = hn_ts_sanitise($descending);

	$where="WHERE ";
	$limit=hn_ts_getLimitStatement($limit, $offset);
	$sort = "";

	if($minimumTime){
		$where=$where."valid_time >= '$minimumTime' ";

		if($maximumTime){
			$where=$where."AND valid_time <= '$maximumTime'";
		}
	}else if($maximumTime){
		$where=$where."valid_time <= '$maximumTime'";
	}

	if(0==strcmp($where,"WHERE ")){
		$where="";
	}

	if($sortcolumn) {
		$sort = "ORDER BY " . $sortcolumn;
		if($descending)
			$sort .= " DESC";
	}else if($descending){
		$sort .= "ORDER BY id DESC";
	}

	$sql = "SELECT * FROM $table $where $sort $limit;";
	echoJsonQuery($sql, "data");
}

/**
 * Echos the metadata corresponding to the given measurement container.
 * @param $mcName is the measurement container table name,
 * @param $limit (optional) is an integer for the maximum number of records to return
 * @param $offset (optional) is an integer for the record offset to return
 */
function hn_ts_select_metadata_by_name($mcName, $limit, $offset){
	if($mcName){
		$limitstatement = hn_ts_getLimitStatement($limit, $offset);
		$sql = "SELECT * FROM wp_ts_metadata WHERE tablename='$mcName' $limitstatement";
		echoJsonQuery($sql, "metadata");
	}else{
		hn_ts_error_msg("Missing measurement container name.");
	}
}

/**
 * Adds a context record.
 * @param context_type, context_value, start time (optional), end time(optional)
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_context($context_type, $value, $start, $end, $user_id){
	$context_type = hn_ts_sanitise($context_type);
	$value = hn_ts_sanitise($value);
	$start = hn_ts_sanitise($start);
	$end = hn_ts_sanitise($end);
	$user_id = hn_ts_sanitise($user_id);
	if(!isset($user_id)){
		$user_id = 0;
	}

	$sql = "INSERT INTO wp_ts_context (context_type, value, start_time, end_time, user_id) VALUES
	('$context_type', '$value', '$start', '$end', '$user_id');";
	sqlInsert($sql);
}

/**
 * Checks username password then selects context records.
 * @param array $args should have 2 parameters:
 * $username, $password,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context($id){
	hn_ts_error_msg("hn_ts_select_context");
	echo "id: $id";

}

/**
 * Builds a SQL where statement given params
 * @param $params if the format: $params = array(
 array("name"=>"context_type","param"=>$typeParam),
 array("name"=>"value","param"=>$valueParam),
 array("name"=>"start_time","param"=>$startParam),
 array("name"=>"end_time","param"=>$endParam)
 );
 * @return string with the WHERE statement
 */
function buildWhere($params){
	$where = "";
	$prevParams=0;
	foreach($params as $param){
		if(isset($param['param'])){
			if(!strcmp($where, "")){
				$where = "WHERE ";
			}else if($prevParams > 0){
				$where = $where." AND ";
			}
			$where = $where.$param['name']."="."'".$param['param']."'";
			$prevParams++;
		}
	}
	return $where;
}

/**
 * Checks username password then selects context records.
 * @param array $args should have 2 parameters:
 * $username, $password,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_contexts($typeParam, $valueParam, $startParam, $endParam, $limit, $offset){
	$typeParam = hn_ts_sanitise($typeParam);
	$valueParam = hn_ts_sanitise($valueParam);
	$startParam = hn_ts_sanitise($startParam);
	$endParam = hn_ts_sanitise($endParam);
	$limit = hn_ts_sanitise($limit);
	$offset = hn_ts_sanitise($offset);

	$sql = "SELECT * FROM wp_ts_context ";
	$params = array(
			array("name"=>"context_type","param"=>$typeParam),
			array("name"=>"value","param"=>$valueParam),
			array("name"=>"start_time","param"=>$startParam),
			array("name"=>"end_time","param"=>$endParam)
	);
	$where = buildWhere($params);
	$limit=hn_ts_getLimitStatement($limit, $offset);
	echoJsonQuery($sql.$where.$limit, "contexts");
}

/**
 * Updates the end time of the context records matching the given values.
 * @param string $context_type
 * @param string $context_value
 * @param timestamp $start_time
 * @param timestamp $end_time
 * @todo Make it so that only the context owners can update their own contexts
 */
function hn_ts_update_context($context_id, $context_type, $context_value, $start_time, $end_time){
	if(!isset($end_time)){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing parameter: end");
		return;
	}

	$context_id = hn_ts_sanitise($context_id);
	$context_type = hn_ts_sanitise($context_type);
	$context_value = hn_ts_sanitise($context_value);
	$start_time = hn_ts_sanitise($start_time);
	$end_time = hn_ts_sanitise($end_time);

	$params = array(
			array("name"=>"context_id","param"=>$context_id),
			array("name"=>"context_type","param"=>$context_type),
			array("name"=>"context_value","param"=>$context_value),
			array("name"=>"start_time","param"=>$start_time)
	);

	$where=buildWhere($params);

	if(!strcmp($where,"")){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing parameters.");
		return;
	}
	$sql = "UPDATE wp_ts_context SET end_time='$end_time' $where";
	hn_ts_sqlUpdate($sql);
}

/**
 * Updates data source hearbeat record
 */
function hn_ts_heartbeat($name, $ipaddress){
	$name = hn_ts_sanitise($name);
	$ipaddress = hn_ts_sanitise($ipaddress);
	if(!isset($name) || !isset($ipaddress)){
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Missing name or ip address.");
	}
	$sql = "UPDATE wp_ts_metadata SET last_IP_Addr='$ipaddress', heartbeat_time=CURRENT_TIMESTAMP() WHERE tablename='$name'";
	hn_ts_sqlUpdate($sql);
}

/**
 * Checks username password then does partial replication for all
 * continuous replication records
 * @todo Output angle brackets instead of html codes
 * @param array $args should have 4 parameters:
 * $username, $password, tablename, ipaddress
 * @return string XML-XPC response with either an error message or 1
 */
function hn_ts_replicate(){
	hn_ts_error_msg("hn_ts_replication");

}

//function hn_ts_siteinfo'] =  array(&$this, 'hn_ts_siteinfo');


/**
 * Returns rows for the given head id
 * @param $headId is the id of the head to return rows for
 */
function hn_ts_getReadHead($headId)
{
	$sql = "SELECT * FROM wp_ts_head WHERE head_id = $headId";
	return querySql($sql);
}

// internal interface

/**
 * Returns the databases current timestamp as a php timestamp
 * @return timestamp or false
 */
function hn_ts_getTimeNow(){
	$sql = "SELECT CURRENT_TIMESTAMP";
	$_now = querySql($sql);
	return strtotime($_now[0]->CURRENT_TIMESTAMP);
}

/**
 * Updates and outputs the head for the given timestream id
 * @param $timestreamId is an id for a timestream
 * @todo Add error checking to update sql commands
 */
function hn_ts_int_get_timestream_head($timestreamId){
	$sql = "SELECT * FROM wp_ts_timestreams WHERE timestream_id = $timestreamId";
	$timestream = querySql($sql);
	if($timestream==null) {
		hn_ts_error_msg("Timestream not found.");
		return;
	}else{
		$timestream=$timestream[0];
	}
	$head = hn_ts_getReadHead($timestream->head_id);

	if($head==null) {
		hn_ts_error_msg("Head not found.");
		return;
	}else{
		$head=$head[0];
	}

	// TODO ratelimit?
	// TODO rate should be a float

	// update/move read head based on timestream time

	// currenttime = time in data source frame
	// lasttime = real time head last moved
	// distance to move = (now - lasttime) * rate

	$now = hn_ts_getTimeNow();

	$newcurrent = (($now - strtotime($head->lasttime)) * $head->rate) + strtotime($head->currenttime);

	if(strcmp($timestream->endtime, "0000-00-00 00:00:00")==0)
	{
		$timestream->endtime = "1970-01-01 00:00:00";
	}

	//if(strtotime($timestream->endtime) > 0)
	//	error_log("blaj");

	if(strtotime($timestream->endtime) > 0 && $newcurrent > strtotime($timestream->endtime))
	{
		//error_log("reset to starttime");
		$currenttime = $timestream->starttime;
	}
	else
	{
		$currenttime = date ("Y-m-d H:i:s", $newcurrent);
	}

	$lasttime = date ("Y-m-d H:i:s", $now);
	//echo "now " . $now . "\n";
	//echo "newcur " . $newcurrent . "\n";

	///$wpdb->update('wp_ts_head',
	//		array(
	//				'lasttime' => $lasttime,
	//				'currenttime' => $currenttime,
	//		),
	//		array('head_id' => $timestream->head_id)
	//);
	$db = getConnection();
	$sql = "UPDATE wp_ts_head SET currenttime='$currenttime', lasttime='$lasttime'
	WHERE head_id = $timestream->head_id";

	$count0 = $db->exec($sql);
	$db = null;

	$head->lasttime = strtotime($lasttime);
	$head->currenttime = strtotime($currenttime);
	echo '{"head": ' . json_encode($head) .  '}';
}

/**
 * Utility function to build a SQL limit statement
 * @param $limit (optionally NULL) is an integer for the number of records
 * @param $offset (optionally NULL) is the record retrieval offset
 */
function hn_ts_getLimitStatement($limit, $offset){
	$limitStmt="";
	//1844674407370955161 is the upper limit of an 8 byte unsigned long integer
	$MAXRECORDS = "1844674407370955161";
	if($limit){
		if($limit < 1){
			$limit = 1;
		}else if($limit > $MAXRECORDS){
			$limit = $MAXRECORDS;
		}
		if($offset){
			if($offset < 1){
				$offset = 1;
			}else if($offset > $MAXRECORDS-1){
				$offset = $MAXRECORDS-1;
			}
			$limitStmt = "LIMIT $offset,$limit";
		}else{
			$limitStmt = "LIMIT 1,$limit";
		}
	}else if($offset){
		if($offset < 1){
			$offset = 1;
		}else if($offset > $MAXRECORDS){
			$offset = $MAXRECORDS;
		}
		$limitStmt = "LIMIT $offset,$MAXRECORDS";
	}
	return $limitStmt;
}

/**
 * Get readings for a given measurement container table
 * @param $tablename is the table name for the measurement container
 * @param $limit is the number of rows to return
 * @param $offset is the row offset
 * @param $lastTimestamp is the last time the database was queried
 */
function hn_ts_int_get_timestream_data($tablename, $limit, $offset, $lastTimestamp){
	$where = "";

	if($lastTimestamp)
	{
		$timeStr = date ("Y-m-d H:i:s", $lastTimestamp);
		$where = "WHERE valid_time > \"$timeStr\"";
	}

	$limitStmt = hn_ts_getLimitStatement($limit, $offset);

	/*
	$sql = "SELECT id,value,valid_time AS timestamp,transaction_time
	FROM (SELECT * FROM $tablename $where ORDER BY valid_time DESC $limitStmt)
	AS T1 ORDER BY timestamp ASC";

	echoJsonQuery($sql, "measurements");
	*/
	
	// mdf - interface expects a unix timestamp
	$sql = "SELECT * FROM (SELECT * FROM $tablename $where ORDER BY valid_time DESC $limitStmt) 
			 AS T1 ORDER BY valid_time ASC";
		
	$readings = querySql($sql);
		
	for($i = 0; $i < count($readings); $i++)
	{
		$newts = strtotime($readings[$i]->valid_time);
		$readings[$i]->timestamp = $newts;
	}
	
	echo '{"measurements": ' . json_encode($readings) .  '}';
}

/**
 * Update a timestream head
 * @param $timestreamId is the id of the timestream to update
 * @param $newHead is the new head time
 * @param $newStart is the new start time
 * @param $newEnd is the new end time
 * @param $newRate is the new rate
 * @todo Add error checking to update commands
 */
function hn_ts_int_update_timestream_head($timestreamId, $newHead, $newStart, $newEnd, $newRate){
	if(!$timestreamId){
		echo '{"error":{"text":"timestream not found: '.$timestreamId.'"}}';
		return;
	}
	if(!$newHead){
		echo '{"error":{"text":"Missing current time."}}';
		return;
	}
	if(!$newStart){
		echo '{"error":{"text":"Missing new start value"}}';
		return;
	}
	if(!$newEnd){
		echo '{"error":{"text":"Missing new end value"}}';
		return;
	}
	if(!$newRate){
		echo '{"error":{"text":"Missing new rate value"}}';
		return;
	}

	$currenttime = date ("Y-m-d H:i:s", $newHead);
	$starttime = date ("Y-m-d H:i:s", $newStart);
	$endtime = date ("Y-m-d H:i:s", $newEnd);
	$sql = "SELECT * FROM wp_ts_timestreams WHERE timestream_id = $timestreamId";

	$db = getConnection();
	$stmt = $db->query($sql);
	$timestreams = $stmt->fetchAll();

	if($timestreams==null) {
		hn_ts_error_msg("Timestream not found");
		return;
	}
	/*$wpdb->update('wp_ts_head',
	 array(
	 		'currenttime' => $currenttime,
	 		'rate' => $newRate,
	 ),
			array('head_id' => $timestreams->head_id)
	);*/
	$id = $timestreams[0]["head_id"];
	$sql = "UPDATE wp_ts_head SET currenttime='$currenttime', rate='$newRate'
	WHERE head_id = $id";

	$count1 = $db->exec($sql);
	$sql = "UPDATE wp_ts_timestreams SET starttime='$starttime', endtime='$endtime'
	WHERE timestream_id = $timestreamId";

	$count2 = $db->exec($sql);
	echo '{"updates":[{"table":"wp_ts_head","rows":'.$count1.'},
	{"table":"wp_ts_timestreams","rows":'.$count2.'}]}';
	$db = null;
}


// external api
function hn_ts_ext_get_time(){
	$sql = "SELECT CURRENT_TIMESTAMP";
	// need to return a unix timestamp due to js date parsing issues
	$res = querySql($sql);
	$res[0]->CURRENT_TIMESTAMP = strtotime($res[0]->CURRENT_TIMESTAMP);
	echo '{"timestamp": ' . json_encode($res) . '}';
}

/**
 * Returns all timestreams
 */
function hn_ts_ext_get_timestreams(){
	$sql = "SELECT * FROM wp_ts_timestreams";
	echoJsonQuery($sql, "timestreams");
}

/**
 * Returns the measurement table's metadata row for the given timestream id
 * @param $timestreamId is the id of the timestream to return the metadata id for
 */
function hn_ts_ext_get_timestream_metadata($timestreamId){
	// mdf - this api call should return the metadata itself, not just the id.
	$sql = "SELECT metadata_id FROM wp_ts_timestreams WHERE timestream_id = $timestreamId";
	$timestream = querySql($sql);
	
	if($timestream==null) {
		hn_ts_error_msg("Timestream not found.");
		return;
	} else{
		$timestream = $timestream[0];
	}
	
	$metadata = hn_ts_getMetadata($timestream->metadata_id);
	
	if($metadata==null) {
		hn_ts_error_msg("Metadata not found.");
		return;		
	} else {
		echo '{"metadata": ' . json_encode($metadata) . '}';		
	}
}

/**
 * Updates the read head
 * @param $timestream is a query row array for a timestream
 * @uses triggered by viz getting data, update read head at given rate
 * @return NULL on failure or the updated head row data as array
 * @todo ratelimit?
 * @todo rate should be a float
 * @todo Add error checkign to Update sql commands
 */
function hn_ts_timestream_update($timestream)
{
	if($timestream==null) {
		hn_ts_error_msg("Timestream not found");
		return;
	}

	$head = hn_ts_getReadHead($timestream->head_id);

	if($head==null) {
		hn_ts_error_msg("Head not found.");
		return null;
	} else{
		$head = $head[0];
	}

	// update/move read head based on timestream time

	// currenttime = time in data source frame
	// lasttime = real time head last moved
	// distance to move = (now - lasttime) * rate

	$now = hn_ts_getTimeNow();

	//echo "head->lasttime " . $head->lasttime . "\n";
	//echo "head->lasttime ut " . strtotime($head->lasttime) . "\n";

	$newcurrent = (($now - strtotime($head->lasttime)) * $head->rate) + strtotime($head->currenttime);

	if(strcmp($timestream->endtime, "0000-00-00 00:00:00")==0) {
		$timestream->endtime = "1970-01-01 00:00:00";
	}

	//if(strtotime($timestream->endtime) > 0)
	//	error_log("blaj");

	if(strtotime($timestream->endtime) > 0 && $newcurrent > strtotime($timestream->endtime)) {
		//error_log("reset to starttime");
		$currenttime = $timestream->starttime;
	}
	else {
		$currenttime = date ("Y-m-d H:i:s", $newcurrent);
	}

	$lasttime = date ("Y-m-d H:i:s", $now);

	//echo "now " . $now . "\n";
	//echo "newcur " . $newcurrent . "\n";

	//$wpdb->update('wp_ts_head',
	//		array(
	//				'lasttime' => $lasttime,
	//				'currenttime' => $currenttime,
	//		),
	//		array('head_id' => $timestream->head_id)
	//);

	$db = getConnection();
	$sql = "UPDATE wp_ts_head SET currenttime='$currenttime', lasttime='$lasttime'
	WHERE head_id = $timestream->head_id";
	$count0 = $db->exec($sql);
	$db = null;

	$head->lasttime = strtotime($lasttime);
	$head->currenttime = strtotime($currenttime);

	return $head;
}

/**
 * Returns a data row for a given metadataId
 * @param $metadataId
 */
function hn_ts_getMetadata($metadataId)
{
	$sql = "SELECT * FROM wp_ts_metadata WHERE metadata_id = $metadataId";
	$meta = querySql($sql);
	if($meta==null) {
		hn_ts_error_msg("Metadata not found.");
		return null;
	} else{
		$meta = $meta[0];
	}
	return $meta;
}

/**
 * Returns data corresponding to a given timestream since the last time the function was called.
 * @param $timestreamId is the id of a timestream
 * @param $lastAskTime is the last time this function was called
 * @param $limit is the maximum number of rows to output
 * @param $order ["ASC"|"DESC"] to set the output ordering
 * @todo Return current server time for initial request sync
 * @
 */
function hn_ts_ext_get_timestream_data($timestreamId, $lastAskTime, $limit, $order="ASC"){
	// JMB added to allow user to choose the order (ASC or DESC) of the results
	if(strcasecmp($order, "DESC")){
		$order = "ASC";
	}

	$sql = "SELECT * FROM wp_ts_timestreams WHERE timestream_id = $timestreamId";
	$timestream = querySql($sql);
	if($timestream==null) {
		hn_ts_error_msg("Timestream not found");
		return;
	}else{
		$timestream = $timestream[0];
	}

	hn_ts_timestream_update($timestream);


	$head = hn_ts_getReadHead($timestream->head_id);
	if($head==null) {
		hn_ts_error_msg("Head not found.");
		return null;
	} else{
		$head = $head[0];
	}

	$metadata = hn_ts_getMetadata($timestream->metadata_id);
	if($metadata==null)	{
		return;	// if it is null then hn_ts_getMetadata will emit a message
	}

	// how much timestream has elapsed since last ask
	if($head->rate==0)	{
		// no data, stopped
		hn_ts_error_msg("Data not found.");
	}

	$now = hn_ts_getTimeNow();

	//echo "now " . $now . "\n";
	//echo "lastask " . $lastAskTime . "\n";

	$elapsed = ($now - $lastAskTime) * $head->rate;

	//echo "head ct " . $head->currenttime . "\n";
	//echo "elapsed since last ask " . $elapsed . "\n";

	// get data between head->currenttime and head->currenttime - elapsed
	$maxdate = $head->currenttime;
	$mindate = date ("Y-m-d H:i:s", strtotime($head->currenttime) - $elapsed);

	//echo "maxdate " . $maxdate . "\n";
	//echo "mindate " . $mindate . "\n";
	//echo $metadata->tablename . "\n";

	$limitstr = "";

	if($limit!=0)
	{
		//	if($order == "ASC"){
		//		$count = $wpdb->get_row("SELECT COUNT FROM $metadata->tablename WHERE valid_time > $mindate AND valid_time <= $maxdate");
		//		$lt = $count - $limit;
		//		$limitstr = " LIMIT $lt , $limit";
		//	}else{
		//
			$limitstr = " LIMIT 0 , $limit";
			//}
	}

	$sql = "SELECT * FROM $metadata->tablename WHERE valid_time > '$mindate' AND
	valid_time <= '$maxdate' ORDER BY valid_time DESC $limitstr";
	$ret = querySql($sql);
	if(!strcmp($order,'ASC')){
		$ret= array_reverse($ret);
	}
	echo '{"timestream": ' . json_encode($ret) . '}';
}

/** Documentation Functions **************************************************/
/**
 * Describes this API
 */
function describeAPI(){
	echo '<!DOCTYPE html>
	<html lang="en">
		<head>
			<title>Timestreams API '. HN_TS_VERSION . '</title>
			<meta charset="utf-8">
			<meta name="description" content="Timestreams API '. HN_TS_VERSION . '">
			<meta name="keywords" content="timestreams">
			<meta itemprop="name" content="Timestreams API '. HN_TS_VERSION . '">
		</head>
		<body>';
			echo
			'<h1>Timestreams API '. HN_TS_VERSION . '</h1>
			<div id="doc-description">
				<p>
					Timestreams provides functions to add and organise sensor data.
					It allows you to connect information from your community or school with your blog and the rest
					of the world.</p><p>The Timestreams API provides access to the underlying Timestreams plugin data.
					This document explains the API features.
				</p>
			</div>
			<div id="contents">
				<h2>Contents</h2>
				<ol>
					<li><a href="#notes">Notes</a></li>
						<ol>
							<li><a href="#credits">Credits</a></li>
							<li><a href="#license">License</a></li>
							<li><a href="#version">Version</a></li>
							<li><a href="#security">Security</a></li>
							<li><a href="#date">Date Format</a></li>
							<li><a href="#request-response-format">Request and Response Format</a></li>
							<li><a href="#errors">Errors</a></li>
						</ol>
					</li>
					<li><a href="#services">Services</a></li>
						<ol>
							<li><a href="#service-metadata">Metadata</a>
								<ol>
									<li><a href="#service-metadata-get-mc-entries">Get Measurement Container Metadata </a></li>
									<li><a href="#service-metadata-get-mc-id">Get Metadata Id for Container Id</a></li>
									<li><a href="#service-metadata-for-mc">Get Metadata for Container</a></li>									
								</ol>
							</li>
							<li><a href="#service-container">Measurement Container Services</a>
								<ol>
									<li><a href="#service-container-get-mc-names">Get Measurement Container Names</a></li>
									<li><a href="#service-container-get-all-data">Get All Container Data</a></li>
									<li><a href="#service-container-get-first-data">Get First Container Data</a></li>
									<li><a href="#service-container-get-latest-data">Get Latest Container Data</a></li>
									<li><a href="#service-container-get-count-data">Get Container Data Count</a></li>
									<li><a href="#service-container-create-container">Create Measurement Container</a></li>
								</ol>
							</li>
							<li><a href="#service-measurements">Measurements</a>
								<ol>
									<li><a href="#service-measurements-add-measurement">Add Measurement</a></li>
									<li><a href="#service-measurements-add-measurements">Add Measurements</a></li>
								</ol>
							</li>
							<li><a href="#service-context">Context</a>
								<ol>
									<li><a href="#service-context-get-context">Get Context</a></li>
									<li><a href="#service-context-add">Add Context</a></li>
									<li><a href="#service-context-update">Update Context</a></li>
								</ol>
							</li>
						</ol>
					</li>
				</ol>
			</div>
			<div id="notes">
				<h2>Notes</h2>
				<div id="authoriship">
					<h3 id="credits">Credits</h3>
					<p>Timestreams has been developed as part of the Relate Project.
					The Relate Project is a collaboration between the Horizon Digital Economy Research Institute,
					artist company Active Ingredient, Dr Carlo Buontempo from the Met Office Hadley Centre,
					Brazilian curator Silvia Leal, staff in Computer Science, Psychology,
					Performance and New Media from Horizon (University of Nottingham and University of Exeter),
					and a number of communities in Brazil and the UK.
					The project is funded by RCUK. Relate aims to develop sensor kits and
					Timestreams to support remote communities in reflecting about the relationship
					between energy and climate change.</p>
					<p>This document is mainted by Jesse Blum. 
					The Timestreams Wordpress plugin code can be found <a href="https://github.com/pszjmb1/Timestreams" title="Timestreams">here</a>.
					Issues to do with the API or plugin can be filed <a href="https://github.com/pszjmb1/Timestreams/issues" title="Timestreams issues">here.</a>
					<h3 id="license">License</h3>
					<p>Copyright (C) 2012 Horizon Digital Economy Research Institute</p>
					<p>This program is free software: you can redistribute it and/or modify
					it under the terms of the GNU Affero General Public License as
					published by the Free Software Foundation, either version 3 of the
					License, or (at your option) any later version.</p>
					<p>This program is distributed in the hope that it will be useful,
					but WITHOUT ANY WARRANTY; without even the implied warranty of
					MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
					GNU Affero General Public License for more details.</p>
					<p>You should have received a copy of the GNU Affero General Public License
					along with this program.  If not, see <a href="http://www.gnu.org/licenses/" title="license">this site.</a></p>
				</div>
				<div id="version">
					<h3>Version</h3>
					<p>This is version 2 of the API. Version 1 (the XML-RPC API) is deprecated for performance reasons and
					effort should be made to convert client applications to using version 2 of the API.
					Most version 1 methods will continue to work during alpha testing, but the use of these causes
					Timestreams issues and will be disabled shortly. </p>
					<p>Note that this version of the API is an alpha test version subject to change before public release.</p>
				</div>
				<div id="security">
					<h3>Security</h3>
					<p>Currently no authentication or authorisation is required to use the API.
					This is a temporary measure for testing purposes. In the near future we will
					require authentication measures and requests over SSL.</p>
					</div>
					<div id="date">
					<h3>Date Format</h3>
					<p>Dates in the API are strings in the following format:</p>
					<pre>"2012-10-20 16:30:51"</pre>
				</div>
				<div id="request-response-format">
					<h3>Request and Response Format</h3>
					<p>Requests and responses in the API use standard HTTP with content
					in JSON. We have future plans to support XML as well.</p>
					<div id="errors">
						<h3>Errors</h3>
						<p>Errors are returned using HTTP error code syntax.
						Any additional info is included in the body of the return call, JSON-formatted.
						Error codes not listed here are in the REST API methods listed below.</p>
						The following table describes the error codes that may be encountered:
						<p><table border="1">
							<tr>
								<th>Response Code</th><th>Description</th>
							</tr>
							<tr>
								<td>400</td>
								<td>
									Bad request.
									Error message should indicate the cause (usually to do with parameters).
								</td>
							</tr>
							<tr>
								<td>404</td>
								<td>Resource not found.</td>
							</tr>
						</table></p>
					</div>
				</div>
			</div>
			<div id="services">
				<h2>Services</h2>
				<p>The following services are implemented in this API.</p><ul>';
	hn_ts_describe_metadata();
	hn_ts_describe_mc();
	hn_ts_document_measurements();
	hn_ts_describe_context();
	echo '</ul></div></body></html>';

	/**
	 * Removed the following for future work:
	 */
	/*<tr>
	 <td>./replicate/
	<form name="hn_tsreplicate" action="../2/replicate" method="post">
	<input type="submit" value="Submit">
	</form></td>
	<td>Replaces timestreams.replication.</td><td>POST</td>
	<td>... </td><td>Success or failure message.</td><td>Incomplete.</td>
	</tr>
	*/
}

/**
 * Describes the metadata service
 */
function hn_ts_describe_metadata(){
	echo '
		<li class="service" id="service-metadata">
			<h3>Metadata Services</h3>		
			<div class="service-description">
				Metadata records describe measurement containers and the type of data contained within.<br/><br/>
				<table class="record-description" border="1">
				    <tr>
				        <th>Attribute</th><th>Description</th>
				    </tr>
				    <tr>
				        <td>metadata_id</td><td>Id of a metadata record</td>
				    </tr>
				    <tr>
				        <td>tablename</td><td>Corresponding measurement container name</td>
				    </tr>
				    <tr>
				        <td>producer_site_id</td><td>Site id that this measurement container belongs to.</td>
				    </tr>
				    <tr>
				        <td>producer_blog_id</td><td>Blog id that this measurement container belongs to.</td>
				    </tr>
				    <tr>
				        <td>producer_id</td><td>User id that this measurement container belongs to.</td>
				    </tr>
				     <tr>
				        <td>unit</td><td>The unit of measurement (such as Celsius)</td>
				    </tr>
				    <tr>
				        <td>unit_symbol</td><td>The symbol associated with the given unit of measurement (such as C)</td>
				    </tr>
				    <tr>
				        <td>measurement_type</td><td>The data-class (such as temperature or CO2) that the corresponding measurement container is recording data for</td>
				    </tr>
				    <tr>
				        <td>device_details</td><td>Information pertaining to the device that took the measurement</td>
				    </tr>
				    <tr>
				        <td>other_info</td><td>Anything else useful to know about this measurement container</td>
				    </tr>
				    <tr>
				        <td>data_type</td><td>Any legal MySQL data type (usually a numeric type such as "DECIMAL(4,1)")</td>
				    </tr>
				    <tr>
				        <td>min_value</td><td>Minimum legal value</td>
				    </tr>
				    <tr>
				        <td>max_value</td><td>Maximum legal value</td>
				    </tr>
				    <tr>
				        <td>missing_data_value</td><td>Value used to represent missing time series data</td>
				    </tr>
				    <tr>
				        <td>last_IP_Addr</td><td>The last known ip address for the client sending the data</td>
				    </tr>
				    <tr>
				        <td>heartbeat_time</td><td>The last tiem contact was made with the client sending the data</td>
				    </tr>
				    <tr>
				        <td>friendlyname</td><td>The measurement container\'s friendly name</td>
				    </tr>
				</table>
			</div><ul><li>
				<h4 class="service-method" id="service-metadata-get-mc-entries">Get Measurement Container Metadata</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>If no parameter is given then returns the metadata for all of 
				        the measurement container entries. </p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./metadata" title="Get metadata">/metadata</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><pre>timestreams.ext_get_timestream_metadata</pre></dd>			
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr><td>tsid</td><td>Id of a Timestream</td><td>Optional</td>
				    		<td>Counting Number</td><td>Returns the metadata record id for 
				        	the given timestream id (see <a href="#service-metadata-get-mc-id">Get Metadata Id for Container Id</a>).</td></tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Metadata list</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
        {"metadata": [{"metadata_id":"39","tablename":"wp_1_ts_Blog_Id_39","producer_site_id":"1","producer_blog_id":"1","producer_id":"1",
        "unit":"100","unit_symbol":"SomeU","measurement_type":"Blog_Id","device_details":"SU","other_info":"SomeDevice","data_type":
        "SomeOtherInformation","min_value":"SomeMeasurementType","max_value":"-100","missing_data_value":"DECIMAL(4,1)",
        "last_IP_Addr":null,"heartbeat_time":"2012-11-13 10:38:11","friendlyname":"ghij"},
        {"metadata_id":"40","tablename":"wp_1_ts_Blog_Id_40","producer_site_id":"0",
        "producer_blog_id":"1","producer_id":"1","unit":"100","unit_symbol":"SomeU","measurement_type":"Blog_Id","device_details":"SU","other_info":
        "SomeDevice","data_type":"SomeOtherInformation","min_value":"SomeMeasurementType","max_value":"-100","missing_data_value":"DECIMAL(4,1)","last_IP_Addr":null,
        "heartbeat_time":"2012-11-13 10:53:46","friendlyname":"asdf"}]}
				        </pre>
				    </dd>
				</dl>
				</li>
				<li>
				<h4 class="service-method" id="service-metadata-get-mc-id">Get Metadata Id for Container Id</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>When used with id parameter returns the metadata record id for 
				        the given timestream id.</p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./metadata?tsid=1" title="Get metadata">/metadata?tsid=[id]</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><pre>timestreams.ext_get_timestream_metadata</pre></dd>			
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr><td>tsid</td><td>Id of a Timestream</td><td>Optional</td>
				    		<td>Counting Number</td><td>Returns the metadata record id for 
				        	the given timestream id.</td></tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Metadata record id</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
	{"metadata_id": [{"metadata_id":"30"}]}
	        			</pre>
				    </dd>
				</dl>
				</li>
				<li>
				<h4 class="service-method" id="service-metadata-for-mc">Get Metadata for Container</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Returns the metadata for the named measurement container.</p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./metadata/wp_1_ts_temperature_1" title="Get metadata">/metadata/[container tablename]</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><pre>timestreams.select_metadata_by_name</pre></dd>			
				    <dt>Parameters</dt>		
				    <dd><p>None.</p>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Metadata record for given measurement container table</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
	{"metadata": [{"metadata_id":"1","tablename":"wp_1_ts_temperature_1","producer_site_id":"1",
	"producer_blog_id":"2","producer_id":null,"unit":"text\/x-data-celsius","unit_symbol":"C",
	"measurement_type":"temperature","device_details":"WH1081 USB Weather Station","other_info":"",
	"data_type":"decimal(4,1)","min_value":"-40","max_value":"60","missing_data_value":null,
	"last_IP_Addr":"","heartbeat_time":"2012-11-12 14:30:05"}]}
	        			</pre>
				    </dd>
				</dl>
				</li>
			</ul>
		</li>	
	';
}


function hn_ts_describe_mc(){
	echo '	
			<li class="service" id="service-container">
			<h3>Measurement Container Services</h3>		
			<div class="service-description">
				Measurement containers record values at given points in time.
			</div><ul><li>
				<h4 class="service-method" id="service-container-get-mc-names">Get Measurement Container Names</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Returns the names of the measurement containers.</p> 
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./measurement_containers" title="Get measurement containers">/measurement_containers</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><p>None.</p></dd>			
				    <dt>Parameters</dt>		
				    <dd><p>None.</p></dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Container list</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
        {"measurementContainers": [{"id":"1","name":"wp_1_ts_temperature_1","friendlyname":"asdf"},
        {"id":"13","name":"wp_1_ts_C02_13","friendlyname":"gdfgfd"}]}
				        </pre>
				    </dd>
				</dl>
				</li>
				<li>
				<h4 class="service-method" id="service-container-get-all-data">Get All Container Data</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Returns the data for the given measurement container.</p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./measurement_container/wp_1_ts_Pressure_25" title="Get data">/measurement_container/[id]</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><pre>timestreams.select_measurements</pre></dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>min</td><td>Minimum timestamp for returned data.</td><td>Optional</td>
					    		<td>Timestamp</td><td>Sets the minimum time to return measurements for.</td>
				        	</tr>
				    		<tr>
				    			<td>max</td><td>Maximum timestamp for returned data.</td><td>Optional</td>
					    		<td>Timestamp</td><td>Sets the maximum time to return measurements for.</td>
				        	</tr>
				    		<tr>
				    			<td>limit</td><td>Record set limit</td><td>Optional</td>
					    		<td>Counting number</td><td>Restricts the number of returned measurements for.</td>
				        	</tr>
				    		<tr>
				    			<td>offset</td><td>Record set offset.</td><td>Optional</td>
					    		<td>Counting number</td><td>Sets the starting record.</td>
				        	</tr>
				    		<tr>
				    			<td>sort</td><td>Column that the records should be sorted by.</td><td>Optional</td>
					    		<td>Column name (id|value|valid_time|transaction_time)</td><td>Orders the measurements.</td>
				        	</tr>
				    		<tr>
				    			<td>desc</td><td>Descending order.</td><td>Optional</td>
					    		<td>String (true)</td><td>Returns the records in descending order.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Measurement List</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
	{"data": [{"id":"1","value":"1030.5","valid_time":"2012-07-21 00:00:23",
	"transaction_time":"2012-07-12 18:57:01"},
	{"id":"2","value":"1030.4","valid_time":"2012-07-21 00:05:23",
	"transaction_time":"2012-07-12 18:57:01"},
	{"id":"3","value":"1030.4","valid_time":"2012-07-21 00:10:23",
	"transaction_time":"2012-07-12 18:57:01"}]}
	        			</pre>
				    </dd>
				</dl>
				</li>				
				<li>
				<h4 class="service-method" id="service-container-get-first-data">Get First Container Data</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Returns the first measurement for the given container.</p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./measurement_container/wp_1_ts_Pressure_25?action=first" title="Get first data">/measurement_container/[id]?action=first</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><pre>timestreams.select_first_measurement</pre></dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>action</td><td>action=first</td><td>Optional</td>
					    		<td>String (first)</td><td>Returns the first measurement in the container.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Measurement List</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
	{"wp_1_ts_Pressure_25": [{"id":"223","value":"2.0","valid_time":"2012-07-21 17:10:23",
	"transaction_time":"2012-11-12 10:10:34"}]}
	        			</pre>
				    </dd>
				</dl>
				</li>		
				<li>
				<h4 class="service-method" id="service-container-get-latest-data">Get Latest Container Data</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Returns the latest measurement for the given container.</p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./measurement_container/wp_1_ts_Pressure_25?action=latest" title="Get latest data">/measurement_container/[id]?action=latest</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><pre>timestreams.select_latest_measurement</pre></dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>action</td><td>action=latest</td><td>Optional</td>
					    		<td>String (count)</td><td>Returns container measurement count.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Measurement List</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
	{"wp_1_ts_Pressure_25": [{"id":"1","value":"1030.5","valid_time":"2012-07-21 00:00:23",
	"transaction_time":"2012-07-12 18:57:01"}]}
	        			</pre>
				    </dd>
				</dl>
				</li>
				<li>
				<h4 class="service-method" id="service-container-get-count-data">Get Container Data Count</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Returns the number of measurements in a container.</p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./measurement_container/wp_1_ts_Pressure_25?action=count" title="Get latest data">/measurement_container/[id]?action=count</a></pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><pre>timestreams.count_measurements</pre></dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>action</td><td>action=count</td><td>Optional</td>
					    		<td>String (latest)</td><td>Returns the latest measurement in the container.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Measurement List</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
	{"wp_1_ts_Pressure_25": [{"COUNT(*)":"223"}]}
	        			</pre>
				    </dd>
				</dl>
				</li>
				<li>
				<h4 class="service-method" id="service-container-create-container">Create Measurement Container</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>POST</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Adds a new measurement container.</p>
				    </dd>			
				    <dt class="url-label">Post Structure</dt>
				    <dd>
				        <pre>
curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X POST 
-d "name=myContainer&measuretype=temperature&min=0&max=100&
unit=text/x-data-C&symbol=C&device=testDev&otherinfo=blah&
datatype=DECIMAL(5,2)&siteid=1&blogid=1&userid=1"
http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/measurement_container
				        </pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt><dd><ul><li><pre>
timestreams.hn_ts_create_measurements</pre></li>
<li><pre>timestreams.hn_ts_create_measurementsForBlog</pre></li></ul></dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>name</td><td>Unique friendly name for the measurement container</td><td>Required</td>
					    		<td>String (up to 45 chars)</td><td>Sets the container\'s friendly name.</td>
				        	</tr>
				    		<tr>
				    			<td>measuretype</td><td>The data-class (such as temperature or CO2) 
				    			that the corresponding measurement container is recording data for</td><td>Required</td>
					    		<td>String (up to 45 chars)</td><td>Sets the container\'s measurement_type.</td>
				        	</tr>
				    		<tr>
				    			<td>min</td><td>Minimum legal value</td><td>Optional</td>
					    		<td>String (up to 45 chars)</td><td>Sets the container\'s min_value.</td>
				        	</tr>
				    		<tr>
				    			<td>max</td><td>Maximum legal value</td><td>Optional</td>
					    		<td>String (up to 45 chars)</td><td>Sets the container\'s max_value.</td>
				        	</tr>
				    		<tr>
				    			<td>unit</td><td>The unit of measurement (such as Celsius)</td><td>Required</td>
					    		<td>String (up to 45 chars) in internet media type format. 
					    		For sensor data follow a protocol of: text/x-data-Unit, 
					    		where Unit would be the unit of measurement (such as Celsius or Decibels). 
					    		For example: text/x-data-celsius or image/png.</td><td>Sets the container\'s unit.</td>
				        	</tr>
				    		<tr>
				    			<td>symbol</td><td>The symbol for the measurement (e,g, C)</td><td>Optional</td>
					    		<td>String (up to 5 chars)</td><td>Sets the container\'s unit_symbol.</td>
				        	</tr>
				    		<tr>
				    			<td>device</td><td>Name of device containing the sensor (e.g. Rachel\'s Eco Sense)</td>
				    			<td>Required</td><td>String (up to 255 chars)</td>
				    			<td>Sets the container\'s device_details.</td>
				        	</tr>
				    		<tr>
				    			<td>otherinfo</td><td>Other information pertaining to colelcting data in this container</td><td>Optional</td>
					    		<td>String (up to 255 chars)</td><td>Sets the container\'s other_info.</td>
				        	</tr>
				    		<tr>
				    			<td>datatype</td><td>MySQL data type</td><td>Required</td>
					    		<td>String (up to 45 chars) with a 
					    		<a href="https://dev.mysql.com/doc/refman/5.5/en/data-types.html" 
					    		title="mysql data types">MySQL data type</a> 
					    		such as VARCHR(255)</td><td>Sets the container\'s data_type.</td>
				        	</tr>
				    		<tr>
				    			<td>missingdatavalue</td><td>Missing data value</td><td>Optional</td>
					    		<td>String (up to 64 chars)</td><td>Sets the container\'s missing_data_value.</td>
				        	</tr>
				    		<tr>
				    			<td>siteid</td><td>Site id</td><td>Required</td>
					    		<td>Counting number</td><td>Sets the container\'s producer_site_id.</td>
				        	</tr>
				    		<tr>
				    			<td>blogid</td><td>Blog id</td><td>Required</td>
					    		<td>Counting number</td><td>Sets the container\'s producer_blog_id.</td>
				        	</tr>
				    		<tr>
				    			<td>userid</td><td>User id</td><td>Required</td>
					    		<td>Counting number</td><td>Sets the container\'s producer_id.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Measurement Container Table name</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
{"measurementcontainer": "tablename"} 
	        			</pre>
				    </dd>
				</dl>
				</li>
			</ul>
		</li>	
	';	
}

function hn_ts_document_measurements(){
	echo '	
			<li class="service" id="service-measurements">
			<h3>Measurement Services</h3>		
			<div class="service-description">
				Measurements contain values at given points in time.<br/><br/>
				<table class="record-description" border="1">
				    <tr>
				        <th>Attribute</th><th>Description</th>
				    </tr>
				    <tr>
				        <td>[type][blog_id]_[device_id]_id</td><td>Id of this measurement</td>
				    </tr>
				    <tr>
				        <td>value</td><td>Measurement reading</td>
				    </tr>
				    <tr>
				        <td>timestamp</td><td>The time the measurement was taken</td>
				    </tr>
				</table>
			</div><ul><li>
				<h4 class="service-method" id="service-measurements-add-measurement">Add Measurement</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>POST</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Adds a new measurement to a container.</p>
				    </dd>			
				    <dt class="url-label">Post Structure</dt>
				    <dd>
				        <pre>
curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X POST 
-d "value=1" 
http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/measurement/wp_1_ts_Pressure_25
				        </pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt>
				    <dd>
				    	<pre>timestreams.hn_ts_add_measurement</pre>
				    	<p>Note that this can be used as a replacement for timestreams.add_measurement_file(s) / 
				    	timestreams.import_data_from_files. To do so first upload your file 
				    	(which can be accomplished on this server using XML-RPC: 
				    	wp.uploadFile -- https://codex.wordpress.org/XML-RPC_wp#wp.uploadFile)</p>
				    </dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>value</td><td>Measurement value</td><td>Required</td>
					    		<td>A valid data type as per the given measurement container</td>
					    		<td>Sets the measurement value.</td>
				        	</tr>
				    		<tr>
				    			<td>ts</td><td>Timestamp that the measurement was taken</td><td>Optional<p>Note that if this is excluded then the server\'s current time will be used.</td>
					    		<td>Timestamp</td><td>Sets the measurement timestamp.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Insert result</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
{"insertresult": "1 rows inserted"}
	        			</pre>
				    </dd>
				</dl>
				</li><li>
				<h4 class="service-method" id="service-measurements-add-measurements">Add Measurements</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>POST</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Adds new measurements to a container.</p>
				    </dd>			
				    <dt class="url-label">Post Structure</dt><dd>
				        <pre>
curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X POST 
-d "measurements={\"measurements\":[{\"v\":1,\"t\":\"2012-11-09 12:10:23\"},
{\"v\":2,\"t\":\"2012-07-21 17:10:23\"}]}" 
http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/measurements/wp_1_ts_Pressure_25
				        </pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt>	
				    <dd>
				    	<pre>timestreams.add_measurements</pre>
				    </dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>measurements</td><td>JSON array of measurements</td><td>Required</td>
					    		<td>JSON array in the format: {"measurements":[{"v":"[value]","t":"[timestamp]"},...]}</td>
					    		<td>Sets the measurement value.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Insert result</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
{"insertresult": "# rows inserted"}
	        			</pre>
				    </dd>
				</dl>
				</li>
			</ul>
		</li>	
	';	
}

function hn_ts_describe_context(){
	echo '
	<li class="service" id="service-context">
			<h3>Context Services</h3>		
			<div class="service-description">
				Context records describe a range of time points. 
				They are user defined key,value pairs that can help annotate the data. 
				Examples of keys include place, activity or session.<br/><br/>
				<table border="1">
				    <tr>
				        <th>Attribute</th><th>Description</th>
				    </tr>
				    <tr>
				        <td>context_id</td><td>Id of this context item</td>
				    </tr>
				    <tr>
				        <td>user_id</td><td>Id of the user that created the context</td>
				    </tr>
				    <tr>
				        <td>Context_type</td><td>The key (such as "place")</td>
				    </tr>
				    <tr>
				        <td>value</td><td>The context value (such as "Nottingham")</td>
				    </tr>
				    <tr>
				        <td>start_time</td><td>The timestamp this context record holds from</td>
				    </tr>
				    <tr>
				        <td>end_time</td><td>The timestamp this context record ceases to hold. If this value is not set, then the record is presume to continue to hold indefinitely.</td>
				    </tr>
				</table>
			</div><ul><li>
				<h4 class="service-method" id="service-context-get-context">Get Context</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>GET</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Selects context records.</p>
				    </dd>			
				    <dt class="url-label">URL Structure</dt>
				    <dd>
				        <pre><a href="./context" title="Get context">/context</a></pre>
				    </dd>	
				    <dt>Version 1 API replacement</dt>
				    <dd>
				    	<ul>
				    		<li><pre>timestreams.select_context_by_type</pre>
				    		<li><pre>timestreams.select_context_by_value</pre>
				    		<li><pre>timestreams.select_context_by_type_and_value</pre>
				    		<li><pre>timestreams.select_context_within_time_range</pre>
				    	</ul>
				    </dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>type</td><td>Context type</td><td>Optional</td>
					    		<td>A valid context type (String under 100 chars)</td>
					    		<td>Retrieves contexts of this type.</td>
				        	</tr>
				    		<tr>
				    			<td>value</td><td>Context type</td><td>Optional</td>
					    		<td>A valid context type (String under 100 chars)</td>
					    		<td>Retrieves contexts of this type.</td>
				        	</tr>
				    		<tr>
				    			<td>start</td><td>Start time</td><td>Optional</td>
					    		<td>Timestamp</td>
					    		<td>Limits the returned values to those after the start time.</td>
				        	</tr>
				    		<tr>
				    			<td>end</td><td>End time</td><td>Optional</td>
					    		<td>Timestamp</td>
					    		<td>Limits the returned values to those before the end time.</td>
				        	</tr>
				    		<tr>
				    			<td>limit</td><td>Record set limit</td><td>Optional</td>
					    		<td>Counting number</td><td>Restricts the number of returned contexts</td>
				        	</tr>
				    		<tr>
				    			<td>offset</td><td>Record set offset.</td><td>Optional</td>
					    		<td>Counting number</td><td>Sets the starting record.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>List of contexts</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
{"contexts": [{"context_id":"26","user_id":"1","context_type":"place",
"value":"Nottingam","start_time":"2012-11-12 10:10:23","end_time":"2012-11-12 10:20:23"}]}
	        			</pre>
				    </dd>
				</dl>
				</li><li>
				<h4 class="service-method" id="service-context-add">Add Context</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>POST</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Adds a new context.</p>
				    </dd>			
				    <dt class="url-label">Post Structure</dt><dd>
				        <pre>
curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X POST -d 
"type=place&value=Nottingam&start=2012-11-12 10:10:23&end=2012-11-12 10:20:23&user=1" 
http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/context
				        </pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt>	
				    <dd>
				    	<pre>timestreams.add_context</pre>
				    </dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>type</td><td>Context type</td><td>Optional</td>
					    		<td>String (up to 100 chars)</td>
					    		<td>Sets the context_type.</td>
				        	</tr>
				    		<tr>
				    			<td>value</td><td>Context value</td><td>Optional</td>
					    		<td>String (up to 100 chars)</td>
					    		<td>Sets the context_value.</td>
				        	</tr>
				    		<tr>
				    			<td>start</td><td>Start time</td><td>Optional</td>
					    		<td>Timestamp</td>
					    		<td>Enters the context start time.</td>
				        	</tr>
				    		<tr>
				    			<td>end</td><td>End time</td><td>Optional</td>
					    		<td>Timestamp</td>
					    		<td>Enters the context end time.</td>
				        	</tr>
				    		<tr>
				    			<td>user</td><td>user id</td><td>Optional</td>
					    		<td>Counting number</td>
					    		<td>Sets the user id for the owner of the context.</td>
				        	</tr>
				    	</table><br/>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Insert result</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
{"insertresult": "1 rows inserted"}
	        			</pre>
				    </dd>
				</dl>
				</li><li>
				<h4 class="service-method" id="service-context-update">Update Context</h4>		
				<dl>
				    <dt>Method</dt>
				    <dd>
				        <p>PUT</p>
				    </dd>
				    <dt>Description</dt>
				    <dd>
				        <p>Updates the end time of the context records matching the given values.</p>
				    </dd>			
				    <dt class="url-label">Put Structure</dt><dd>
				        <pre>
curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X PUT -d 
"type=a&start=2012-05-22 13:36:11&end=2012-05-22 13:36:11" 
http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/context
				        </pre>
				    </dd>			
				    <dt>Version 1 API replacement</dt>	
				    <dd>
				    	<pre>timestreams.update_context</pre>
				    </dd>		
				    <dt>Parameters</dt>		
				    <dd><br/>
				    	<table border="1">
				    		<tr><th>Name</th><th>Description</th><th>Required or Optional</th>
				    		<th>Type</th><th>Affect</th></tr>
				    		<tr>
				    			<td>end</td><td>End time</td><td>Required</td>
					    		<td>Timestamp</td>
					    		<td>Updates the context\s end time.</td>
				        	</tr>
				    		<tr>
				    			<td>type</td><td>Context type</td><td>Optional</td>
					    		<td>String (up to 100 chars)</td>
					    		<td>Updates the contexts with this context_type.</td>
				        	</tr>
				    		<tr>
				    			<td>value</td><td>Context value</td><td>Optional</td>
					    		<td>String (up to 100 chars)</td>
					    		<td>Updates the contexts with this context_value.</td>
				        	</tr>
				    		<tr>
				    			<td>start</td><td>Start time</td><td>Optional</td>
					    		<td>Timestamp</td>
					    		<td>Updates the contexts with this start time.</td>
				        	</tr>
				    		<tr>
				    			<td>id</td><td>Context id</td><td>Optional</td>
					    		<td>Counting number</td>
					    		<td>Updates the context with this id.</td>
				        	</tr>
				    	</table><br/><p>Note that one of the optional parameters must be used.</p>
				    </dd>
				    <dt>Response</dt>
				    <dd>
				        <p>Update result</p>
				        <p><strong>Sample response</strong></p>
				        <pre>
{"updateresult": "Updated 1 row(s)."}
	        			</pre>
				    </dd>
				</dl>
				</li>
			</ul>
		</li>
	';
}
?>