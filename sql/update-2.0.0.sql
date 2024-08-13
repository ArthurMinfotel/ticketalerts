DROP TABLE IF EXISTS `glpi_plugin_ticketalerts_alertgroups`;
CREATE TABLE `glpi_plugin_ticketalerts_alertgroups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
   `entities_id` int(11) NOT NULL default '0',
   `is_recursive` tinyint(1) NOT NULL default '0',
   PRIMARY KEY (`id`),
   KEY `entities_id` (`entities_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_ticketalerts_alertgroupcriterias`;
CREATE TABLE `glpi_plugin_ticketalerts_alertgroupcriterias` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `criteria` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `filter` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `plugin_ticketalerts_alertgroups_id` int(11) NOT NULL default '0',
  `is_recursive` tinyint(1) NOT NULL default '0',
   PRIMARY KEY (`id`),
	KEY `criteria` (`criteria`),
    KEY `plugin_ticketalerts_alertgroups_id` (`plugin_ticketalerts_alertgroups_id`),
    UNIQUE KEY `unicity` (`criteria`,`plugin_ticketalerts_alertgroups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

