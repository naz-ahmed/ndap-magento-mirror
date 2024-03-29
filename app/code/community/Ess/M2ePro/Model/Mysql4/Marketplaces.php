<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Marketplaces extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement = false;

    public function _construct()
    {
        $this->_init('M2ePro/Marketplaces', 'id');
        $this->_isPkAutoIncrement = false;
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_null($object->getOrigData())) {
            $object->setData('create_date',Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        $object->setData('update_date',Mage::helper('M2ePro')->getCurrentGmtDate());

        return $this;
    }
}