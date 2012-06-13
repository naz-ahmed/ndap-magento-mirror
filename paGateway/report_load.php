<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

include "/var/etl/bin/includes/conn.php";
include "/var/etl/bin/includes/logging.php";
include ("/var/etl/bin/includes/database_functions.php");
include ("/var/etl/bin/includes/functions.php");

// $db = mysql_select_db("my_db", $con);
$db = mysql_select_db('ETL_Lookup', $con);
if (!$db) {echo "cant select database";}

//load paAPI
require_once('/var/www/html/magento/paGateway/reportAPI/invoiceReport.php');

//load Mage
require_once("/var/www/html/magento/app/Mage.php");
Mage::app();

$config_info = parse_ini_file('/var/www/html/magento/paGateway/paGateway.ini', true);

$uid = $config_info['login']['uid'];
$pass = $config_info['login']['pass'];

$today = date("Ymd");
$yesterday = date("Y-m-d", strtotime("yesterday"));

$nw = new invoiceReport();
$nw->setUser($uid,$pass);
$nw->setAction('getInvoiceInRange');//only supported function 
// $nw->setStartDate('20111101');//start date
$nw->setStartDate($yesterday);//start date
$nw->setEndDate($yesterday);//end date

echo "sending request \r\n";
$obj = $nw->sendRequest();

// print_r($nw->sendRequest()); //send request and get response in json format

$response = json_decode($obj, true); 

// var_dump($response);

echo "yo, request received \r\n";

if($response)
{
$i = 0;

	$responseDetail = $response['responseDetail'];
	//var_dump($responseDetail);

		foreach($responseDetail as $item)
		{
														
			$norm_partnumber = preg_replace("/[^\p{L}\p{N}]/u", '', $responseDetail[$i]['xsku']);
				//set up an insert statement
				
			$xqty = $responseDetail[$i]['xqty'];
			$xprice = $responseDetail[$i]['xprice'];
			$xmpline = $responseDetail[$i]['xmpline'];
			$xline = "'".$responseDetail[$i]['xline']."'";
			$xbran = $responseDetail[$i]['xbran'];
			$xfreight = $responseDetail[$i]['xfreight'];
			$xcore = $responseDetail[$i]['xcore'];
			$xord = $responseDetail[$i]['xord'];
			$mhind = "'".$responseDetail[$i]['mhind']."'";
			$year = substr($responseDetail[$i]['xdate'], 0, 4);
			$month = substr($responseDetail[$i]['xdate'], 4, 2);
			$day = substr($responseDetail[$i]['xdate'], 6, 2);
			$xdate = "'".$year."-".$month."-".$day."'";
			$xinvtotal = $responseDetail[$i]['xinvtotal'];
			$xinv = $responseDetail[$i]['xinv'];
			$xourpo = "'".$responseDetail[$i]['xourpo']."'";
			$xsku = "'".$norm_partnumber."'";

			$sql_insert = "";
			$sql_insert .= "INSERT INTO ETL_Lookup.pa_invoice(xqty, xprice, xmpline, xline, xbran, ";
			$sql_insert .= " xfreight, xcore, xord, mhind, xdate, ";
			$sql_insert .= " xinvtotal, xinv, xourpo, xsku )";
			$sql_insert .= "VALUES ($xqty, $xprice, $xmpline, $xline, $xbran, ";
			$sql_insert .= " $xfreight, $xcore, $xord, $mhind, $xdate, ";
			$sql_insert .= " $xinvtotal, $xinv, $xourpo, $xsku  )";
			
			
			//echo $sql_insert;
			//echo "\r\n";
			mysql_query($sql_insert);


			
			
			$i++;
		}
		
		echo "all done. ";
}
else
{ 
	echo "getting no response from paAPI"; 
}

















/*

   array(14) {
      ["xqty"]=>
      string(1) "1"
      ["xprice"]=>
      string(4) "4.48"
      ["xmpline"]=>
      string(1) "4"
      ["xline"]=>
      string(2) "SI"
      ["xbran"]=>
      string(1) "8"
      ["xfreight"]=>
      string(4) "0.00"
      ["xcore"]=>
      string(4) "0.00"
      ["xord"]=>
      string(5) "32684"
      ["mhind"]=>
      string(1) "I"
      ["xdate"]=>
      string(8) "20120501"
      ["xinvtotal"]=>
      string(5) "56.13"
      ["xinv"]=>
      string(6) "625297"
      ["xourpo"]=>
      string(7) "4281100"
      ["xsku"]=>
      string(3) "GV3"
    }


*/









?>