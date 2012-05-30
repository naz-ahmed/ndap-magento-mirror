<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

include "/var/etl/bin/includes/conn.php";
include "/var/etl/bin/includes/logging.php";
include ("/var/etl/bin/includes/database_functions.php");
include ("/var/etl/bin/includes/functions.php");

require_once 'std.table.class.inc';
require_once 'db.inc';
require_once 'error.inc';


include 'PaInvoice.class.inc';
$dbobject = new PaInvoice;


//load paAPI
require_once('/var/www/html/magento/paGateway/reportAPI/invoiceReport.php');

//load Mage
require_once("/var/www/html/magento/app/Mage.php");
Mage::app();

$config_info = parse_ini_file('/var/www/html/magento/paGateway/paGateway.ini', true);

$uid = $config_info['login']['uid'];
$pass = $config_info['login']['pass'];

$nw = new invoiceReport();
$nw->setUser($uid,$pass);
$nw->setAction('getInvoiceInRange');//only supported function 
$nw->setStartDate('20120101');//start date
$nw->setEndDate('20120515');//end date

$obj = $nw->sendRequest();

// print_r($nw->sendRequest()); //send request and get response in json format

$response = json_decode($obj, true); 

// var_dump($response);

echo "yo";

if($response)
{
$i = 0;

	$responseDetail = $response['responseDetail'];
	//var_dump($responseDetail);

		foreach($responseDetail as $item)
		{
			// var_dump($item);
			//echo "<tr>";
			
			/*
			foreach($item as $detail)
			{
			
				echo "<td>$detail</td>";
				//print_r($detail."-----");
				
			}
			*/
						
			$ins_array = array();
			
											
			$norm_partnumber = preg_replace("/[^\p{L}\p{N}]/u", '', $responseDetail[$i]['xsku']);
				//set up an insert statement
				
			$ins_array["xqty"] = $responseDetail[$i]['xqty'];
			$ins_array["xprice"] = $responseDetail[$i]['xprice'];
			$ins_array["xmpline"] = $responseDetail[$i]['xmpline'];
			$ins_array["xline"] = $responseDetail[$i]['xline'];
			$ins_array["xbran"] = $responseDetail[$i]['xbran'];
			$ins_array["xfreight"] = $responseDetail[$i]['xfreight'];
			$ins_array["xcore"] = $responseDetail[$i]['xcore'];
			$ins_array["xord"] = $responseDetail[$i]['xord'];
			$ins_array["mhind"] = $responseDetail[$i]['mhind'];
			$year = substr($responseDetail[$i]['xdate'], 0, 4);
			$month = substr($responseDetail[$i]['xdate'], 4, 2);
			$day = substr($responseDetail[$i]['xdate'], 6, 2);
			$ins_array["xdate"] = $year."-".$month."-".$day;
			$ins_array["xinvtotal"] = $responseDetail[$i]['xinvtotal'];
			$ins_array["xinv"] = $responseDetail[$i]['xinv'];
			$ins_array["xourpo"] = $responseDetail[$i]['xourpo'];
			$ins_array["xsku"] = $norm_partnumber;


			$fieldarray = $dbobject->insertRecord($ins_array);
			$errors = $dbobject->getErrors();

			
			
			$i++;
			echo $i;
			echo "\r\n";
		}
		
		
}
else
{ 
	echo "getting no response from paAPI"; 
}


?>