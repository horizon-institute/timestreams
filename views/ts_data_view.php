<?php
/**
 * Functions to display time series data table records
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * To do: Add search and edit functionality
 */

	/**
	 * Retrieves the given $_GET variable and checks for its existence 
	 * @return the value or null, if not found.
	 */
	function hn_ts_setVar($varToCheck){			
		if(isset($_GET["$varToCheck"]) && !($_GET["$varToCheck"] === "") ){
			return $_GET["$varToCheck"];
		}
		return null;
	}
	
	
	/**
	 * Enqueue javascript scripts
	 */
	function hn_ts_load_Datasources_scripts($hook){
		wp_enqueue_script('ts-ajax', '/wp-content/plugins/timestreams/js/hn_ts_ajax.js');
	}

	
	add_action('admin_enqueue_scripts', 'hn_ts_load_Datasources_scripts');
	/**
	 * Displays data in a table from the given name of the table
	 * To do: Complete pagination functionality.
	 * @param String $tablename is the name of the table to display. 
	 * The table is expected to be a data table with a name in the format of
	 * wp_<site_id>_ts_<measurement_type>_<device_id>
	 */	
	 function hn_ts_showDataRecord($tablename){
		?>
		<table class="widefat">
			<thead>
				<tr>
					<th>id</th>
					<th>value</th>
					<th>timestamp</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th>id</th>
					<th>value</th>
					<th>timestamp</th>
				</tr>
			</tfoot>
			<tbody>
			<?php 
			$db = new Hn_TS_Database();
			$max=hn_ts_setVar('max');
			$min=hn_ts_setVar('min');
			$limit=hn_ts_setVar('limit');
			if(!$limit){
				$limit = 100;
			}
			$offset=hn_ts_setVar('pageNum');
			if($offset){
				$offset = $offset - 1;
			}
			$offset = $offset*$limit;
			//$args = array( username,password,table name,minimum timestamp,maximum timestamp,limit -- optional,offset -- optional);
			$args = array( null,null,$tablename,$min,$max,$limit,$offset,"valid_time");
			$rows = $db->hn_ts_get_readings_from_name($args);
			if($rows){
				foreach ( $rows as $row )
				echo "<tr>
					<td>$row->id</td>
					<td>$row->value</td>
					<td>$row->valid_time</td>
				</tr>";
			}?>
			</tbody>
		</table>
		<div class="tablenav">
			<div class="tablenav-pages">
				<?php /*
				<span class="page-numbers current">1</span>
				<?php //<a href="#" class="page-numbers">2</a> ?>
				<?php //<a href="#" class="next page-numbers">&raquo;</a> ?>
				*/
				buildPagination($db, $offset, $limit, $args);
				?>
			</div>
		</div>
		<hr />
		<h3>Upload data in CSV format</h3></br>
		<h4>Please use the format [value],[timestamp];</h4>
		e.g.: </br></br> 
		2.345,2012-11-09 12:10:23;</br>
		13,2012-11-09 12:10:24;</br></br>
		or</br></br>
		some text,2012-11-09 12:10:23;</br>
		some other text,2012-11-09 12:10:24;</br></br>
		<strong>Notice: the character "," is forbidden inside the fields</strong>
		<form method="post" action="">
			<textarea name="csv-upload" id="csv-upload" cols="50" rows="10" tabindex="4"></textarea></br>
			<button type="submit" id="upload-button" class="button-primary">
				Upload
			</button>
		</form>
		
		<?php	

		if(isset($_POST)){
			addMeasurements($db,$tablename,$_POST['csv-upload']);

		}else{
			return;

		}

	}

	function checkCSV($text){
		$data = str_getcsv4($text,";");
		$counter=0;
		foreach ($data as $line) {
			$counter+=1;
			if(strlen($line)){
				if(count(explode(',', $line))!=2 ){
					return false;
				}
			}else{
				if($counter != count($data)){
					return false;
				}
			}
		}
		return true;
	}

	function CSV2Obj($text){
		$data = str_getcsv4($text,";");
		foreach ($data as $line) {
			if(strlen($line)){
				$foo=explode(',', $line);
				$obj['v']=$foo[0];
				$obj['t']=$foo[1];
				$temp[]=$obj;
			}
		}
		return $temp;

	}

	function str_getcsv4($input, $delimiter = ',', $enclosure = '"') {

	    if( ! preg_match("/[$enclosure]/", $input) ) {
	      return (array)preg_replace(array("/^\\s*/", "/\\s*$/"), '', explode($delimiter, $input));
	    }

	    $token = "##"; $token2 = "::";
	    $t1 = preg_replace(array("/\\\[$enclosure]/", "/$enclosure{2}/",
	         "/[$enclosure]\\s*[$delimiter]\\s*[$enclosure]\\s*/", "/\\s*[$enclosure]\\s*/"),
	         array($token2, $token2, $token, $token), trim(trim(trim($input), $enclosure)));

	    $a = explode($token, $t1);
	    foreach($a as $k=>$v) {
	        if ( preg_match("/^{$delimiter}/", $v) || preg_match("/{$delimiter}$/", $v) ) {
	            $a[$k] = trim($v, $delimiter); $a[$k] = preg_replace("/$delimiter/", "$token", $a[$k]); }
	    }
	    $a = explode($token, implode($token, $a));
	    return (array)preg_replace(array("/^\\s/", "/\\s$/", "/$token2/"), array('', '', $enclosure), $a);

	}

	

	function addMeasurements($db,$tablename,$measurements){
		
		if(!$tablename){
			//error no name
		}else{
			if(checkCSV($measurements)){
				$measurements=CSV2Obj($measurements);
				$sql = "INSERT INTO $tablename (value, valid_time) VALUES ";
				$args=["username","password",$tablename];
				foreach($measurements as $m){
					$args[]=$db->hn_ts_sanitise($m['v']);
					$args[]=$db->hn_ts_sanitise($m['t']);	
				}

				$no_rows = $db->hn_ts_insert_readings($args);

			}else{
				//error no measurements
			}

		}
		
	}


	function buildPagination($db, $offset, $limit, $args){
		$numRows = $db->hn_ts_get_count_from_name($args);
		if (isset($_GET['pageNum'])) {
			$pageNum = $_GET['pageNum'];
		} else {
			$pageNum = 1;
		}
		$last = ceil($numRows/$limit);
		$pageNum = (int)$pageNum;
		$uri = stripOldAttributes($_SERVER['REQUEST_URI']);
		if ($pageNum > $last) {
			$pageNum = $last;
		}
		if ($pageNum < 1) {
			$pageNum = 1;
		}
		
		$max=$offset+$limit;
		if($max > $numRows){
			$max = $numRows;
		}
		$dispOffset = $offset + 1;
		echo "<span class='displaying-num'>"; _e('Displaying',HN_TS_NAME); echo" $dispOffset - $max "; _e(' of ',HN_TS_NAME); echo" $numRows</span>";
		if ($pageNum == 1) {
			echo " FIRST PREV ";
		} else {
			echo " <a href='{$uri}&limit=$limit&pageNum=1'>FIRST</a> ";
			$prevpage = $pageNum-1;
			echo " <a href='{$uri}&limit=$limit&pageNum=$prevpage'>PREV</a> ";
		}
		if ($pageNum == $last) {
			echo " NEXT LAST ";
		} else {
			$next = $pageNum+1;
			echo " <a href='{$uri}&limit=$limit&pageNum=$next'>NEXT</a> ";
			echo " <a href='{$uri}&limit=$limit&pageNum=$last'>LAST</a> ";
		}
	}
	/**
	 * Rmoves old limit and pageNum attributes from a given uri
	 */
	function stripOldAttributes($uri){
		$uri= preg_replace('&limit=.*&', "&", $uri);
		/*$uri= preg_replace('&limit=.*', "", $uri);
		$uri= preg_replace('&pageNum=.*&', "&", $uri);
		$uri= preg_replace('&pageNum=.*&', "", $uri);*/
		$uri = str_replace('&', "", $uri);
		$uri = str_replace('table', "&table", $uri);
		return $uri;
	}
?>