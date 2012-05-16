<?php
/**   /var/www/html/magento/app/code/local/Elite/Vaf/Block/Product/Result  **MERGED**
* Vehicle Fits Free Edition - Copyright (c) 2008-2010 by Ne8, LLC
* PROFESSIONAL IDENTIFICATION:
* "www.vehiclefits.com"
* PROMOTIONAL SLOGAN FOR AUTHOR'S PROFESSIONAL PRACTICE:
* "Automotive Ecommerce Provided By Ne8 llc"
*
* All Rights Reserved
* VEHICLE FITS ATTRIBUTION ASSURANCE LICENSE (adapted from the original OSI license)
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the conditions in license.txt are met
*/
class Elite_Vaf_Block_Product_Result_Group2 extends Elite_Vaf_Block_Product_Result
{
    function __construct()
    {
        parent::__construct();
        $this->setTemplate('vaf/group2/result.phtml');
    }
    
    // this is to null out functionality that adds 'group by' on the `product_id` field. SInce we only care about category IDs in group2, this is less rows.
    function doProductCategoryHashMap($select)
    {
    }
    
    function drawItem($category, $level=0, $last=false)
    {
        $html = '';
        if ( !$category->getIsActive() && !$this->categoryIsRoot($category) )
        {
            return $html;
        }
        
        if( !$this->categoryHasProductUnderIt($category) )
        {
            return;
        }

        $html .='<li class="item">' ; 
        //$html.= '<div class="product-name-container">';
            $html .= $this->renderParent($category);
        //$html .= '</div>';  
             $html .='</li>' ;
        return $html;
    }
    
    function renderParent($category)
    {
        $html = '<div class="product-name-container"><h2 class="product-name"><a href="' . $this->categoryUrl($category) . '">' . $this->htmlEscape($category->getName()) . '</a></h2></div>';
        
        $thisCat = $this->htmlEscape($this->imagePath($category));
        $thisCat = str_replace(",", "", $thisCat);
        
        $html .= '<div class="product-image"><img align="left" src="' . Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) .'categories/'. $thisCat . '.jpg" /></div>';
        
        if ($category->hasChildren())
        {
            //$html .= '<ul>';
            foreach ($this->getChildren($category) as $child)
            {
                $html .= $this->renderChild($child);
            }
            //$html .= '</ul>';
        }
        return $html;
    }
    
    function renderChild($category)
    {
        if( !$category->getIsActive() || !$this->categoryHasProductUnderIt($category) )
        {
            return '';
        }
        return  '<div class="product-shop"><a href="' . $this->baseUrl() . $category->getRequestPath() . '">' . $this->htmlEscape($category->getName()) . '</a></div>';
    }

    function imagePath($category)
    {
        $name = $category->getName();
        $name = str_replace(' ','-',$name);
        $name = strtolower($name);
        return $name;
    }
}