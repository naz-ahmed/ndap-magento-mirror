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
interface Elite_Vaf_Model_Schema_Interface
{
    function getLeafLevel();
    
    function getRootLevel();
    
    function getLevels();
    
    /**
    * put your comment there...
    * 
    * @param string $entity_type
    * @return string entity type
    */
    function getNextLevel( $entity_type );
    
    /**
    * put your comment there...
    * 
    * @param string $entity_type
    * @return string entity type
    */
    function getPrevLevel( $entity_type );
}