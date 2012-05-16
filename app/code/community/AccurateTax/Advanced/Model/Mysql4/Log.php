<?php

class AccurateTax_Advanced_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract {
    
    protected function _construct() {
        $this->_init('advanced/log', 'log_id');
    }
    
	
    protected function _beforeSave(Mage_Core_Model_Abstract $object) {
        $object->setReqDate(gmdate('Y-m-d H:i:s'));
        if(!$object->getLevel()) {
        	$object->setLevel('Unknown');
        }
        return $this;
    }
}
