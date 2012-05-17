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


define('SAVE_FEED_LOCATION','/var/www/html/magento/export/');
define('SAVE_FEED_FILENAME','google_base'); // ".txt" estension will be added
define('SAVE_FEED_FILE_EXTENSION','.txt');
set_time_limit(0);
require_once '/var/www/html/magento/app/Mage.php';
Mage::app();

try 
{
        $file_index=1;
	$handle = fopen(SAVE_FEED_LOCATION.SAVE_FEED_FILENAME.$file_index.SAVE_FEED_FILE_EXTENSION, 'w');
	$heading = 			array('id','title','description','google_product_category','product_type','link','image_link','condition','availability','price','brand','mpn','weight');
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
	
        $counter=0;
	foreach ($products as $product)
	{
                $counter++;
		$product_data = array();
		
		//sku
		$product_data['sku'] = $product->getSku();
		
		//title
		$product_data['title'] = $product->getName();
		
		//description	
		$product_data["description"] = $product->getGoogleFitmentText();
		
		//google_product_category
		$thecat = "Vehicles & Parts > Automotive Parts";
		$product_data["google_product_category"] = $thecat;
		
		//product_type
		foreach ($product->getCategoryIds() as $_categoryId)
		{
			$category = Mage::getModel('catalog/category')->load($_categoryId);
			$product_data['product_type'] .= $category->getName().', ';
		}
		$product_data['product_type'] = rtrim($product_data['product_type'],', ');
		
		
		//link
		$product_data['Deeplink'] = $product->getProductUrl();
		
		//image_link
		$product_data['image_link'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
		
		if($product->getImage() != NULL)
		{
			$product_data['image_link'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
		}
		else
		{
			$product_data['image_link'] = "";
		}
		
		//additional_image_link		
/*
		$_images = Mage::getModel('catalog/product')->load($product->getEntityId())->getMediaGalleryImages();

*/

/*
		echo "<pre>";
		print_r($_images);
		echo "</pre>\n";
*/
		

/*
		for($i=0; $i<4; $i++)
		{
			
			if($_images != NULL)
        	{
        		$field_name = 'additional_image_link'.'_'.$i+1;
                $product_data[$field_name]=$_images->url;
        	}
        	else
        	{
        		$field_name = 'additional_image_link'.'_'.$i+1;
                $product_data[$field_name] = "";
        	
        	}
		}
*/
		
/*	          
        $i=1;
        $num = 0;
        foreach($_images as $_image)
        {
        	if($i==1) { continue; }
        	$field_name = 'additional_image_link'.'_'.$i;
        	$product_data[$field_name]=$_image->url;
        	$i++;
        	$num = $i;
     	}
     	
     	if( ($num-1) < 4 )  //four is our number of fields
     	{
     		//how much less than 4 are we
     		$image_count = $num - 1;
     		$fakes_needed = 4 - $image_count;
     		
     		for($x=0; $x<$fakes_needed; $x++; )
     		{
     			$field_name = 'additional_image_link'.'_'.$x;
        		$product_data[$field_name]="";	
     		
     		}
     	}
*/
                  	
		//condition
		$product_data['condition'] = "new";
		
		//availability
		$stockItem = $product->getStockItem();
		if($stockItem->getIsInStock())
		{
			$product_data['availability'] = "in stock";
		}
		else
		{
			$product_data['availability'] = "out of stock";
		}
		
		//price
		if($product->getSpecialPrice() != NULL)
		{
			$product_data['price'] = round($product->getSpecialPrice(),2);
		}
		else
		{
			$product_data['price'] = round($product->getPrice(),2);
		}
		
		//brand
		$product_data["brand"] = $product->getManufacturerName();
		
		//mpn
		$product_data["mpn"] = $product->getPartNumber();
		
		//weight
		$product_data['weight'] = $product->getWeight();
		
		echo "Added Successfully ".$product->getName()." (".$product_data["sku"].")<br/>";
		
		foreach ($product_data as $k=>$val)
		{
			$bad=array('"',"\r\n","\n","\r","\t");
			$good=array(""," "," "," ","");
			$product_data[$k] = '"'.str_replace($bad,$good,$val).'"';
		}
		
		$feed_line = implode("\t", $product_data)."\r\n";
		fwrite($handle, $feed_line);
		fflush($handle);
                if ($counter==200000){
                    $counter=0;
                    $file_index++;
                    fclose($handle);
                    $handle = fopen(SAVE_FEED_LOCATION.SAVE_FEED_FILENAME.$file_index.SAVE_FEED_FILE_EXTENSION, 'w');
                    $heading = 			array('id','title','description','google_product_category','product_type','link','image_link','condition','availability','price','brand','mpn','weight');
                    $feed_line= implode("\t", $heading)."\r\n";
                    fwrite($handle, $feed_line);
	
                }
		
	}
	
	fclose($handle);
	
}
catch(Exception $e){
    die($e->getMessage());
}
