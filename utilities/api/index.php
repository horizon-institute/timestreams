<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/', 'getmetadata');
/*$app->get('/wines', 'getWines');
$app->get('/wines/:id',	'getWine');
$app->get('/wines/search/:query', 'findByName');
$app->post('/wines', 'addWine');
$app->put('/wines/:id', 'updateWine');
$app->delete('/wines/:id',	'deleteWine');
*/
$app->run();

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

function getmetadata() {
	$sql = "select * FROM wp_ts_metadata ORDER BY tablename";
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

?>
