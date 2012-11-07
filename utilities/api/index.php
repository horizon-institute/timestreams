<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/', 'describeAPI');
$app->get('/measurementContainerMetadata', function() use ($app) {
	$paramValue = $app->request()->get('tsid');
	if(!$paramValue){
		measurementContainerMetadata();
	}else{
		hn_ts_ext_get_timestream_metadata($paramValue);
	}
});
$app->get('/measurementContainerMetadata/:name', 'hn_ts_select_metadata_by_name');
$app->get('/measurementContainer', 'hn_ts_list_mc_names');
$app->post('/measurementContainer', 'hn_ts_create_measurement_containerForBlog');
$app->get('/measurementContainer/:id', 'hn_ts_select_measurements');
$app->get('/measurement/:id', function($id) use ($app) {
	$paramValue = $app->request()->get('action');
	if(NULL == $paramValue){
		hn_ts_select_measurements($id);
	}else if(!strcasecmp($paramValue, "first")){
		hn_ts_select_first_measurement($id);
	}else if(!strcasecmp($paramValue, "latest")){
		hn_ts_select_latest_measurement($id);
	}else if(!strcasecmp($paramValue, "count")){
		hn_ts_count_measurements($id);
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
$app->post('/measurementFile/:id', 'hn_ts_add_measurement_file');
$app->post('/measurementFiles/:id', 'hn_ts_add_measurement_files');
$app->put('/import', 'hn_ts_import_data_from_files'); // not reall a put or a post -- do we need to define a new verb for activation?
$app->post('/heartbeat/:id', 'hn_ts_heartbeat');
$app->put('/replicate', 'hn_ts_replicate'); // not reall a put or a post -- do we need to define a new verb for activation?
$app->get('/timestream', 'hn_ts_ext_get_timestreams');
$app->get('/timestream/id/:id', 'hn_ts_ext_get_timestream_data');	// id is the timestream id
$app->get('/timestream/name/:name', 'hn_ts_int_get_timestream_data'); // name is the timestream table name
$app->get('/timestream/head/:id', 'hn_ts_int_get_timestream_head');
$app->put('/timestream/head/:id', 'hn_ts_int_update_timestream_head');
$app->get('/time', 'hn_ts_ext_get_time');

$app->run();

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

/**API FUNCTIONS********************************************************/
function describeAPI(){	
	echo'<h1>Timestreams API</h1>';
	echo '	<table border="1">
				<thead>
					<tr><th>URL</th><th>Description</th><th>Method</th><th>Parameters</th><th>Output</th><th>Status</th></tr>
				</thead>
				<tbody>
				<tr>
					<td><a href="../api">./</a></td><td>Describes the Api</td><td>GET</td>
					<td>None</td><td>This table.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>
						<a href="../api/measurementContainerMetadata">./measurementContainerMetadata</a><br/>
						<a href="../api/measurementContainerMetadata?tsid=1">./measurementContainerMetadata?tsid=1</a>
					</td>
					<td>Returns the metadata for all of the measurement container entries.</td><td>GET</td>
					<td>(Optional)tsid: Id of a Timestream</td><td>Metadata list</td><td>Complete.</td>
				</tr>
				<tr>
					<td><a href="../api/measurementContainerMetadata/wp_1_ts_temperature_1">./measurementContainerMetadata/name</a></td>
					<td>Returns the metadata for the named measurement container.</td><td>GET</td>
					<td>None</td><td>Metadata list</td><td>Incomplete.</td>
				</tr>
				
				<tr>
					<td><a href="../api/measurementContainer">./measurementContainer</a></td>
					<td>Returns the names of the measurement containers.</td><td>GET</td>
					<td>None</td><td>Measurement container name list</td><td>Complete.</td>
				</tr>
				<tr>
					<td><a href="../api/measurementContainer/1">./measurementContainer/id</a></td>
					<td>Returns the data for the given measurement container.</td><td>GET</td>
					<td>measurement container id</td><td>Measurements list</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./api/measurementContainer/id
						<form name="hn_ts_addMC" action="../api/measurementContainer/1" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>Creates a new measurement container owned by the given blog id.</td><td>POST</td>
					<td>blog id ... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td><a href="../api/measurement/:id:first">./measurement/:id:first</a></td>
					<td>Returns the first measurement from a measurement container.</td><td>GET</td>
					<td>blog id ... </td><td>First measurement.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td><a href="../api/measurement/latest:1">./measurement/latest:id</a></td>
					<td>Returns the latest measurement from a measurement container.</td><td>GET</td>
					<td>blog id ... </td><td>Latest measurement.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td><a href="../api/measurement/count:1">./measurement/count:id</a></td>
					<td>Returns the number of measurements in the given measurement container.</td><td>GET</td>
					<td>blog id ... </td><td>Count of measurements.</td><td>Incomplete.</td>
				</tr>				
				<tr>
					<td><a href="../api/measurement:1">./measurement:id</a></td>
					<td>Returns the data for the given measurement container.</td><td>GET</td>
					<td>measurement container id</td><td>Measurements list</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td><a href="../api/measurements:1">./measurements:id</a></td>
					<td>Returns the data for the given measurement container.</td><td>GET</td>
					<td>measurement container id</td><td>Measurements list</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./measurement/add:id
						<form name="hn_ts_addMeaurement" action="../api/measurement/add:id" method="post">
							<input type="submit" value="Submit">
						</form>
					</td>
					<td>Adds a new measurement to the given measurement container.</td><td>POST</td>
					<td>blog id ... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./measurements/add:id
						<form name="hn_ts_addMeaurements" action="../api/measurements/add:id" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>Adds new measurements to the given measurement container.</td><td>POST</td>
					<td>measurement container id ... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
				<tr>
					<td>./context/add
						<form name="hn_ts_addContext" action="../api/context/add" method="post">
							<input type="submit" value="Submit">
						</form></td>
					<td>Adds new measurements to the given measurement container.</td><td>POST</td>
					<td>... </td><td>Success or failure message.</td><td>Incomplete.</td>
				</tr>
			</table>';
	/*
	 * 
$app->post('/context/add', 'hn_ts_add_context');
$app->get('/context', 'hn_ts_select_contexts');
$app->get('/context/:id', 'hn_ts_select_context');
$app->get('/context/:type/', 'hn_ts_select_context_by_type');
$app->get('/context/:value/', 'hn_ts_select_context_by_value');
$app->get('/context/:type:value/', 'hn_ts_select_context_by_type_and_value');
$app->get('/context/:startTime:endTime/', 'hn_ts_select_context_within_time_range');
$app->put('/context/:id', 'hn_ts_update_context');
$app->post('/measurementFile', 'hn_ts_add_measurement_file');
$app->post('/measurementFiles', 'hn_ts_add_measurement_files');
$app->post('/import', 'hn_ts_import_data_from_files');
$app->post('/heartbeat', 'hn_ts_heartbeat');
$app->put('/replicate', 'hn_ts_replicate'); // not reall a put or a post -- do we need to define a new verb for activation?
$app->get('/timestream', 'hn_ts_ext_get_timestreams');
$app->get('/timestream/:id', 'hn_ts_ext_get_timestream_data');	// id is the timestream id
$app->get('/timestream/:name', 'hn_ts_int_get_timestream_data'); // name is the timestream table name
$app->get('/timestream/head/:id', 'hn_ts_int_get_timestream_head');
$app->put('/timestream/head/:id', 'hn_ts_int_update_timestream_head');
$app->get('/time', 'hn_ts_ext_get_time');
	 */
	
}
/**
 * Returns wp_ts_metadata entries
 */
function measurementContainerMetadata() {
	$sql = "select * FROM wp_ts_metadata ORDER BY tablename";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$measurementContainers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"measurementContainerMetadata": ' . json_encode($measurementContainers) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

/**
 * Returns the names of the measurement containers.
 */
function hn_ts_list_mc_names() {
	$sql = "select metadata_id AS id, tablename AS name FROM wp_ts_metadata ORDER BY id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$measurementContainers = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"measurementContainers": ' . json_encode($measurementContainers) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


/**
 * Checks username password then creates a new measurement container.
 * @param array $args should have 13 parameters:
 * $username, $password, $measurementType, $minimumvalue, $maximumvalue,
 *	$unit, $unitSymbol, $deviceDetails, $otherInformation, $dataType,
 *	$missing_data_value, $siteId, $blogId
 * @return string XML-XPC response with either an error message as a param or the
 * name of the measurement container
 */
function hn_ts_create_measurement_containerForBlog(){
	echo '{"error":{"text":"Function incomplete: hn_ts_create_measurement_containerForBlog"}}';
}

/**
 * Checks username password then adds a measurement to a measurement container.
 * @param array $args should have 4 or 5 parameters:
 * $username, $password, measurement container name, measurement value, timestamp
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_measurement(){
	echo '{"error":{"text":"Function incomplete: hn_ts_add_measurement"}}';
	
}		

/**
 * Checks username password then adds measurements to a measurement container.
 * @param array $args should have at least 5 parameters:
 * $username, $password, measurement container name, array containing [measurement value, timestamp]
 * @return string XML-XPC response with either an error message as a param or the
 * number of insertions
 */
function hn_ts_add_measurements(){
	echo '{"error":{"text":"Function incomplete: hn_ts_add_measurements"}}';
	
}

/**
 * Selects measurements for a given measurement container id. Request parameters can
 * cause it to return the count, first or latest value
 * @param measurement container id with optional query parameters: first, latest, count
 */
function hn_ts_select_measurements($id){	
	echo '{"error":{"text":"Function incomplete: hn_ts_select_measurements"}}<br/>';
}

/**
 * Checks username password then selects the first measurement from a measurement container.
 * @param array $args should have 3 parameters:
 * $username, $password, measurement container name
 * @return string XML-XPC response with either an error message as a param or measurement data
 */
function hn_ts_select_first_measurement($id){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_first_measurement"}}<br/>';
	echo $id;	
}

/**
* Checks username password then selects the latest measurement from a measurement container.
* @param array $args should have 3 parameters:
* $username, $password, measurement container name
* @return string XML-XPC response with either an error message as a param or measurement data
*/
function hn_ts_select_latest_measurement($id){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_latest_measurement"}}';
	
}

/**
 * Checks username password then selects the latest measurement from a measurement container.
 * @param array $args should have 3 parameters:
 * $username, $password, measurement container name
 * @return string XML-XPC response with either an error message as a param or count value
 */
function hn_ts_count_measurements($id){
	echo '{"error":{"text":"Function incomplete: hn_ts_count_measurements"}}';
	
}

/**
 * Checks username password then selects the metadata corresponding to the given measurement container.
 * @param array $args should have 3-5 parameters:
 * $username, $password, measurement container name,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or the
 * metadata
 */
function hn_ts_select_metadata_by_name(){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_metadata_by_name"}}';
	
}

/**
 * Checks username password then adds a context record to the context container.
 * @param array $args should have 6 parameters:
 * $username, $password, context_type, context_value, start time (optional), end time(optional)
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_context(){
	
	echo '{"error":{"text":"Function incomplete: hn_ts_add_context"}}';
}

/**
 * Checks username password then selects context records.
 * @param array $args should have 2 parameters:
 * $username, $password,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context($id){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_context"}}<br/>';
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
	echo '{"error":{"text":"Function incomplete: hn_ts_select_contexts"}}';
	
}

/**
 * Checks username password then selects context records matching the given type.
 * @param array $args should have 3-5 parameters:
 * $username, $password, context type,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_by_type(){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_context_by_type"}}';
	
}

/**
 * Checks username password then selects context records matching the given value.
 * @param array $args should have 3-5 parameters:
 * $username, $password, context value,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_by_value(){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_context_by_value"}}';
	
}

/**
 * Checks username password then selects context records matching the given value.
 * @param array $args should have 4-6 parameters:
 * $username, $password, context type, context value,
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_by_type_and_value(){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_context_by_type_and_value"}}';
	
}

/**
 * Checks username password then selects context records matching the given time values.
 * @param array $args should have 4-6 parameters:
 * $username, $password, context type, start time (optional -- use NULL if not desired), End time (optional -- use NULL if not desired),
 * $limit (optional), $offset (optional)
 * @return string XML-XPC response with either an error message as a param or context records
 */
function hn_ts_select_context_within_time_range($start,$end){
	echo '{"error":{"text":"Function incomplete: hn_ts_select_context_within_time_range"}}<br/>';
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
	echo '{"error":{"text":"Function incomplete: hn_ts_update_context"}}';
	
}

/**
 * Checks username password then uploads and stores details about a file.
 * @param array $args should have 4 or 5 parameters:
 * $username, $password, measurement container name, struct with file details (name, type, bits), timestamp
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_measurement_file(){
	echo '{"error":{"text":"Function incomplete: hn_ts_add_measurement_file"}}';
	
}

/**
 * Checks username password then uploads and stores details about files.
 * @param array $args should have at least 4 parameters:
 * $username, $password, measurement container name, struct with file details (name, type, bits), timestamp
 * @return string XML-XPC response with either an error message as a param or 1 (the number of insertions)
 */
function hn_ts_add_measurement_files(){
	echo '{"error":{"text":"Function incomplete: hn_ts_add_measurement_files"}}';
}

/**
 * Imports data from files sitting on the server.
 * @param array $args should have at least 2 parameters:
 * $username, $password
 * @return string XML-XPC response with either an error message or 1
 */
function hn_ts_import_data_from_files(){
	echo '{"error":{"text":"Function incomplete: hn_ts_import_data_from_files"}}';
	
}

/**
 * Checks username password then updates data source hearbeat record
 * @param array $args should have 3 parameters:
 * $username, $password, tablename, ipaddress
 * @return string XML-XPC response with either an error message or 1
 */
function hn_ts_heartbeat(){
	echo '{"error":{"text":"Function incomplete: hn_ts_heartbeat"}}';
	
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
	echo '{"error":{"text":"Function incomplete: hn_ts_replication"}}';
	
}

//function hn_ts_siteinfo'] =  array(&$this, 'hn_ts_siteinfo');

// internal interface
function hn_ts_int_get_timestream_head(){
	echo '{"error":{"text":"Function incomplete: hn_ts_int_get_timestream_head"}}';
	
}

function hn_ts_int_get_timestream_data(){
	echo '{"error":{"text":"Function incomplete: hn_ts_int_get_timestream_data"}}';
	
}

function hn_ts_int_update_timestream_head(){
	echo '{"error":{"text":"Function incomplete: hn_ts_int_update_timestream_head"}}';
	
}

	
// external api
function hn_ts_ext_get_time(){
	echo '{"error":{"text":"Function incomplete: hn_ts_ext_get_time"}}';
	
}

function hn_ts_ext_get_timestreams(){
	echo '{"error":{"text":"Function incomplete: hn_ts_ext_get_timestreams"}}';
	
}

function hn_ts_ext_get_timestream_metadata($timestreamId){
	echo '{"error":{"text":"Function incomplete: hn_ts_ext_get_timestream_metadata"}}';
	
}

function hn_ts_ext_get_timestream_data(){
	echo '{"error":{"text":"Function incomplete: hn_ts_ext_get_timestream_data"}}';
	
}	


?>