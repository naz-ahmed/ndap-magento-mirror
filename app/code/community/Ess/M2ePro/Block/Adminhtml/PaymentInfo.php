<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Block_Adminhtml_PaymentInfo extends Mage_Payment_Block_Info
{
    protected $_ebayPaymentMethod = null;
    protected $_ebayOrderId = null;
    protected $_externalTransactions = null;
    protected $_ebayFinalValueFee = null;

    /**
     * Get absolute path to template
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $params = array(
            '_relative' => true,
            '_area' => 'adminhtml',
            '_package' => 'default',
            '_theme' => 'default'
        );

        $templateName = Mage::getDesign()->getTemplateFilename($this->getTemplate(), $params);
        return $templateName;
    }

    /**
     * Retrieve available order
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }
        if (Mage::registry('current_order')) {
            return Mage::registry('current_order');
        }
        if (Mage::registry('order')) {
            return Mage::registry('order');
        }
        return;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('M2ePro/orders/payment/info.phtml');
    }

    public function getEbayPaymentMethod()
    {
        if (is_null($this->_ebayPaymentMethod)) {
            $this->_convertAdditionalData();
        }
        return $this->_ebayPaymentMethod;
    }

    public function getEbayOrderId()
    {
        if (is_null($this->_ebayOrderId)) {
            $this->_convertAdditionalData();
        }
        return $this->_ebayOrderId;
    }

    public function getEbayFinalValueFee()
    {
        if (is_null($this->_ebayFinalValueFee)) {
            $this->_convertAdditionalData();
        }
        return $this->_ebayFinalValueFee;
    }

    public function getExternalTransactions()
    {
        if (is_null($this->_externalTransactions)) {
            $this->_convertAdditionalData();
        }
        return $this->_externalTransactions;
    }

    protected function _convertAdditionalData()
    {
        $details = @unserialize($this->getInfo()->getAdditionalData());
        if (is_array($details)) {
            $this->_ebayPaymentMethod = isset($details['ebay_payment_method']) ? (string)$details['ebay_payment_method'] : '';
            $this->_ebayOrderId = isset($details['ebay_order_id']) ? (string)$details['ebay_order_id'] : '';
            $this->_ebayFinalValueFee = isset($details['ebay_final_value_fee']) ? (string)$details['ebay_final_value_fee'] : '';
            if (isset($details['external_transactions']) && count($details['external_transactions'])) {
                $this->_externalTransactions = $details['external_transactions'];
            } else {
                $this->_externalTransactions = array();
            }
        } else {
            $this->_ebayPaymentMethod = '';
            $this->_ebayOrderId = '';
            $this->_ebayFinalValueFee = '';
            $this->_externalTransactions = array();
        }
        return $this;
    }

    public function toPdf()
    {
        $this->setTemplate('M2ePro/orders/payment/pdf.phtml');
        return $this->toHtml();
    }
}