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
$apiMode = "live";

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

//construct paAPI class
require_once('/var/www/html/magento/paGateway/paAPI.php');
$paOrder = new paAPI();


$myOrder=Mage::getModel('sales/order'); 
// print_r($myOrder);


$orders=Mage::getModel('sales/mysql4_order_collection');
// print_r($orders);

$now = date('Y.m.d \: h:m:s');
$to = $config_info['email']['to'];

$uid = $config_info['login']['uid'];
$pass = $config_info['login']['pass'];

function getPaShipCode($text)
{
	$sql_string = "SELECT pa_code FROM cpk_shipping_translation WHERE magento_code LIKE '%".$text."%' OR magento_description LIKE '%".$text."%'";
	$result = mysql_query($sql_string);
	if($result)
	{
		$row = mysql_fetch_row($result);
		
		if($row){ 
			return $row[0];
		}
	}
	else
	{
		return "";
	}

	
}


//FILTER to only orders in processing status
$orders->addFieldToFilter('status',Array('eq'=>"sent_to_pa"));  //Status is "processing"

$allIds=$orders->getAllIds();

// print_r($allIds);

foreach($allIds as $thisId) {
	$myOrder->reset()->load($thisId);

	// pull in fields 
	$CustomerName = $myOrder->getShippingAddress()->getFirstname()." ".$myOrder->getShippingAddress()->getLastname();
	$Address = $myOrder->getShippingAddress()->getStreet();
	$City = $myOrder->getShippingAddress()->getCity();
	$State = $myOrder->getShippingAddress()->getRegionCode();
	$PostalCode = $myOrder->getShippingAddress()->getPostcode();
	$CountryId = $myOrder->getShippingAddress()->getCountryId();
	//get country name instead
	
	$countryName = Mage::getModel('directory/country')->load($CountryId)->getName();
	
	var_dump($countryName);
	
	//$Country = $countryName;
	
	//$Country = $myOrder->getShippingAddress()->getCountry()->getName(); 
	
	
	echo "Country = ".$Country."\r\n";
	
}

?>