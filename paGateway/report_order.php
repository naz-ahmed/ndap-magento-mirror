<?php
//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 


//load Mage
require_once("/var/www/html/magento/app/Mage.php");
Mage::app();

$config_info = parse_ini_file('/var/www/html/magento/paGateway/paGateway.ini', true);

require_once 'classes/std.table.class.inc';
require_once 'classes/db.inc';
require_once 'classes/error.inc';
include 'classes/PaInvoice.class.inc';


$dbobject = new PaInvoice;


$data = $dbobject->getData($where, $orderby);
$errors = $dbobject->getErrors();
if (!empty($errors)) {
   // deal with error message(s)
} 








?>