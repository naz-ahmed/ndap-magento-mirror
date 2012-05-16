<?php
/**
* Wraps Zend_Db_Adapter_Abstract objects, and provides a better wrapper for using insert() method
* 
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
class Elite_Vaf_Model_Db_Adapter_InsertWrapper
{
    /** @var Zend_Db_Adapter_Abstract */
    protected $wrappedAdapter;
    
    function __construct(Zend_Db_Adapter_Abstract $adapterToWrap)
    {
        $this->wrappedAdapter = $adapterToWrap;
    }
    
    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    public function insert($table, array $bind)
    {
        $sql = $this->insertSql($table, $bind);

        // execute the statement and return the number of affected rows
        $stmt = $this->wrappedAdapter->query($sql, array_values($bind));
        $result = $stmt->rowCount();
        return $result;
    }
    
    /**
     * Inserts a table row with specified data.
     *
     * @param mixed $table The table to insert data into.
     * @param array $bind Column-value pairs.
     * @return int The number of affected rows.
     */
    function insertSql($table, array $bind)
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        $this->extractAndQuoteCols($bind,$cols,$vals);

        // build the statement
        $sql = "INSERT INTO "
             . $this->wrappedAdapter->quoteIdentifier($table, true)
             . ' (' . implode(', ', $cols) . ') '
             . 'VALUES (' . implode(', ', $vals) . ')';
        return $sql;
    }
    
    function extractAndQuoteCols($bind, &$cols, &$vals )
    {
        // extract and quote col names from the array keys
        $cols = array();
        $vals = array();
        foreach ($bind as $col => $val) {
            $cols[] = $this->wrappedAdapter->quoteIdentifier($col, true);
            if ($val instanceof Zend_Db_Expr) {
                $vals[] = $val->__toString();
                unset($bind[$col]);
            } else {
                $vals[] = '?';
            }
        }
    }
}