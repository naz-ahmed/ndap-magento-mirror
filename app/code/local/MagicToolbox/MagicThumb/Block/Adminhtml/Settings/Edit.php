<?php

class MagicToolbox_MagicThumb_Block_Adminhtml_Settings_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {

    public function __construct() {

        //$this->_objectId = 'id';
        $this->_blockGroup = 'magicthumb';
        $this->_controller = 'adminhtml_settings';
        //$this->_mode = 'edit';

        parent::__construct();

        $this->_removeButton('delete');

        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    public function getHeaderText() {

        $package = Mage::registry('magicthumb_data')->getPackage();
        $theme = Mage::registry('magicthumb_data')->getTheme();
        return Mage::helper('magicthumb')->__("Edit setting for <i>%s</i> package".($package=='all'?"s":"").", <i>%s</i> theme".($theme=='all'?"s":""), $this->htmlEscape($package), $this->htmlEscape($theme));

    }

    public function getValidationUrl()
    {
        return $this->getUrl('*/*/validate', array('_current'=>true));
    }


}
