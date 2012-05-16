<?php

class MagicToolbox_MagicThumb_Block_Adminhtml_Settings extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {

        parent::__construct();
        $this->_controller = 'adminhtml_settings';
        $this->_blockGroup = 'magicthumb';
        $this->_headerText = Mage::helper('magicthumb')->__('Settings Manager');
        $this->setTemplate('magicthumb/settings.phtml');

    }

    protected function _prepareLayout() {

        $this->setChild('settings_grid', $this->getLayout()->createBlock('magicthumb/adminhtml_settings_grid', 'magicthumb.grid'));
        $this->setChild('custom_design_settings_form', $this->getLayout()->createBlock('magicthumb/adminhtml_settings_form', 'magicthumb.form'));
        return parent::_prepareLayout();

    }

    public function getAddCustomSettingsFormHtml() {

        $html = $this->getChildHtml('custom_design_settings_form');
        if(Mage::registry('magicthumb_custom_design_settings_form')) {
            return $html;
        } else {
            return '';
        }

    }

    public function getSettingsGridHtml() {

        return $this->getChildHtml('settings_grid');

    }

}
