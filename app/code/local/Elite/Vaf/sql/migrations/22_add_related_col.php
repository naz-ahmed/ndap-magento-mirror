<?php
class Vaf22
{
    function run()
    {
        $schema = new Elite_Vaf_Model_Schema();
        $db = Elite_Vaf_Helper_Data::getInstance()->getReadAdapter();

        $db->query('ALTER TABLE `elite_mapping` ADD `related` FLOAT NOT NULL  ');
    }
}
Vaf22::run();