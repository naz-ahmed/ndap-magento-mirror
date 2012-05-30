<?php
require_once('invoiceReport.php');
$nw = new invoiceReport();
$nw->setUser('username','password');
$nw->setAction('getInvoiceInRange');//only supported function 
$nw->setStartDate('20090601');//start date
$nw->setEndDate('20090601');//end date
print_r($nw->sendRequest());//send request and get response in json format
?>
