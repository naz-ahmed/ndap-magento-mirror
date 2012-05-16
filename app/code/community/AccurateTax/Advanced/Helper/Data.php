<?php
    class AccurateTax_Advanced_Helper_Data extends Mage_Core_Helper_Abstract {
        
        public function loadClass ($className) {
		    require_once $this->getLibPath() . DS . $className . '.php';
		    return $this;
	    }
	    
	    public function loadClasses (array $classes) {
		    foreach ($classes as $class) {
			    $this->loadClass($class);
		    }
		    return $this;
	    }
        
        public function getATObjectPath () {
		    return dirname(dirname(__FILE__)) . DS . 'ATObjects';
    	}
    	
    	protected function _getConfigVal($path, $store = null) {
	    	return Mage::getSingleton('advanced/config')->getConfig($path, $store);
    	}
    	
    	public function isAnyStoreDisabled() {
        	$disabled = false;
        	$storeCollection = Mage::app()->getStores();
        	
        	foreach($storeCollection as $store) {
        		$disabled |= Mage::getStoreConfig('tax/advanced/disable', $store->getId()) == 0;
        	}
        	return $disabled;
        }
        
        public function fullStopOnError($store=null) {
            return (bool)true;
        }

        public function getEtcPath () {
		    return dirname(dirname(__FILE__)) . DS . 'etc';
	    }
	    
	    public function getLibPath () {
		    return dirname(dirname(__FILE__)) . DS . 'ATObjects';
	    }
	    
        public function addErrorMessage($store=null, $messages) {
        	static $isMessageSet = false;
    	
    		if(Mage::app()->getStore()->isAdmin()) {
    		    foreach( $messages as $message ) {
            		if(!$isMessageSet) Mage::getSingleton('adminhtml/session_quote')->addError($message);
                }
    		} else {
    		    foreach( $messages as $message ) {
        			if(!$isMessageSet) Mage::getSingleton('checkout/session')->addError($message);
                }
    		}
        	$message = join( '<br />', $messages );
        	$isMessageSet = true;
        	return $message;
        }
    }
?>
