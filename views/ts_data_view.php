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
		<h3>Data Table</h3>
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
				$limit = 10;
			}
			$offset=hn_ts_setVar('pageNum');
			if($offset){
				$offset = $offset - 1;
			}
			$offset = $offset*$limit;
			//$args = array( username,password,table name,minimum timestamp,maximum timestamp,limit -- optional,offset -- optional);
			$args = array( null,null,$tablename,$min,$max,$limit,$offset);
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
		<?php	
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
		echo "<span class='displaying-num'>"; _e('Displaying',$HN_TS_NAME); echo" $dispOffset - $max "; _e(' of ',$HN_TS_NAME); echo" $numRows</span>";
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