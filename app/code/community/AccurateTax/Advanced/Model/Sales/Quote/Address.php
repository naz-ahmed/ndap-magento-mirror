<?php
    class AccurateTax_Advanced_Model_Sales_Quote_Address extends Mage_Sales_Model_Quote_Address {
        protected $_validator = null;
        
        protected function _getATValidator () {
		    if (!$this->_validator) {
			    $this->_validator = Mage::getModel('advanced/validate_address')->setAddress($this);
		    }
		    return $this->_validator;
	    }
        
        
        public function validate () {
		    $billData = Mage::app()->getFrontController()->getRequest()->getPost('billing', array());
		    $result = parent::validate();  // Perform default magento validation
		
		    if ($result !== true) {
			    return $result;
		    } else if ( ($this->getAddressType() == self::TYPE_SHIPPING) || ($this->getAddressType() == self::TYPE_BILLING && isset($billData['use_for_shipping']) && (int) $billData['use_for_shipping']) ) {
			    return $this->_getATValidator()->validate(); 
		    }
		    return $result;
	    }	
    }
?>
