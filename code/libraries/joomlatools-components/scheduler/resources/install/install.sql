CREATE TABLE IF NOT EXISTS `#__scheduler_jobs` (
  `uuid` char(36) NOT NULL,
  `identifier` varchar(255) NOT NULL DEFAULT '',
  `package` varchar(64) NOT NULL DEFAULT '',
  `frequency` varchar(128) NOT NULL DEFAULT '',
  `status` int(11) NOT NULL DEFAULT '0',
  `queue` tinyint(4) NOT NULL DEFAULT '0',
  `ordering` int(11) NOT NULL DEFAULT '0',
  `state` text,
  `modified_on` datetime DEFAULT NULL,
  `completed_on` datetime DEFAULT NULL,
  UNIQUE KEY `name` (`identifier`),
  UNIQUE KEY `uuid` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__scheduler_metadata` (
  `type` varchar(32) NOT NULL DEFAULT '',
  `sleep_until` datetime DEFAULT NULL,
  `last_run` datetime DEFAULT NULL,
  UNIQUE KEY `unique_type` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `#__scheduler_jobs` CHANGE `created_on` `created_on` datetime DEFAULT NULL;
ALTER TABLE `#__scheduler_jobs` CHANGE `modified_on` `modified_on` datetime DEFAULT NULL;

UPDATE `#__scheduler_jobs` SET `created_on` = NULL WHERE `created_on` = 0000-00-00;
UPDATE `#__scheduler_jobs` SET `modified_on` = NULL WHERE `modified_on` = 0000-00-00;

ALTER TABLE `#__scheduler_metadata` CHANGE `sleep_until` `sleep_until` datetime DEFAULT NULL;
ALTER TABLE `#__scheduler_metadata` CHANGE `last_run` `last_run` datetime DEFAULT NULL;

UPDATE `#__scheduler_metadata` SET `sleep_until` = NULL WHERE `sleep_until` = 0000-00-00;
UPDATE `#__scheduler_metadata` SET `last_run` = NULL WHERE `last_run` = 0000-00-00;
