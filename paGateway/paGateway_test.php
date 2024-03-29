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
include "/var/etl/bin/includes/functions.php";

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
	
	$IncrementId = $myOrder->getIncrementId();
	if ($IncrementId != '100002665') { continue; }   //100002668   100002665
	
	// pull in fields 
	$CustomerName = $myOrder->getShippingAddress()->getFirstname()." ".$myOrder->getShippingAddress()->getLastname();
	$Address = $myOrder->getShippingAddress()->getStreet();
	$City = $myOrder->getShippingAddress()->getCity();
	$State = $myOrder->getShippingAddress()->getRegionCode();
	$PostalCode = $myOrder->getShippingAddress()->getPostcode();
	//Fedex wants country code, and USPS wants country name
	//when PA API is ready to handle that, we'll implement a check ship method and send appropriate. 
	//until then, we will send CountryID
	$CountryId = $myOrder->getShippingAddress()->getCountryId();
	//get country name instead
	$Country = Mage::getModel('directory/country')->load($CountryId)->getName();
	
	//var_dump($myOrder->getShippingAddress());
	/*
	//use full region name if state is not in US
	if($Country !== "US")
	{
		$State = $myOrder->getShippingAddress()->getRegion();
	}
	*/
	
	echo "region name = ".$myOrder->getShippingAddress()->getRegion();
	echo "\r\n";
	echo "region code = ".$myOrder->getShippingAddress()->getRegionCode();
	
	// shipping method $ShippingMethod2
	//will have to check text of magento for googlecheckout [shipping_description] => Flat Rate - Fixed
	$MagentoShipCode = $myOrder->getShippingMethod();
	$MagentoShipDescription = $myOrder->getShippingDescription();
	if(($MagentoShipCode == "googlecheckout_carrier") || ($MagentoShipCode == "m2eproshipping_m2eproshipping") )
	{
		$ShippingMethod2 = getPaShipCode($MagentoShipDescription);
	}
	else
	{
		$ShippingMethod2 = getPaShipCode($MagentoShipCode);
	}	
	
	if($ShippingMethod2 == "")
	{
		//$log->lwrite("PartId=".$PartId."\n");
		$log->lwrite("PA Shipping method code not found. Order ".$thisId." skipped.\n");
		$log->lwrite($MagentoShipCode."\n");
		$log->lwrite($MagentoShipDescription."\n");
		$subject = "Error in PA Gateway - order submission:shipping";
		$body = "PA Shipping method code not found. Order ".$thisId." skipped.\n";
		mail($to, $subject, $body);
		continue;
	}
	
	//id is different from invoce number which is what appears in the Admin interface.
	// send invoice ID to parts authority
	//echo "ORDER NUMBER".$myOrder->getIncrementId()." thisId = ".$thisId; 
	$InvoiceId = $myOrder->getIncrementId();
	
	// var_dump($myOrder->getInvoiceCollection());
	
	/*
	//REVIEW
	echo "ORDERID = ".$thisId;
	echo "\r\n";
	echo "STATUS = ".$myOrder->getStatus();
	echo "\r\n";
	echo "Customer Name = ".$CustomerName;
	echo "\r\n";
	echo "Address1 = ".$Address[0];
	echo "\r\n";
	echo "Address2 = ".$Address[1];
	echo "\r\n";
	echo "City = ".$City;
	echo "\r\n";
	echo "State = ".$State;
	echo "\r\n";
	echo "PostalCode = ".$PostalCode;
	echo "\r\n";
	echo "Country = ".$Country;
	echo "\r\n";
	echo "MagentoShipCode = ".$MagentoShipCode;
	echo "\r\n";
	echo "ShippingDescription = ".$MagentoShipDescription;
	echo "\r\n";
	echo "ShippingMethod2 = ".$ShippingMethod2;
	echo "\r\n";
	echo "Total paid = ".$myOrder->getTotal_paid();
	echo "\r\n";
	*/
	
	// prepare order to send to PA  
	$paOrder = new paAPI();  //construct paAPI class
	
	$paOrder->setUser($uid, $pass);//api account username and pass
		// $paOrder->setClient("Sample Client v1");//passes the name of the connection client *optional*
	$paOrder->setAccountNum($config_info['login']['paAccountNum']);
	$paOrder->set_cust_name(normalize_special_characters($CustomerName));//ship to name
	$paOrder->set_ship_add1(normalize_special_characters($Address[0]));//ship to address1
	
	if($CountryId == "US" || $CountryId == "CA")
	{
		if($Address[1])
		{
			$paOrder->set_ship_add2(normalize_special_characters($Address[1]));//ship to address2
		}
		$paOrder->set_ship_city(normalize_special_characters($City));//ship to city
		$paOrder->set_ship_state(normalize_special_characters($State));//ship to state
		$paOrder->set_ship_zip($PostalCode);//ship to postal code
		$paOrder->set_ship_meth($ShippingMethod2);//ship method FDG=Ground, FD2=2nd Day, FDO=Overnight
		$paOrder->set_order_num($InvoiceId);//customer PO#
		$paOrder->set_ship_country($CountryId);//ship to country code
			// $paOrder->set_conf_number($ConfNumber);//confirmation from DST
			// $paOrder->set_paypal_number($BTN);//paypal reference number
			// $paOrder->set_tax_amount($TAX);//tax on order
			// $paOrder->set_ship_amount($HSC);//calculated shipping cost of order
	}
	else
	{
		// this is a workaround for PA/DST issues with foreign orders
		// move city to ship_add2, state to ship_city since DST state is 2-char max
	
		// Field lengths on PA side are 30 char. Foreign orders where Address line 2 show are going to have issues. 
		// Nick will have to handle this and then we can improve this code. 
		
		$State = $myOrder->getShippingAddress()->getRegion();

		if(empty($State))
		{
			if($Address[1])
			{
				$paOrder->set_ship_add2(normalize_special_characters($Address[1]));//ship to address2
			}

			$paOrder->set_ship_city(normalize_special_characters($City)); //ship to city
			$paOrder->set_ship_state('');//ship to state
			$paOrder->set_ship_zip($PostalCode);//ship to postal code
			$paOrder->set_ship_country(normalize_special_characters($Country));//ship to country name
			echo "if\r\n";
			var_dump($paOrder);
		}
		else
		{
			$paOrder->set_ship_add2(normalize_special_characters($City));  //ship to address2

			$paOrder->set_ship_city(normalize_special_characters($State)); //ship to city
			$paOrder->set_ship_state('');//ship to state
			$paOrder->set_ship_zip($PostalCode);//ship to postal code
			$paOrder->set_ship_country(normalize_special_characters($Country));//ship to country name
			echo "else\r\n";
			var_dump($paOrder);
		}
		
		
		$paOrder->set_ship_meth($ShippingMethod2);//ship method FDG=Ground, FD2=2nd Day, FDO=Overnight
		$paOrder->set_order_num($InvoiceId);//customer PO#
		

	}
	
	$items = $myOrder->getAllItems();
	$itemcount = count($items);
	// echo "number of items = ".$itemcount;
	// echo "\r\n";
	$name = array();
	$unitPrice = array();
	$sku = array();
	$ids = array();
	$qty = array();
	
	// echo "PARTS: ";
	
	$i = 0;
	
	foreach($items as $itemId=>$item)
	{
		$name[] = $item->getName();
		$unitPrice[]=$item->getPrice();
		$sku[]=$item->getSku();
		$ids[]=$item->getProductId();
		//$qty[]=$item->getQtyToInvoice();
		$qty[]=$item->getQtyInvoiced();
		
		$linecode = strtoupper(substr($item->getSku(),0,2));
		$partnumber = substr($item->getSku(),2);
		$partnumber = substr($partnumber,0,strpos($partnumber, '.'));
		
		/*
		echo "Linecode = ".$linecode." | Partnumber = ".$partnumber." | sku = ".$sku[$i];
		//echo $linecode.", ";
		echo "\r\n";
		echo "Quantity = ".$qty[$i]." | Cost = ".$unitPrice[$i]; 
		echo "\r\n";
		*/
		
		$paOrder->addItem(array('line_code'=>$linecode,'part_num'=>$partnumber,'quantity'=>$qty[$i],'cost'=>$unitPrice[$i]));
		
		$i++;
	}
	
	
/*
*/
	if($itemcount>0 && $CustomerName && $PostalCode && $ShippingMethod2 && $InvoiceId)
	{
		$paOrder->set_status($apiMode); //takes either ('test') or ('live')
		$ret = json_decode($paOrder->sendOrder('enterOrder')); //order entry response in json string
	}
	/*
	echo "************ RESPONSE ******************";
	echo "\r\n";
	var_dump($ret);
	echo "\r\n";
	echo "\r\n";
	*/

	
	if(!$ret)
	{
		$log->lwrite("FAILED to received response from paAPI ".$now.". \r");
		$subject = "Error in PA Gateway - order submission: api failed";
		$body = "Did not receive any response from paAPI ".$now.". \r"; 
		$body .= "These order ids involved: ";
		foreach($allIds as $id)
		{
			$body .= $id.",";
		}
		$body .= ". \n"; 
		
		mail($to, $subject, $body); 
		
	}
	
	if($ret->responseStatus == 'Success')
	{
		$myOrder->setStatus('sent_to_pa');
		if ($apiMode == 'live')
		{
			//$myOrder->save();
			$state = "processing";
			$status = "sent_to_pa";
			$isCustomerNotified = FALSE;
			$comment = 'Changing state to Processing and status to sent_to_pa';
			$myOrder->setState($state, $status, $comment, $isCustomerNotified);
			$myOrder->save();
		}
		// echo "STATUS UPDATED: ".$myOrder->getStatus();
		$log->lwrite("Order Id ".$thisId." success : ".$now." \r");
		//DEBUG
		$log->lwrite("APIMODE = ".$apiMode."\r");
		$log->lwrite($ret->orderFileXML."\r");
		$log->lwrite("ORDERID = ".$thisId." InvoiceId = ".$InvoiceId." STATUS = ".$myOrder->getStatus()." Customer Name = ".$CustomerName." Address1 = ".$Address[0]." Address2 = ".$Address[1]." City = ".$City." State = ".$State." PostalCode = ".$PostalCode." Country = ".$Country." MagentoShipCode = ".$MagentoShipCode." ShippingDescription = ".$MagentoShipDescription." ShippingDescription = ".$MagentoShipDescription." ShippingMethod2 = ".$ShippingMethod2."\r");

	}
	else
	{
		$log->lwrite("FAILED: Order Id ".$InvoiceId."\r");
		$subject = "Error in PA Gateway - order submission: order failed";
		$body = "Submitting order ".$InvoiceId." to paAPI failed. Response detail = ".$ret->responseDetail." \r";
		mail($to, $subject, $body);
		$log->lwrite("ORDERID = ".$thisId." InvoiceId = ".$InvoiceId." STATUS = ".$myOrder->getStatus()." Customer Name = ".$CustomerName." Address1 = ".$Address[0]." Address2 = ".$Address[1]." City = ".$City." State = ".$State." PostalCode = ".$PostalCode." Country = ".$Country." MagentoShipCode = ".$MagentoShipCode." ShippingDescription = ".$MagentoShipDescription." ShippingDescription = ".$MagentoShipDescription." ShippingMethod2 = ".$ShippingMethod2. " Total paid = ".$myOrder->getTotal_paid()."\r\n");
		$log->lwrite($ret->orderFileXML."\r\n");
		continue;

	}

/*	
*/
	
}

$log->lwrite("Script FINISHED.\r\n\r\n");

?>