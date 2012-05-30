<?php

//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL); 

require_once 'std.table.class.inc';
require_once 'db.inc';
require_once 'error.inc';


include 'PaInvoice.class.inc';
$dbobject = new PaInvoice;

// if $where is null then all rows will be retrieved 
//$where = "column='value'";
$where = "";
// user may specify a particular page to be displayed
/*
if (isset($_GET['pageno'])) {
   $dbobject->setPageno($_GET['pageno']);
} // if
*/

$data = $dbobject->getData($where);
$errors = $dbobject->getErrors();
if (!empty($errors)) {
   // deal with error message(s)
} // if

foreach ($data as $row) 
{

	var_dump($row);
	//echo array_keys($data);
    foreach ($row as $field => $value) 
    {
    		//var_dump($value); 
    		//var_dump($field); 
    } // foreach
} // foreach

?>