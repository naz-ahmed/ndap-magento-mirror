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

require_once 'classes/std.table.class.inc';
require_once 'classes/db.inc';
require_once 'classes/error.inc';
include 'classes/PaInvoice.class.inc';
include 'classes/PaInvoiceByPOandInv.class.inc';

function makeDate($strDate)
{
// 012-/0-1/' AND '012-/0-3/

	$year = substr($strDate, 6, 4);
	$month = substr($strDate, 0, 2);
	$day = substr($strDate, 3, 2);
	
	if(strlen($month) == 1) { $month = "0".$month; }
	if(strlen($day) == 1) { $day = "0".$day; }
	
	$formatted_date = $year."-".$month."-".$day;
	return($formatted_date);

}
$pagename = $_SERVER['REQUEST_URI'];

$where = "";

$groupby = "xourpo, xinv ";

$orderby = "xdate, xourpo, xinv";

$oInvoiceList = new PaInvoiceByPOandInv;
$data = $oInvoiceList->getData($where, $groupby, $orderby);
$errors = $oInvoiceList->getErrors();
if (!empty($errors)) {
   // deal with error message(s)
} 

$oInvoiceParts = new PaInvoice;


if($_POST)
{
		$date_from = makeDate($_POST['date-from']);
		$date_to = makeDate($_POST['date-to']);
		$orderId = $_POST['orderId'];
		//TODO: make sure to is not before from - can be jquery validation
		
		
		
		$where = "xdate between '".$date_from."' AND '".$date_to."'";
				
		//header("Location: http://localhost/var/etl/bin/categories/tool/assign1.html");
		//echo "submitted.";
		
		$data = $oInvoiceList->getData($where, $groupby, $orderby);
		$errors = $oInvoiceList->getErrors();
		if (!empty($errors)) {
		   // deal with error message(s)
		}
	
}

if($_GET)
{
	echo "yo mama: ". $_GET['po'];
	$where = "xourpo = '".$_GET['po']."'";
	$groupby = "";
	$data = NULL; 
	$data = $oInvoiceParts->getData($where, $groupby, $orderby);
	$errors = $oInvoiceParts->getErrors();
	if (!empty($errors)) {
	   // deal with error message(s)
	}
}


echo $where;






echo "<html><head>";
echo '<link type="text/css" href="includes/jquery-ui-1.8.19.custom/css/smoothness/jquery-ui-1.8.19.custom.css" rel="Stylesheet" />';
echo '<script type="text/javascript" src="includes/jquery-ui-1.8.19.custom/js/jquery-1.7.2.min.js"></script>';
echo '<script type="text/javascript" src="includes/jquery-ui-1.8.19.custom/js/jquery-ui-1.8.19.custom.min.js"></script>';
echo '<script type="text/javascript" src="report.js"></script>';
echo "</head><body style='font-family: Arial, sans-serif; font-size: 12px;'>";


echo '<div><form id="form1" method="post" action="report_test_o.php">';
echo '<div id="select-date">';
echo '<label for="date-from">Date from: </label><input id="date-from" name="date-from" type="text" value="" />';
echo '<label for="date-to">Date to: </label><input id="date-to" name="date-to" type="text" value="" />';
echo '<input id="btn_reset" type="reset" value="reset" name="btn_reset"/>';
echo '<input id="btn_submit" type="submit" value="SUBMIT" name="btn_submit"/>';
echo '</div>';
echo '<div id="select-order" style="display:none;">';
echo '<label for="orderId">Order Id</label>';
echo '<input id="orderId" name="orderId" type="text" value="" /> ';
echo '</div>';
echo "</form></div>";








echo "<table cellpadding=5 cellspacing=0 border=1>";

echo "<tr style='font-weight:bold; background-color:1D4E51; color:#f1f1f1;'>";
echo "<td>xdate</td>";
echo "<td>xourpo</td>";
echo "<td>mhind</td>";
echo "<td>magento order w/o ship</td>";
echo "<td>xinvtotal + core</td>";
echo "<td>Magento Ship Cost</td>";
echo "<td>xfreight</td>";
echo "<td>Magento Order Total</td>";
echo "<td>xinvtotal + xcore + xfreight</td>";

 echo "<td>magento ppu</td>";
 echo "<td>per part margin</td>";
 echo "<td>mqty</td>";
  echo "<td>total margin</td>";

echo "</tr>";



if($data)
{
$i = 0;

		foreach($data as $row) 
		{
			// var_dump($item);
			echo "<tr>";
			
			/*
			foreach($row as $field => $value) 
			{
			
				if($field == "xourpo")
				{
					echo '<td><a href="'.$pagename.'?po='.$value.'">'.$value.'</a></td>';
				}
				else
				{
					echo "<td>$value</td>";
					//print_r($detail."-----");
				}
				
			}
			*/
			
			echo "<td>".$row['xdate']."</td>";
			echo "<td>".$row['xourpo']."</td>";
			echo "<td>".$row['mhind']."</td>";
			
			$orderId = $row['xourpo'];
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			
			$grandTotal = $order->getGrandTotal();
			$shipAmount = $order->getShippingAmount();
			
			$amount = $grandTotal - $shipAmount;
			echo "<td>".$amount."</td>";
			
			$total = $row['Total'];
			$core = $row['core cost'];
			$totalPlusCore = $total + $core;
			$freight = $row['xfreight'];
			
			echo "<td>".$totalPlusCore."</td>";
			echo "<td>".$shipAmount."</td>";
			echo "<td>".$freight."</td>";
			
			echo "<td>".$grandTotal."</td>";
			
			$invTotalPlusCorePlusFreight = $total + $core + $freight;
			echo "<td>".$invTotalPlusCorePlusFreight."</td>";
			
			//echo "order = ".var_dump($order);
			
			$items = $order->getAllItems();
			//echo "items = ".var_dump($items);
			
			$itemcount = count($items);
			
			$c=0;
			foreach($items as $itemId=>$item)
			{

				$linecode = strtoupper(substr($item->getSku(),0,2));
				$partnumber = substr($item->getSku(),2);
				$partnumber = substr($partnumber,0,strpos($partnumber, '.'));
				
				// $norm_partnumber = preg_replace("/[^\p{L}\p{N}]/u", '', $responseDetail[$i]['xsku']);
				if($row['xsku'] == $partnumber)
				{
					echo "<td>";
					echo $item->getPrice();
					echo "</td>";
					echo "<td>";
					echo $item->getPrice() - $row['xprice'];
					echo "</td>";
					
					echo "<td>";
					echo number_format($item->getQtyInvoiced());
					echo "</td>";
					
					echo "<td>";
					echo ($item->getPrice() - $row['xprice']) * $item->getQtyInvoiced();
					echo "</td>";
				}
				
				
				
				//if($c == 0) { echo "</td>"; }
				
				$c++;
			}

						
			echo "</tr>";
			$i++;
		}
		
		
}
else
{ 
	echo "no object created"; 
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