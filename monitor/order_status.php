<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

// ini_set("include_path", ".:/var/www/html/magento/paGateway:/usr/local/lib/php:/home/path/to/pear");

$con = mysql_connect("magento01.c2lug8itjgui.us-east-1.rds.amazonaws.com", "magento", "C3HKMVYD7DQJsSVY");
if (!$con)
{
	die('Could not connect: ' . mysql_error());
}

$db = mysql_select_db("magento", $con);
if (!$db) {echo "cant select database";}



//External script - Load magento framework
require_once("/var/www/html/magento/app/Mage.php");
Mage::app();

$myOrder=Mage::getModel('sales/order'); 
// print_r($myOrder);


$orders=Mage::getModel('sales/mysql4_order_collection');
// print_r($orders);

//FILTER to only orders in processing status
 // $orders->addFieldToFilter('status',Array('eq'=>"payment_review"));  //Status is "processing"

$to = "sales@ndap-llc.com";
$msg_body = "";

$allIds=$orders->getAllIds();

$sendMsg = FALSE;

foreach($allIds as $thisId) 
{
	$myOrder->reset()->load($thisId);
	// DEBUG -- 
	// if($myOrder->getIncrementId() != '100000358'){continue;}
	
	
	$status_array = array('holded', 'payment_review', 'pending', 'pending_payment', 'pending_paypal', 'fraud');
	
	if(in_array($myOrder->getStatus(), $status_array))
	{
		//echo strtoupper($myOrder->getStatus());
	
		$PoNumber = $myOrder->getIncrementId();
		$OrderStatus = $myOrder->getStatus();
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
		$Phone = $myOrder->getShippingAddress()->getTelephone();
		$TotalPaid = $myOrder->getTotal_paid();
		
		// var_dump($myOrder->getShippingAddress());
		
		/*
		//REVIEW
		echo "ORDERID = ".$myOrder->getIncrementId();
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
		echo "Phone = ".$Phone;
		echo "\r\n";
		echo "Total paid = ".$myOrder->getTotal_paid();
		echo "\r\n";
		*/
		
		$msg_body .= "ORDERID = ".$PoNumber;
		$msg_body .= "\r\n";
		$msg_body .= "STATUS = ".$OrderStatus;
		$msg_body .= "\r\n";
		$msg_body .=  "Customer Name = ".$CustomerName;
		$msg_body .=  "\r\n";
		$msg_body .=  "Address1 = ".$Address[0];
		$msg_body .=  "\r\n";
		$msg_body .=  "Address2 = ".$Address[1];
		$msg_body .=  "\r\n";
		$msg_body .=  "City = ".$City;
		$msg_body .=  "\r\n";
		$msg_body .=  "State = ".$State;
		$msg_body .=  "\r\n";
		$msg_body .=  "PostalCode = ".$PostalCode;
		$msg_body .=  "\r\n";
		$msg_body .=  "Country = ".$Country;
		$msg_body .=  "\r\n";
		$msg_body .=  "Phone = ".$Phone;
		$msg_body .=  "\r\n";
		$msg_body .=  "Total paid = ".$TotalPaid;	
		$msg_body .=  "\r\n";
		$msg_body .= "\r\n";
		$msg_body .= "\r\n";

		
		$sendMsg = TRUE;
		
	}
	
	
	


}

//PREPARE AN EMAIL with this info

$query = "SELECT * FROM m2epro_ebay_orders WHERE magento_order_id IS NULL AND payment_status_m2e_code =3 AND account_id = 1";
	$result = mysql_query($query);
	
	if(mysql_num_rows($result) > 0 )
	{
		while($row =  mysql_fetch_assoc($result))
		{
			$msg_body .= "\r\n";
			$msg_body .= "\r\n";
			$msg_body .= "EBAY ORDERS WHERE magento_order_id IS NULL AND payment_status_m2e_code =3 AND account_id = 1 -- means we don't actually have it in stock anymore and it was paid for";
			$msg_body .= "\r\n";
			$msg_body .= "Buyer Name: ".$row['buyer_name'];
			$msg_body .= "Buyer Email: ".$row['buyer_email'];
			$msg_body .= "Buyer User Id: ".$row['buyer_userId'];
			$msg_body .= "Ebay Order Id: ".$row['ebay_order_id'];
			
			$sendMsg = TRUE;
					
		}

	}
	else
	{
		$msg_body .= "\r\n";
		$msg_body .= "\r\n";
		$msg_body .= "EBAY ORDERS are good: all ebay orders have magento_order_ids. \r\n";
	}




// echo $msg_body;

$msg_subject = "[CPK] Orders for Review";

if($sendMsg == TRUE)
{
	mail($to, $msg_subject, "The following orders need review. \r\n"." ".$msg_body);
}





?>