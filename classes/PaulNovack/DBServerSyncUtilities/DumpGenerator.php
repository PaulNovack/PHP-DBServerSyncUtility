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
     * @param string $extraArgs
     * @return string
     */
    public function GenerateTableCommand(string $table = null)
    {
        $processingFile = $this->sl->dumpSqlDirectory ."/" . $this->bsc->database . "/" . $table . '.sql';
        $doneFile = $this->sl->sqlDirectory ."/" . $this->bsc->database . "/" . $table . '.sql';
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
            . " " . $table . " > " . $this->sl->dumpSqlDirectory ."/" . $this->bsc->database . "/" . $table . '.sql';
        echo $command . PHP_EOL;
        system($command);
        rename($processingFile,$doneFile);
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