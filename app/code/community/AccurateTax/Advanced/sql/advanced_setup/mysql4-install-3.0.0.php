<?php
$installer = $this;

$installer->startSetup();

$installer->run("
    ALTER TABLE `{$this->getTable('tax/tax_calculation_rate')}` 
    	ADD `checkAT` TINYINT(1) DEFAULT 1 NOT NULL COMMENT 'Used for AT Module' 
    	AFTER `zip_to`;
    CREATE TABLE `{$this->getTable('advanced/log')}` (
    	`log_id` INT UNSIGNED AUTO_INCREMENT,
    	`store_id` SMALLINT (5) UNSIGNED,
    	`level` VARCHAR (50),
    	`type` VARCHAR (50),
    	`request` TEXT,
    	`result` TEXT,
    	`additional` TEXT,
    	`req_date` DATETIME,
    	PRIMARY KEY(`log_id`)) TYPE = InnoDB;"
);

$installer->endSetup(); 
