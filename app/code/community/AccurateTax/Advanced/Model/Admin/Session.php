<?php
    class AccurateTax_Advanced_Model_Admin_Session extends Mage_Admin_Model_Session {

        public function isAllowed($resource, $privilege=null)
        {
        	$block = array(
        		'admin/sales/tax/rules',
        		'admin/sales/tax/rates',
        		'admin/sales/tax/import_export'
        	);
        	
        	if(in_array($resource, $block) && !Mage::helper('advanced')->isAnyStoreDisabled()) {
        		return false;
        	} else {
        		return parent::isAllowed($resource, $privilege);
        	}
        }
    }
