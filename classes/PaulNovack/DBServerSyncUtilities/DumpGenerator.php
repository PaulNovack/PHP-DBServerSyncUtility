<?php

namespace PaulNovack\DBServerSyncUtilities;

class DumpGenerator
{
    private  $cl;
    private  $sl;
    private  $bsc;

    public function __construct(ConfigLoader $cl,
                                SettingsLoader $sl,
                                BackupStateContainer $bsc)
    {
        $this->cl = $cl;
        $this->sl = $sl;
        $this->bsc = $bsc;
    }

    /**
     * @param string|null $table
     * @return string
     */
    public function GenerateTableCommand(string $table = null) : void
    {
        $processingFile = $this->sl->dumpSqlDirectory ."/" . $this->bsc->database . "/" . $table . '.sql';
        $doneFile = $this->sl->sqlDirectory ."/" . $this->bsc->database . "/" . $table . '.sql';
        $command =
            $this->cl->mysqldumpBinaryPath . " -h "
            . $this->cl->server
            . " -u"
            . $this->cl->user
            . " -p'"
            . $this->cl->password
            . "' " . implode(" ",$this->sl->mysqlExtraArgs)
            . " "
            . $this->bsc->database
            . " " . $table;
        $idx = 0;
        foreach($this->bsc->filterTables as $index){
            if(in_array($table,$this->bsc->filterTables[$idx]->tables)){
                $command .= " --where='" . $this->bsc->filterTables[$idx]->filterWhere . "'";
            }
            $idx++;
        }
        $command .=" > " . $this->sl->dumpSqlDirectory ."/" . $this->bsc->database . "/" . $table . '.sql';
        $startTime = microtime(true);
        if($this->sl->debug){
            echo $command . PHP_EOL;
        }
        echo "Processing: " . $table . PHP_EOL;
        system($command);
        $endTime = microtime(true);
        $elapsedTime = $endTime - $startTime;
        $formattedTime = number_format($elapsedTime, 2);
        if(file_exists($processingFile)){
            $fileSizeInBytes = filesize($processingFile);
            echo "\033[F";
            echo "Processing: " . $table;
            echo " " . sprintf("%.4f",$fileSizeInBytes / (1024 * 1024)) . " MB ";
            $Checkbox = '✔';
            echo $Checkbox . " Done";
            echo "  Elapsed time: " . $formattedTime . " seconds.";
            rename($processingFile,$doneFile);
        } else {
            echo " Error occurred no dump file created.";
        }
        echo PHP_EOL;
    }
    public function GenerateFullDBCommand()
    {
            $processingFile = $this->sl->dumpSqlDirectory ."/" . $this->bsc->database . "/" . $this->bsc->database . '.sql';
            $doneFile = $this->sl->sqlDirectory ."/" . $this->bsc->database . "/" . $this->bsc->database . '.sql';
            $command =
                $this->cl->mysqldumpBinaryPath . " -h "
                . $this->cl->server
                . " -u"
                . $this->cl->user
                . " -p"
                . $this->cl->password
                . " " . implode(" ",$this->sl->mysqlExtraArgs)
                . " "
                . $this->bsc->database
                . " > " . $this->sl->dumpSqlDirectory ."/" . $this->bsc->database . "/" . $this->bsc->database . '.sql';
                $this->bsc->completed = true;
                echo $command . PHP_EOL;
        system($command);
        rename($processingFile,$doneFile);
    }
}