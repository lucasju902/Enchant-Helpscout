<?php
// Copyright 1999-2016. Parallels IP Holdings GmbH. All Rights Reserved.
require_once('APIClass.php');

//Execution time start
$time_start = microtime(true); 
$helpscout = new APIClass();
$posting_data = array("client_id" => "2e3120d532e2458389ba32c27d7f23c1",
	"client_secret"=> "b7ecdfece4ff4c81817034b44e58b7a1",
  	"grant_type"=> "client_credentials"
	);

$postString = json_encode($posting_data);
$token = json_decode($helpscout->post_request('api.helpscout.net/v2/oauth2/token',$postString),true);
echo $token['access_token'];
$result = json_decode($helpscout->get_request('api.helpscout.net/v2/conversations',"asdf"), true);
if ($result['error'] == "invalid_token") 
	echo "Invalid Token";
$time_end = microtime(true);
echo "Execution Time:".($time_end - $time_start);