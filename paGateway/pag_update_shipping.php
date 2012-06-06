<?php

//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

include "/var/etl/bin/includes/logging.php";

// Logging class initialization
$log = new Logging();

$log->lfile('/var/log/paGateway/pag_update_shipping.log');

$log->lwrite("starting file. \r\n");

// **************************************** SCRIPT MODE ***************************************************************/
// set to test so that magento status is not set to complete. no way to move from complete back to processing         */
	$scriptMode = "live";

$config_info = parse_ini_file('/var/www/html/magento/paGateway/paGateway.ini', true);

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
	$paStatus = $paDetails['Status'];
	
	$log->lwrite("Order id = ".$thisId." Increment Id = ".$IncrementId." PA Status = ".$paStatus." Messages = ".$paDetails['Messages']."\r\n");
	
	//skip over anything that doesn't have "Shipped" status from PA
	if($paStatus !== "Shipped") { continue; }

	$TrackingNumber = $paDetails['TrackingNumber'];
	$comment .= " ".$paDetails['Time']; //adds the shipping time to the comment
	
	//DEV
	// going to try it for all. should only make changes if we have a tracking number
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
	//$MagentoShipCode = $myOrder->getCarrier_code(); this doesn't work. need to find cx way to get CarrierCode
	$CarrierCode = substr($MagentoShipMethod,0,strpos($MagentoShipMethod,'_'));
	
	echo "MagentoShipMethod = ".$MagentoShipMethod."\r\n";
	echo	"MagentoShipDescription = ".$MagentoShipDescription."\r\n";
		
	// reset CarrierCode for googlecheckout or ebay orders to appropriate carrier
	if(($CarrierCode == "googlecheckout") || ($CarrierCode == "m2eproshipping_m2eproshipping") || ($CarrierCode == "channelunitycustomrate") )
	{ 
		// continue;
		// check from shipping description which carrier selected
		if(strpos($MagentoShipDescription, "fedex" )) 
		{
			$CarrierCode = "fedex";
		}
		if(strpos($MagentoShipDescription, "usps"))
		{
			$CarrierCode = "usps";
		}
		if(strpos($MagentoShipDescription, "Cont US Street Addr")) //channel unity std and exp
		{
			$CarrierCode = "fedex";
		}
		if(strpos($MagentoShipDescription, "Std US Prot PO Box")) //channel unity usps
		{
			$CarrierCode = "usps";
		}
		if( (strpos($MagentoShipDescription, "ChannelUnity Shipping - Exp Canada")) || (strpos($MagentoShipDescription, "ChannelUnity Shipping - Std Canada")) ) 
		{
			$CarrierCode = "usps";
		}

		
	}
	
	
	$data = array();
	$data['carrier_code'] = $CarrierCode;
	$data['title'] = ucwords($MagentoShipDescription);
	$data['number'] = $TrackingNumber;
	
	$track = Mage::getModel('sales/order_shipment_track')->addData($data);
	$shipment->addTrack($track);
	
	Mage::register('current_shipment', $shipment);
	
	$shipment->register();
	$shipment->addComment($comment, $email && $includeComment);
	
	// Changing to false is not being respected by magento
	$shipment->setEmailSent(false);
	if($scriptMode == 'live') { $shipment->setEmailSent(true); }
	
	var_dump($data);
	
	
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