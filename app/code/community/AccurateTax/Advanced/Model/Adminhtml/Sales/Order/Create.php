<?php
class AccurateTax_Advanced_Model_Adminhtml_Sales_Order_Create extends Mage_Adminhtml_Model_Sales_Order_Create {

	protected $_messageAdded = false;

	public function setShippingAddress($address)
	{
		parent::setShippingAddress($address);
		if (!Mage::app()->getFrontController()->getRequest()->getParam('isAjax')) {
			$result = $this->getShippingAddress()->validate();
			if ($result !== true) {
				$storeId = $this->_session->getStore()->getId();
				if(Mage::helper('advanced')->fullStopOnError($storeId)) {
					foreach ($result as $error) {
						$this->getSession()->addError($error);
					}
					Mage::throwException('');
				}
			}
			else if ($this->getShippingAddress()->getAddressNormalized() && !$this->_messageAdded) {
				Mage::getSingleton('advanced/session')->addNotice(Mage::helper('advanced')->__('The shipping address has been modified during the validation process.  Please confirm the address below is accurate.'));
				$this->_messageAdded = true;  // only add the message once
			}
		}
		return $this;
	}
}
