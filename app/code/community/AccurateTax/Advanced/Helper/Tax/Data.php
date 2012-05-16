<?php

    class Accuratetax_Advanced_Helper_Tax_Data extends Mage_Tax_Helper_Data {
       
        public function priceIncludesTax($store = null) {
        	return false;
        }
        
        public function shippingPriceIncludesTax($store = null) {
        	return false;
        }
        
        public function getTaxBasedOn($store = null) {
            return 'shipping';
        }

        public function applyTaxOnCustomPrice($store = null) {
            return true;
        }

        public function applyTaxOnOriginalPrice($store = null) {
            return false;
        }

        public function applyTaxAfterDiscount($store = null) {
            return true;
        }

        public function discountTax($store = null) {
            return true;
        }
        
        public function displayFullSummary($store = null)
        {
            return false;
        }
        
    }
