<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Observer_Shipped
{
    //####################################
    
    /**
     * Call after create shipping for magento order
     * 
     * Work only for order imported from eBay Sale
     *
     * @param Varien_Event_Observer $observer
     * @return void
     */
    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {
        try {

            if (Mage::registry('m2epro_skip_shipping_observer')) {
                // Not process invoice observer when set such flag
                Mage::unregister('m2epro_skip_shipping_observer');
                return;
            }

            $shipment = $observer->getEvent()->getShipment();

            /** @var $magentoOrder Mage_Sales_Model_Order */
            $magentoOrder = $shipment->getOrder();

            if (is_null($magentoOrderId = $magentoOrder->getData('entity_id'))) {
                return;
            }

            /** @var $loadedOrder Ess_M2ePro_Model_Orders_Order */
            $loadedOrder = Mage::getModel('M2ePro/Orders_Order')->load($magentoOrderId, 'magento_order_id');

            if (!$loadedOrder->getId()) {
                return;
            }

            $track = $shipment->getTracksCollection()->getFirstItem();

            if ($track->getData('number')) {
                $ebayCarriers = Mage::helper('M2ePro/Sales')->getEbayCarriers();
                $carrier = strtolower($track->getData('carrier_code'));

                if (isset($ebayCarriers[$carrier])) {
                    $carrier = $ebayCarriers[$carrier];
                } else {
                    $carrier = str_replace('.', '', $track->getData('title'));
                    $carrier == '' && $carrier = 'Other';
                }

                $trackingDetails = array(
                    'carrier_code' => $carrier,
                    'tracking_number' => $track->getData('number')
                );

                $result = $loadedOrder->shipTrackOnEbay($trackingDetails);
            } else {
                $result = $loadedOrder->shipOnEbay();
            }

            if ($result) {
                $message = Mage::helper('M2ePro')->__('Shipping status for eBay order was updated to Shipped.');
                Mage::getSingleton('adminhtml/session')->addSuccess($message);
            } else {
                $startLink = '<a href="' . Mage::getUrl('M2ePro/adminhtml_orders/view', array('id' => $loadedOrder->getId())) . '" target="_blank">';
                $endLink = '</a>';
                $message = Mage::helper('M2ePro')->__('Shipping status for eBay order was not updated. View %sl%order log%el% for more details.');

                Mage::getSingleton('adminhtml/session')->addError(str_replace(array('%sl%', '%el%'), array($startLink, $endLink), $message));
            }

        } catch (Exception $exception) {

            try {
                Mage::helper('M2ePro/Exception')->process($exception,true);
            } catch (Exception $exceptionTemp) {}

            return;
        }
    }

    //####################################
}