<?php
class Vaf19
{
    function run()
    {
        $schema = new Elite_Vaf_Model_Schema();
        $db = Elite_Vaf_Helper_Data::getInstance()->getReadAdapter();

        $db->query('CREATE TABLE IF NOT EXISTS `elite_product_servicecode` (
  `product_id` int(100) NOT NULL,
  `service_code` varchar(100) NOT NULL,
  PRIMARY KEY (`product_id`,`service_code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;');

        $db->query('ALTER TABLE  `elite_import` ADD  `service_code` VARCHAR( 100 ) NOT NULL');
        $db->query('ALTER TABLE  `elite_definition` ADD  `service_code` VARCHAR( 100 ) NOT NULL');
        $db->query('ALTER TABLE `elite_product_servicecode` ADD `illustration_id` VARCHAR( 10 )  NULL');
        
        $db->query('ALTER TABLE `elite_product_servicecode` ADD `category1_id` INT( 10 ) NOT NULL ,
				ADD `category2_id` INT( 10 ) NOT NULL ,
				ADD `category3_id` INT( 10 ) NOT NULL ,
				ADD `category4_id` INT( 10 ) NOT NULL;');
        
        $db->query('ALTER TABLE  `elite_product_servicecode` ADD  `callout` INT( 3 ) NOT NULL');
          
        
    }
}
Vaf19::run();

