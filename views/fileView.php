<?php
/**
 * Functions to display contents of a text file
 * Author: Jesse Blum (JMB)
 * Date: 2012
 * Meant for testing purposes
 */

/**
 * Displays file contents.
 */
	function hn_ts_showFile(){
		$contents = file(
				'http://localhost/wordpress/wp-content/uploads/weather/data/raw/2012/2012-03/2012-03-21.txt', 
				FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
		echo $contents[0];
	}
?>