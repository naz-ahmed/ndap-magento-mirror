<?php

class Elite_Vaflinks_Block_Cms extends Elite_Vaf_Block_Abstract
{

    function __construct()
    {
        parent::__construct();
        $this->setTemplate('vaflinks/cms.phtml');
    }

    function isEnabled()
    {
        return (bool) $this->getConfig()->directory->cmsEnable;
    }

}