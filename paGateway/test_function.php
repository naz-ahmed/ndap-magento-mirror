<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

//load paAPI
require_once("paOrder.php"); 

function getStatus($orderId)
{
	$config_info = parse_ini_file('paGateway.ini', true);

	$uid = $config_info['login']['uid'];
	$pass = $config_info['login']['pass'];
	
	// PO Number entered via API. Use Magento Invoice ID.
	//$_orderId = "100000043";
	

	$order = new paOrder();
	$order->setUser($uid, $pass);
	$order->setPoNumber($orderId);
	$obj = $order->sendRequest();  //response is JSON object
	
	//get the response
	$response = json_decode($obj, true); 
	$OrderStatus = array();
	if($response)
	{
		if(array_key_exists("responseStatus", $response))
		{
			if($response["responseStatus"] == "Failed")
			{
				//PO number was wrong or response failed for some other reason
				echo "Response failed \r\n";
				$log->lwrite("Response failed while checking order status at PA. Order# ".$orderId." ResponseDetail: ".$response['responseDetail']."\r\n"); 
				$OrderStatus['Status'] = "Failed";
			}
			
		}
		
	//prepare array to return
	$OrderStatus['Status'] = $response[0]['Status'];
	$OrderStatus['TrackingNumber'] = $response[0]['TrackingNum'];
	$OrderStatus['Time'] = $response[0]['entryTime'];
	
	}
	
	return $OrderStatus;
	
}

/*
$id = $_GET["id"];
*/

$IncrementId = '100000058';

$paDetails = getStatus($IncrementId);
$paStatus = $paDetails['Status'];

var_dump($paDetails);


?>
