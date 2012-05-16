<?php

class MagicToolbox_MagicThumb_Helper_Settings extends Mage_Core_Helper_Abstract {

    static private $_toolCoreClass = null;
    static private $_scrollCoreClass = null;
    private $_interface;
    private $_theme;
    //private $_skin;
    private $_productViewMediaTemplateFilename;
    private $_productsListTemplateFilename;
    private $_productsNewTemplateFilename;
    private $_magicToolboxListTemplateFilename;

    public function __construct() {

        $designPackage = Mage::getSingleton('core/design_package');
        $this->_interface = $designPackage->getPackageName();
        $this->_theme = $designPackage->getTheme('template');
        //$this->_skin = $designPackage->getTheme('skin');
        $this->_productViewMediaTemplateFilename = $designPackage->getTemplateFilename('catalog'.DS.'product'.DS.'view'.DS.'media.phtml');
        $this->_productsListTemplateFilename = $designPackage->getTemplateFilename('catalog'.DS.'product'.DS.'list.phtml');
        $this->_productsNewTemplateFilename = $designPackage->getTemplateFilename('catalog'.DS.'product'.DS.'new.phtml');
        $this->_magicToolboxListTemplateFilename = $designPackage->getTemplateFilename('magicthumb'.DS.'magictoolbox_list.phtml');

    }

    public function getProductViewMediaTemplateFilename() {

        return $this->_productViewMediaTemplateFilename;

    }

    public function getProductsListTemplateFilename() {

        return $this->_productsListTemplateFilename;

    }

    public function getProductsNewTemplateFilename() {

        return $this->_productsNewTemplateFilename;

    }

    public function getMagicToolboxListTemplateFilename() {

        return $this->_magicToolboxListTemplateFilename;

    }

    public function loadTool($page = 'product') {

        if (null === self::$_toolCoreClass) {

            require_once(BP . str_replace('/', DS, '/app/code/local/MagicToolbox/MagicThumb/core/magicthumb.module.core.class.php'));
            self::$_toolCoreClass = new MagicThumbModuleCoreClass();

            $designPackage = Mage::getSingleton('core/design_package');
            $interface = $designPackage->getPackageName();
            $theme = $designPackage->getTheme('template');

            $coll = Mage::getModel('magicthumb/settings')->getCollection();
            $coll->getSelect()->columns('value')->where('package = ?', $interface)->where('theme = ?', $theme);
            if(!$coll->getSize()) {
                $coll->getSelect()->reset(Zend_Db_Select::WHERE)->where('package = ?', 'all')->where('theme = ?', 'all');
            }
            $params = $coll->getFirstItem()->getValue();
            if(!empty($params)) {
                $params = unserialize($params);
                foreach($params  as $id => $value) {
                    self::$_toolCoreClass->params->params[$id]['value'] = $value;
                }
            }

            /* load locale */

            $mz_m = $this->__('MagicThumb_Message');
            if($mz_m != 'MagicThumb_Message') {
                self::$_toolCoreClass->params->set('message', $mz_m);
            }




            if(self::$_toolCoreClass->type == 'standard' && self::$_toolCoreClass->params->checkValue('magicscroll', 'yes')) {
                require_once(BP . str_replace('/', DS, '/app/code/local/MagicToolbox/MagicThumb/core/magicscroll.module.core.class.php'));
                self::$_scrollCoreClass = new MagicScrollModuleCoreClass();
                self::$_scrollCoreClass->params->appendArray(self::$_toolCoreClass->params->getArray());
                self::$_scrollCoreClass->params->set('direction', self::$_toolCoreClass->params->checkValue('template', array('left', 'right')) ? 'bottom' : 'right');
            }

            require_once(BP . str_replace('/', DS, '/app/code/local/MagicToolbox/MagicThumb/core/magictoolbox.templatehelper.class.php'));
            MagicToolboxTemplateHelper::setPath(dirname(Mage::getSingleton('core/design_package')->getTemplateFilename('magicthumb'.DS.'media.phtml')) . DS . 'templates');
            MagicToolboxTemplateHelper::setOptions(self::$_toolCoreClass->params);

        }

        return self::$_toolCoreClass;
    }

    public function loadScroll($page = 'product') {
        return self::$_scrollCoreClass;
    }

    public function magicToolboxGetSizes($sizeType, $originalSizes = null) {

        $w = self::$_toolCoreClass->params->getValue($sizeType.'-max-width');
        $h = self::$_toolCoreClass->params->getValue($sizeType.'-max-height');

        if(self::$_toolCoreClass->params->checkValue('square-images', 'No')) {
            list($w, $h) = self::calculate_size($originalSizes[0], $originalSizes[1], $w, $h);
        } else {
            $h = $w = min($w, $h);
        }
        return array($w, $h);
    }

    /*public function magicToolboxResizer($product = null, $watermark = 'image', $type = null, $imageFile = null) {
        if($product == null) return false;

        $subdir = 'image';
        $helper = Mage::helper('catalog/image')->init($product, $subdir, $imageFile);
        if($type !== null) {
            $helper->watermark(Mage::getStoreConfig('design/watermark/' . $watermark . '_image'),
                Mage::getStoreConfig('design/watermark/' . $watermark . '_position'),
                Mage::getStoreConfig('design/watermark/' . $watermark . '_size'),
                Mage::getStoreConfig('design/watermark/' . $watermark . '_imageOpacity'));
        }

        $model = Mage::getModel('catalog/product_image');
        $model->setDesctinationSubdir($subdir);
        try {
            if($imageFile == null) {
                $model->setBaseFile($product->getData($subdir));
            } else {
                $model->setBaseFile($imageFile);
            }
        } catch ( Exception $e ) {
            $img = Mage::getDesign()->getSkinUrl() . $helper->getPlaceholder();
            if($type == null) return $img;
            return array($img, $img);
        }

        $img = $helper->__toString();
        if($type == null) return $img;

        $squareImages = false;
        if(self::$_toolCoreClass) {
            if(self::$_toolCoreClass->params->checkValue('square-images', 'Yes')) {
                $squareImages = true;
            }
        }

        $w = self::$_toolCoreClass->params->getValue($type.'-max-width');
        $h = self::$_toolCoreClass->params->getValue($type.'-max-height');

        if(!$squareImages) {
            $size = getimagesize($model->getBaseFile());
            list($w, $h) = self::calculate_size($size[0], $size[1], $w, $h);
        } else {
            $h = $w = min($w, $h);
        }

        $helper->resize($w, $h);
        $thumb = $helper->__toString();
        return array($img, $thumb);
    }*/

    private function calculate_size($originalW, $originalH, $maxW = 0, $maxH = 0) {
        if(!$maxW && !$maxH) {
            return array($originalW, $originalH);
        } elseif(!$maxW) {
            $maxW = ($maxH * $originalW) / $originalH;
        } elseif(!$maxH) {
            $maxH = ($maxW * $originalH) / $originalW;
        }
        $sizeDepends = $originalW/$originalH;
        $placeHolderDepends = $maxW/$maxH;
        if($sizeDepends > $placeHolderDepends) {
            $newW = $maxW;
            $newH = $originalH * ($maxW / $originalW);
        } else {
            $newW = $originalW * ($maxH / $originalH);
            $newH = $maxH;
        }
        return array(round($newW), round($newH));
    }

}
