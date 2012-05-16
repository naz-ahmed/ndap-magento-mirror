<?php

class AccurateTax_Advanced_Block_Adminhtml_Tax_Rate_Grid extends Mage_Adminhtml_Block_Tax_Rate_Grid
{

    protected function _prepareColumns() {
        $p = parent::_prepareColumns();
        $p->addColumn('checkAT',
            array(
                'header'    => Mage::helper('tax')->__('Pull from AccurateTax'),
                'align'     => 'left',
                'index'     => 'checkAT',
                'type'      => 'options',   // Used to display select values without select box for grid
                'options' => array(
                    '1' => Mage::helper('tax')->__('Yes'),
                    '0' => Mage::helper('tax')->__('No')
                ),
                'sortable'  => true,
                'default'   => '1'
            )
        );
        return $p;
    }
    
}
