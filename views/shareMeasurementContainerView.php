<?php
/**
 * Functions to display blogs that a measurement container can be shared with
 * Author: Jesse Blum (JMB)
 * Date: 2012
 */

/**
 * Displays the blogs in the network and provides a form for the user to select which ones to
 * share the given measurement container table with.
 * @param String $tablename is the name of the table to share. 
 */	
 function hn_ts_showShareBlogList($tablename){
	hn_ts_shareMC($tablename);
 }