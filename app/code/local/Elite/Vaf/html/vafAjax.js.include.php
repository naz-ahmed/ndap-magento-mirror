jQuery.noConflict();
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
$front = (bool)( isset( $_GET['front'] ) && $_GET['front'] );
$command = $front ? 'getLevels' : 'getLevels';
$schema = new Elite_Vaf_Model_Schema;


$CONFIG['unavailableSelections'] = isset( $_GET['unavailableSelections'] ) ? $_GET['unavailableSelections'] : Mage::helper('vaf')->getConfig()->search->unavailableSelections;
$CONFIG['loadingStrategy'] = isset( $_GET['loadingStrategy'] ) ? $_GET['loadingStrategy'] : Mage::helper('vaf')->getConfig()->search->loadingStrategy;

if( isset( $_GET['leafFirst']) && $_GET['leafFirst'])
{
	Elite_Vaf_Helper_Data::getInstance()->getConfig()->search->leafLevelFirst = true;
}

if( $front )
{
    function shouldAutoSubmit()
    {
        return !Mage::helper('vaf')->showSearchButton();
    }
}
else
{    /* 
    function shouldAutoSubmit()
    {
        return false;
    }
    
   
    
    jQuery(document).ready( function() {
        jQuery( '.vafDeleteSelected' ).click( function() {
            jQuery( '.vafcheck:checked' ).each( function() {
                jQuery( this ).nextAll( '.multiTree-closeLink' ).click();
            });
        });
        
        toggleVafFits = function()
        {
            var val = jQuery( '#universal' ).attr( 'checked' );
            if( val == true ) {
                jQuery( '#vaf-toggle' ).hide();
            } else {
                jQuery( '#vaf-toggle' ).show();
            }
        }
        
        jQuery( '#universal' ).click( toggleVafFits );
        toggleVafFits();
        
        jQuery( '.vafCheckAll' ).click( function() {
            jQuery( '.vafcheck' ).attr( 'checked', jQuery(this).attr( 'checked' ) );
        })
    });
    
    <?php
   // require_once( 'multiTree.js.include.php' );
   // Mage::log('Andrewz log: multiTree.js.include.php was just called in vafAjax.js.include.php'); */
   //Mage::log('Andrewz log: *bang head on desk* ....pause.... are there errors below?  $front = '.$front);
}



class VafJs
{
    protected $CONFIG, $schema, $front;
    public $decorators;
      
    function main( $CONFIG, $schema, $front )
    {
        $this->CONFIG = $CONFIG;
        $this->schema = $schema;
        $this->front = $front;
        
        $content = '';
        foreach( $this->decorators as $decorator )
        {
            $content = $decorator->main( $content, $this );
        }
        echo $content;
    }  
    
    function getConfig()
    {
        return $this->CONFIG;
    } 
    
    function getSchema()
    {
        return $this->schema;
    }
    
    function isFront()
    {
        return $this->front;
    }
}

interface VafJs_Decorator
{
    function main( $content, $main );
}     

class VafJs_Docready implements VafJs_Decorator
{
    function main( $content, $main )
    {
        ob_start();
        include( dirname(__FILE__).'/Js/docready.js.php' );
        return ob_get_clean();
    }
}     

class VafJs_Ucfirst implements VafJs_Decorator
{
    function main( $content, $main )
    {
        return file_get_contents(dirname(__FILE__).'/Js/ucfirst.js') . $content;
    }
} 

class VafJs_UnavailableSelections implements VafJs_Decorator
{
    function main( $content, $main )
    {
        $CONFIG = $main->getConfig(); 
        $mode = $CONFIG['unavailableSelections'];
        $schema = $main->getSchema();
        $levelsExceptRoot = $schema->getLevelsExceptRoot();
        ob_start();
        include( dirname(__FILE__).'/Js/unavailable-selections.js.php' ); 
        return ob_get_clean();
    }
}

class VafJs_Callbacks implements VafJs_Decorator
{
    function main( $content, $main )
    {
        $CONFIG = $main->getConfig(); 
        $schema = $main->getSchema();
        $levels = $schema->getLevels();
        ob_start();
        include( dirname(__FILE__).'/Js/callbacks.js.php' ); 
        return ob_get_clean();
    }
}

class VafJs_Submits implements VafJs_Decorator
{
    function main( $content, $main )
    {
        $levels = $main->getSchema()->getLevels();
        $c = count( $levels );
        ob_start();
        include( dirname(__FILE__).'/Js/submits.js.php' ); 
        return ob_get_clean();
    }
}  

class VafJs_Loader_Ajax implements VafJs_Decorator
{
    function main( $content, $main )
    {
        $levels = $main->getSchema()->getLevels();
        $leafLevel = $main->getSchema()->getLeafLevel();
        
        $c = count( $levels );
        ob_start();
        include( dirname(__FILE__).'/Js/loader_ajax.js.php' ); 
        return ob_get_clean();
    }
    
    protected function loadingText()
    {
        return Elite_Vaf_Helper_Data::getInstance()->getLoadingText();
    }
}     

class VafJs_Loader_Offline implements VafJs_Decorator
{
    function main( $content, $main )
    {
        $levels = $main->getSchema()->getLevels();
        $schema = $main->getSchema();
        $leafLevel = $schema->getLeafLevel();
        $c = count( $levels );
        ob_start();
        include( dirname(__FILE__).'/Js/loader_offline.js.php' ); 
        return ob_get_clean();
    }
    
    protected function leafFirst()
    {
        return Elite_Vaf_Helper_Data::getInstance()->getConfig()->search->leafLevelFirst;
    }
    
    protected function loadingText()
    {
        return Elite_Vaf_Helper_Data::getInstance()->getLoadingText();
    }
}     

class VafJs_Default implements VafJs_Decorator
{
    
    function main( $content, $main )
    {
        ob_start();
        
        include( dirname(__FILE__).'/Js/default.js.php' ); 
        
        $html = ob_get_clean();
        return $content . $html;
    }
    
    protected function leafLevel()
    {
        return Elite_Vaf_Helper_Data::getInstance()->getLeafLevel();
    }
}


$vafJs = new VafJs;
$vafJs->decorators = array(
    new VafJs_Ucfirst(),
    new VafJs_UnavailableSelections(),
    new VafJs_Default,
    new VafJs_DocReady()
);
$vafJs->main( $CONFIG, $schema, $front );