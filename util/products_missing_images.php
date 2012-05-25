<?php  
// Error reporting
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once '/var/www/html/magento/app/Mage.php';
Mage::app();

//this builds a collection that's analagous to 
//select * from products where image = 'no_selection'
$products = Mage::getModel('catalog/product')
->getCollection()
->addAttributeToSelect('*')
->addAttributeToFilter('image', 'no_selection');

foreach($products as $product)
{
    echo  $product->getSku() . " has no image \n<br />\n";
    //var_dump($product->getData()); //uncomment to see all product attributes
                                     //remove ->addAttributeToFilter('image', 'no_selection');
                                     //from above to see all images and get an idea of
                                     //the things you may query for
}  
?>     