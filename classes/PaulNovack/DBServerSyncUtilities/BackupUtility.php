<?php

namespace PaulNovack\DBServerSyncUtilities;

use PaulNovack\DBServerSyncUtilities;

class BackupUtility
{
    private  $cl;
    private  $sl;
    private  $si;
    private  $bscs;


    public function __construct($settingFilename = "Default_SyncJobSettings.json")
    {
        $this->startSeconds = time();  // Get start time in seconds integer...
        $this->maxRunTimeSeconds = 60 * 60 * 23; //Shutdown after 23 hours
        $this->cl = new ConfigLoader(ConfigEnum::SOURCE);
        $this->sl = new SettingsLoader($settingFilename);
        $this->si = new SQLInterface($this->cl,$this->sl);
        $this->bscs = $this->sl->getStateContainersArray($this->si,true);

        $remainingWork = true;
        while($remainingWork == true){
            $remainingWork = false;
            foreach($this->bscs as &$backup){
                echo $backup->database . PHP_EOL;
                $dg = new DumpGenerator($this->cl,$this->sl,$backup);
                if($backup->dumpStrategy == "FULL"){
                    $dg->GenerateFullDBCommand();
                    $backup->completed = true;
                } else {
                    foreach($backup->anytimeTablesNotProcessed as $table){
                            $dg->GenerateTableCommand($table);
                        if (($key = array_search($table, $backup->anytimeTablesNotProcessed)) !== false) {
                            unset($backup->anytimeTablesNotProcessed[$key]);
                        }
                    }
                    if(sizeof($backup->anytimeTablesNotProcessed) == 0){
                        $backup->completed = true;
                    } else {
                        $remainingWork = true;
                    }
                }
            }
            if($remainingWork == true){
                sleep(300); // sleep 5 minutes if there is remaining work must be waiting for wait condition
            }

        }
    }
}