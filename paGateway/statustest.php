<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

// ini_set("include_path", ".:/var/www/html/magento/paGateway:/usr/local/lib/php:/home/path/to/pear");

$con = mysql_connect("ndap01.c2lug8itjgui.us-east-1.rds.amazonaws.com", "magento", "C3HKMVYD7DQJsSVY");
if (!$con)
{
	die('Could not connect: ' . mysql_error());
}

$db = mysql_select_db("magento", $con);
if (!$db) {echo "cant select database";}

//************************ SET STATUS for API ***********************************//

// $apiMode = "live" or "test"
$apiMode = "test";

//******************************************************************************//

$config_info = parse_ini_file('/var/www/html/magento/paGateway/paGateway.ini', true);

include "/var/etl/bin/includes/logging.php";

// Logging class initialization
$log = new Logging();

$log->lfile('/var/log/paGateway/paGateway.log');
$log->lwrite("Script STARTED.\r\n");

//External script - Load magento framework
require_once("/var/www/html/magento/app/Mage.php");
Mage::app();


$myOrder=Mage::getModel('sales/order'); 
// print_r($myOrder);


$orders=Mage::getModel('sales/mysql4_order_collection');
// print_r($orders);


//FILTER to only orders in processing status
$orders->addFieldToFilter('status',Array('eq'=>"processing"));  //Status is "processing"

$allIds=$orders->getAllIds();

// print_r($allIds);

foreach($allIds as $thisId) {
	$myOrder->reset()->load($thisId);
	$InvoiceId = $myOrder->getIncrementId();
	
	print($InvoiceId);

}






?>