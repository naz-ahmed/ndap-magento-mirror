<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Orders_Magento_Tax
{
    public function createProductTaxClass($customerTaxClassId = null, $taxRate = 0, $countryId = 'US')
    {
        if ((int)$taxRate <= 0) {
            return 0;
        }

        // Init product tax class
        // -------------------------
        $productTaxClass = Mage::getModel('tax/class')->getCollection()
                                                      ->addFieldToFilter('class_name', 'eBay Product Tax Class')
                                                      ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT)
                                                      ->getFirstItem();

        if (is_null($productTaxClass->getId())) {
            $productTaxClass->setClassName('eBay Product Tax Class')
                            ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_PRODUCT);
            $productTaxClass->save();
        }
        // -------------------------

        // Init customer tax class
        // -------------------------
        if (is_null($customerTaxClassId)) {
            $customerTaxClass = Mage::getModel('tax/class')->getCollection()
                                                           ->addFieldToFilter('class_name', 'eBay Customer Tax Class')
                                                           ->addFieldToFilter('class_type', Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER)
                                                           ->getFirstItem();

            if (is_null($customerTaxClass->getId())) {
                $customerTaxClass->setClassName('eBay Customer Tax Class')
                                 ->setClassType(Mage_Tax_Model_Class::TAX_CLASS_TYPE_CUSTOMER);
                $customerTaxClass->save();
            }

            $customerTaxClassId = $customerTaxClass->getId();
        }
        // -------------------------

        // Init tax rate
        // -------------------------
        $taxRateModel = Mage::getModel('tax/calculation_rate')->load('eBay Tax Rate', 'code');

        $taxRateModel->setRate((float)$taxRate)
                     ->setCode('eBay Tax Rate')
                     ->setTaxCountryId((string)$countryId);
        $taxRateModel->save();
        // -------------------------

        // Combine tax classes and tax rate in tax rule
        // -------------------------
        $taxRule = Mage::getModel('tax/calculation_rule')->load('eBay Tax Rule', 'code');

        $taxRule->setCode('eBay Tax Rule')
                ->setTaxCustomerClass(array($customerTaxClassId))
                ->setTaxProductClass(array($productTaxClass->getId()))
                ->setTaxRate(array($taxRateModel->getId()));
        $taxRule->save();
        // -------------------------

        return $productTaxClass->getId();
    }
}