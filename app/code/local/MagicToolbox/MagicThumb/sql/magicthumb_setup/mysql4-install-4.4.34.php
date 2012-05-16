<?php

$installer = $this;

$installer->startSetup();

$installer->run("

DROP TABLE IF EXISTS {$this->getTable('magicthumb/settings')};
CREATE TABLE {$this->getTable('magicthumb/settings')} (
    `setting_id` int( 11 ) unsigned NOT NULL AUTO_INCREMENT,
    `package` varchar( 255 ) NOT NULL default '',
    `theme` varchar( 255 ) NOT NULL default '',
    `last_edit_time` datetime default NULL,
    `value` text default NULL,
    PRIMARY KEY (`setting_id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8;

INSERT INTO {$this->getTable('magicthumb/settings')} (`setting_id`, `package`, `theme`, `last_edit_time`, `value`) VALUES (NULL, 'all', 'all', NULL, NULL);

");

$installer->endSetup();

?>
