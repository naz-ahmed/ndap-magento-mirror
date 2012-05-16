<?php
    abstract class AccurateTax_Advanced_Model_Abstract extends Varien_Object {
        protected $_helper = null;
        
        protected function _construct () {
		    Mage::getSingleton('advanced/config');
	    }
	    
	    protected function _log ($request, $result, $storeId=null, $additional=null) {
		   return;
		
		    $requestType = str_replace('Request', '', get_class($request));
		    $resultType = str_replace('Result', '', get_class($result));
		    $type = $requestType ? $requestType : $resultType;
		    if($type == 'Varien_Object') $type = 'Unknown';
		
		    Mage::getModel('advanced/log')
			    ->setStoreId($storeId)
			    ->setLevel($result->getResultCode())
			    ->setType($type)
			    ->setRequest(print_r($request, true))
			    ->setResult(print_r($result, true))
			    ->setAdditional($additional)
			    ->save();
	    }
	    
	    public function getSession () {
		    return Mage::getSingleton('advanced/session');
	    }
	    
	    public function getHelper () {
		    if (!$this->_helper) {
			    $this->_helper = Mage::helper('advanced');
		    }
		    return $this->_helper;
	    }
	    
	    public function __ ($message) {
		    return $this->getHelper()->__($message);
	    }
    }
?>
