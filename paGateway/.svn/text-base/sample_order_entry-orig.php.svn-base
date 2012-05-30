<?
require_once('paAPI.php');
$order = new paAPI();//construct paAPI class
$order->setUser('username','pass');//api account username and pass
$order->setClient("Sample Client v1");//passes the name of the connection client *optional*
$order->set_cust_name($CustomerName);//ship to name
$order->set_ship_add1($Address1);//ship to address1
$order->set_ship_add2($Address2);//ship to address2
$order->set_ship_city($City);//ship to city
$order->set_ship_state($State);//ship to state
$order->set_ship_zip($PostalCode);//ship to postal code
$order->set_ship_meth($ShippingMethod2);//ship method FDG=Ground, FD2=2nd Day, FDO=Overnight
$order->set_order_num($PONumber);//customer PO#
$order->set_ship_country('US');//ship to country code
$order->set_conf_number($ConfNumber);//confirmation from DST
$order->set_paypal_number($BTN);//paypal reference number
$order->set_tax_amount($TAX);//tax on order
$order->set_ship_amount($HSC);//calculated shipping cost of order


///pass line item information
$order->addItem(array('line_code'=>$lc1,'part_num'=>$part1,'quantity'=>$qty1,'cost'=>$cost1));
$order->addItem(array('line_code'=>$lc2,'part_num'=>$part2,'quantity'=>$qty2,'cost'=>$cost2));
$order->addItem(array('line_code'=>$lc3,'part_num'=>$part3,'quantity'=>$qty3,'cost'=>$cost3));
$order->set_status('test');//takes either ('test') or ('live')
$ret = json_decode($order->sendOrder('enterOrder'));//order entry response in json string
?>
