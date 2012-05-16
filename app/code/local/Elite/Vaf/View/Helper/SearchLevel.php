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
class Elite_Vaf_View_Helper_SearchLevel
{
    protected $block;
    protected $level;
    protected $prevLevel;
    protected $displayBrTag;
    
    function display( Elite_Vaf_Block_Search $block, $level, $prevLevel = false, $displayBrTag = null )
    {
        $this->displayBrTag = $displayBrTag;
        $this->block = $block;
        $this->level = $level;
        $this->prevLevel = $prevLevel;
        return $this->_display();
    }
    
    protected function _display()
    {
        ob_start();
        if( $this->helper()->showLabels())
        {
            echo '<label>';
            echo $this->__( ucfirst( $this->level ) );
            echo ':</label>';
        }
        
        $prevLevelsIncluding = $this->schema()->getPrevLevelsIncluding($this->level);
        $prevLevelsIncluding = implode(',', $prevLevelsIncluding);
        ?>
        <select name="<?=str_replace(' ','_',$this->level)?>" class="<?=str_replace(' ','_',$this->level)?>Select {prevLevelsIncluding: '<?=$prevLevelsIncluding?>'}">
            <option value="0"><?=$this->__($this->helper()->getDefaultSearchOptionText($this->level))?></option>  
            <?php
            foreach( $this->getEntities() as $entity )
            {   
                ?>
                <option value="<?=$entity->getId()?>" <?=( $this->getSelected( $entity ) ? ' selected="selected"' : '' )?>><?=$entity->getTitle()?></option>
                <?php
            }
            ?>
        </select>
        <?php
        if( $this->displayBrTag() )
        {
            echo '<br />';
        }
        return ob_get_clean();
    }
    
    function schema()
    {
        return new Elite_Vaf_Model_Schema();
    }
    
    /** @return bool */
    function getSelected( $entity )
    {
        $selected = false;
        if( $this->level != $this->leafLevel() )
        {
            return (bool)( $entity->getId() == $this->block->getSelected( $this->level ) );
        }
        $fit = Elite_Vaf_Helper_Data::getInstance()->getFit();
        if( false !== $fit )
        {
            $level = $fit->getLevel( $this->leafLevel() );
            if( $level )
            {
                return (bool)( $entity->getTitle() == $level->getTitle() );
            }
        }
        return false;
    }
    
    protected function getEntities()
    {
        if( $this->prevLevel )
        {
            return $this->block->listEntities( $this->level );
        }
        return $this->block->listEntities( $this->level );
    }
    
    protected function leafLevel()
    {
		return Elite_Vaf_Helper_Data::getInstance()->getLeafLevel();
    }
    
    protected function displayBrTag()
    {
    	if( is_bool($this->displayBrTag))
    	{
			return $this->displayBrTag;
    	}
		return Elite_Vaf_Helper_Data::getInstance()->displayBrTag();
    }
    
    protected function __( $text )
    {
        return $this->block->__( $text );
    }
    
    protected function helper()
    {
        return Elite_Vaf_Helper_Data::getInstance();
    }
}