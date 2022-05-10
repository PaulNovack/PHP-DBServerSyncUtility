<?php

ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
include_once dirname(__FILE__) . "/autoload.php";
$lockFileName = '/tmp/' . basename(dirname(__FILE__) . "Backup.lock");

system("touch " . $lockFileName);
$fp = fopen($lockFileName, 'r+');
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    exit;
}

use PaulNovack\DBServerSyncUtilities\BackupUtility;

$settingsFileName = "Default_SyncJobSettings.json";

if(isset($argv[1])){
    $settingsFileName = $argv[1];
}




new BackupUtility($settingsFileName);

fclose($fp);