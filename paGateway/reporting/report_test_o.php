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

require_once '../classes/std.table.class.inc';
require_once '../classes/db.inc';
require_once '../classes/error.inc';
include '../classes/PaInvoice.class.inc';
include '../classes/PaInvoiceByPOandInv.class.inc';

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
$detailPage = "report_test_o_detail.php";

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
		
		if($_POST['selMhind'])
		{
			$mhind = $_POST['selMhind'];
			if($mhind != "B")
			{
				$where .= " AND mhind = '".$mhind."'";
			}
		}
				
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
	//echo "yo mama: ". $_GET['po'];
	$where = "xourpo = '".$_GET['po']."'";
	$groupby = "";
	$data = NULL; 
	$data = $oInvoiceParts->getData($where, $groupby, $orderby);
	$errors = $oInvoiceParts->getErrors();
	if (!empty($errors)) {
	   // deal with error message(s)
	}
	$otherCols = TRUE;
}

//DEBUG
//echo $where;






echo "<html><head>";
echo '<link type="text/css" href="../includes/jquery-ui-1.8.19.custom/css/smoothness/jquery-ui-1.8.19.custom.css" rel="Stylesheet" />';
echo '<script type="text/javascript" src="../includes/jquery-ui-1.8.19.custom/js/jquery-1.7.2.min.js"></script>';
echo '<script type="text/javascript" src="../includes/jquery-ui-1.8.19.custom/js/jquery-ui-1.8.19.custom.min.js"></script>';
echo '<script type="text/javascript" src="../includes/waypoints.js"></script>';

echo '<link rel="stylesheet" href="report-styles.css">';
echo '<script src="../includes/modernizr.custom.js"></script>';

echo "</head><body style='font-family: Arial, sans-serif; font-size: 12px;'>";


echo '<div><form id="form1" method="post" action="report_test_o.php">';
echo '<div id="select-date">';
echo '<label for="date-from">Date from: </label><input id="date-from" name="date-from" type="text" value="" />';
echo '<label for="date-to">Date to: </label><input id="date-to" name="date-to" type="text" value="" />';
echo '<label for="selMhind">Show: </label>';
echo '<select id="selMhind" name="selMhind">';
echo '<option value="I">Invoice</option>';
echo '<option value="C">Credit</option>';
echo '<option value="B">Both</option>';
echo '</select>';
// echo '<br/>';
echo '<input id="btn_reset" type="reset" value="reset" name="btn_reset"/>';
echo '<input id="btn_submit" type="submit" value="SUBMIT" name="btn_submit"/>';
echo '</div>';
echo '<div id="select-order" style="display:none;">';
echo '<label for="orderId">Order Id</label>';
echo '<input id="orderId" name="orderId" type="text" value="" /> ';
echo '</div>';
echo "</form></div>";




echo '<div id="wrapper">';

// echo '<div id="main-nav-holder"><nav id="main-nav"><ul><li>Section 1</li><li>Section 2</li><li>Section 3</li>				<li>Section 4</li><li>Section 5</li></ul></nav></div>';

echo '<table id="main-nav-holder">';

echo '<thead><tr  id="main-nav">';


echo "<th>xdate</th>";
echo "<th>xourpo</th>";
echo "<th>mhind</th>";
echo "<th>magento order w/o ship</th>";
echo "<th>xinvtotal + core</th>";
echo "<th>Magento Ship Cost</th>";
echo "<th>xfreight</th>";
echo "<th>Magento Order Total</th>";
echo "<th>xinvtotal + xcore + xfreight</th>";
echo "<th>Margin $</th>";
echo "<th>Margin $</th>";

/*
 echo "<td>magento ppu</td>";
 echo "<td>per part margin</td>";
 echo "<td>mqty</td>";
  echo "<td>total margin</td>";
*/ 

  

echo "</tr></thead><tbody>";



if($data)
{
$i = 0;

		foreach($data as $row) 
		{
			// var_dump($item);
			echo "<tr>";
			
			echo "<td>".$row['xdate']."</td>";
			if($otherCols == TRUE)
			{
				echo "<td>".$row['xourpo']."</td>";
			}
			else
			{
				echo '<td><a href="'.$detailPage.'?po='.$row['xourpo'].'">'.$row['xourpo'].'</a></td>';
			}
			echo "<td>".$row['mhind']."</td>";
			
			$orderId = $row['xourpo'];
			$order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
			
			$grandTotal = $order->getGrandTotal();
			$shipAmount = $order->getShippingAmount();
			
			$amount = $grandTotal - $shipAmount;
			
			if($amount == NULL)
			{
				echo "<td>&nbsp; - &nbsp; </td>";
			}
			else 
			{
				echo "<td>".$amount."</td>";
			}
			
			$total = $row['Total'];
			$core = $row['core cost'];
			$totalPlusCore = $total + $core;
			$freight = $row['xfreight'];
			
				
			echo "<td>".$totalPlusCore."</td>";
			if($shipAmount == NULL)
			{
				echo "<td>&nbsp; - &nbsp; </td>";
			}
			else
			{
				echo "<td>".$shipAmount."</td>";
			}
			
			echo "<td>".$freight."</td>";
			
			if($grandTotal == NULL)
			{
				echo "<td>&nbsp; - &nbsp; </td>";
			}
			else
			{
				echo "<td>".$grandTotal."</td>";
			}
			
			$invTotalPlusCorePlusFreight = $total + $core + $freight;
			echo "<td>".$invTotalPlusCorePlusFreight."</td>";
			
			//margin $ and margin %
			// Gross margin Percentage = (Revenue - Cost of goods sold) / Revenue *100%
			// Gross margin = (Revenue - Cost of goods sold) / Revenue
			
			if($grandTotal == NULL)
			{
				echo "<td>&nbsp; - &nbsp; </td>";
				echo "<td>&nbsp; - &nbsp; </td>";
			}
			else
			{
				//$marginDollars = $grandTotal - $invTotalPlusCorePlusFreight;
				$tax = $order->getBaseTaxAmount();
				$revenue = $grandTotal - $tax;
				
				//cogs can't be revenue minus. cogs is just sum of total cost, core, and freight
				//$cogs = $revenue - $total - $core - $freight;
				$cogs = $total + $core + $freight;
				
				
				// $grossMargin = ($revenue - $cogs) / $revenue;
				$grossMargin = ($revenue - $cogs);
				
				// var_dump($order);
				echo "<td>".$grossMargin."</td>";
				
				$grossMarginPercent = $grossMargin / ($revenue / 100);
				
				echo "<td>".$grossMarginPercent."</td>";
			}
			
			
			
			//echo "order = ".var_dump($order);
			
			$items = $order->getAllItems();
			//echo "items = ".var_dump($items);
			
			$itemcount = count($items);
						
			echo "</tr>";
			$i++;
		}
		
		
}
else
{ 
	echo "no object created"; 
}



echo "</tbody></table>";
echo '<footer><nav><ul><li><a class="top" href="'.$pagename.'" title="Back to top">Top</a></li></ul></nav></footer>';
echo "</div>";

echo '<script type="text/javascript" src="../report.js"></script>';

echo '<script type="text/javascript">';
echo '$(document).ready(function() { ';
echo "$('.top').addClass('hidden');";
echo "$.waypoints.settings.scrollThrottle = 30;";

echo "$('#wrapper').waypoint(function(event, direction) {";
echo "$('.top').toggleClass('hidden', direction === \"up\");";
echo "}, {";
echo "offset: '-100%'";
echo "}).find('#main-nav-holder').waypoint(function(event, direction) {";
echo "$(this).parent().toggleClass('sticky', direction === \"down\");";
echo "event.stopPropagation();";
echo "});";



echo '  });';
echo '</script>';

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