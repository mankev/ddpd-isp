<?php

$config_directories = [CONFIG_SYNC_DIRECTORY => '/Users/joelb/Sites/cradev/config'];

$settings['hash_salt'] = 'iRb-5a2jl7aS2e0v6aaCv12FExWyV8F9cRsGpshXE_iQdIfsmso2WnxG0zyBHqQp-JSBUzTP2g';

$settings['update_free_access'] = TRUE;


$databases['default']['default'] = array (
  'database' => 'craddpd',
  'username' => 'postgres',
  'password' => 'postgres',
  'prefix' => '',
  'host' => 'localhost',
  'port' => '5432',
  'namespace' => 'Drupal\\Core\\Database\\Driver\\pgsql',
  'driver' => 'pgsql',
);

if (isset($settings['trusted_host_patterns'])) {
  unset($settings['trusted_host_patterns']);
}

#$config_directories['sync'] = 'sites/default/files/sync';
