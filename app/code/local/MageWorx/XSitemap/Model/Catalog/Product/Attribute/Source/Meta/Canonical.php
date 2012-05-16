<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @copyright  Copyright (c) 2012 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * SEO Suite extension
 *
 * @category   MageWorx
 * @package    MageWorx_SeoSuite
 * @author     MageWorx Dev Team
 */

class MageWorx_SeoSuite_Model_Catalog_Product_Attribute_Source_Meta_Canonical extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        $productId = Mage::registry('seosuite_product_id');
        if (!$this->_options) {
            $this->_options = array(
                array('value' => '', 'label' => Mage::helper('seosuite')->__('Use Config')),
            );
            //$storeId = Mage::app()->getRequest()->getParam('store', Mage::app()->getDefaultStoreView()->getId());
            //$storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
            if ($productId!=null) {
                $collection = Mage::getResourceModel('seosuite/core_url_rewrite_collection')
                                //->addStoreFilter($storeId, false)
                                ->filterAllByProductId($productId)
                                ->groupByUrl()
                                ->sortByLength('ASC');
//                echo $collection->getSelect()->assemble(); exit;
                if ($collection->count()) {
                    foreach ($collection->getItems() as $urlRewrite) {
                        $this->_options[] = array('value' => $urlRewrite->getIdPath(), 'label' => $urlRewrite->getRequestPath());
                    }
                }
            }
        }
        return $this->_options;
    }
}