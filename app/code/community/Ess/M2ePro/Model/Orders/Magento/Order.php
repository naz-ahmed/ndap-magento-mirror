<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Order
{
    protected $_order = NULL;

    /** @var $_proxyQuote Ess_M2ePro_Model_Orders_Magento_Quote */
    protected $_proxyQuote = NULL;

    // ########################################

    public function setOrder(Ess_M2ePro_Model_Orders_Order $order)
    {
        $this->_order = $order;

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_Orders_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order) || !$this->_order->getId()) {
            throw new Exception('Order was not set.');
        }

        return $this->_order;
    }

    // ########################################

    public function createOrder()
    {
        $result = false;

        try {

            $this->processTransactions();

            // Prepare quote
            // ----------------
            $this->_proxyQuote = Mage::getModel('M2ePro/Orders_Magento_Quote');
            $this->_proxyQuote->setOrder($this->getOrder());
            $this->_proxyQuote->prepareMagentoQuote();
            // ----------------

            $this->createMagentoOrder();

            // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was created.');
            $message = 'Magento Order was created.';
            $this->getOrder()->addSuccessLogMessage($message);

            $result = true;

        } catch (Exception $e) {

            try {
                Mage::helper('M2ePro/Exception')->process($e, true);
            } catch (Exception $exceptionTemp) {}

            if (!$this->getOrder()->hasMagentoOrder()) {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was not created. Reason: %msg%');
                $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Magento Order was not created. Reason: %msg%', array('msg' => $e->getMessage()));
                $this->getOrder()->addErrorLogMessage($message, $e->getTraceAsString());

                $result = false;
            } else {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was created with error: %msg%');
                $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Magento Order was created with error: %msg%', array('msg' => $e->getMessage()));
                $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());

                $result = true;
            }

        }

        $this->rollbackChanges();

        return $result;
    }

    // ########################################

    protected  function createMagentoOrder()
    {
        $magentoQuote = $this->_proxyQuote->getMagentoQuote();

        $service = Mage::getModel('sales/service_quote', $magentoQuote);

        if ($service && method_exists($service, 'submitAll')) {
            $service->submitAll();
            $orderObj = $service->getOrder();
        } else {
            // Magento version 1.4.0.x

            $convertQuoteObj = Mage::getSingleton('sales/convert_quote');
            /** @var $orderObj Mage_Sales_Model_Order */
            $orderObj = $convertQuoteObj->addressToOrder($magentoQuote->getShippingAddress());

            $orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($magentoQuote->getBillingAddress()));
            $orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($magentoQuote->getShippingAddress()));
            $orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($magentoQuote->getPayment()));

            $items = $magentoQuote->getShippingAddress()->getAllItems();

            foreach ($items as $item) {
                //@var $item Mage_Sales_Model_Quote_Item
                $orderItem = $convertQuoteObj->itemToOrderItem($item);
                if ($item->getParentItem()) {
                    $orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
                }
                $orderObj->addItem($orderItem);
            }

            $orderObj->setCanShipPartiallyItem(false);
            $orderObj->place();
        }
        // ----------------

        // ----------------
        $orderObj->setStatus($this->getOrder()->getAccount()->getOrderStatusOnCheckoutComplete())
                 ->save();

        $this->getOrder()->setData('magento_order_id', $orderObj->getId())
                         ->save();
        // ----------------

        $this->processOrderNotifications($orderObj);

		return $orderObj;
    }

    // ########################################

    protected function processOrderNotifications(Mage_Sales_Model_Order $magentoOrder)
    {
        if ($this->getOrder()->getAccount()->isCustomerOrderNotificationEnabled()) {
            $magentoOrder->sendNewOrderEmail();
        }

        $checkoutMessage = $this->getOrder()->getData('checkout_message');
        $orderCommentsArray = $this->_proxyQuote->getOrderComments();

        if (!$checkoutMessage && !count($orderCommentsArray)) {
            return;
        }

        $comments = '<br /><b><u>' . Mage::helper('M2ePro')->__('M2E Pro Notes') . ':</u></b><br /><br />';
        if ($checkoutMessage) {
            $comments .= '<b>' . Mage::helper('M2ePro')->__('Checkout Message From Buyer') . ': </b>';
            $comments .= $checkoutMessage . '<br />';
        }

        foreach ($orderCommentsArray as $comment) {
            $comments .= $comment . '<br /><br />';
        }

        $magentoOrder->addStatusHistoryComment($comments)->save();
    }

    // ########################################

    protected function processTransactions()
    {
        if ($this->getOrder()->isSingle() || $this->getOrder()->getAccount()->isOrdersCombinedDisabled()) {
            return;
        }

        $relatedOrders = $this->getOrder()->getRelatedOrdersCollection()->getItems();

        foreach ($relatedOrders as $relatedOrder) {
            /** @var $magentoOrder Mage_Sales_Model_Order */
            if (is_null($magentoOrder = $relatedOrder->getMagentoOrder())) {
                continue;
            }

            if ($magentoOrder->isCanceled()) {
                continue;
            }

            if ($isCancelSuccess = $magentoOrder->canCancel()) {
                try {
                    $magentoOrder->cancel()->save();
                } catch (Exception $e) {
                    $isCancelSuccess = false;
                }
            }

            if ($isCancelSuccess) {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order was cancelled.');
                $relatedOrder->addWarningLogMessage('Magento Order was cancelled.');
            } else {
                // Parser hack -> Mage::helper('M2ePro')->__('Magento Order cannot be cancelled.');
                $relatedOrder->addWarningLogMessage('Magento Order cannot be cancelled.');
            }
        }
    }

    // ########################################

    protected function rollbackChanges()
    {
        if (is_null($this->_proxyQuote)) {
            return;
        }

        // Rollback store settings
        // ----------------
        if (!is_null($this->_proxyQuote->getStoreShippingTaxClass())) {
            $this->_proxyQuote->getMagentoQuote()->getStore()->setConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_SHIPPING_TAX_CLASS, $this->_proxyQuote->getStoreShippingTaxClass());
        }
        // ----------------

        $this->_proxyQuote->getMagentoQuote()->setIsActive(false)
                                             ->save();
    }

    // ########################################

    /**
     * Updates address info for exist order
     *
     * @return boolean
     */
    public function updateAddress()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();
        if (!$magentoOrder) {
            return false;
        }

        $addressInfo = $this->getOrder()->getPreparedShippingAddress();
        unset($addressInfo['address_id']);

        try {
            $billingAddress = $magentoOrder->getBillingAddress();
            if ($billingAddress instanceof Mage_Sales_Model_Order_Address) {
                $billingAddress->addData($addressInfo);
                $billingAddress->implodeStreetAddress()->save();
            }

            $shippingAddress = $magentoOrder->getShippingAddress();
            if ($shippingAddress instanceof Mage_Sales_Model_Order_Address) {
                $shippingAddress->addData($addressInfo);
                $shippingAddress->implodeStreetAddress()->save();
            }

            $this->updateCustomerAddress();

            // Parser hack -> Mage::helper('M2ePro')->__('Shipping address for Magento Order was updated.');
            $message = 'Shipping address for Magento Order was updated.';
            $this->getOrder()->addSuccessLogMessage($message);
        } catch (Exception $e) {
            // Parser hack -> Mage::helper('M2ePro')->__('Shipping address for Magento Order was not updated. Reason: %msg%.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Shipping address for Magento Order was not updated. Reason: %msg%.', array('msg' => $e->getMessage()));
            $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());

            return false;
        }

        return true;
    }

    // ----------------------------------------

    private function updateCustomerAddress()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();
        if (!$magentoOrder) {
            return false;
        }

        if ($magentoOrder->getCustomerIsGuest()) {
            return false;
        }

        $addressInfo = $this->getOrder()->getPreparedShippingAddress();
        unset($addressInfo['address_id']);

        /** @var $customer Mage_Customer_Model_Customer */
        $customer = Mage::getModel('customer/customer')->load($magentoOrder->getData('customer_id'));
        if (!$customer->getId()) {
            return false;
        }

        // Find same customer address
        // ---------------------------
        foreach ($customer->getAddressesCollection() as $address) {
            /** @var $address Mage_Customer_Model_Address */
            if ($address->getData('firstname') != $addressInfo['firstname'] ||
                $address->getData('lastname') != $addressInfo['lastname'] ||
                $address->getData('city') != $addressInfo['city']) {
                continue;
            }

            $newStreets = array_diff($address->getStreet(), $addressInfo['street']);
            if (count($newStreets) == 0) {
                return false;
            }
        }
        // ---------------------------

        /** @var $customerAddress Mage_Customer_Model_Address */
        $customerAddress = Mage::getModel('customer/address')->setData($addressInfo)
                                                             ->setCustomerId($customer->getId())
                                                             ->setIsDefaultBilling(false)
                                                             ->setIsDefaultShipping(false);
        $customerAddress->implodeStreetAddress();
        $customerAddress->save();

        return true;
    }

    // ########################################

    /**
     * Updates payment method title for exist order
     *
     * @return boolean
     */
    public function updatePaymentData()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();
        if (!$magentoOrder) {
            return false;
        }

        try {

            $newPaymentData = array(
                'ebay_payment_method'   => $this->getOrder()->getData('payment_used'),
                'ebay_order_id'         => $this->getOrder()->getData('ebay_order_id'),
                'ebay_final_value_fee'  => $this->getOrder()->getData('ebay_final_value_fee'),
                'external_transactions' => $this->getOrder()->getPreparedExternalTransactions()
            );

            $payment = $magentoOrder->getPayment();

            if (!$payment) {
                return false;
            }

            $payment->setData('additional_data', serialize($newPaymentData))->save();

            // Parser hack -> Mage::helper('M2ePro')->__('Payment data for Magento Order was updated.');
            $message = 'Payment data for Magento Order was updated.';
            $this->getOrder()->addSuccessLogMessage($message);

        } catch (Exception $e) {
            // Parser hack -> Mage::helper('M2ePro')->__('Payment data for Magento Order was not updated. Reason: %msg%.');
            $message = Mage::getSingleton('M2ePro/LogsBase')->encodeDescription('Payment data for Magento Order was not updated. Reason: %msg%.', array('msg' => $e->getMessage()));
            $this->getOrder()->addWarningLogMessage($message, $e->getTraceAsString());

            return false;
        }

        return true;
    }

    // ########################################

    public function updateCheckoutMessage()
    {
        $magentoOrder = $this->getOrder()->getMagentoOrder();
        if (!$magentoOrder) {
            return false;
        }

        $comment = '<b>' . Mage::helper('M2ePro')->__('Checkout Message From Buyer') . ': </b>';
        $comment .= $this->getOrder()->getData('checkout_message') . '<br />';

        $magentoOrder->addStatusHistoryComment($comment);
    }

    // ########################################
}