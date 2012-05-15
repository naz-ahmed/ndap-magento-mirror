<?php 

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../app/Mage.php';
Mage::app()

/*
$_products = Mage::getModel('catalog/product')->getCollection()
   ->addAttributeToSelect(array('name', 'product_url', 'small_image'))
   ->addAttributeToFilter('sku', array('like' => 'UX%'))
    ->load();
*/
The above code would get a product collection, with each product having it's name, url, price and small image loaded in it's data array. The product collection would be filtered and contain only products that have an SKU starting with UX.

addAttributeToFilter Conditionals
Notice above, I used the LIKE operator? There are many more operators in SQL and addAttributeToFilter will accept them all. I include them below as well as a reference for you. Hopefully this will save you some time.

Equals: eq
?
1
$_products->addAttributeToFilter('status', array('eq' => 1));
Not Equals - neq
?
1
$_products->addAttributeToFilter('sku', array('neq' => 'test-product'));
Like - like
?
1
$_products->addAttributeToFilter('sku', array('like' => 'UX%'));
One thing to note about like is that you can include SQL wildcard characters such as the percent sign.

Not Like - nlike
?
1
$_products->addAttributeToFilter('sku', array('nlike' => 'err-prod%'));
In - in
?
1
$_products->addAttributeToFilter('id', array('in' => array(1,4,74,98)));
When using in, the value parameter accepts an array of values.

Not In - nin
?
1
$_products->addAttributeToFilter('id', array('nin' => array(1,4,74,98)));
NULL - null
?
1
$_products->addAttributeToFilter('description', 'null');
Not NULL - notnull
?
1
$_products->addAttributeToFilter('description', 'notnull');
Greater Than - gt
?
1
$_products->addAttributeToFilter('id', array('gt' => 5));
Less Than - lt
?
1
$_products->addAttributeToFilter('id', array('lt' => 5));
Greater Than or Equals To- gteq
?
1
$_products->addAttributeToFilter('id', array('gteq' => 5));
Less Than or Equals To - lteq
?
1
$_products->addAttributeToFilter('id', array('lteq' => 5));
addFieldToFilter()
As far as I'm aware, addAttributeToFilter only works with products in Magento. When I first found out this fact I was not only shocked, I was worried! I thought that without it, I would have to custom craft all of my SQL queries. After scouring the Magento core code one night, I found addFieldToFilter(). This functions works in the exact same way and takes the same paramters, however it works on ALL collections and not just on products!

Debugging The SQL Query
There are two ways to debug the query being executed when loading a collection in Magento.

?
1
2
3
4
5
6
// Method 1
Mage::getModel('catalog/product')->getCollection()->load(true);
 
// Method 2 (Quicker, Recommended)
$collection = Mage::getModel('catalog/product')->getCollection();
echo $collection->getSelect();
Both method 1 and method 2 will print out the query but both will do it in slightly different ways. Method 1 prints the query out as well as loading the products while method 2 will just convert the query object to a string (ie. will print out the SQL). The second method is definitely better as it will be executed much quicker but I include them both here for reference.

On a side note, I will soon be writing an article on the getSelect() function as it opens up a door in Magento Collections that gives them (and you) true power!


This post was posted in	 General
     