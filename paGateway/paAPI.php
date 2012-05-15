<?php
class paAPI{
    function paAPI(){$this->PoNumber = '';$this->accountNum='default';$this->client='Remote User';}
    function setUser($userName,$password){$this->userName=$userName;$this->password=$password;}
    function setAccountNum($accountNum){$this->accountNum=$accountNum;}
    function setClient($client){$this->client=$client;}
    function setPoNumber($PoNumber){$this->PoNumber=$PoNumber;}
    function setLineCode($x){$this->line_code=$x;}
    function setPartNumber($x){$this->part_number=$x;}
    function set_cust_name($setvar){$this->orderHeader['cust_name']=$setvar;}
    function set_ship_add1($setvar){$this->orderHeader['ship_add1']=$setvar;}
    function set_ship_add2($setvar){$this->orderHeader['ship_add2']=$setvar;}
    function set_ship_city($setvar){$this->orderHeader['ship_city']=$setvar;}
    function set_ship_state($setvar){$this->orderHeader['ship_state']=$setvar;}
    function set_ship_zip($setvar){$this->orderHeader['ship_zip']=$setvar;}
    function set_ship_meth($setvar){$this->orderHeader['ship_meth']=$setvar;}
    function set_shipping_cost($setvar){$this->orderHeader['shipping_cost']=$setvar;}
    function set_order_num($setvar){$this->orderHeader['order_num']=$setvar;}
    function set_ship_country($setvar){$this->orderHeader['ship_country']=$setvar;}
    function set_status($setvar){$this->orderHeader['status']=$setvar;}
    function set_shipping_details($setvar){$this->orderHeader['shipping_details']=$setvar;}
    function set_ship_alert($setvar){$this->orderHeader['ship_alert']=$setvar;}
    function set_special_services($setvar){$this->orderHeader['special_services']=$setvar;}
	function set_conf_number($setvar){$this->orderHeader['conf_number']=$setvar;}
	function set_paypal_number($setvar){$this->orderHeader['paypal_number']=$setvar;}
	function set_tax_amount($setvar){$this->orderHeader['tax_amount']=$setvar;}
	function set_ship_amount($setvar){$this->orderHeader['ship_amount']=$setvar;}
    function addItem($itemArr){$this->orderItems[]=$itemArr;}//array('line_code'=>$lineCode,'part_num'=>$partNum,'quantity'=>$qty,'cost'=>$cost);
    function setStartDate($x){$this->startDate=$x;}
    function setEndDate($x){$this->endDate=$x;}
    function checkOrderStatus($action='getOrderInformation')
    {
	//possible action values
	// getOrderDetail
	// getOrderInformation (default) if no action value this will be executed
	$order = array(
	       'userName'=>$this->userName,
	       'userPass'=>$this->password,
	       'accountNum'=>$this->accountNum,
	       'client'=>$this->client,
	       'PoNumber'=>$this->PoNumber,
		'action'=>$action
	       );
	$handle = fopen("http://eorders.panetny.com/api/checkOrderStatus.psp?reqData=".urlencode(json_encode($order)), "r");
	$contents='';
	while (!feof($handle)) {$contents .= fread($handle, 8192);}
	fclose($handle);
	return $contents;
    }
    function sendOrder($action='enterOrder')
    {
	//possible action values
	// enterOrder (default) if no action value this will be executed
	// checkStock
	if($action=='enterOrder')
	{
	    $order = array(
		   'userName'=>$this->userName,
		   'userPass'=>$this->password,
		   'accountNum'=>$this->accountNum,
		   'client'=>$this->client,
		   'orderHeader'=>$this->orderHeader,
		   'orderItems'=>$this->orderItems,
		   'action'=>$action
		   );
	}elseif($action=='checkStock')
	{
	    $order = array(
		   'userName'=>$this->userName,
		   'userPass'=>$this->password,
		   'accountNum'=>$this->accountNum,
		   'client'=>$this->client,
		   'line_code'=>$this->line_code,
		   'part_number'=>$this->part_number,
		   'action'=>$action
		   );
	}
        $handle = fopen("http://eorders.panetny.com/api/orderEntry.psp?reqData=".urlencode(json_encode($order)), "r");
        $contents='';
        while (!feof($handle)) {$contents .= fread($handle, 8192);}
        fclose($handle);
        return $contents;
    }
    function invoiceReport($action='getInvoiceInRange')
    {
	//possbile action values
	// getInvoiceInRange (default)
        $req = array(
               'userName'=>$this->userName,
               'userPass'=>$this->password,
	       'accountNum'=>$this->accountNum,
	       'client'=>$this->client,
               'action'=>$action,
               'startDate'=>$this->startDate,
               'endDate'=>$this->endDate
               );
        $handle = fopen("http://eorders.panetny.com/api/invoiceReport.psp?reqData=".urlencode(json_encode($req)), "r");
        $contents='';
        while (!feof($handle)) {$contents .= fread($handle, 8192);}
        fclose($handle);
        return $contents;
    }
}
?>