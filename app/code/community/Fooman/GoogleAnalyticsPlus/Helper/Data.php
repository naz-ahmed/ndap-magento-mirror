<?php

class Fooman_GoogleAnalyticsPlus_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    const XML_PATH_GOOGLEANALYTICSPLUS_SETTINGS = 'google/analyticsplus/';

    /**
     * Return store config value for key
     *
     * @param   string $key
     * @return  string
     */
    public function getGoogleanalyticsplusStoreConfig ($key, $flag=false)
    {
        $path = self::XML_PATH_GOOGLEANALYTICSPLUS_SETTINGS . $key;
        if ($flag) {
            return Mage::getStoreConfigFlag($path);
        } else {
            return Mage::getStoreConfig($path);
        }
    }

}