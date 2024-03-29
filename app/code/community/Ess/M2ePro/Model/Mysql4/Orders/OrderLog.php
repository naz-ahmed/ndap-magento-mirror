<?php

/*
 * @copyright  Copyright (c) 2011 by  ESS-UA.
 */

class Ess_M2ePro_Model_Mysql4_Orders_OrderLog extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        $this->_init('M2ePro/Orders_OrderLog', 'id');
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if (is_null($object->getOrigData())) {
            $object->setData('create_date',Mage::helper('M2ePro')->getCurrentGmtDate());
        }

        return $this;
    }
}