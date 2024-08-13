ALTER TABLE `glpi_plugin_ticketalerts_alertgroupcriterias` ADD `rule_criterion_negation` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'AFFIRMATION';
ALTER TABLE `glpi_plugin_ticketalerts_alerts` ADD `users_id_requester` INT(11) NOT NULL DEFAULT '0';


