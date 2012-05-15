<?php  
// Error reporting
ini_set('display_errors', 'On');
error_reporting(E_ALL);

// Disable use of the deflate header
@ini_set('zlib.output_compression', 0);
// Flushes the write buffers 
@ini_set('implicit_flush', 1);

//Flush (send) the output buffer
function flush_buffers()
{
	ob_end_flush();
	ob_flush();
	flush();
	ob_start();
}


define('SAVE_FEED_LOCATION','/var/www/html/magento/export/bing_shopping.txt');
set_time_limit(0);
require_once '/var/www/html/magento/app/Mage.php';
Mage::app();

try 
{
	$handle = fopen(SAVE_FEED_LOCATION, 'w');
	$heading = 			array('MerchantProductID','Title','Brand','MPN','UPC','ISBN','SKU','ProductURL','Price','Availability','Description','ImageURL','Shipping','MerchantCategory','ShippingWeight','Condition','B_Category','Tax');
	$feed_line= implode("\t", $heading)."\r\n";
	fwrite($handle, $feed_line);
	
	$products = Mage::getModel('catalog/product')->getCollection();
	//$products->addAttributeToFilter('status', 1);
	//$products->addAttributeToFilter('visibility', 4);
	//$products->addAttributeToFilter('price', array('gt' => 1000));
	//$products->addAttributeToFilter('entity_id',array('in' => array(22333)));
	$products->addAttributeToSelect('sku');
	$products->addAttributeToSelect('name');
	$products->addAttributeToSelect('google_fitment_text');
	$products->addAttributeToSelect('category_ids');
	$products->addAttributeToSelect('url');
	$products->addAttributeToSelect('image');
	$products->addAttributeToSelect('price');
	$products->addAttributeToSelect('special_price');
	$products->addAttributeToSelect('manufacturer_name');
	$products->addAttributeToSelect('part_number');
	$products->addAttributeToSelect('weight');
	
	$count = count($products);
	echo "Writing $count Products<br/>";
	
	foreach ($products as $product)
	{
		$product_data = array();
		
		//MerchantProductID
		$product_data['MerchantProductID'] = $product->getSku();
		
		//Title
		$product_data['Title'] = $product->getName();
		
		//Brand
		$product_data["Brand"] = $product->getManufacturerName();
		
		//MPN
		$product_data["MPN"] = $product->getPartNumber();
			
		//UPC
		$product_data["UPC"] = "";

		//ISBN
		$product_data["ISBN"] = "";
		
		//SKU
		$product_data['SKU'] = $product->getSku();

		//ProductURL
		$product_data['ProductURL'] = $product->getProductUrl();

		//Price
		if($product->getSpecialPrice() != NULL)
		{
			$product_data['Price'] = round($product->getSpecialPrice(),2);
		}
		else
		{
			$product_data['Price'] = round($product->getPrice(),2);
		}
		
		//Availability
		$stockItem = $product->getStockItem();
		if($stockItem->getIsInStock())
		{
			$product_data['Availability'] = "in stock";
		}
		else
		{
			$product_data['Availability'] = "out of stock";
		}
		
		//Description	
		$product_data["Description"] = $product->getGoogleFitmentText();

		//ImageURL
		$product_data['ImageURL'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();

		if($product->getImage() != NULL)
		{
			$product_data['ImageURL'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
		}
		else
		{
			$product_data['ImageURL'] = "";
		}
		
		//Shipping
		$product_data["Shipping"] = "";
				
		//MerchantCategory
		foreach ($product->getCategoryIds() as $_categoryId)
		{
			$category = Mage::getModel('catalog/category')->load($_categoryId);
			$product_data['MerchantCategory'] = $category->getName().', ';
		}
		$product_data['MerchantCategory'] = rtrim($product_data['product_type'],', ');
		
		//ShippingWeight
		$product_data['ShippingWeight'] = $product->getWeight();
		
		//Condition
		$product_data['Condition'] = "New";
		
		//B_Category
		$thecat = "Car & Garage";
		$product_data["B_Category"] = $thecat;
		
		//Tax
		$product_data["Tax"] = "";

		echo "Added Successfully ".$product->getName()." (".$product_data["sku"].")<br/>";
		
/*
		foreach ($product_data as $k=>$val)
		{
			$bad=array('"',"\r\n","\n","\r","\t");
			$good=array(""," "," "," ","");
			$product_data[$k] = '"'.str_replace($bad,$good,$val).'"';
		}
*/
		
		$feed_line = implode("\t", $product_data)."\r\n";
		fwrite($handle, $feed_line);
		fflush($handle);
		
	}
	
	fclose($handle);
	
}
catch(Exception $e){
die($e->getMessage());
}
