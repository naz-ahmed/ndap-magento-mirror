<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Helper_Sales extends Mage_Core_Helper_Abstract
{
    private $defaultWebsite = NULL;
    private $defaultStoreGroup = NULL;
    private $defaultStore = NULL;

    // ########################################

    public function getProductFirstStoreId($product)
    {
        if (!$product->getId()) {
            return false;
        }

        $storeIdList = array_values($product->getStoreIds());
        if (count($storeIdList) == 0) {
            return $this->getDefaultStoreId();
        }
        // Get first from all available store view
        return $storeIdList[0];
    }

    // ########################################

    public function getDefaultWebsite()
    {
        if (is_null($this->defaultWebsite)) {
            $this->defaultWebsite = Mage::getModel('core/website')->load(1,'is_default');
            if (is_null($this->defaultWebsite->getId())) {
                $this->defaultWebsite = Mage::getModel('core/website')->load(0);
                if (is_null($this->defaultWebsite->getId())) {
                    throw new Exception('Getting default website is failed');
                }
            }
        }
        return $this->defaultWebsite;
    }

    public function getDefaultStoreGroup()
    {
        if (is_null($this->defaultStoreGroup)) {

            $defaultWebsite = $this->getDefaultWebsite();
            $defaultStoreGroupId = $defaultWebsite->getDefaultGroupId();

            $this->defaultStoreGroup = Mage::getModel('core/store_group')->load($defaultStoreGroupId);
            if (is_null($this->defaultStoreGroup->getId())) {
                $this->defaultStoreGroup = Mage::getModel('core/store_group')->load(0);
                if (is_null($this->defaultStoreGroup->getId())) {
                    throw new Exception('Getting default store group is failed');
                }
            }
        }
        return $this->defaultStoreGroup;
    }

    public function getDefaultStore()
    {
        if (is_null($this->defaultStore)) {

            $defaultStoreGroup = $this->getDefaultStoreGroup();
            $defaultStoreId = $defaultStoreGroup->getDefaultStoreId();

            $this->defaultStore = Mage::getModel('core/store')->load($defaultStoreId);
            if (is_null($this->defaultStore->getId())) {
                $this->defaultStore = Mage::getModel('core/store')->load(0);
                if (is_null($this->defaultStore->getId())) {
                    throw new Exception('Getting default store is failed');
                }
            }
        }
        return $this->defaultStore;
    }

    //------------------------------------------

    public function getDefaultWebsiteId()
    {
        return (int)$this->getDefaultWebsite()->getId();
    }

    public function getDefaultStoreGroupId()
    {
        return (int)$this->getDefaultStoreGroup()->getId();
    }

    public function getDefaultStoreId()
    {
        return (int)$this->getDefaultStore()->getId();
    }

    // ########################################

    public function getEbayCarriers()
    {
        return array(
            'dhl'   => 'DHL',
            'fedex' => 'FedEx',
            'ups'   => 'UPS',
            'usps'  => 'USPS'
        );
    }

    // ########################################
}