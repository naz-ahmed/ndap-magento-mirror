<?php
    class AccurateTax_Advanced_Model_Config extends Varien_Object {
        protected $_config;
        protected $_ATClient = null;
        
        protected function _construct () {
		    $helper = Mage::helper('advanced');
		    
		    $helper->loadClasses(array(			
			    'ATTaxRequest',
			    'ATAddressRequest'
		    ));
        }
        
        public function getConfig ($path, $store=null) {
		    return Mage::getStoreConfig('tax/advanced/' . $path, $store);
	    }
        
        public function getLicenseKey() {
            $storeId = Mage::app()->getStore()->getId();
		    return $this->getConfig('license', $storeId);
        }
        
        public function getChecksum() {
            $storeId = Mage::app()->getStore()->getId();
		    return $this->getConfig('checksum', $storeId);
        }
        
        public function getAgree() {
            $storeId = Mage::app()->getStore()->getId();
            return $this->getConfig('agree', $storeId);
        }
        
        public function getURL() {
            $storeId = Mage::app()->getStore()->getId();
		    return $this->getConfig('url', $storeId);
        }
        
        public function getAllowInvalid() {
            $storeId = Mage::app()->getStore()->getId();
            return $this->getConfig('allowinvalid', $storeId );
        }
        
        public function getOnlyScrubTaxable() {
            $storeId = Mage::app()->getStore()->getId();
            return $this->getConfig('onlyscrubtaxable', $storeId );
        }
        
        public function getDebug() {
            $storeId = Mage::app()->getStore()->getId();
            return $this->getConfig('debug', $storeId );
        }
        
        public function getSendClientInfo() {
            $storeId = Mage::app()->getStore()->getId();
            return $this->getConfig('clientinfo', $storeId );
        }
    }
?>
