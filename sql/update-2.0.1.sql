ALTER TABLE `glpi_plugin_ticketalerts_alertgroupcriterias` DROP INDEX `unicity`;
ALTER TABLE `glpi_plugin_ticketalerts_alertgroupcriterias` ADD `rule_criterion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL;

