<?php
// Copyright 1999-2016. Parallels IP Holdings GmbH. All Rights Reserved.
require_once('APIClass.php');

//Execution time start
$time_start = microtime(true); 
//Const Values
$user_trans = array('580d5dbebbddbd29156d52af' => 413427,
	'579b73c6e694aa7322c46746' => 413103);
$mailbox_trans = array('57b5b557e694aa71a8904de3' => 193301,
	'579b73c6e694aa7322c46744' => 193302);
$status_trans = array('open' => 'active', 'hold' => 'pending', 'closed' => 'closed','archived' => 'closed');
$token_request_data = array("client_id" => "2e3120d532e2458389ba32c27d7f23c1",
	"client_secret"=> "b7ecdfece4ff4c81817034b44e58b7a1",
  	"grant_type"=> "client_credentials"
	);
$enchant = new APIClass();
$helpscout = new APIClass();
$enchant_token = 'ePlYXubvv3uWaXhd90nFSHoo6pPnX2Y7';
$helpscout_token = get_helpscout_token($helpscout, $token_request_data);
//$helpscout_token = "asdfsadf";

//Inserting into Helpscout
$ticket_id = "59b9684fe694aa415dd9db83";
$result = json_decode($enchant->get_request('nourishedessentials.enchant.com/api/v1/tickets/'.$ticket_id.'?embed=customers,messages',$enchant_token),true);
//Set posting data

$assignTo = 1;

if ($result['user_id'] != null) {
	$assignTo = $user_trans[$result['user_id']];
}
$customer_type = 'email';
$customer_value = $result['customer']['contacts'][0]['value'];
if ($result['customer']['contacts'][0]['type'] == 'twitter') {
	$customer_info = array(
		"socialProfiles"=> [
			[
				"type" => "twitter",
				"value" => $result['customer']['contacts'][0]['value']
			]
		]
	);
	if ($result['customer']['first_name'] != '') {
		$customer_info['firstName'] = substr($result['customer']['first_name'],0,40);
	}
	if ($result['customer']['last_name'] != '') {
		$customer_info['lastName'] = substr($result['customer']['last_name'],0,40);
	}
	$customer_type = 'id';
	$customer_value = create_customer($helpscout, $customer_info, $helpscout_token);
}
$subject = $result['subject'] != null ? $result['subject'] : $result['summary'];
$posting_data = array("subject" => $result['subject'],
	"mailboxId" => $mailbox_trans[$result['inbox_id']],
	"status" => $status_trans[$result['state']],
	"subject" => $subject,
	"imported" => true,
	"type" => "email",
	"assignTo" => $assignTo,
	"createdAt" => $result['created_at'],
	"customer" => array(
		$customer_type => $customer_value
	),
	"threads" => []
	);

if ($result['customer']['first_name'] != '') {
	$posting_data['customer']['firstName'] = substr($result['customer']['first_name'],0,40);
}
if ($result['customer']['last_name'] != '') {
	$posting_data['customer']['lastName'] = substr($result['customer']['last_name'],0,40);
}
foreach ($result['messages'] as $message) {
	$thread = array();
	if ($message['type'] == 'reply' && $message['user_id'] == null) {
		$thread['type'] = 'customer';
		$thread['customer'] = array($customer_type => $customer_value);
	}
	if ($message['type'] == 'reply' && $message['user_id'] != null) {
		$thread['type'] = 'reply';
		$thread['customer'] = array($customer_type => $customer_value);
		$thread['user'] = $user_trans[$message['user_id']];
	}
	if ($message['type'] == 'note') {
		$thread['type'] = 'note';
		$thread['user'] = $user_trans[$message['user_id']];
	}
	$thread['text'] = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/s', "$1$3", $message['body']);
	if ($message['body'] == "" && $message['attachments'] != null) {
		foreach($message['attachments'] as $attachment) {
			$thread['text'] .= $attachment['name']."<br/>";	
		}
	}
	
	$thread['imported'] = true;
	$thread['createdAt'] = $message['created_at'];
	array_push($posting_data['threads'],$thread);
		
}
$postString = json_encode($posting_data);

$step_result = json_decode($helpscout->post_request('api.helpscout.net/v2/conversations',$postString,$helpscout_token),true);
echo "Ticket ID:".$ticket_id;
if ($step_result == '') {
	echo " has successed. CreatedAt:".$result['created_at']."<br/>";
} else if ($step_result['error'] == 'invalid_token') {
	$helpscout_token = get_helpscout_token($helpscout, $token_request_data);
	echo " Invlid Token detected.<br/>";
} else { 
	print_r($step_result);
	echo "<br/>"; 
}

$time_end = microtime(true);
echo "Execution Time:".($time_end - $time_start);

function get_helpscout_token($helpscout, $data) {
	$token = json_decode($helpscout->post_request('api.helpscout.net/v2/oauth2/token',json_encode($data)),true);
	return $token['access_token'];
}
function create_customer($helpscout, $customer_info, $helpscout_token) {
	$postString = json_encode($customer_info);
	$headers = $helpscout->post_request_header_response('api.helpscout.net/v2/customers',$postString,$helpscout_token);

	$headers = explode("\r\n", $headers); // The seperator used in the Response Header is CRLF (Aka. \r\n) 
	$headers = array_filter($headers);
	$rid = explode(": ", $headers[9]);
	$rid = array_filter($rid);
	return (int)$rid[1];
}