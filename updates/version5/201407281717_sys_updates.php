<?php
if (!$updater_utils->does_table_exist('sys_updates')) {
  $sql = <<< QUERY
CREATE TABLE `sys_updates` (
  `name` varchar(255),
  `updated` datetime NOT NULL,
  KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=0;
QUERY;
  $updater_utils->execute_query($sql, true);

}