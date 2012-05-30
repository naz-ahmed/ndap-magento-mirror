<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 


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
$nw->setStartDate('20120501');//start date
$nw->setEndDate('20120502');//end date

$obj = $nw->sendRequest();

// print_r($nw->sendRequest()); //send request and get response in json format

$response = json_decode($obj, true); 

// var_dump($response);


echo "<html><head></head><body style='font-family: Arial, sans-serif; font-size: 12px;'>";
echo "<table cellpadding=5 cellspacing=0 border=1>";

echo "<tr style='font-weight:bold; background-color:1D4E51; color:#f1f1f1;'>";
echo "<td>xqty</td>";
echo "<td>xprice</td>";
echo "<td>xmpline</td>";
echo "<td>xline</td>";
echo "<td>xbran</td>";
echo "<td>xfreight</td>";
echo "<td>xcore</td>";
echo "<td>xord</td>";
echo "<td>mhind</td>";
echo "<td>xdate</td>";
echo "<td>xinvtotal</td>";
echo "<td>xinv</td>";
echo "<td>xourpo</td>";
echo "<td>xsku</td>";
echo "<td>magento ppu</td>";
echo "<td>per part margin</td>";

echo "</tr>";



if($response)
{
$i = 0;

	$responseDetail = $response['responseDetail'];
	//var_dump($responseDetail);
		foreach($responseDetail as $item)
		{
			// var_dump($item);
			echo "<tr>";
			
			foreach($item as $detail)
			{
			
				echo "<td>$detail</td>";
				//print_r($detail."-----");
				
			}
			
			$orderId = $responseDetail[$i]['xourpo'];
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			
			//echo "order = ".var_dump($order);
			
			$items = $order->getAllItems();
			//echo "items = ".var_dump($items);
			
			$itemcount = count($items);
			
			echo "<td>";
			$c=0;
			foreach($items as $itemId=>$item)
			{
				//if($c == 0) {echo "<td>";}
				$linecode = strtoupper(substr($item->getSku(),0,2));
				$partnumber = substr($item->getSku(),2);
				$partnumber = substr($partnumber,0,strpos($partnumber, '.'));
				
				$norm_partnumber = preg_replace("/[^\p{L}\p{N}]/u", '', $responseDetail[$i]['xsku']);
				if($norm_partnumber == $partnumber)
				{
					echo $item->getPrice();
				}
				
				
				
				//if($c == 0) { echo "</td>"; }
				
				$c++;
			}
			echo "</td>";
			
			// calculate margin
			echo "<td>";
			foreach($items as $itemId=>$item)
			{
				$linecode = strtoupper(substr($item->getSku(),0,2));
				$partnumber = substr($item->getSku(),2);
				$partnumber = substr($partnumber,0,strpos($partnumber, '.'));
				
				$norm_partnumber = preg_replace("/[^\p{L}\p{N}]/u", '', $responseDetail[$i]['xsku']);
				if($norm_partnumber == $partnumber)
				{
					echo $item->getPrice() - $responseDetail[$i]['xprice'];
				}

			
			}
			echo "</td>";
						
			echo "</tr>";
			$i++;
		}
		
		
}
else
{ 
	echo "getting no response from paAPI"; 
}



echo "</table>";
echo "</body></html>";














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