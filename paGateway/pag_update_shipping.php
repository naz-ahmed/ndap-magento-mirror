<?php

//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

include ("/var/www/html/magento/paGateway/includes/conn.php"); 
include "/var/etl/bin/includes/logging.php";
include "/var/etl/bin/includes/database_functions.php";
include "/var/etl/bin/includes/functions.php";

// Logging class initialization
$log = new Logging();

$log->lfile('/var/log/paGateway/pag_update_shipping.log');

$log->lwrite("starting file. \r\n");

// **************************************** SCRIPT MODE ***************************************************************/
// set to test so that magento status is not set to complete. no way to move from complete back to processing         */
	$scriptMode = "live";

$config_info = parse_ini_file('/var/www/html/magento/paGateway/paGateway.ini', true);

$now = date('Y.m.d \: h:m:s');
$to = $config_info['email']['to'];

$db = mysql_select_db($config_info['db_info']['db_selected'], $con);
if (!$db) {echo "cant select database";}

//load paAPI
require_once("/var/www/html/magento/paGateway/paOrder.php");


function getStatus($orderId)
{
	$config_info = parse_ini_file('/var/www/html/magento/paGateway/paGateway.ini', true);

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
				//echo "Response failed \r\n";
				$logMessage = "Response failed while checking order status at PA. Order# ".$orderId." ResponseDetail: ".$response['responseDetail']."\r\n"; 
				$OrderStatus['Status'] = "Failed";
				$OrderStatus['Messages'] = $logMessage;
				$OrderStatus['Id'] = $orderId;
			}
			
		}
		else
		{
			//prepare array to return
			$OrderStatus['Status'] = $response[0]['Status'];
			$OrderStatus['TrackingNumber'] = $response[0]['TrackingNum'];
			$OrderStatus['Time'] = $response[0]['entryTime'];
			$logMessage = "Response status: ".$response[0]['Status'];
			$OrderStatus['Messages'] = $logMessage;
			$OrderStatus['Id'] = $orderId;
		}
		
	}
	else
	{
		$OrderStatus = FALSE;
		// echo "didn't get response?"; 
	}
	
	
	return $OrderStatus;
	
}


require_once("/var/www/html/magento/app/Mage.php");
Mage::app();


//if shipping, shipping time will be added to the comment later
$comment = "shipped";

$email = false;
if($scriptMode == "live") { $email = true; }

$includeComment = false;  //sets if the comment should be included in the email to customer.
if($scriptMode == "live") { $includeComment = false; }


//$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
$myOrder = Mage::getModel('sales/order');

$orders=Mage::getModel('sales/mysql4_order_collection');
$orders->addFieldToFilter('status',Array('eq'=>"sent_to_pa"));  //filter to orders whose Status is "sent_to_pa"


// print_r($orders);


$allIds=$orders->getAllIds();


foreach($allIds as $thisId) 
{

	$myOrder->reset()->load($thisId);
	$myOrder->load(Mage::getSingleton('sales/order')->getLastOrderId());
	$IncrementId = $myOrder->getIncrementId();
	$paDetails = getStatus($IncrementId);
	/*
	if($paDetails == FALSE)
	{
		//getStatus should only return false if the API is down.
		$subject = "Error in getting tracking numbers back from PA - api failed";
		$body = "Did not receive any response from paAPI ".$now.". \r"; 
		mail($to, $subject, $body);
		die();
		
	}
	*/
	$paStatus = $paDetails['Status'];
	
	//DEBUG more verbose logs
	// $log->lwrite("Order id = ".$thisId." Increment Id = ".$IncrementId." PA Status = ".$paStatus." Messages = ".$paDetails['Messages']."\r\n");
	
	//skip over anything that doesn't have "Shipped" status from PA
	if($paStatus !== "Shipped") { continue; }

	$TrackingNumber = $paDetails['TrackingNumber'];
	$comment .= " ".$paDetails['Time']; //adds the shipping time to the comment
	
	//DEV
	// if($IncrementId !== '100000034') { continue; }
	
	echo "Increment Id = ".$IncrementId."\r\n";
	echo	"Status = ".$myOrder->getStatus()."\r\n";
		
	$convertor = Mage::getModel('sales/convert_order');
	$shipment = $convertor->toShipment($myOrder);

	foreach ($myOrder->getAllItems() as $orderItem) {
	    
	    if (!$orderItem->getQtyToShip()) {
	        continue;
	    }
	    if ($orderItem->getIsVirtual()) {
	        continue;
	    }
	    
	    $item = $convertor->itemToShipmentItem($orderItem);
	
	    $qty = $orderItem->getQtyToShip();
	
	    $item->setQty($qty);
	    $shipment->addItem($item);
	}
	
	

	$MagentoShipMethod = strtolower($myOrder->getShippingMethod());
	$MagentoShipDescription = strtolower($myOrder->getShippingDescription());

	//just returns the useless googlecheckout_carrier var	
	//var_dump($myOrder->getShippingCarrier()->getCarrierCode());
	//var_dump($myOrder->getShippingCarrier());
	
	//find what carrier PA actually shipped with. may differ from customer-specified method
	//if tracking doesn't match a pattern we know, use the customer-specified method and log the issue.
	$carrier = find_carrier($TrackingNumber);
	if($carrier == "")
	{
		$carrier = getShipCarrier($MagentoShipDescription);
		$log->lwrite("Tracking number not matched to carrier: ".$TrackingNumber);
	}
	
	
	$data = array();
	$data['carrier_code'] = $carrier;
	
	// translate the actual shipping method title
	$carrier_name = ucwords($MagentoShipDescription);
	
	switch($carrier)
	{
		case "ups":
			$carrier_name = "United Parcel Service";
			break;
		
		case "fedex":
			$carrier_name = "Federal Express";
			break;
		
		case "usps":
			$carrier_name = "United States Postal Service";
			break;
	
	}
	
	$data['title'] = $carrier_name;
	$data['number'] = $TrackingNumber;
	
	$track = Mage::getModel('sales/order_shipment_track')->addData($data);
	$shipment->addTrack($track);
	
	Mage::register('current_shipment', $shipment);
	
	$shipment->register();
	$shipment->addComment($comment, $email && $includeComment);
	
	// Changing to false is not being respected by magento
	$shipment->setEmailSent(false);
	if($scriptMode == 'live') { $shipment->setEmailSent(true); }
	
	//DEBUG
	// var_dump($data);
	
	
	if($scriptMode == "live")
	{
		//TODO enable this line for real LIVE useage.
		$shipment->getOrder()->setIsInProcess(true); // this is what marks the order complete
		
		
		$transactionSave = Mage::getModel('core/resource_transaction')
		    ->addObject($shipment)
		    ->addObject($shipment->getOrder())
		    ->save();
		
		$shipment->sendEmail($email, ($includeComment ? $comment : ''));
		
		$log->lwrite("shipping recorded and order updated for Order Id: ".$IncrementId."\r\n");
	}

	// to remove the error that the shipment already exists for the second item in the loop. 
	Mage::unregister('current_shipment');

} //for-each $allIds


$log->lwrite("finished. \r\n");


/******  DEV


$convertor = Mage::getModel('sales/convert_order');
$shipment = $convertor->toShipment($order);

foreach ($order->getAllItems() as $orderItem) {
    
    if (!$orderItem->getQtyToShip()) {
        continue;
    }
    if ($orderItem->getIsVirtual()) {
        continue;
    }
    
    $item = $convertor->itemToShipmentItem($orderItem);

    $qty = $orderItem->getQtyToShip();

    $item->setQty($qty);
    $shipment->addItem($item);
}

//TODO will need to find what actual carrier was and match

$data = array();
$data['carrier_code'] = 'ups';
$data['title'] = 'United Parcel Service';
$data['number'] = 'test';

$track = Mage::getModel('sales/order_shipment_track')->addData($data);
$shipment->addTrack($track);

Mage::register('current_shipment', $shipment);

$shipment->register();
$shipment->addComment($comment, $email && $includeComment);
// TODO in admin marked email sent but don't see an email yet. doublecheck.
$shipment->setEmailSent(true);

// TODO marks item COMPLETE - disable for testing?
if($scriptMode == "live")
{
	$shipment->getOrder()->setIsInProcess(true);
	
	$transactionSave = Mage::getModel('core/resource_transaction')
	    ->addObject($shipment)
	    ->addObject($shipment->getOrder())
	    ->save();
	
	$shipment->sendEmail($email, ($includeComment ? $comment : ''));
}


DEV *******/ 




?>