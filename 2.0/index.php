<?php

require 'Slim/Slim.php';
$HN_TS_DEBUG = true;
$app = new Slim();

/** ROUTES (resource URIs to callback function mappings) *********************/

$app->get('/', 'describeAPI');
$app->get('/measurementContainerMetadata', function() use ($app) {
	$paramValue = $app->request()->get('tsid');
	if(!$paramValue){
		measurementContainerMetadata();
	}else{
		hn_ts_ext_get_timestream_metadata($paramValue);
	}
});
$app->get('/measurementContainerMetadata/:name', function($name) use ($app) {
	$limit = $app->request()->get('limit');
	$offset = $app->request()->get('offset');
	
	hn_ts_select_metadata_by_name($name, $limit, $offset);
});
$app->get('/measurementContainer', 'hn_ts_list_mc_names');
$app->post('/measurementContainer', function() use ($app) {
	$measuretype = $app->request()->post('measuretype');
	$minimumvalue = $app->request()->post('minimumvalue');
	$maximumvalue = $app->request()->post('maximumvalue');
	$unit = $app->request()->post('unit');
	$unitSymbol = $app->request()->post('unitsymbol');
	$device = $app->request()->post('device');
	$otherInformation = $app->request()->post('otherinfo');
	$datatype = $app->request()->post('datatype');
	$missingDataValue = $app->request()->post('missingdatavalue');
	$siteId = $app->request()->post('siteId');
	$blogid = $app->request()->post('blogid');
	$userid = $app->request()->post('userid');
	hn_ts_create_measurement_containerForBlog(
		$measuretype, $minimumvalue, $maximumvalue, $unit,
		$unitSymbol, $device, $otherInformation, $datatype,
		$missingDataValue, $siteId, $blogid, $userid);
});
$app->get('/measurementContainer/:name', function($name) use ($app) {
	if(!isset($name)){
		$app->response()->status(404);
		hn_ts_error_msg("Invalid measurement container: $name");
		return;
	}
	$name = hn_ts_sanitise($name);
	$paramValue = $app->request()->get('action');
	
	if(NULL == $paramValue){
		hn_ts_select_measurements($name);
	}else if(!strcasecmp($paramValue, "first")){
		$sql="SELECT * FROM $name LIMIT 1";
		echoJsonQuery($sql, $name);
	}else if(!strcasecmp($paramValue, "latest")){
		$sql = "SELECT * FROM $name WHERE id = ( SELECT MAX( id ) FROM $name ) ";
		echoJsonQuery($sql, $name);
	}else if(!strcasecmp($paramValue, "count")){
		$sql="SELECT COUNT(*) FROM $name;";
		echoJsonQuery($sql, $name);
	}
});
$app->post('/measurement/:id/add/', 'hn_ts_add_measurement');
$app->post('/measurements/:id/add', 'hn_ts_add_measurements');
$app->post('/context/add', 'hn_ts_add_context');
$app->get('/context', function() use ($app) {
	if(NULL == $app->request()->get()){
		hn_ts_select_contexts();
	}
	$typeParam = $app->request()->get('type');
	$valueParam = $app->request()->get('value');
	$startParam = $app->request()->get('start');
	$endParam = $app->request()->get('end');
	if($typeParam){
		if(NULL == $valueParam){
			hn_ts_select_context_by_type();
		}else{
			hn_ts_select_context_by_type_and_value();			
		}
	}else if($valueParam){
		hn_ts_select_context_by_value();
	}else if($startParam || $endParam){
		hn_ts_select_context_within_time_range($startParam,$endParam);
	}
});
$app->get('/context/:id', 'hn_ts_select_context');
$app->put('/context/:id', 'hn_ts_update_context');
$app->post('/measurementfile/:id', 'hn_ts_add_measurement_file');
$app->post('/measurementfiles/:id', 'hn_ts_add_measurement_files');
$app->put('/import', 'hn_ts_import_data_from_files'); // not really a put or a post -- do we need to define a new verb for activation?
$app->post('/heartbeat/:id', 'hn_ts_heartbeat');
$app->put('/replicate', 'hn_ts_replicate'); // not reall a put or a post -- do we need to define a new verb for activation?
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
	global $HN_TS_DEBUG;
	if($HN_TS_DEBUG){
		echo '{"error":{"message":"'.$txt.'"}}';
	}
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
	$dbuser="root";
	$dbpass="tow4mfN";
	$dbname="wordpress";
	//$dbname="cellar";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

/**
 * Makes a SQL database call given a SQL string
 * @param $sql is a SQL string
 * returns the recordset;
 */
function querySql($sql){
		$db = getConnection();
		$stmt = $db->query($sql);  
		$recordset = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;	
		return $recordset;
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
		hn_ts_error_msg($e->getMessage()); 
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
		return preg_replace('/[^-a-zA-Z0-9_]/', '_', $arg);
	}else{
		return null;
	}	
}

/** API Callback functions ***************************************************/

/**
 * Describes this API
 */
function describeAPI(){	
	echo'<h1>Timestreams API v. 2.0</h1>';
	echo '	<table border="1">
				<thead>
					<tr><th>Example URL</th><th>Description</th><th>Method</th><th>Parameters</th><th>Output</th><th>Status</th></tr>
				</thead>
				<tfoot>
					<tr><th>Example URL</th><th>Description</th><th>Method</th><th>Parameters</th><th>Output</th><th>Status</th></tr>
				</tfoot>
				<tbody>
				<tr>
					<td><a href="../api">./</a></td><td>Describes the Api</td><td>GET</td>
					<td>None</td><td>This table.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>
						<a href="../2.0/measurementContainerMetadata">./measurementContainerMetadata</a><br/>
						<a href="../2.0/measurementContainerMetadata?tsid=1">./measurementContainerMetadata?tsid=1</a>
					</td>
					<td>If no parameter is given then returns the metadata for all of the measurement container entries.
					<br/>When used with id parameter returns the metadata record id for the given timestream id. Replaces timestreams.ext_get_timestream_metadata. </td><td>GET</td>
					<td>tsid (optional): Id of a Timestream</td><td>Metadata list</td><td>Complete.</td>
				</tr>
				<tr>
					<td><a href="../2.0/measurementContainerMetadata/wp_1_ts_temperature_1">./measurementContainerMetadata/name</a></td>
					<td>Returns the metadata for the named measurement container. Replaces timestreams.select_metadata_by_name.</td><td>GET</td>
					<td><ul>
						<li>limit (optional): natural number for the maximum number of rows to return</li>	
						<li>offset (optional): natural number to begin returning rows from</li>
					</ul></td><td>Metadata list</td><td>Complete.</td>
				</tr>				
				<tr>
					<td><a href="../2.0/measurementContainer">./measurementContainer</a></td>
					<td>Returns the names of the measurement containers.</td><td>GET</td>
					<td>None</td><td>Measurement container name list</td><td>Complete.</td>
				</tr>
				<tr>
					<td>./measurementContainer
						curl --noproxy 192.168.56.101 -i -H "Accept: application/json" 
						-X POST -d 
						"measuretype=temperature&minimumvalue=0&maximumvalue=100&
						unit=text/x-data-C&unitsymbol=C&device=testDev&otherinfo=blah&
						datatype=DECIMAL(5,2)&siteId=1&blogid=1&userid=1"
						http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2.0/measurementContainer
					</td>
					<td>Creates a new measurement container owned by the given blog id. 
					Replaces timestreams.hn_ts_create_measurements and 
					timestreams.hn_ts_create_measurementsForBlog</td><td>POST</td>
					<td><ul>
						<li>String $measurementType
						</li><li>String (optional) $minimumvalue
						</li><li>String (optional) $maximumvalue
						</li><li>String MimeType $unit
						</li><li>String (optional) $unitSymbol
						</li><li>String $deviceDetails
						</li><li>String (optional) $otherInformation
						</li><li>String mySQL data type $dataType is the type of value to use. Any MySQL type (such as decimal(4,1) ) is a legal value.
						</li><li>String (optional) $missing_data_value is a value of type $dataType which represents rows in the timeseries with unknown values.
						</li><li>natural number (optional) $siteId site that owns the measurement container
						</li><li>natural number (optional) $blogId blog that owns the measurement container
						</li><li>natural number (optional) $userid user that owns the measurement container
						</ul>
					</td>
					<td>
						On success: {"measurementcontainer": "tablename"} <br/>
						Failure messages:<ul>
							<li>400 Bad Request - Missing required parameter</li>
							<li>400 Bad Request - Invalid parameter(s)</li>
						</ul>
					</td><td>Complete.</td>
				</tr>
				<tr>
					<td><ul>
						<li><a href="../2.0/measurementContainer/wp_1_ts_Pressure_25">./measurementContainer/wp_1_ts_Pressure_25</a></li>
						<li><a href="../2.0/measurementContainer/wp_1_ts_Pressure_25?action=first">./measurementContainer/wp_1_ts_Pressure_25?action=first</a></li>
						<li><a href="../2.0/measurementContainer/wp_1_ts_Pressure_25?action=latest">./measurementContainer/wp_1_ts_Pressure_25?action=latest</a></li>
						<li><a href="../2.0/measurementContainer/wp_1_ts_Pressure_25?action=count">./measurementContainer/wp_1_ts_Pressure_25?action=count</a></li>
					</ul></td>
					<td><ul>
						<li>(No parameter) Returns the data for the given measurement container. Replaces timestreams.select_measurements.</li>
						<li>(action=first) Returns the first measurement from a measurement container. Replaces timestreams.select_first_measurement.</li>
						<li>(action=latest) Returns the latest measurement from a measurement container. Replaces timestreams.select_latest_measurement.</li>
						<li>(action=count) Returns the number of meaurements in the measurement container. Replaces timestreams.count_measurements.</li></ul>
					</td><td>GET</td>
					<td>action (optional)[first|latest|count]</td>
					<td>
						Measurements list, first measurement, latest measurement or count of measurements.
					</td><td>Complete.</td>
				</tr>	
				<tr>
					<td>./measurement/add:id
						<form name="hn_ts_addMeaurement" action="../2.0/measurement/add:id" method="post">
							<input type="submit" value="Submit">
						</form>
					</td>
					<td>Adds a new measurement to the given measurement container.</td><td>POST</td>
					<td>blog id ... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./measurements/add:id
						<form name="hn_ts_addMeaurements" action="../2.0/measurements/add:id" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>Adds new measurements to the given measurement container.</td><td>POST</td>
					<td>measurement container id ... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./measurementfile/1
						<form name="hn_ts_addMeaurementFile" action="../2.0/measurementfile/1" method="post">
							<input type="submit" value="Submit">
						</form>
					</td>
					<td>...</td><td>POST</td>
					<td> ... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./measurementfiles/1
						<form name="hn_ts_addMeaurementFiles" action="../2.0/measurementfiles/1" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>...</td><td>POST</td>
					<td> ... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./context/add
						<form name="hn_ts_addContext" action="../2.0/context/add" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>Adds new measurements to the given measurement container.</td><td>POST</td>
					<td>... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>		
					
				<tr>
					<td><a href="../2.0/context">./context</a></td>
					<td>...</td><td>GET</td>
					<td>...</td><td>...</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td><a href="../2.0/context/1">./context/1</a></td>
					<td>...</td><td>GET</td>
					<td>...</td><td>...</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>
						curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X PUT -d<br/>
						"..."<br/>
						http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2.0/timestream/context/1
					</td>
					<td>....</td><td>PUT</td>
					<td>...</td>
				</tr>				
				<tr><td>*****************</td></tr>
				<tr>
					<td><a href="../2.0/timestream">./timestream</a></td>
					<td>Returns all of the Timestreams. Replaces timestreams.ext_get_timestreams.</td><td>GET</td>
					<td>None </td><td>The list of timestreams</td><td>Complete.</td>
				</tr>
				<tr>
					<td><a href="../2.0/time">./time</a></td>
					<td>Returns the current timestamp. Replaces timestreams.ext_get_time</td><td>GET</td>
					<td>None </td><td>The current timestamp</td><td>Complete.</td>
				</tr>
				<tr>
					<td><a href="../2.0/timestream/name/wp_1_ts_temperature_23">./timestream/name/wp_1_ts_temperature_23</a></td>
					<td>Returns timestream readings for a given measurement container name. Replaces timestreams.int_get_timestream_data.</td><td>GET</td>
					<td>limit<br/>offset<br/>lastts </td><td>The readings.</td><td>Complete.</td>
				</tr>
				<tr>
					<td>
						curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X PUT -d<br/>
						"curtime=1352315401&start=1352315401&end=1352315401&rate=2"<br/>
						http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2.0/timestream/head/1
					</td>
					<td>Updates a timestream head. Replaces timestreams.int_update_timestream_head.</td><td>PUT</td>
					<td>limit<br/>offset<br/>lastts </td><td>The readings.</td><td>Complete.</td>
				</tr>
				<tr>
					<td><a href="../2.0/timestream/head/1">./timestream/head/1</a></td>
					<td>Updates and outputs the head for the given timestream id.<br>Replaces timestreams.hn_ts_int_get_timestream_head.</td><td>GET</td>
					<td>None</td><td>The head data</td><td>Complete.</td>
				</tr>
				<tr>
					<td><a href="../2.0/timestream/id/1?limit=10&order=DESC">./2.0/timestream/id/1?limit=10&order=DESC</a></td>
					<td>Returns data corresponding to a given timestream since the last time 
					the function was called. Replaces timestreams.ext_get_timestream_data.</td><td>GET</td>
					<td><ul><li>last (optional): integer representing a php timestamp for last time a call was made</li><li>limit (optional): integer for the maximum number of rows to return</li><li>order (optional): string ["ASC"|"DESC"] sets the order of the returned results</li></ul></td><td>The timestream data</td><td>Complete.</td>
				</tr>
				<tr><td>*****************</td></tr>
				<tr>
					<td>
						curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X PUT -d<br/>
						"..."<br/>
						http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2.0/import
					</td>
					<td>....</td><td>PUT</td>
					<td>...</td>
				</tr>
				<tr>
					<td>./heartbeat
						<form name="hn_ts_heartbeat" action="../2.0/heartbeat" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>...</td><td>POST</td>
					<td>... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./replicate/
						<form name="hn_tsreplicate" action="../2.0/replicate" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>...</td><td>POST</td>
					<td>... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>	
				
			</table>';
}

/**
 * Returns wp_ts_metadata entries
 */
function measurementContainerMetadata() {
	$sql = "select * FROM wp_ts_metadata ORDER BY tablename";
	echoJsonQuery($sql, "measurementContainerMetadata");
}

/**
 * Returns the names of the measurement containers.
 */
function hn_ts_list_mc_names() {
	$sql = "select metadata_id AS id, tablename AS name FROM wp_ts_metadata ORDER BY id";
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
	$dataType,$missingDataValue, $siteId, $blogId, $userid){
	if(!$blogId || $blogId < 1){
		$blogId = 1;
	}
	
	//Ensure that there aren't empty or null values going into mandatory fields.
	if(		!hn_ts_issetRequiredParameter($measurementType, "measuretype") || 
			!hn_ts_issetRequiredParameter($unit, "unit") ||
			!hn_ts_issetRequiredParameter($deviceDetails, "device") ||
			!hn_ts_issetRequiredParameter($dataType, "datatype")
	){
		return;
	}
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
	
	$sql = "SHOW TABLE STATUS LIKE 'wp_ts_metadata';";
	$nextdevice=querySql($sql);
	$nextdevice=$nextdevice[0]->Auto_increment;
	
	$tablename = "wp_$blogId"."_ts_$measurementType"."_$nextdevice";
	
	$db = getConnection();
	$sql = "INSERT INTO wp_ts_metadata (tablename, measurement_type, min_value,
			max_value, unit, unit_symbol, device_details, other_info,
			data_type, missing_data_value, producer_site_id, producer_blog_id,
			producer_id) VALUES ('$tablename','$measurementType','$minimumvalue',
			'$maximumvalue','$unit','$unitSymbol','$deviceDetails',
			'$otherInformation','$dataType','$missingDataValue','$siteId',
			'$blogId','$userid')";	
	$count0 = $db->exec($sql);
	if($count0 == 1){
		$sql =
		'CREATE TABLE IF NOT EXISTS '.$tablename.' (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		value '.$dataType.' DEFAULT NULL,
		valid_time timestamp NULL,
		transaction_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;';		
		$count0 = $db->exec($sql);		
		$db = null;
		echo '{"measurementcontainer": ' . json_encode($tablename) .  '}';
	}else{		
		global $app;
		$app->response()->status(400);
		hn_ts_error_msg("Invalid parameter(s)");
	}	
}

/**
 * Checks username password then adds a measurement to a measurement container.
 * @param array $args should have 4 or 5 parameters:
 * $username, $password, measurement container name, measurement value, timestamp
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_measurement(){
	hn_ts_error_msg("hn_ts_add_measurement");	
}		

/**
 * Checks username password then adds measurements to a measurement container.
 * @param array $args should have at least 5 parameters:
 * $username, $password, measurement container name, array containing [measurement value, timestamp]
 * @return string XML-XPC response with either an error message as a param or the
 * number of insertions
 */
function hn_ts_add_measurements(){
	hn_ts_error_msg("hn_ts_add_measurements");	
}

/**
 * Retrieves records from a readings table of the form
 * wp_[blog-id]_ts_[measurement-type]_[device-id]
 * @param $args is an array in the expected format of:
 * [2]table name
 * [3]minimum timestamp
 * [4]maximum timestamp
 * [5]limit -- optional
 * [6]offset -- optional
 * [7]sort by column -- optional
 * [8]descending boolean -- optional
 * To do: Sanitise parameters
 * @return the result of the select
 */
function hn_ts_select_measurements($name, $minTs, $maxTs, $limit, $offset, $sort, $desc){	
	global $wpdb;
	if(count($args) < 3){
		return $this->missingcontainername;
	}
	$table=$args[2];
	$minimumTime=$args[3];
	$maximumTime=$args[4];
	$where="WHERE ";
	$limit=$this->hn_ts_getLimitStatement($args[5], $args[6]);
	$sortcolumn=(count($args) > 7 ? $args[7] : "");
	$descending=(count($args) > 8 ? $args[8] : "");
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
	}
	return $wpdb->get_results( 	$wpdb->prepare(
			"SELECT * FROM $table $where $sort $limit;" )	);
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
 * Checks username password then adds a context record to the context container.
 * @param array $args should have 6 parameters:
 * $username, $password, context_type, context_value, start time (optional), end time(optional)
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_context(){
	
	hn_ts_error_msg("hn_ts_add_context");
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
 * Checks username password then selects context records.
 * @param array $args should have 2 parameters:
 * $username, $password,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_contexts(){
	hn_ts_error_msg("hn_ts_select_contexts");
	
}

/**
 * Checks username password then selects context records matching the given type.
 * @param array $args should have 3-5 parameters:
 * $username, $password, context type,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_by_type(){
	hn_ts_error_msg("hn_ts_select_context_by_type");
	
}

/**
 * Checks username password then selects context records matching the given value.
 * @param array $args should have 3-5 parameters:
 * $username, $password, context value,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_by_value(){
	hn_ts_error_msg("hn_ts_select_context_by_value");
	
}

/**
 * Checks username password then selects context records matching the given value.
 * @param array $args should have 4-6 parameters:
 * $username, $password, context type, context value,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_by_type_and_value(){
	hn_ts_error_msg("hn_ts_select_context_by_type_and_value");
	
}

/**
 * Checks username password then selects context records matching the given time values.
 * @param array $args should have 4-6 parameters:
 * $username, $password, context type, start time (optional -- use NULL if not desired), End time (optional -- use NULL if not desired),
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_within_time_range($start,$end){
	hn_ts_error_msg("hn_ts_select_context_within_time_range");
	echo "start:$start<br>";
	echo "end:$end<br>";	
}

/**
 * Checks username password then updates the end time of the context records matching the given values.
 * @param array $args should have 6 parameters:
 * $username, $password, context type, context value, start time (optional -- use NULL if not desired), End time (this is the new end time)
 * @return string XML-XPC response with either an error message as a param or number of updated records
 */
function hn_ts_update_context(){
	hn_ts_error_msg("hn_ts_update_context");
	
}

/**
 * Checks username password then uploads and stores details about a file.
 * @param array $args should have 4 or 5 parameters:
 * $username, $password, measurement container name, struct with file details (name, type, bits), timestamp
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_measurement_file(){
	hn_ts_error_msg("hn_ts_add_measurement_file");
	
}

/**
 * Checks username password then uploads and stores details about files.
 * @param array $args should have at least 4 parameters:
 * $username, $password, measurement container name, struct with file details (name, type, bits), timestamp
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_measurement_files(){
	hn_ts_error_msg("hn_ts_add_measurement_files");
}

/**
 * Imports data from files sitting on the server.
 * @param array $args should have at least 2 parameters:
 * $username, $password
 * @return string XML-XPC response with either an error message or 1
 */
function hn_ts_import_data_from_files(){
	hn_ts_error_msg("hn_ts_import_data_from_files");
	
}

/**
 * Checks username password then updates data source hearbeat record
 * @param array $args should have 3 parameters:
 * $username, $password, tablename, ipaddress
 * @return string XML-XPC response with either an error message or 1
 */
function hn_ts_heartbeat(){
	hn_ts_error_msg("hn_ts_heartbeat");
	
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
	
	$sql = "SELECT id,value,valid_time AS timestamp,transaction_time 
			FROM (SELECT * FROM $tablename $where ORDER BY valid_time DESC $limitStmt)
			AS T1 ORDER BY timestamp ASC";
	
	echoJsonQuery($sql, "measurements");
	/*	
	$sql = "SELECT * FROM (SELECT * FROM $tablename $where ORDER BY valid_time DESC $limitStmt) 
			 AS T1 ORDER BY valid_time ASC";
		
	$readings = querySql($sql);
		
	for($i = 0; $i < count($readings); $i++)
	{
		$newts = strtotime($readings[$i]->valid_time);
		$readings[$i]->timestamp = $newts;
	}
		
	return $readings;*/
}

/**
 * Update a timestream head
 * @param $timestreamId is the id of the timestream to update
 * @param $newHead is the new head time
 * @param $newStart is the new start time
 * @param $newEnd is the new end time
 * @param $newRate is the new rate
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
	echoJsonQuery($sql, "timestamp");
}

/**
 * Returns all timestreams
 */
function hn_ts_ext_get_timestreams(){
	$sql = "SELECT * FROM wp_ts_timestreams";
	echoJsonQuery($sql, "timestreams");
}

/**
 * Returns the measurement table's metadata row id for the given timestream id
 * @param $timestreamId is the id of the timestream to return the metadata id for
 */
function hn_ts_ext_get_timestream_metadata($timestreamId){
	$sql = "SELECT metadata_id FROM wp_ts_timestreams WHERE timestream_id = $timestreamId";
	echoJsonQuery($sql, "metadata_id");	
}

/**
 * Updates the read head
 * @param $timestream is a query row array for a timestream
 * @uses triggered by viz getting data, update read head at given rate
 * @return NULL on failure or the updated head row data as array
 * @todo ratelimit?
 * @todo rate should be a float
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


?>