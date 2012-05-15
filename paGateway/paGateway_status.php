<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

	//load paAPI
	require_once("paOrder.php");
	
//External script - Load magento framework
require_once("/var/www/html/magento/app/Mage.php");
Mage::app();

$myOrder=Mage::getModel('sales/order'); 
// print_r($myOrder);


$orders=Mage::getModel('sales/mysql4_order_collection');
// print_r($orders);

$orders->addFieldToFilter('status',Array('eq'=>"sent_to_pa"));  //Status is "processing"


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
	$order->sendRequest();  //response is JSON object
	
	//$ret = json_decode($order->sendRequest(),true);
	return $order->sendRequest();
	
}



try
{
	$allIds=$orders->getAllIds();
	$allIncrementIds = array();

	
	echo "<html><head></head><body style='font-family: Arial, sans-serif; font-size: 12px;'>";
	echo "<table cellpadding=5 cellspacing=0 border=1>";


	//the function responsds differently based on the success of the query. Since we will end up sending some old bogus po#s
	//we just write out the appropriate header row ahead of time.
	echo "<tr style='font-weight:bold; background-color:1D4E51; color:#f1f1f1;'>";
	echo "<td>Status</td>";
	echo "<td>OrderNum</td>";
	echo "<td>InvoiceNum</td>";
	echo "<td>PaPoNum</td>";
	echo "<td>ShippingCost</td>";
	echo "<td>TrackingNum</td>";
	echo "<td>ShippingWeight</td>";
	echo "<td>cust_num</td>";
	echo "<td>entryTime</td>";
	echo "<td>branch</td>";
	echo "<td>CustPoNum</td>";
	echo "<td>brord</td>";
	
	echo "</tr>";
			
	foreach($allIds as $thisId) 
	{
		
		$myOrder->reset()->load($thisId);
		$myOrder->load(Mage::getSingleton('sales/order')->getLastOrderId());
		$IncrementId = $myOrder->getIncrementId();
		

		$obj = getStatus($IncrementId);
		
		$myArray = json_decode($obj, true); 
		
		/*
		var_dump($myArray); 
		
		$Status = $myArray[0]['Status'];
		echo "DISPLAY VARIABLES: \r\n";
		echo "<Status>".$Status;
		echo "</Status>";
		echo "\r\n"; 
		
		$TrackingNumber = $myArray[0]['TrackingNum'];
		echo "<Tracking>".$TrackingNumber;
		echo "</Tracking>";
		*/

				
		echo "<tr>";
		
		foreach($myArray[0] as $item)
		{
			echo "<td>$item</td>";
		}
		echo "</tr>";
		

	}

echo "</table>";
echo "</body></html>";

}
catch(Exception $e)
{}







?>