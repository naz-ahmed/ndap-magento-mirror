<?php

class MagicToolbox_MagicThumb_Block_Product_New extends Mage_Catalog_Block_Product_New {

    protected function _toHtml() {

        $_productCollection = $this->getProductCollection();

        if($_productCollection && $_productCollection->getSize()) {

            $magicToolboxHelper = Mage::helper('magicthumb/settings');
            $tool = $magicToolboxHelper->loadTool('category');
            if(!$tool->params->checkValue('use-effect-on-category-page', 'No')) {

                $contents = parent::_toHtml();
                require($magicToolboxHelper->getMagicToolboxListTemplateFilename());
                return $contents;

            }

        }

        return parent::_toHtml();
    }

}