DROP TABLE IF EXISTS `glpi_plugin_ticketalerts_alerts`;
CREATE TABLE `glpi_plugin_ticketalerts_alerts`
(
    `id`                                int(11) NOT NULL auto_increment,
    `entities_id`                       int(11) NOT NULL default '0',
    `is_recursive`                      tinyint(1) NOT NULL default '0',
    `name`                              varchar(255) collate utf8mb4_unicode_ci default NULL,
    `plugin_ticketalerts_alerttypes_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_plugin_ticketalerts_alerttypes (id)',
    `tickets_id`                        int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_tickets (id)',
    `users_id_requester`                INT(11) NOT NULL DEFAULT '0',
    `state`                             int(11) NOT NULL DEFAULT '1',
    `alert_date`                        datetime                                default NULL,
    `taking_into_account_date`          datetime                                default NULL,
    `users_id`                          int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_users (id)',
    `comment`                           text collate utf8mb4_unicode_ci,
    `notepad`                           longtext collate utf8mb4_unicode_ci,
    `date_mod`                          datetime                                default NULL,
    `is_deleted`                        tinyint(1) NOT NULL default '0',
    PRIMARY KEY (`id`),
    KEY                                 `name` (`name`),
    KEY                                 `entities_id` (`entities_id`),
    KEY                                 `plugin_ticketalerts_alerttypes_id` (`plugin_ticketalerts_alerttypes_id`),
    KEY                                 `users_id` (`users_id`),
    KEY                                 `tickets_id` (`tickets_id`),
    KEY                                 `alert_date` (`alert_date`),
    KEY                                 `taking_into_account_date` (`taking_into_account_date`),
    KEY                                 `date_mod` (`date_mod`),
    KEY                                 `is_deleted` (`is_deleted`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_ticketalerts_alerttypes`;
CREATE TABLE `glpi_plugin_ticketalerts_alerttypes`
(
    `id`      int(11) NOT NULL AUTO_INCREMENT,
    `name`    varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `comment` text COLLATE utf8mb4_unicode_ci,
    PRIMARY KEY (`id`),
    KEY       `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


DROP TABLE IF EXISTS `glpi_plugin_ticketalerts_configs`;
CREATE TABLE `glpi_plugin_ticketalerts_configs`
(
    `id`    int(11) NOT NULL AUTO_INCREMENT,
    `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_ticketalerts_alertgroups`;
CREATE TABLE `glpi_plugin_ticketalerts_alertgroups`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT,
    `name`             varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `comment`          varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `entities_id`      int(11) NOT NULL default '0',
    `is_recursive`     tinyint(1) NOT NULL default '0',
    `groups_operators` LONGTEXT NULL,
    PRIMARY KEY (`id`),
    KEY                `entities_id` (`entities_id`)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `glpi_plugin_ticketalerts_alertgroupcriterias`;
CREATE TABLE `glpi_plugin_ticketalerts_alertgroupcriterias`
(
    `id`                                 int(11) NOT NULL AUTO_INCREMENT,
    `criteria`                           varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `filter`                             varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `plugin_ticketalerts_alertgroups_id` int(11) NOT NULL default '0',
    `is_recursive`                       tinyint(1) NOT NULL default '0',
    `rule_criterion`                     varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `rule_criterion_negation`            varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'AFFIRMATION',
    `group_criteria`                     varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'AFFIRMATION',
    `group_number`                       varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT '1',
    `rank`                               int unsigned NOT NULL default '0',
    PRIMARY KEY (`id`),
    KEY                                  `criteria` (`criteria`),
    KEY                                  `plugin_ticketalerts_alertgroups_id` (`plugin_ticketalerts_alertgroups_id`),
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `glpi_notificationtemplates` (`name`, `itemtype`, `comment`)
VALUES ('Import error', 'PluginTicketalertsAlert', '');

INSERT INTO `glpi_plugin_ticketalerts_configs`
VALUES (1, '');
