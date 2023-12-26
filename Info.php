<?php

return [
    'name'          =>  'Khanza D2W',
    'description'   =>  'Modul SIMRS Khanza desktop ke web',
    'author'        =>  'Basoro',
    'version'       =>  '1.0',
    'compatibility' =>  '4.0.*',
    'icon'          =>  'desktop',
    'install'       =>  function () use ($core) {
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'host', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'database', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'port', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'username', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'password', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'version', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'update_changelog', '')");
      $core->db()->pdo()->exec("INSERT INTO `mlite_settings` (`module`, `field`, `value`) VALUES ('khanza', 'update_version', '')");
    },
    'uninstall'     =>  function() use($core)
    {
      #$core->db()->pdo()->exec("DELETE FROM `settings` WHERE `module` = 'khanza'");
      $core->db()->pdo()->exec("DELETE FROM `mlite_settings` WHERE `module` = 'khanza'");
    }
];
