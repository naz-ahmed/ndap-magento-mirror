<?php
require_once('config.php');

$file = 'product-fitments-import.csv';

$writer = new Zend_Log_Writer_Stream('product-fitments-import.csv.log');
$log = new Zend_Log($writer);

$importer = new Elite_Vafimporter_Model_ProductFitments_CSV_Import($file);
$importer->setLog($log);

$importer->import();