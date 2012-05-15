<?php 

// Error reporting
ini_set('display_errors', 'On');
error_reporting(E_ALL);

require_once '/var/www/html/magento/app/Mage.php';

Mage::app();

                                                  //529040 22333
$_images = Mage::getModel('catalog/product')->load(22333)->getMediaGalleryImages();
print_r($_images);
echo '<br/><br/><br/>';
if($_images){            
            foreach($_images as $_image){ 
               //print_r($_image);
                echo $_image->url.'<br/>';
                
                                //echo $this->helper('catalog/image')->init($_product, 'thumbnail', $_image->getFile())->resize(200, 130);      
                                //echo $this->htmlEscape($_image->getLabel());  
   }
}



echo " done ";
?>
