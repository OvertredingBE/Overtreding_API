<?php

require_once '../include/DbHandler.php';
require '.././libs/Slim/Slim.php';
require '.././libs/PHPExcel/Classes/PHPExcel.php';
require '.././libs/PHPMailer/PHPMailerAutoload.php';
require '.././libs/PHPMailer/class.phpmailer.php';

\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

header('Access-Control-Allow-Origin: *');

$app->get('/populateDB', function() use ($app) {
	$response = array();
	$db = new DbHandler();
	$result = $db->bulkInsert();
	// $resposne["asd"] = $result;
	// if($result)  {
	// 	$response["error"] = false;
	// }
	// else{
	// 	$response["error"] = true;
	// }

	echoResponse(200, $response);
});
$app->post('/email', function() {
	header('Access-Control-Allow-Origin: *');

	echo 'test';
// 	$mail             = new PHPMailer();
// 	$mail->SMTPOptions = array(
// 'ssl' => array(
//     'verify_peer' => false,
//     'verify_peer_name' => false,
//     'allow_self_signed' => true
// ));
// 	$body             = "test";
// 	$mail->IsSMTP();
// 	$mail->SMTPAuth   = true;
// 	$mail->Host       = "smtp.gmail.com";
// 	$mail->Port       = 587;
// 	$mail->Username   = "martin.dzhonov@gmail.com";
// 	$mail->Password   = "Crtstr#21";
// 	$mail->SMTPSecure = 'tls';
// 	$mail->SetFrom('martin.dzhonov@gmail.com', 'Your name');
// 	$mail->AddReplyTo("martin.dzhonov@gmail.com","Your name");
// 	$mail->Subject    = "asd";
// 	$mail->AltBody    = "Any message.";
// 	$mail->MsgHTML($body);
// 	$address = "martin.dzhonov@gmail.com";
// 	$mail->AddAddress($address, "martin.dzhonov@gmail.com");
// 	if(!$mail->Send()) {
// 		echo 0;
// 	} else {
// 		echo 1;
// 	}
});
$app->get('/db', function() use ($app) {
	$response = array();
	$response["rights"] = array();
	$response["texts"] = array();
	$response["alcohol"] = array();
	$response["drugs"] = array();
	$response["speed"] = array();
	$response["other"] = array();
	$response["other_tags"] = array();
	$db = new DbHandler();
	$result = $db->getRights();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["rights"], $tmp);
	}

	$result = $db->getTexts();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["texts"], $tmp);
	}

	$result = $db->getAlchohol();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["alcohol"], $tmp);
	}

	$result = $db->getDrugs();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["drugs"], $tmp);
	}

	$result = $db->getSpeed();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["speed"], $tmp);
	}

	$result = $db->getOther();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["other"], $tmp);
	}
	$result = $db->getOtherTags();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["other_tags"], $tmp);
	}


	echoResponse(200, $response);
});

$app->get('/texts', function() use ($app) {
	$app->response()->header('Access-Control-Allow-Origin', 'http://localhost:8100');
	$response = array();
	$response["texts"] = array();
	$db = new DbHandler();
	$result = $db->getTexts();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["texts"], $tmp);
	}


	echoResponse(200, $response);
});

$app->get('/rights', function() {
	$response = array();
	$response["rights"] = array();
	$db = new DbHandler();
	$result = $db->getRights();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["rights"], $tmp);
	}

	echoResponse(200, $response);
});

$app->get('/alchohol', function() {
	$response = array();
	$response["alchohol"] = array();
	$db = new DbHandler();
	$result = $db->getAlchohol();
	while ($row = $result->fetch_assoc()) {
		$tmp = array();
		$keys = array_keys($row);

		foreach($keys as $key) {
			$tmp[$key] = $row[$key];
		}
		array_push($response["alchohol"], $tmp);
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
