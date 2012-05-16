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
class Elite_Vaf_Model_Level_Finder_Updater extends Elite_Vaf_Model_Level_Finder_Abstract implements Elite_Vaf_Model_Level_Saver
{
    /** @var integer id of the model being saved */
    protected $id;
    
    /** @var integer */
    protected $parent_id;
    
    /** @var Elite_Vaf_Model */
    protected $entity;
    
    /**
    * @param Elite_Vaf_Model_Level $entity
    * @return Elite_Vaf_Model_Level_Saver
    */
    function __construct( Elite_Vaf_Model_Level $entity, $parent_id = 0 )
    {
        $this->entity = $entity;
        $this->parent_id = $parent_id;
    }
    
    function save()
    {
        $this->query( $this->getQuery() );
        return $this->entity->getId();
    }
    
    protected function getQuery()
    {
        if( $this->entity->getPrevLevel() )
        {
            return $this->saveLevelWithParent();
        }
        else
        {
            return $this->saveRootLevel();
        }
    }
    
    protected function saveLevelWithParent()
    {
        return sprintf(
            "UPDATE %s SET `title` = %s, %s = %d WHERE id = %d",
            $this->getReadAdapter()->quoteIdentifier( $this->entity->getTable() ),
            $this->getReadAdapter()->quote( $this->entity->getTitle() ),
            $this->getReadAdapter()->quoteIdentifier( $this->entity->getPrevLevel() . '_id' ),
            (int)$this->parent_id,
            (int)$this->entity->getId()
        );   
    }
    
    protected function saveRootLevel()
    {
        return sprintf(
            "UPDATE %s SET `title` = %s WHERE id = %d",
            $this->getReadAdapter()->quoteIdentifier( $this->entity->getTable() ),
            $this->getReadAdapter()->quote( $this->entity->getTitle() ),
            (int)$this->entity->getId()
        );
    }
    
}