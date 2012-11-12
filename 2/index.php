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

define(HN_TS_DEBUG, true);
define (HN_TS_VERSION, "v. 2.0.0-Alpha-0.1");
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
$app->put('/replicate', 'hn_ts_replicate'); // not really a put or a post -- do we need to define a new verb for activation?
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
	$dbhost="127.0.0.2";
	$dbuser="root";
	$dbpass="tow4mfN";
	$dbname="wordpress";
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
		return preg_replace('/[^-a-zA-Z0-9_\s://]/', '_', $arg);
	}else{
		return null;
	}
}

/** API Callback functions ***************************************************/

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
	echo'<h1>Timestreams API '. HN_TS_VERSION . '</h1>';
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
	<td>None</td><td>This table.</td><td>Complete.</td>
	</tr>
	<tr>
	<td>
	<a href="../2/measurementContainerMetadata">./measurementContainerMetadata</a><br/>
	<a href="../2/measurementContainerMetadata?tsid=1">./measurementContainerMetadata?tsid=1</a>
	</td>
	<td>If no parameter is given then returns the metadata for all of the measurement container entries.
	<br/>When used with id parameter returns the metadata record id for the given timestream id. Replaces timestreams.ext_get_timestream_metadata. </td><td>GET</td>
	<td>tsid (optional): Id of a Timestream</td><td>Metadata list</td><td>Complete.</td>
	</tr>
	<tr>
	<td><a href="../2/measurementContainerMetadata/wp_1_ts_temperature_1">./measurementContainerMetadata/wp_1_ts_temperature_1</a></td>
	<td>Returns the metadata for the named measurement container. Replaces timestreams.select_metadata_by_name.</td><td>GET</td>
	<td><ul>
	<li>limit (optional): natural number for the maximum number of rows to return</li>
	<li>offset (optional): natural number to begin returning rows from</li>
	</ul></td><td>Metadata list</td><td>Complete.</td>
	</tr>
	<tr>
	<td><a href="../2/measurementContainer">./measurementContainer</a></td>
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
	http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/measurementContainer
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
	<li><a href="../2/measurementContainer/wp_1_ts_Pressure_25">./measurementContainer/wp_1_ts_Pressure_25</a></li>
	<li><a href="../2/measurementContainer/wp_1_ts_Pressure_25?action=first">./measurementContainer/wp_1_ts_Pressure_25?action=first</a></li>
	<li><a href="../2/measurementContainer/wp_1_ts_Pressure_25?action=latest">./measurementContainer/wp_1_ts_Pressure_25?action=latest</a></li>
	<li><a href="../2/measurementContainer/wp_1_ts_Pressure_25?action=count">./measurementContainer/wp_1_ts_Pressure_25?action=count</a></li>
	</ul></td>
	<td><ul>
	<li>(No parameter) Returns the data for the given measurement container. Replaces timestreams.select_measurements.</li>
	<li>(action=first) Returns the first measurement from a measurement container. Replaces timestreams.select_first_measurement.</li>
	<li>(action=latest) Returns the latest measurement from a measurement container. Replaces timestreams.select_latest_measurement.</li>
	<li>(action=count) Returns the number of meaurements in the measurement container. Replaces timestreams.count_measurements.</li></ul>
	</td><td>GET</td>
	<td><ul>
	<li>action (optional)[first|latest|count]: If this parameter is set to one of these values then the other parameters are ignored.</li>
	<li>min (optional) <timestamp> Sets the minimum time to return measurements for.</li>
	<li>max (optional) <timestamp> Sets the maximum time to return measurements for.</li>
	<li>limit (optional) <natural number> Maximum number of measurements to return.</li>
	<li>offset (optional) <natural number> Starting record.</li>
	<li>sort (optional) <column name> Column that the records should be sorted by.</li>
	<li>desc (optional) [true] If set then the sort order will be descending </li>
	</ul></td>
	<td>
	Measurements list, first measurement, latest measurement or count of measurements.
	</td><td>Complete.</td>
	</tr>
	<tr>
	<td>./measurement/wp_1_ts_Pressure_25
	curl --noproxy 192.168.56.101 -i -H "Accept: application/json"
	-X POST -d
	"value=1"
	http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/measurement/wp_1_ts_Pressure_25
	</td>
	<td>Adds a new measurement to the given measurement container. Replaces timestreams.hn_ts_add_measurement. 
	Note that this can be used as a replacement for timestreams.add_measurement_file(s) / timestreams.import_data_from_files. 
	To do so first upload your file (which can be accomplished on this server using XML-RPC: wp.uploadFile -- https://codex.wordpress.org/XML-RPC_wp#wp.uploadFile)
	</td><td>POST</td>
	<td><ul>
	<li>value</li>
	<li>ts (optional) the timestamp that the measurement was taken. If excluded then uses the server\'s current time.</li>
	</ul></td><td><ul><li>On success: {"insertresult": "1 rows inserted"}</li></ul></td><td>Complete.</td>
	</tr>
	<tr>
	<td>./measurements/wp_1_ts_Pressure_25
	curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X
	POST -d "measurements={\"measurements\":[{\"v\":1,\"t\":\"2012-11-09 12:10:23\"},
	{\"v\":2,\"t\":\"2012-07-21 17:10:23\"}]}"
	http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/measurements/wp_1_ts_Pressure_25
	</td>
	<td>Adds new measurements to the given measurement container. Replaces timestreams.hn_ts_add_measurements</td><td>POST</td>
	<td>measurements in the format: {"measurements":[{"v":1,"t":"2012-11-09 12:10:23"},{"r":2,"t":"2012-07-21 17:10:23"} </td>
	<td><ul><li>On success: {"insertresult": "1 rows inserted"}</li></ul></td><td>Complete.</td>
	</tr>
	<tr>
	<td><a href="../2/context">./context</a></td>
	<td>Replaces timestreams.select_context_by_type,
	timestreams.select_context_by_value,
	timestreams.select_context_by_type_and_value and
	timestreams.select_context_within_time_range
	</td><td>GET</td>
	<td><ul>
	<li>type</li>
	<li>value</li>
	<li>start</li>
	<li>end</li>
	<li>limit</li>
	<li>offset</li>
	</ul></td><td>List of contexts</td><td>Complete.</td>
	</tr>
	<tr>
	<td>curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X POST -d
	"type=place&value=Nottingam&start=2012-11-12 10:10:23&end=2012-11-12 10:20:23&user=1"
	http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/context
	<td>Adds new contexts. Replaces timestreams.add_context.</td>
	<td>POST</td>
	<td><ul>
	<li>type</li>
	<li>value</li>
	<li>start</li>
	<li>end</li>
	<li>user</li>
	</ul></td>
	<td>
	<ul><li>On success: {"insertresult": "1 rows inserted"}</li></ul>
	</td><td>Complete.</td>
	</tr>
	<tr>
	<td>
	curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X
	PUT -d "type=a&start=2012-05-22 13:36:11&end=2012-05-22 13:36:11"
	http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/context
	</td>
	<td>Updates the end time of the context records matching the given values. Replaces timestreams.update_context.</td>
	</td><td>PUT</td>
	<td><ul>
	<li>id</li>
	<li>type</li>
	<li>value</li>
	<li>start</li>
	<li>end</li>
	</ul>
	<td><ul><li>On success: {"updateresult": "Updated 1 row(s)."}</li></ul></td><td>Complete</td>
	</tr>
	<tr>
	<td><a href="../2/timestream">./timestream</a></td>
	<td>Returns all of the Timestreams. Replaces timestreams.ext_get_timestreams.</td><td>GET</td>
	<td>None </td><td>The list of timestreams</td><td>Complete.</td>
	</tr>
	<tr>
	<td><a href="../2/time">./time</a></td>
	<td>Returns the current timestamp. Replaces timestreams.ext_get_time</td><td>GET</td>
	<td>None </td><td>The current timestamp</td><td>Complete.</td>
	</tr>
	<tr>
	<td><a href="../2/timestream/name/wp_1_ts_temperature_23">./timestream/name/wp_1_ts_temperature_23</a></td>
	<td>Returns timestream readings for a given measurement container name. Replaces timestreams.int_get_timestream_data.</td><td>GET</td>
	<td>limit<br/>offset<br/>lastts </td><td>The readings.</td><td>Complete.</td>
	</tr>
	<tr>
	<td>
	curl --noproxy 192.168.56.101 -i -H "Accept: application/json" -X PUT -d<br/>
	"curtime=1352315401&start=1352315401&end=1352315401&rate=2"<br/>
	http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/timestream/head/1
	</td>
	<td>Updates a timestream head. Replaces timestreams.int_update_timestream_head.</td><td>PUT</td>
	<td>limit<br/>offset<br/>lastts </td><td>The readings.</td><td>Complete.</td>
	</tr>
	<tr>
	<td><a href="../2/timestream/head/1">./timestream/head/1</a></td>
	<td>Updates and outputs the head for the given timestream id.<br>Replaces timestreams.hn_ts_int_get_timestream_head.</td><td>GET</td>
	<td>None</td><td>The head data</td><td>Complete.</td>
	</tr>
	<tr>
	<td><a href="../2/timestream/id/1?limit=10&order=DESC">./2/timestream/id/1?limit=10&order=DESC</a></td>
	<td>Returns data corresponding to a given timestream since the last time
	the function was called. Replaces timestreams.ext_get_timestream_data.</td><td>GET</td>
	<td><ul><li>last (optional): integer representing a php timestamp for last time a call was made</li><li>limit (optional): integer for the maximum number of rows to return</li><li>order (optional): string ["ASC"|"DESC"] sets the order of the returned results</li></ul></td><td>The timestream data</td><td>Complete.</td>
	</tr>
	<tr>
	<td>curl --noproxy 192.168.56.101 -i -H "Accept: application/json"
	-X PUT -d
	"ipaddress=192.168.56.101" http://192.168.56.101/wordpress/wp-content/plugins/timestreams/2/heartbeat/wp_1_ts_temperature_1
	<td>Updates the measurement container with the source
	device\'s current ip address. This is useful for reporting that the device
	is still alive when no new data is being captured for a long period of time.
	Replaces timestreams.heartbeat.
	</td><td>PUT</td>
	<td>ipaddress</td>
	<td><ul>
		<li>success: {"updateresult": "Updated 1 row(s)."}</li>
		<li>failure: 400 Bad Request {"error":{"message":"Missing name or ip address."}}</li>
	</td><td>Complete.</td>
	</tr>
	</table>';
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
 * Returns wp_ts_metadata entries
 */
function measurementContainerMetadata() {
	$sql = "SELECT * FROM wp_ts_metadata ORDER BY tablename";
	echoJsonQuery($sql, "measurementContainerMetadata");
}

/**
 * Returns the names of the measurement containers.
 */
function hn_ts_list_mc_names() {
	$sql = "SELECT metadata_id AS id, tablename AS name FROM wp_ts_metadata ORDER BY id";
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
	}

	$sql = "SELECT * FROM $table $where $sort $limit;";
	echoJsonQuery($sql, "measurementContainerMetadata");
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


?>