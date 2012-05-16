<?php
/**
* Vehicle Fits Free Edition - Copyright (c) 2008-2010 by Ne8, LLC
* PROFESSIONAL IDENTIFICATION:
* "www.vehiclefits.com"
* PROMOTIONAL SLOGAN FOR AUTHOR'S PROFESSIONAL PRACTICE:
* "Automotive Ecommerce Provided By Ne8 llc"
*
* All Rights Reserved
* VEHICLE FITS ATTRIBUTION ASSURANCE LICENSE (adapted from the original OSI license)
* Redistribution and use in source and binary forms, with or without
* modification, are permitted provided that the conditions in license.txt are met
*/
class Elite_Vaf_Admin_DefinitionsController extends Mage_Adminhtml_Controller_Action
{   
    /** @var Zend_Config */
    protected $config;
    
    protected $block;
    
    function indexAction()
    {
        $version = new Elite_Vafinstall_Migrate;
        if( $version->needsUpgrade() )
        {
            echo 'Please run the upgrade-vaf.php script as per the documentation. Your database is out of date.';
            exit();
        }
        
        $this->loadLayout();
        $this->_setActiveMenu('vaf');
        
        $this->block = $this->getLayout()->createBlock('adminhtml/vaf_definitions', 'vaf' );
        $this->doIndexAction();
        $this->_addContent( $this->block );
        $this->renderLayout();
    }     
    
    function doIndexAction()
    { 
        $this->block->setTemplate( 'vaf/entity.phtml' );
        
        $this->block->entity = $this->getEntity();
        
        $this->block->rs = call_user_func_array( array( $this->getEntity(), 'listAll' ), array($this->requestLevels()) );
        $this->block->label = $this->getLabel();
        $this->block->add_url = $this->getAddUrl();
        $this->block->request_levels = $this->requestLevels();
    }
    
    function deleteAction()
    {
    	$version = new Elite_Vafinstall_Migrate;
        if( $version->needsUpgrade() )
        {
            echo 'Please run the upgrade-vaf.php script as per the documentation. Your database is out of date.';
            exit();
        }
        
        $this->loadLayout();
        $this->_setActiveMenu('vaf');
        
        $this->block = $this->getLayout()->createBlock('adminhtml/vaf_definitions', 'vaf' );
        $this->doDeleteAction();
        $this->_addContent( $this->block );
        $this->renderLayout();
	}
    
    function doDeleteAction()
    {
        $id = $this->getRequest()->getParam( 'delete' );    

        $params = $this->requestLevels();
        $params[ $this->getEntity()->getType()] = $id;
        
        foreach($this->schema()->getLevels() as $level)
        {
            if(!isset($params[$level]))
            {
                $params[$level] = 0;
            }
        }
        
        $vehicle = $this->vehicleFinder()->findByLevelIds($params, true);
	if(!count($vehicle))
	{
	    $this->redirectAfterDelete();
            exit();
	}
	
        $vehicle = $vehicle[0];
        
        if( $this->getRequest()->getParam( 'confirm' ) )
        {
            $vehicle->unlink();
	    $this->redirectAfterDelete();
            exit();
        }
        
        $this->block->model = $vehicle->getLevel($this->getEntity()->getType());
        $this->block->setTemplate( 'vaf/delete.phtml' );  
    }

    function redirectAfterDelete()
    {
	header( 'Location:' . $this->getListUrl2($this->getEntity()->getType()) . '?' . http_build_query($this->requestLevels()));
    }
    
    function saveAction()
    {
        $id = (int)$this->getRequest()->getParam( 'save' ); 
        $entity = new Elite_Vaf_Model_Level( $this->getEntity()->getType(), $id ); 
        $entity->setTitle( $this->getRequest()->getParam( 'title' ) );
        $entity->save( $this->requestLevels() ); 
        if($this->getRequest()->isXmlHttpRequest())
        {
            echo $entity->getId();
            exit();
        }
        $this->doSave();
    }
    
    function mergeAction()
    {
        $version = new Elite_Vafinstall_Migrate;
        if( $version->needsUpgrade() )
        {
            echo 'Please run the upgrade-vaf.php script as per the documentation. Your database is out of date.';
            exit();
        }
        
        $this->loadLayout();
        $this->_setActiveMenu('vaf');
        
        $this->block = $this->getLayout()->createBlock('adminhtml/vaf_definitions', 'vaf' );
        $this->block->setTemplate( 'vaf/merge.phtml' );  
        
        if(isset($_POST['master']))
        {
            $master = $this->masterLevel();
            $slave = $this->slaveLevels();
            
            $merge = new Elite_Vaf_Model_Merge($slave, $master);
            $merge->execute();
            
            header('location:' . $this->getListUrl2($_REQUEST['entity']) . http_build_query($this->requestLevels()) );
            exit();             
        }
        
        $this->block->level = $_REQUEST['entity'];
        $this->block->slaveLevels = $this->slaveLevels();
        
        $this->_addContent( $this->block );
        $this->renderLayout();
        
    }
    
    function splitAction()
    {
        $version = new Elite_Vafinstall_Migrate;
        if( $version->needsUpgrade() )
        {
            echo 'Please run the upgrade-vaf.php script as per the documentation. Your database is out of date.';
            exit();
        }
        
        $this->loadLayout();
        $this->_setActiveMenu('vaf');
        
        $this->block = $this->getLayout()->createBlock('adminhtml/vaf_definitions', 'vaf' );
        $this->block->setTemplate( 'vaf/split.phtml' ); 
        
        if(isset($_POST['submit']))
        {
            $titles = explode(',', $_POST['new_titles']);
            $vehicle = $this->vehicleFinder()->findOneByLevelIds($this->requestLevels(), Elite_Vaf_Model_Vehicle_Finder::EXACT_ONLY);
            $split = new Elite_Vaf_Model_Split($vehicle, $_POST['entity'], $titles);
            $split->execute();
            header('location:' . $this->getListUrl2($_REQUEST['entity']) );
            exit();
        }
        
        $params = $this->requestLevels();
        $params[$this->getRequest()->getParam('entity')] = $this->getRequest()->getParam('id');
        $this->block->vehicle = $this->vehicleFinder()->findOneByLevelIds($params, Elite_Vaf_Model_Vehicle_Finder::EXACT_ONLY);
        if(!$this->block->vehicle)
        {
            header('location:' . $this->getListUrl2($_REQUEST['entity']) );
        }
        $this->_addContent( $this->block );
        $this->renderLayout();
        
    }
    
    function productAction()
    {
        $this->loadLayout();
        $this->_setActiveMenu('vaf');
        
        $this->block = $this->getLayout()->createBlock('adminhtml/vaf_definitions', 'vaf' );
        $this->block->setTemplate( 'vaf/product.phtml' ); 
        
        $this->block->products = Elite_Vaf_Helper_Data::getInstance()->getProductIds();
        
        $this->_addContent( $this->block );
        $this->renderLayout();
    }
    
    function levelFinder()
    {
        return new Elite_Vaf_Model_Level_Finder();
    }
    
    function masterLevel()
    {
        $params = $this->requestLevels();
        $params[$_REQUEST['entity']] = $_REQUEST['master'];
        $vehicle = $this->vehicleFinder()->findByLevelIds($params,true);
        $masterLevel = array($_REQUEST['entity'], $vehicle[0]);
        
        return $masterLevel;
    }
    
    function slaveLevels()
    {
        $slaveLevels = array();
        foreach($_POST['selected'] as $selected)
        {
            $params = $this->requestLevels();
            $params[$_REQUEST['entity']] = $selected;
            $vehicle = $this->vehicleFinder()->findByLevelIds($params,true);
            if(!count($vehicle))
            {
                continue;
            }
            array_push($slaveLevels, array($_REQUEST['entity'], $vehicle[0]));
        }
        return $slaveLevels;
    }
    
    protected function doSave()
    {
        header( 'Location:' . $this->getListUrl( $this->getEntity()->getType(), $this->getId() ) );  
        exit();
    }
    
    protected function getListUrl( $entityType, $id )
    {
        $url = Mage::helper('adminhtml')->getUrl('*/*/index', array(
            'entity' => $entityType,
            'id' => $id
        )) . '?' . http_build_query($this->requestLevels());
        return $url;
    }
    
    protected function getListUrl2( $entityType )
    {
        $url = Mage::helper('adminhtml')->getUrl('*/*/index', array(
            'entity' => $entityType
        ));
        return $url;
    }
    
    protected function getAddUrl()
    {
        $url = Mage::helper('adminhtml')->getUrl('*/*/save', array(
            'id' => $this->getCurrentId()
        )) . '?' . http_build_query($this->requestLevels());
        return $url;
    }
    
    function getCurrentId()
    {
        return $this->getRequest()->getParam( 'id' );
    }
    
    protected function getLabel()
    {
        return $this->getEntity()->getLabel();
    }
    
    protected function getType()
    {
        return $this->getEntity()->getType();
    }
    
    protected function getParentTitle()
    {
        if( $this->getId() && $this->getEntity()->getPrevLevel() )
        {
            $entity = new Elite_Vaf_Model_Level( $this->getEntity()->getPrevLevel(), $this->getId() );
            return $entity->getTitle();
        }
    }
    
    protected function hasParentTitle()
    {
        return (bool)$this->getParentTitle();
    }
    
    function getEntity()
    {

        $entity = $this->getRequest()->getParam( 'entity' );
        if( empty( $entity ) )
        {
            $entity = $this->getDefaultLevel();
        }
        return new Elite_Vaf_Model_Level( $entity );
    }
    
    function getId()
    {
        return $this->getRequest()->getParam( 'id', 0 );
    }
    
    function getConfig()
    {
        if( !$this->config instanceof Zend_Config )
        {
            
            $this->config = Elite_Vaf_Helper_Data::getInstance()->getConfig();
        }    
        return $this->config;
    }
    
    function setConfig( Zend_Config $config )
    {
        $this->config = $config;
    }
    
    function getDefaultLevel()
    {
        $schema = new Elite_Vaf_Model_Schema;
        $schema->setConfig( $this->getConfig() );
        return $schema->getRootLevel();
    }
    
    function schema()
    {
        return new Elite_Vaf_Model_Schema();
    }
    
    function requestLevels()
    {
        $params = array();
        foreach($this->schema()->getLevels() as $level)
        {
            if($this->getRequest()->getParam($level))
            {
                $params[$level] = $this->getRequest()->getParam($level);
            }
        }
        return $params;
    }
    
    function vehicleFinder()
    {
        return new Elite_Vaf_Model_Vehicle_Finder(new Elite_Vaf_Model_Schema());
    }
}
