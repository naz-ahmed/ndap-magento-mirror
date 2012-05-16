<?php
    class AccurateTax_Advanced_Model_Adminhtml_Config extends Mage_Adminhtml_Model_Config {

        protected function _initSectionsAndTabs() {
        	if(Mage::helper('advanced')->isAnyStoreDisabled()) {
	            $mergeConfig = Mage::getModel('core/config_base');
	            $config = Mage::getConfig()->loadModulesConfiguration('system.xml');
	            
	            //these 4 lines are the only added content
	            $configFile = Mage::helper('advanced')->getEtcPath() . DS . 'system-disabled.xml';
	        	$mergeModel = new Mage_Core_Model_Config_Base();
	            $mergeModel->loadFile($configFile);        
	        	$config = $config->extend($mergeModel, true);
	
	            $this->_sections = $config->getNode('sections');        
	            $this->_tabs = $config->getNode('tabs');
        	} else {
        		return parent::_initSectionsAndTabs();
        	}

        	return parent::_initSectionsAndTabs();
        }

    }
