
--
-- Table structure for table `ips_search`
--

CREATE TABLE IF NOT EXISTS `ips_search` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `port` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `proxy_user` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `proxy_pass` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `usage_count` bigint(20) NOT NULL DEFAULT '0',
  `banned` int(14) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;

--
-- Dumping data for table `ips_search`
-- Substitute these IPs with your own IPs (that are configured as outgoing network interfaces or proxies) !!
-- Add port, proxy username, and proxy password for each IP, if needed.
-- Add more IPs by copying the format and adding more lines in the INSERT command below!
--

INSERT INTO `ips_search` (`ip`, `port`, `proxy_user`, `proxy_pass`, `usage_count`, `banned`) VALUES
('123.123.123.11', '', '', '', 0, 0),
('123.123.123.12', '', '', '', 0, 0),
('123.123.123.13', '', '', '', 0, 0),
('123.123.123.14', '', '', '', 0, 0),
('123.123.123.15', '', '', '', 0, 0),
('123.123.123.16', '', '', '', 0, 0),
('123.123.123.17', '', '', '', 0, 0),
('123.123.123.18', '', '', '', 0, 0),
('123.123.123.19', '', '', '', 0, 0),
('123.123.123.20', '', '', '', 0, 0);
