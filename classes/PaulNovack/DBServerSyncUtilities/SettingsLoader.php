<?php

namespace PaulNovack\DBServerSyncUtilities;

class SettingsLoader
{
    public $configFields;
    public  $dumpSqlDirectory;
    public  $sqlDirectory;
    public  $gzDirectory;
    public $tempDB;
    public $mysqlExtraArgs;

    public function __construct($settingsFilename)
    {
        echo dirname(__FILE__,4) . "/" . $settingsFilename . PHP_EOL;
        $this->configFields = json_decode(file_get_contents(dirname(__FILE__,4) . "/" . $settingsFilename));
        $this->makeDirectories();
    }

    private function makeDirectories()
    {
        $home = getenv("HOME");

        $dr = $home . '/' . $this->configFields->directorySettings->dataRoot;
        if(!is_dir($dr))mkdir($dr, 0777);
        $dp = $dr . "/" . $this->configFields->directorySettings->dumpSqlDirectory;
        $this->dumpSqlDirectory = $dp;
        if(!is_dir($dp))mkdir($dp, 0777);
        $ds = $dr . "/" . $this->configFields->directorySettings->sqlDirectory;
        $this->sqlDirectory = $ds;
        if(!is_dir($ds))mkdir($ds, 0777);
        $dz = $dr . "/" . $this->configFields->directorySettings->gzDirectory;
        $this->gzDirectory = $dz;
        if(!is_dir($dz))mkdir($dz, 0777);
        foreach ($this->configFields->databases as $database) {
            if(!is_dir($d = $dp . "/" . $database->database))mkdir($d, 0777);
            if(!is_dir($d = $ds . "/" . $database->database))mkdir($d, 0777);
            if(!is_dir($d = $dz . "/" . $database->database))mkdir($d, 0777);
        }
        $this->tempDB = $this->configFields->directorySettings->tempDatabase;
        $this->mysqlExtraArgs = $this->configFields->mysqlDumpExtraArgs;
        return null;
    }

public function getStateContainersArray(SQLInterface $sqlInterface,$backingup = false) : array
    {
        $backupStateContainersArray = [];
        foreach ($this->configFields->databases as $database) {
            $d = $database;
            $bsc = new BackupStateContainer($d->database,
                            $d->dumpStrategy,
                            $d->excludeTables,
                            $d->filterIds,
                            $d->filterTables,
                            $sqlInterface,
                            $backingup);
            array_push($backupStateContainersArray,$bsc);
        }
        return $backupStateContainersArray;
    }


}