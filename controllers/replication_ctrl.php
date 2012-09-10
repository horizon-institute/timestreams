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
			$this->error = new IXR_Error($this->message->faultCode, $this->message->faultString);
			return false;
		}
	
		// Message must be OK
		return true;
	}
}
/**
 * Performs a complete copy of a local table to a remote table.
 * @param int $replRecordID is a row id from the Replication Table
 * @return mixed|string, on success the replication response and time or 'Replication failed.'
 */
function hn_ts_replicate_full($replRecordID){
	$db = new Hn_TS_Database();
	$replRow = $db->hn_ts_getReplRow($replRecordID);
	if ($replRow != null) {
		$date = new DateTime();
		$date = str_replace("T"," ",
				substr_replace(gmdate("Y-m-d\TH:i:s\Z", $date->getTimestamp() ) ,"",-1));
		$resp = replicateXmlRpc($replRow);
		if($db->hn_ts_updateReplRow($replRecordID, $resp."<br />".$date)){
			return "$resp<br />$date";
		}else{
			return 'Replication failed.';
		}
	}else{
		return 'Replication failed.';
	}
}
/**
 * Performs a partial copy of a local table to a remote table.
 * @param int $replRecord is a row from the Replication Table
 * @return mixed|string, on success the replication response and time started or 'Replication failed.'
 */
function hn_ts_replicate_partial($replRecord){
	// get most recent record from external table (which could be empty)
	$db = new Hn_TS_Database();
	
	// if empty then hn_ts_replicate_full($replRecord->replication_id)
	// else
		// lock
		// select all subsequent rows from internal table
		// if rows
			// post records to external table
			// record response and time to $replRecord 
		// unlock
	/*$replRow = $db->hn_ts_getReplRow($replRecord);
	if ($replRow != null) {
		$date = new DateTime();
		$date = str_replace("T"," ",
				substr_replace(gmdate("Y-m-d\TH:i:s\Z", $date->getTimestamp() ) ,"",-1));
		$resp = replicateXmlRpc($replRow);
		if($db->hn_ts_updateReplRow($replRecordID, $resp.$date)){
			return "<br />$resp $date";
		}else{
			return 'Replication failed.';
		}
	}else{
		return 'Replication failed.';
	}*/
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
	$readings = $db->hn_ts_get_readings_from_name(array("","",$replRow->local_table,0,0,0,0,0,0));
	$readingsArgs = array();
	foreach($readings as $reading){
		array_push($readingsArgs, $reading->value, $reading->valid_time);
	}
	//var_dump($readings);
	$queryArgs = array_merge(array('timestreams.add_measurements',$replRow->remote_user_login,
			$replRow->remote_user_pass,$replRow->remote_table),$readingsArgs);
	
	//$client->debug=True;
	if (!call_user_func_array(array($client,'query'), $queryArgs)){
		die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
	}
	/*if (!$client->query('wp.getCategories','', 'admin','Time349')) {
		die('An error occurred - '.$client->getErrorCode().":".$client->getErrorMessage());
	}*/
	return $response = $client->getResponse();
	//var_dump($response);
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
			<th scope="row">Local Table *</th>
			<td><input type="text" name="local_table" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Remote-User Login Name *</th>
			<td><input type="text" name="remote_user_login" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Remote-User Password *</th>
			<td><input type="password" name="pwrd" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Remote Url *</th>
			<td><input type="text" name="remote_url" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Remote Table *</th>
			<td><input type="text" name="remote_table" />
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">Continuous</th>
			<td><input type="checkbox" name="continuous" value="Yes" class="hn_ts_cont_chk"/>
			</td>
		</tr>

	</table>

	<p class="submit">
		<input type="submit" name='submit' class="button-primary"
			value="<?php _e('Add Replication Record') ?>" />
	</p>

</form>
<hr />
<?php
$cont = 0;
if(isset($_POST['continuous']) && $_POST['continuous'] == 'Yes'){
	$cont = 1;
}
if(isset($_POST['local_table']) && $_POST['local_table'] &&
		isset($_POST['remote_user_login']) && $_POST['remote_user_login'] &&
		isset($_POST['pwrd']) && $_POST['pwrd'] &&
		isset($_POST['remote_url']) && $_POST['remote_url'] &&
		isset($_POST['remote_table']) && $_POST['remote_table']) {
	$db = new Hn_TS_Database();
	$replication = $db->hn_ts_insert_replication(array("","",
			$_POST['local_table'], $_POST['remote_user_login'],
			$_POST['pwrd'], $_POST['remote_url'], $_POST['remote_table'],
			$cont,"")
	);
	echo 'Record added.';
}
}