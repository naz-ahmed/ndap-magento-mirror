<?php

class MagicToolbox_MagicThumb_Model_Observer {

    public function __construct() {

    }

    public function checkForMagic360Product($observer) {
            $event = $observer->getEvent();
            $product = $event->getProduct();
            $id = $product->getId();
            $gallery = $product->getMediaGalleryImages();

            $helper = Mage::helper('magicthumb/settings');
            if((!method_exists($helper, 'isModuleOutputEnabled') && !Mage::getStoreConfigFlag('advanced/modules_disable_output/MagicToolbox_MagicThumb')) || $helper->isModuleOutputEnabled()) {
                $tool = $helper->loadTool('product');
                if($tool->enabled($gallery->getItems(), $id)) {
                    Mage::register('magic360ClassName', 'magicthumb');
                } else {
                    Mage::register('magic360ClassName', false);
                }
            }
        }

    }

?>