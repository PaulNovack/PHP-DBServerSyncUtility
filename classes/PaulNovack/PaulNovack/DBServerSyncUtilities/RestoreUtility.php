<?php

namespace PaulNovack\DBServerSyncUtilities;

use PaulNovack\DBServerSyncUtilities;

class RestoreUtility
{
    private  $cl;
    private  $sl;
    private  $si;
    private  $bscs;
    private  $startSeconds; // seconds since epoch
    private  $maxRunTimeSeconds; // diff in start and current seconds that will do shutdown
    private  $currentSeconds; // seconds since epoch

    public function __construct($settingFilename = "Default_SyncJobSettings.json")
    {
        $this->startSeconds = time();  // Get start time in seconds integer...
        $this->maxRunTimeSeconds = 60 * 60 * 23; //Shutdown after 23 hours
        // BE SURE THE CONFIGENUM IS DESTINATION IN THIS AND CHECK PROD DB SETTINGS ARE NOT IN DestinationServerConfig.json !!!!!!
        $this->cl = new ConfigLoader(ConfigEnum::DESTINATION);
        $this->sl = new SettingsLoader($settingFilename);
        $this->si = new SQLInterface($this->cl,$this->sl);
        $this->bscs = $this->sl->getStateContainersArray($this->si,false);
        // Check for Temp Database will cause app to die if it does not exist
        // This means you could accidentally be pointing to prod.
        $this->si->CheckForTempDatabase();
        $destinationServerDatabases = $this->si->getListOfDatabases();
        $settingsConfigDatabases = [];
        foreach($this->bscs as &$db){
            $settingsConfigDatabases[] = $db->database;
            echo $db->database . PHP_EOL;
        }
        $missingDatabases = array_diff($settingsConfigDatabases,$destinationServerDatabases);
        foreach($missingDatabases as $missingDatabase){
            $this->si->createMissingDatabase($missingDatabase);
        }
        $this->doFileSystemWatch();

    }
    public function doFileSystemWatch(){
        while(true){
            foreach($this->bscs as $bsc) {
                $directory =  $this->sl->sqlDirectory . "/" . $bsc->database;
                $sqlFiles = $this->getFilenames($directory);
                if( sizeof($sqlFiles) > 0){
                    // Check if processing if not run in thread up to RestoreThreads at a time
                    $path_parts = pathinfo($sqlFiles[0]);
                    $table = $path_parts['filename'];
                    $lg = new LoadGenerator($this->cl,$this->sl,$bsc);
                    $lg->GenerateLoadCommand($table);
                    $lg->DeleteSQLFile($bsc->database,$table);
                    $this->si->MoveTable($bsc->database,$table);
                }
            }
            echo "sleeping 1 seconds....." . PHP_EOL;
            sleep(1);
            $this->currentSeconds = time();
            if( ($this->currentSeconds - $this->startSeconds) > $this->maxRunTimeSeconds){
                break; // let program exit if it has run over 23 hours cron will restart it reload configs
            }
        }
    }
    private function getFilenames($path){
        $files = [];
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if($fileInfo->isDot()) continue;
            $file =  $fileInfo->getFilename();
            array_push($files,$file);
        }
        return $files;
    }
}