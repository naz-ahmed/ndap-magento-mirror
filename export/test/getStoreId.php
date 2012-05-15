<?php 

//Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../app/Mage.php';
Mage::app();


echo "<table border=1> <tr> <td>Store Id</td> <td>Store Code</td> <td>Store Name</td> </tr>";

$allStores = Mage::app()->getStores();
foreach ($allStores as $_eachStoreId => $val) 
{
$_storeCode = Mage::app()->getStore($_eachStoreId)->getCode();
$_storeName = Mage::app()->getStore($_eachStoreId)->getName();
$_storeId = Mage::app()->getStore($_eachStoreId)->getId();
echo "<tr> <td> $_storeId</td>";
echo "<td>$_storeCode</td>"; 
echo "<td>$_storeName</td></tr>";
}
echo "</table>"

?>
 