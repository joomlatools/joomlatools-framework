
CREATE TABLE IF NOT EXISTS `#__files_attachments` (
  `files_attachment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `files_container_id` bigint(20) NOT NULL,
  `uuid` char(36) NOT NULL DEFAULT '',
  `path` varchar(2000) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text,
  `created_by` bigint(20) NOT NULL,
  `created_on` datetime NOT NULL,
  `modified_by` bigint(20) NOT NULL,
  `modified_on` datetime NOT NULL,
  `locked_by` bigint(20) NOT NULL,
  `locked_on` datetime NOT NULL,
  PRIMARY KEY (`files_attachment_id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `files_container_id` (`files_container_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__files_attachments_relations` (
  `files_attachment_id` bigint(20) unsigned NOT NULL,
  `table` varchar(255) NOT NULL DEFAULT '',
  `row` bigint(20) unsigned NOT NULL,
  `created_by` bigint(20) NOT NULL,
  `created_on` datetime NOT NULL,
  PRIMARY KEY (`files_attachment_id`,`table`,`row`),
  CONSTRAINT `#__files_attachments_relations_ibfk_1` FOREIGN KEY (`files_attachment_id`) REFERENCES `#__files_attachments` (`files_attachment_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
