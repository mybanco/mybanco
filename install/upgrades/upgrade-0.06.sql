-- -------------------------
--   MyInfo 0.05 -> 0.06
-- -------------------------
--
--   This upgrade allows administers to work on the system, and differentiates
--   them from "normal" users. This is therefor a good upgrade for all to do
--   xD


--
-- Create the new admins table.
--

CREATE TABLE `admins` (
	`uid` INT( 7 ) NOT NULL ,
	`admingroup` INT( 4 ) NOT NULL
) ENGINE = MYISAM ;


--
-- Make the first user (in the installer, "tim") an admin
--
INSERT INTO `admins` (
	`uid` , `admingroup`
) VALUES (
	'1', '-1'
);
--
-- MyBanco 0.02-0.05 to 0.06 upgrade
--
SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

-- -------------------------
--   MyInfo 0.06 [stocks demo]
-- -------------------------
--
--   MyInfo Stocks demo data...
--

--
-- Create the table `stock_tickers`
--
CREATE TABLE IF NOT EXISTS `stock_tickers` (
  `stockid` int(5) NOT NULL auto_increment,
  `ticker` varchar(4) NOT NULL,
  `compayName` varchar(128) NOT NULL,
  `companyDescription` text NOT NULL,
  PRIMARY KEY  (`stockid`),
  KEY `ticker` (`ticker`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; 

--
-- Add the IPO functionality
--
ALTER TABLE `stock_tickers` ADD `finishedIPO` TINYINT( 1 ) NOT NULL AFTER `companyDescription` ;
ALTER TABLE `stock_tickers` ADD INDEX ( `finishedIPO` );

INSERT INTO `stock_tickers` (
	`ticker` , `compayName` ,
	`companyDescription` , `finishedIPO` 
)
VALUES (
	'ABC', 'Another Big Company',
	'Another Big Company Inc. maintains an index of Websites and other online content, and makes this information freely available to anyone with an Internet connection. The Company''s automated search technology helps people obtain nearly instant access to relevant information from its online index', '0'
);

--
-- Add the ability to know how many units can be sold in the IPO
--
ALTER TABLE `stock_tickers` ADD `IPO_offers` INT( 11 ) NOT NULL AFTER `finishedIPO`;
ALTER TABLE `stock_tickers` ADD `IPOprice` DECIMAL( 14, 4 ) NOT NULL AFTER `IPO_offers` ;

UPDATE `stock_tickers` SET `IPO_offers` = '10000' WHERE `stock_tickers`.`stockid` =1 LIMIT 1;
UPDATE `stock_tickers` SET `IPOprice` = '12.0000' WHERE `stock_tickers`.`stockid` =1 LIMIT 1;


--
-- The IPO registration table
--
CREATE TABLE `stock_ipo_registration` (
	`stockid` INT( 5 ) NOT NULL ,
	`userid` INT( 7 ) NOT NULL ,
	`volume` INT( 11 ) NOT NULL ,
	`payWithAccount` INT( 7 ) NOT NULL ,
	INDEX ( `stockid` ),
	INDEX ( `payWithAccount` )
) ENGINE = MYISAM;

--
-- Add tim's first IPO registration
--
INSERT INTO `stock_ipo_registration` (
	`stockid`, `userid` , `volume`, `payWithAccount`
)
VALUES (
	'1', '1', '1000', 1
);

--
-- Add a new ticker which has finished it's IPO, and add some detailed
-- buy/sell data. This will make the first graph/etc start working
--
INSERT INTO `stock_tickers` (
	`ticker` , `compayName` ,
	`companyDescription` ,
	`finishedIPO` , `IPO_offers` , `IPOprice` 
)
VALUES (
	'SDC', 'Super Big Corporation',
	'Super Big Corporation (SBC) is a global Internet brand. The Company''s offerings to users fall into five categories: Front Doors; Search; Communications and Communities; Media, and Connected Life',
	'1', '100', '10.'
);

INSERT INTO `stock_ipo_registration` (
	`stockid` , `userid` , `volume` , `payWithAccount` 
)
VALUES (
	'2', '1', '1000', '1'
);













