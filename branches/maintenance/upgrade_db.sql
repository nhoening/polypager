--this SQL file contains all the updates that need to be made
--to the database of PolyPager for every version. A new install of
--PolyPager won't need any of these, it's just for upgrading!
--So if you, for example, upgrade from 0.9.0 to the newest, you
--need to execute all the lines "upgrade 0.9.0" up to the latest.
--Please ensure you upgrade chronologically, one version after the
--other

-- 1.0rc12
ALTER TABLE `_sys_fields` CHANGE `embed_in` `embed_in` VARCHAR( 600 ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL 

-- 1.0rc7
ALTER TABLE `_sys_sys` ADD `salt` VARCHAR( 120 ) NOT NULL DEFAULT 'polypager_default_salt' COMMENT 'salt for password encryption';

-- 1.0rc6

ALTER TABLE `_sys_sections` CHANGE `pagename` `pagename` VARCHAR( 120 )  NOT NULL ;
ALTER TABLE `_sys_multipages` CHANGE `name` `name` VARCHAR( 120 ) NOT NULL ;
ALTER TABLE `_sys_singlepages` CHANGE `name` `name` VARCHAR( 120 ) NOT NULL ;

-- 1.0rc5
ALTER TABLE  `_sys_sys` ADD  `whole_site_admin_access` TINYINT( 1 ) NOT NULL DEFAULT  '0' AFTER  `hide_public_popups` ;
ALTER TABLE `_sys_singlepages` ADD `only_admin_access` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `commentable` ;
ALTER TABLE `_sys_multipages` ADD `only_admin_access` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `commentable` ;
ALTER TABLE `_sys_fields` ADD `embed_in` VARCHAR( 140 ) NOT NULL AFTER `not_brief` ;

-- 1.0rc3
ALTER TABLE `_sys_sys` ADD `use_captchas` TINYINT( 1 ) NOT NULL DEFAULT '0' COMMENT 'True if Captchas shall prevent comment spam',
ADD `public_captcha_key` VARCHAR( 50 ) NOT NULL COMMENT 'provided by recaptcha.org',
ADD `private_captcha_key` VARCHAR( 50 ) NOT NULL COMMENT 'provided by recaptcha.org';

-- 1.0rc2
ALTER TABLE `_sys_feed` ADD `public` TINYINT( 1 ) NOT NULL DEFAULT '1' COMMENT 'tells whether the feed should be seen';
ALTER TABLE `_sys_sys` DROP `encoding`;

-- 1.0rc1
UPDATE _sys_sections SET the_group = 'standard' WHERE the_group = '';
ALTER TABLE `_sys_sys` ADD `full_feed` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `feed_amount` ;

ALTER TABLE `_sys_comments` CHANGE `comment` `comment` TEXT NOT NULL ;
ALTER TABLE `_sys_intros` CHANGE `intro` `intro` TEXT NOT NULL ;
ALTER TABLE `_sys_sections` CHANGE `bla` `bla` TEXT NOT NULL ;

UPDATE `_sys_sys` SET `skin` = 'polly' WHERE `skin` = 'default';

-- 0.9.9

ALTER TABLE `_sys_fields` CHANGE `order_index` `order_index` INT( 11 ) NOT NULL DEFAULT '1';
ALTER TABLE `_sys_sections` CHANGE `the_group` `the_group` VARCHAR( 120 ) NOT NULL DEFAULT 'standard';
ALTER TABLE `_sys_sys` ADD `encoding` ENUM( 'utf-8', 'iso-8859-1' ) NOT NULL DEFAULT 'utf-8' AFTER `keywords` ;

-- 0.9.8

ALTER TABLE `_sys_sys` CHANGE `show_public_popups` `hide_public_popups` TINYINT( 1 ) NOT NULL DEFAULT '1';
ALTER TABLE `_sys_multipages` CHANGE `show_labels` `hide_labels` TINYINT( 1 ) NOT NULL DEFAULT '0';

UPDATE `_sys_multipages` SET hide_labels = 2 WHERE hide_labels = 0;
UPDATE `_sys_multipages` SET hide_labels = 0 WHERE hide_labels = 1;
UPDATE `_sys_multipages` SET hide_labels = 1 WHERE hide_labels = 2;
UPDATE `_sys_sys` SET hide_public_popups = 2 WHERE hide_public_popups = 0;
UPDATE `_sys_sys` SET hide_public_popups = 0 WHERE hide_public_popups = 1;
UPDATE `_sys_sys` SET hide_public_popups = 1 WHERE hide_public_popups = 2;

-- 0.9.7
ALTER TABLE `_sys_fields` ADD `foreign_key_to` VARCHAR( 200 ) NOT NULL ,
ADD `on_update` VARCHAR( 20 ) NOT NULL ,
ADD `on_delete` VARCHAR( 20 ) NOT NULL ;

ALTER TABLE `_sys_sys` ADD `gallery_name` VARCHAR( 120 ) NOT NULL ,
ADD `gallery_index` SMALLINT NOT NULL DEFAULT '99';

ALTER TABLE `_sys_fields` ADD `label` VARCHAR( 160 ) NOT NULL AFTER `name` ;
ALTER TABLE `_sys_fields` ADD `order_index` INT( 11 ) NOT NULL DEFAULT '0' AFTER `label` ;

ALTER TABLE `_sys_singlepages` ADD UNIQUE (
`name`
);

ALTER TABLE `_sys_multipages` ADD UNIQUE (
`name`
);

ALTER TABLE `_sys_sys` ADD `show_public_popups` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `submenus_always_on` ;

-- 0.9.6
ALTER TABLE `_sys_sys` DROP `template` 

--upgrade 0.9.5
ALTER TABLE `_sys_comments` CHANGE `comment` `comment` BLOB NOT NULL 

--upgrade 0.9.4
ALTER TABLE `_sys_sys` ADD `template` VARCHAR( 200 ) NOT NULL AFTER `skin` ;
ALTER TABLE `_sys_sys` CHANGE `template` `template` VARCHAR( 200 )  NOT NULL DEFAULT 'default.php'

--upgrade 0.9.2
ALTER TABLE `_sys_sections` DROP `input_time`;
ALTER TABLE `_sys_sections` CHANGE `input_date` `input_date` DATETIME NULL DEFAULT NULL;
ALTER TABLE `_sys_sections` CHANGE `edited_date` `edited_date` DATETIME NULL DEFAULT NULL;

ALTER TABLE `_sys_comments` DROP `insert_time`;
ALTER TABLE `_sys_comments` CHANGE `insert_date` `insert_date` DATETIME NOT NULL DEFAULT '0000-00-00';

CREATE TABLE `_sys_feed` (
	  `pk` int(11) NOT NULL auto_increment,
	  `edited_date` datetime NOT NULL,
	  `title` varchar(255) NOT NULL,
	  `pagename` varchar(120) NOT NULL,
	  `id` int(11) NOT NULL,
	  PRIMARY KEY  (`pk`),
	  KEY `edited_date` (`edited_date`)
	) TYPE=MyISAM ;
	
ALTER TABLE `_sys_multipages` DROP `feed` ;
ALTER TABLE `_sys_singlepages` DROP `feed` ;

--ALTER TABLE `_sys_multipages` CHANGE `commmentable` `commentable` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `_sys_fields` ADD `order_index` TINYINT NOT NULL DEFAULT '0';

ALTER TABLE `_sys_multipages` DROP `taggable`;



--upgrade 0.9.1
ALTER TABLE `_sys_sections` ADD `input_date` DATE NULL AFTER `id`;
ALTER TABLE `_sys_sections` ADD `input_time` TIME NULL AFTER `input_date`;
ALTER TABLE `_sys_sections` ADD `edited_date` DATE NULL AFTER `input_date` ;
ALTER TABLE `_sys_singlepages` ADD `feed` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `commentable` ;
ALTER TABLE `_sys_singlepages` ADD INDEX ( `feed` ) ;

ALTER TABLE `_sys_singlepages` ADD `hide_toc` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `hide_search` ;
ALTER TABLE `_sys_multipages` ADD `hide_toc` TINYINT( 1 ) NOT NULL DEFAULT '1' AFTER `hide_search` ;

ALTER TABLE `_sys_singlepages` ADD `default_group` VARCHAR( 60 ) NOT NULL DEFAULT 'standard';

ALTER TABLE `_sys_multipages` DROP `show_comments` ;


--upgrade 0.9.0
ALTER TABLE `_sys_sys` DROP `colorset` ;
ALTER TABLE `_sys_sys` ADD `submenus_always_on` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `skin` ;
ALTER TABLE `_sys_sections` ADD `the_group` VARCHAR( 120 ) NOT NULL ;
ALTER TABLE `_sys_sections` ADD INDEX ( `the_group` ) ;
ALTER TABLE `_sys_singlepages` ADD `grouplist` VARCHAR( 255 ) NOT NULL ;

