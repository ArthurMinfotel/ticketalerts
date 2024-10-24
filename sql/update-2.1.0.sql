ALTER TABLE glpi_plugin_ticketalerts_alertgroups ADD groups_operators LONGTEXT NULL;

ALTER TABLE `glpi_plugin_ticketalerts_alertgroupcriterias` ADD `rank` INT UNSIGNED NULL DEFAULT '0';
