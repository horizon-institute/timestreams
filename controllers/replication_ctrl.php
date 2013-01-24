<?php
/**
 * Functions to Add/delete replication entries into replication table
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add edit/delete functionality
 */

/**
 * Extends IXR_Client with a version of query() suitable for use behind proxy servers
 * (without authentication)
 * @author pszjmb
 *
 */
class Proxied_IXR_Client extends IXR_Client{
	var $proxyAddr;
	var $proxyPort;

	function __construct($proxyAddr, $proxyPort, $server, $path = false, $port = 80, $timeout = 15) {
		parent::__construct($server, $path, $port, $timeout);
		$this->proxyAddr = $proxyAddr;
		$this->proxyPort = $proxyPort;
	}

	/**
	 * Same as parent's query() except for minor changes as per
	 * http://thedeadone.net/how-to/livejournal-and-wordpress/
	 * to support proxied requests.
	 * @return boolean
	 */
	function query()
	{
		$args = func_get_args();
		//var_dump($args);
		$method = array_shift($args);
		$request = new IXR_Request($method, $args);
		$length = $request->getLength();
		$xml = $request->getXml();
		$r = "\r\n";
		$request  = "POST {$this->path} HTTP/1.0$r";

		// Merged from WP #8145 - allow custom headers
		$this->headers['Host']          = 'http://'.$this->server;
		$this->headers['Content-Type']  = 'text/xml';
		$this->headers['User-Agent']    = $this->useragent;
		$this->headers['Content-Length']= $length;

		foreach( $this->headers as $header => $value ) {
			$request = "POST http://{$this->server}{$this->path} HTTP/1.0$r";
			$request .= "{$header}: {$value}{$r}";
		}
		$request .= $r;

		$request .= $xml;
		//echo $request;

		// Now send the request
		if ($this->debug) {
			echo '<pre class="ixr_request">'.htmlspecialchars($request)."\n</pre>\n\n";
		}

		if ($this->timeout) {
			//$fp = @fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
			$fp = fsockopen($this->proxyAddr, $this->proxyPort, $errno, $errstr, $this->timeout);
		} else {
			//$fp = @fsockopen($this->server, $this->port, $errno, $errstr);
			$fp = fsockopen($this->proxyAddr, $this->proxyPort, $errno, $errstr);
		}
		if (!$fp) {
			$this->error = new IXR_Error(-32300, 'transport error - could not open socket');
			return false;
		}
		fputs($fp, $request);
		$contents = '';
		$debugContents = '';
		$gotFirstLine = false;
		$gettingHeaders = true;
		while (!feof($fp)) {
			$line = fgets($fp, 4096);
			if (!$gotFirstLine) {
				// Check line for '200'
				if (strstr($line, '200') === false) {
					$this->error = new IXR_Error(-32300, 'transport error - HTTP status code was not 200');
					return false;
				}
				$gotFirstLine = true;
			}
			if (trim($line) == '') {
				$gettingHeaders = false;
			}
			if (!$gettingHeaders) {
				// merged from WP #12559 - remove trim
				$contents .= $line;
			}
			if ($this->debug) {
				$debugContents .= $line;
			}
		}
		if ($this->debug) {
			echo '<pre class="ixr_response">'.htmlspecialchars($debugContents)."\n</pre>\n\n";
		}

		// Now parse what we've got back
		$this->message = new IXR_Message($contents);
		if (!$this->message->parse()) {
			// XML error
			$this->error = new IXR_Error(-32700, 'parse error. not well formed');
			return false;
		}

		// Is the message a fault?
		if ($this->message->messageType == 'fault') {
			$this->error = new IXR_Error($this->message->faultCode, 
					$this->message->faultString);
			return false;
		}

		// Message must be OK
		return true;
	}
}
/**
 * Sets up a complete copy of a local table to a remote table.
 * @param int $replRecordID is a row id from the Replication Table
 * @return mixed|string, on success the replication response and time or Replication failure message.
 */
function hn_ts_replicate_full($replRecordID){
	$db = new Hn_TS_Database();
	$replRow = $db->hn_ts_getReplRow($replRecordID);
	if ($replRow != null) {
		$date = new DateTime();
		$date = str_replace("T"," ",
				substr_replace(gmdate("Y-m-d\TH:i:s\Z", $date->getTimestamp() ) ,"",-1));
		//$resp = replicateXmlRpc($replRow);
		$readings = $db->hn_ts_get_readings_from_name(
				array("","",$replRow->local_table,0,0,0,0,0,0));
		if(count($readings) <= 0){
			return "Local measurement container is empty.";
		}
		if(0==$replRow->copy_files){
			return hn_ts_doDataReplication($replRow, $readings, $db);
		}else{
			return hn_ts_doFileReplication($replRow, $readings, $db);
		}
	}else{
		return  __("Replication failed. Couldn\'t find replication id $replRecordID.",HN_TS_NAME);
	}
}

/**
 * Copies data from a local table to a remote table.
 * @param $replRow is the replication table row
 * @param $readings are the readings from a measurement container
 * @param $db is a Hn_TS_Database
 * @return the replicateRest response
 */
function hn_ts_doDataReplication($replRow, $readings, $db){
	$measurements = "{\"measurements\":[";
	foreach($readings as $reading){
		$measurements = $measurements.
		"{\"v\":\"$reading->value\",\"t\":\"$reading->valid_time\"},";
	}
	$measurements=rtrim($measurements, ",");
	$measurements = $measurements."]}";

	$resp = replicateRest($replRow, $measurements);
	$date = date( "Y-m-d H:i:s");
	$db->hn_ts_updateReplRow($replRow->replication_id, $resp."<br />".$date);
	return $resp."<br />".$date;
}

/**
 * Copies files from a local table to a remote table.
 * @param $replRow is the replication table row
 * @param $readings are the readings from a measurement container presumed to be in the form:
 * http://.../filename.suffix and reachable
 * @param $db is a Hn_TS_Database
 * @return the replicateRest response
 */
function hn_ts_doFileReplication($replRow, $readings, $db){
	$counter = 0;
	foreach($readings as $reading){
		$data = file_get_contents($reading->value);
		$name = basename($reading->value);
		$ts = $reading->valid_time;
		$response = sendFile($name, $data,$replRow,$ts);
		echo $name.': '.$response['response']['code'].' '.
		$response['response']['message']. ' '.$response['body'];
		$counter += 1;
	}
	$db->hn_ts_updateReplRow($replRow->replication_id, date( "Y-m-d H:i:s").' '.$counter." file(s) replicated.<br />");
}

/**
 * Transfers a file using API
 * $data is file data to send
 */
function sendFile($name, $data,$replRow, $timestamp){
	$now=time();
	$args = array(
		'data' => base64_encode($data),
		'filename' => $name,
		'ts' => $timestamp,
		'pubkey' => $replRow->remote_user_login,
		'now' => $now
	);
	$hmac = getHmac($args,$replRow->remote_user_pass);
	$args['hmac']= $hmac;
	$body=array('body'=>$args);
	return wp_remote_post( 
		$replRow->remote_url.'/measurementfile/'.$replRow->remote_table, $body 
	);
}

/**
 * Returns an Hmac for the given parameters
 */
function getHmac($args, $prikey){
	sort($args,SORT_STRING);
	$toHash="";
	foreach($args as $arg){
		$toHash .= $arg . '&';
	}
	return hash_hmac ( 'sha256' , $toHash , $prikey);
}

/**
 * Returns the latest reading from an external table or null
 * @param $replRow is a row from the local replication table
 */
function hn_ts_getExternalLatestReading($replRow){
	if( !class_exists( 'WP_Http' ) )
		include_once( ABSPATH . WPINC. '/class-http.php' );

	handleProxy();
	$request = new WP_Http;

	$pubkey=$replRow->remote_user_login;
	$now=time();
	$prikey=$replRow->remote_user_pass;
	$name=$replRow->remote_table;

	//$tohash="$measurements&$name&$now&$pubkey";
	$body = array(
			'pubkey' => $pubkey,
			'now' => $now,
			'action' => 'latest',
	);
	sort($body,SORT_STRING);
	$tohash="";
	foreach ( $body as $param){
		$tohash = $tohash.$param;
	}
	//echo "httptest hmac: $tohash";
	$hmac = hash_hmac ( 'sha256' , $tohash , $prikey );
	$url = $replRow->remote_url."/measurement_container/$name?pubkey=$pubkey&now=$now&hmac=$hmac&action=latest";
	//print_r($url);
	$result = $request->request($url);
	$latestTime = null;
	if ( is_wp_error($result) ){
		echo $result->get_error_message();
	}else{
		if(intval($result['response']['code'])/100 != 2){
			die('An error occurred - '.
					$result['response']['code']. ":". $result['response']['message']);
		}else{
			$toParse = $result['body'];
			//var_dump(json_decode($toParse));
			$results = json_decode($toParse,true);
			if(array_key_exists($name,$results) && count($results[$name]) > 0
					&& array_key_exists('valid_time',$results[$name][0])){
				return $results[$name][0]['valid_time'];
			}
		}
	}
	return null;
}

/**
 * Calls partial replication for all replication db entries that are marked as continuous
 */
function hn_ts_continuousReplication(){
	global $wpdb;
	$repls = $wpdb->get_results( 	$wpdb->prepare(
			"SELECT replication_id FROM wp_ts_replication
			WHERE continuous=1;" )	);
	foreach ( $repls as $repl )
	{
		call_user_func('hn_ts_replicate_partial',$repl->replication_id);
	}
}

/**
 * Sets up a partial copy of a local table to a remote table.
 * @param $replRecord is a row id from the Replication Table
 * @return mixed|string, on success the replication response and time started or 'Replication failed.'
 */
function hn_ts_replicate_partial($replRowId){
	$db = new Hn_TS_Database();
	$replRow = $db->hn_ts_getReplRow($replRowId);

	if ($replRow != null) {
		// Acquire replication lock
		$lockres = $db->hn_ts_replLock($replRowId);
		if($lockres < 1){
			return  __("Acquiring lock $replRow->replication_id...",HN_TS_NAME);
		}

		// get most recent record from external table (which could be empty)
		$response = hn_ts_getExternalLatestReading($replRow);
		if(null == $response){
			$res = hn_ts_replicate_full($replRow->replication_id);
			$db->hn_ts_replUnlock($replRowId);
			return $res;
		}else{				
			$date = new DateTime();
			$mindate = date( "Y-m-d H:i:s", strtotime( $response )+1 );
			$date = str_replace("T"," ",
					substr_replace(gmdate("Y-m-d\TH:i:s\Z", $date->getTimestamp() ) ,"",-1));
			$readings = $db->hn_ts_get_readings_from_name(
					array("","",$replRow->local_table,$mindate,0,0,0,0,0));
			if(count($readings) >= 1){
				if(0==$replRow->copy_files){
					$res = hn_ts_doDataReplication($replRow, $readings, $db);
				}else{
					$res = hn_ts_doFileReplication($replRow, $readings, $db);
				}
			}else{
				$res = __("No insertions to make.<br />",HN_TS_NAME).$date;
			}
			$db->hn_ts_replUnlock($replRowId);
			return $res;
		}
	}else{
		return  __("Replication failed. Couldn\'t find replication id $replRow->replication_id.",HN_TS_NAME);
	}
}

/**
 * Replicates a databse table using XML-RPC
 * @param  $replRow is an array containing a Replication Table row
 */
function replicateXmlRpc($replRow){
	$options = get_option('hn_ts');

	if(count($options['proxyAddr']) > 0){
		$client = new Proxied_IXR_Client($options['proxyAddr'], $options['proxyPort'], $replRow->remote_url);
	}else{
		$client = new IXR_Client($replRow->remote_url);
	}
	$db = new Hn_TS_Database();
	$readings = $db->hn_ts_get_readings_from_name(
			array("","",$replRow->local_table,0,0,0,0,0,0));
	$readingsArgs = array();
	foreach($readings as $reading){
		array_push($readingsArgs, $reading->value, $reading->valid_time);
	}
	$queryArgs = array_merge(array('timestreams.add_measurements',$replRow->remote_user_login,
			$replRow->remote_user_pass,$replRow->remote_table),$readingsArgs);

	if (!call_user_func_array(array($client,'query'), $queryArgs)){
		die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
	}
	return $response = $client->getResponse();
}

function handleProxy(){
	$options = get_option('hn_ts');

	if(array_key_exists('proxyAddr',$options) &&array_key_exists('proxyPort',$options)){
		if ( !defined( 'WP_PROXY_HOST' ) ){
			define('WP_PROXY_HOST', $options['proxyAddr']);
		}
		if ( !defined( 'WP_PROXY_PORT' ) ){
			define('WP_PROXY_PORT', $options['proxyPort']);
		}
	}
}

/**
 * Replicates a databse table using REST API
 * @param  $replRow is an array containing a Replication Table row
 * @return HTTP response
 */
function replicateRest($replRow, $measurements){
	//echo "measurements=$measurements";
	$pubkey=$replRow->remote_user_login;
	$now=time();
	$prikey=$replRow->remote_user_pass;
	$body = array(
			'measurements' => $measurements,
			'pubkey' => $pubkey,
			'now' => $now,
	);
	sort($body,SORT_STRING);
	$tohash="";
	foreach ( $body as $param){
		$tohash = $tohash.$param."&";
	}
	$hmac = hash_hmac ( 'sha256' , $tohash , $prikey );

	if( !class_exists( 'WP_Http' ) )
		include_once( ABSPATH . WPINC. '/class-http.php' );

	handleProxy();
	//$request = new WP_HTTP;

	$headers = array(
			'Content-Type' => 'application/x-www-form-urlencoded',
			'Content-Length' => strlen($hmac)
	);
	echo $replRow->remote_url."/measurements/$replRow->remote_table";
	$result = wp_remote_post( $replRow->remote_url."/measurements/$replRow->remote_table",
			array( 'method' => 'POST', 'body' => array(
			'measurements' => $measurements,
			'pubkey' => $pubkey,
			'now' => $now,
			'hmac' => $hmac	), 'headers' => $headers) );
	if ( is_wp_error($result) ){
		return $result->get_error_message();
	}else{
		return $result['response']['code']. " ". $result['response']['message']."<br/>".$result["body"];
	}
}

/**
 * A form to add replication records
 * Todo: add validation & make messages stand out more
 * Pass in username and password to hn_ts_insert_replication
 */
function hn_ts_addReplicationRecord(){
	?>
<h3>Add Replication Record</h3>
<form id="replicationform" method="post" action="">
	<table class="form-table">
		<tr valign="top">
			<th scope="row"><?php _e('Local Measurement Container Table',HN_TS_NAME); ?>*</th>
			<td><input type="text" name="local_table" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Remote-User Public Key',HN_TS_NAME); ?> *</th>
			<td><input type="text" name="remote_user_login" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Remote-User Private Key',HN_TS_NAME); ?> *</th>
			<td><input type="password" name="pwrd" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Remote Url',HN_TS_NAME); ?> *</th>
			<td><input type="text" name="remote_url" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Remote Measurement Container Table',HN_TS_NAME); ?> *</th>
			<td><input type="text" name="remote_table" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Continuous',HN_TS_NAME); ?></th>
			<td><input type="checkbox" name="continuous" value="Yes"
				class="hn_ts_cont_chk" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Copy Files',HN_TS_NAME); ?></th>
			<td><input type="checkbox" name="hn_ts_copy" value="Yes"
				class="hn_ts_copy_chk" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><?php _e('Remote Blog Id',HN_TS_NAME); ?> *</th>
			<td><input type="text" name="hn_ts_remote_blog_id" />
			</td>
		</tr>

	</table>

	<p class="submit">
		<input type="submit" name='submit' class="button-primary"
			value="<?php _e('Add Replication Record',HN_TS_NAME) ?>" />
	</p>

</form>
<hr />
<?php
$cont = 0;
$hn_ts_copy = 0;
$hn_ts_remote_blog_id = 0;
if(isset($_POST['continuous']) && $_POST['continuous'] == 'Yes'){
	$cont = 1;
}
if(isset($_POST['hn_ts_copy']) && $_POST['hn_ts_copy'] == 'Yes'){
	$hn_ts_copy = 1;
}
if(isset($_POST['hn_ts_remote_blog_id'])){
	$hn_ts_remote_blog_id = $_POST['hn_ts_remote_blog_id'];
}else{
	$hn_ts_remote_blog_id = 1;
}
$replication = null;
if(isset($_POST['local_table']) &&
		isset($_POST['remote_user_login']) &&
		isset($_POST['pwrd']) &&
		isset($_POST['remote_url']) &&
		isset($_POST['remote_table']) ) {
	$db = new Hn_TS_Database();
	$replication = $db->hn_ts_insert_replication(array("","",
			$_POST['local_table'], $_POST['remote_user_login'],
			$_POST['pwrd'], $_POST['remote_url'], $_POST['remote_table'],
			$cont,"",$hn_ts_copy,$hn_ts_remote_blog_id)
	);
	if($replication){
		_e('Record added.',HN_TS_NAME);
	}
}
//var_dump($replication);
}