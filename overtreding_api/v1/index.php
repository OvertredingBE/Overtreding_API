<?php

require_once '../include/DbHandler.php';
require '.././libs/Slim/Slim.php';
require '.././libs/PHPExcel/Classes/PHPExcel.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/populateDB', function() {
	$response = array();
    $db = new DbHandler();
    $result = $db->bulkInsert(); 

    if($result)  {
    	$response["error"] = false;
    } 
    else{
    	$response["error"] = true;
    }

    echoResponse(200, $response);
});

$app->get('/users', function() {
    $response = array();
    $db = new DbHandler();
    $result = $db->getAllUsers();

    while ($row = $result->fetch_assoc()) {
        $tmp = array();
        $tmp["id"] = $row["id"];
        $tmp["email"] = $row["email"];
        $tmp["phone"] = $row["phone"];
        $tmp["country"] = $row["country"];

        array_push($response, $tmp);
    }

    	echoResponse(200, $response);
});

function echoResponse($status_code, $response) {
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

$app->run();
?>