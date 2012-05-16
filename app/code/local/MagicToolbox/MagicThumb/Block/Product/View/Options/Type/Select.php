<?php

class MagicToolbox_MagicThumb_Block_Product_View_Options_Type_Select extends Mage_Catalog_Block_Product_View_Options_Type_Select {

    public function getValuesHtml() {

        $helper = Mage::helper('magicthumb/settings');
        $tool = $helper->loadTool('product');
        if(!$tool->params->checkValue('use-effect-on-product-page', 'No')) {
            $option = $this->getOption();
            $optionType = $option->getType();
            if($optionType == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DROP_DOWN) {
                $eventType = 'onchange';
            } elseif($optionType == Mage_Catalog_Model_Product_Option::OPTION_TYPE_RADIO) {
                $eventType = 'onclick';
            } else {
                return parent::getValuesHtml();
            }
            $optionsArray = array();
            foreach($option->getValues() as $value) {
                $optionsArray[$value->getOptionTypeId()] = strtolower(trim($value->getTitle()));
            }
            $optionTitle = strtolower(trim($option->getTitle()));
            $html = parent::getValuesHtml();
            $html = str_replace($eventType.'="', $eventType.'="MagicToolboxChangeOption(this, \''.$optionTitle.'\');', $html);
            $html .= '<script type="text/javascript">' .
                     'optionLabels[\''.$option->getId().'\'] = '.Mage::helper('core')->jsonEncode($optionsArray).'; ' .
                     'optionTitles[\''.$option->getId().'\'] = \''.$optionTitle.'\';' .
                     '</script>';
            return $html;
        }
        return parent::getValuesHtml();

    }

}
