ALTER TABLE civicrm_event ADD is_recur tinyint(4) DEFAULT 0 COMMENT 'if true - allows recurring contributions';
ALTER TABLE civicrm_event ADD recur_frequency_unit varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Supported recurring frequency units.';
ALTER TABLE civicrm_event ADD is_recur_interval tinyint(4) DEFAULT 0 COMMENT 'if true - supports recurring intervals';
ALTER TABLE civicrm_event ADD is_recur_installments tinyint(4) DEFAULT 0 COMMENT 'if true - asks user for recurring installments';
