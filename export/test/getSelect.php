<?php 

// Error reporting
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once '/var/www/html/magento/app/Mage.php';

Mage::app();

$collection= Mage::getModel('catalog/product')->getCollection()
->addAttributeToFilter('entity_id',
array('in'=> array(22333) )
)
->addAttributeToSelect( '*' )
->load();
foreach($collection as $_prod) {
echo "<pre>";
var_dump($_prod->getData());
echo "</pre>\n";
}

/* Load a collection with all products */
/*
$collection = Mage::getModel('catalog/product')->getCollection()
->load();
foreach($collection as $product) {
var_dump($product->getData());
}
*/

/* Load a collection with all products and all products' attributes */

/*
$collection = Mage::getModel('catalog/product')->getCollection()
->addAttributeToSelect( '*' )
->load();
foreach($collection as $product) {
var_dump($product->getData());
}
*/

/*
The collection is a powerfull tool that allows you to filter or sort the list according to EAV attributes using the method 
addAttributeToFilter():
*/
 
/*
$collection= Mage::getModel('catalog/product')->getCollection()
->addAttributeToFilter('entity_id',
array('in'=> array(16,17,19,20) )
)
->load();
foreach($collection as $_prod) {
var_dump($_prod->getData());
}
*/

/* The following example show how to filter on a particular attribute. */

/*
$collection= Mage::getModel('catalog/product')->getCollection()
->addAttributeToFilter('entity_id',
array('in'=> array(16,17,19,20) )
)
->load();
foreach($collection as $_prod) {
var_dump($_prod->getData());
}
*/

/*
The complete list of short SQL conditions allowed can be found in lib/Varien/Data/Collection/Db.php under method _getConditionSql

You can also specify an amount of product you want to display, using methods setPageSize($size) and setCurPage($page):
*/

/* 
$collection= Mage::getModel('catalog/product')->getCollection()
->addAttributeToSelect('*')
->setPageSize(5)
->setCurPage(1)
->load();
foreach($collection as $_prod) {
echo $_prod->getName();
}
*/

/* On other way to do this is by using limit() method: */

/* 
$collection=Mage::getModel('catalog/product')->getCollection()
->addAttributeToSelect('*');
$collection->getSelect()->limit(5);
foreach($collection as $item){
echo $item->getName();
}
*/

?>
 
