ALTER TABLE `glpi_plugin_ticketalerts_alertgroupcriterias` ADD `group_criteria` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'AFFIRMATION';
ALTER TABLE `glpi_plugin_ticketalerts_alertgroupcriterias` ADD `group_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT '1';
ALTER TABLE `glpi_plugin_ticketalerts_alertgroups` ADD `group_criterion` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'OR';


