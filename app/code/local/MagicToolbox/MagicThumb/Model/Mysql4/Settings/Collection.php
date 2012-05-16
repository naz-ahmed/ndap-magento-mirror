<?php

class MagicToolbox_MagicThumb_Model_Mysql4_Settings_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    public function _construct() {

        $this->_init('magicthumb/settings');

    }

    protected function _afterLoad() {

        parent::_afterLoad();
        foreach ($this->_items as $item) {
            $package = $item->getData('package');
            $theme = $item->getData('theme');
            if($package == 'all') {
                $item->setData('package_theme', 'Default settings');
            } else {
                $item->setData('package_theme', 'Settings for '.$package.'/'.$theme.' theme');
            }
            $lastEditTime = $item->getData('last_edit_time');
            if(!$lastEditTime) {
                $item->setData('last_edit_time', 'not edited');
            }
        }
        return $this;

    }

}
