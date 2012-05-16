<?php

class AccurateTax_Advanced_Model_Observer extends Mage_Core_Model_Abstract {
	
	// Get Rate
	public function salesQuoteCollectTotalsAfter(Varien_Event_Observer $observer) {
		$config = Mage::getSingleton('advanced/config');
		$agree = (boolean)$config->getAgree();
		if ( $agree !== false ) {
		    $quote = $observer->getEvent()->getQuote();
        	$storeId = $quote->getStore()->getId();
		    Mage::getModel('advanced/advanced_estimate')->collectTotals($quote);
		}
	}
    
	/**
	 * Test for required values when admin config setting related to the this extension are changed
	 *
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @return bool
	 */
    public function adminSystemConfigChangedSectionTax($schedule) {
    	Mage::app()->cleanCache('block_html');
    	
    	$storeId = Mage::getModel('core/store')->load($schedule->getEvent()->getStore())->getStoreId();
    	$errors = array();
    	
    	   	
    	if(!Mage::getStoreConfig('tax/advanced/url', $storeId)) {
    		$errors[] = 'You must enter a connection url';
    	}
    	if(!Mage::getStoreConfig('tax/advanced/license', $storeId)) {
    		$errors[] = 'You must enter a license key';
    	}
    	
    	
    	if(!Mage::getStoreConfig('tax/advanced/checksum', $storeId)) {
    		$errors[] = 'You must enter a checksum';
    	}
    	
    	$session = Mage::getSingleton('adminhtml/session');
    	if(count($errors) == 1) {
			$session->addError(implode('', $errors));
    	} elseif(count($errors)) {
			$session->addError('Please make sure the following fields as filled in in your Accuratetax.com Module Setup:<br /> ' . implode('<br />', $errors) .'<br/>');
    	}
    }
    
	/**
	 * Observer to clean the log every so often so it does not get too big.
	 *
	 * @param Mage_Cron_Model_Schedule $schedule
	 * @return (none)
	 */
    public function cleanLog($schedule) { 
		$mintime = strtotime('-' . floatval(Mage::getStoreConfig('tax/advanced/clean_log')) . ' days');
		$collection = Mage::getModel('advanced/log')->getCollection()
						->addFieldToFilter('req_date', array('lt'=>gmdate('Y-m-d H:i:s', $mintime)))
						->load();
		if($collection->getSize() > 0) {			
	        foreach ($collection->getIterator() as $record) {
	            $record->delete();
	        }
		}
    }
}
