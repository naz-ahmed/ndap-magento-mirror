<?php

class MagicToolbox_MagicThumb_Block_Adminhtml_Settings_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct() {

        parent::__construct();
        $this->setId('settingsGrid');
        $this->setDefaultSort('setting_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);

    }

    protected function _prepareCollection() {

        $collection = Mage::getModel('magicthumb/settings')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();

    }

    protected function _prepareColumns() {

      $this->addColumn('setting_id', array(
          'header'    => Mage::helper('magicthumb')->__('ID'),
          'align'     =>'right',
          'width'     => '50px',
          'index'     => 'setting_id',
      ));

      /*$this->addColumn('package', array(
          'header'    => Mage::helper('magicthumb')->__('Package'),
          'align'     =>'left',
          'index'     => 'package',
      ));*/

      /*$this->addColumn('theme', array(
          'header'    => Mage::helper('magicthumb')->__('Theme'),
          'align'     =>'left',
          'index'     => 'theme',
      ));*/

       $this->addColumn('package_theme', array(
           'header'    => Mage::helper('magicthumb')->__('List of custom settings'),
           'align'     =>'left',
           'index'     => 'package_theme',
       ));

      $this->addColumn('last_edit_time', array(
          'header'    => Mage::helper('magicthumb')->__('Last edit time'),
          'align'     =>'left',
          'index'     => 'last_edit_time',
      ));

      $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('magicthumb')->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('magicthumb')->__('Delete'),
                        'url'       => array('base'=> '*/*/delete'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));

        return parent::_prepareColumns();

    }

    protected function _prepareMassaction() {

        $this->setMassactionIdField('setting_id');
        $this->getMassactionBlock()->setFormFieldName('massactionId');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('magicthumb')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('magicthumb')->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row) {

        return $this->getUrl('*/*/edit', array('id' => $row->getId()));

    }

}
