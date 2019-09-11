<?php
// Copyright 1999-2016. Parallels IP Holdings GmbH. All Rights Reserved.
require_once('APIClass.php');

//Execution time start
$time_start = microtime(true); 
$helpscout = new APIClass();
$token_request_data = array("client_id" => "2e3120d532e2458389ba32c27d7f23c1",
	"client_secret"=> "b7ecdfece4ff4c81817034b44e58b7a1",
  	"grant_type"=> "client_credentials"
	);
$posting_data = array("firstName" => "Michelle1",
	"socialProfiles"=> [
		[
			"type" => "twitter",
			"value" => "@biskitcrumbs"
		]
	]
);
$helpscout_token = get_helpscout_token($helpscout, $token_request_data);

$postString = json_encode($posting_data);
$headers = $helpscout->post_request_header_response('api.helpscout.net/v2/customers',$postString,$helpscout_token);


$headers = explode("\r\n", $headers); // The seperator used in the Response Header is CRLF (Aka. \r\n) 
$headers = array_filter($headers);
print_r($headers);
$rid = explode(": ", $headers[9]);
$rid = array_filter($rid);
print_r($rid);
echo (int)$rid[1];


$time_end = microtime(true);
echo "Execution Time:".($time_end - $time_start);



function get_helpscout_token($helpscout, $data) {
	$token = json_decode($helpscout->post_request('api.helpscout.net/v2/oauth2/token',json_encode($data)),true);
	return $token['access_token'];
}