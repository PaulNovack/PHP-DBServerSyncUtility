<?php

namespace PaulNovack\DBServerSyncUtilities;

class LoadGenerator
{
    private  $cl;
    private  $sl;
    private  $bsc;

    /**
     * @param ConfigLoader $cl
     * @param SettingsLoader $sl
     * @param BackupStateContainer $bsc
     */
    public function __construct(ConfigLoader         $cl,
                                SettingsLoader       $sl,
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
    public function GenerateLoadCommand(string $table) : void
    {
          $command =
            $this->cl->mysqlBinaryPath . " -h "
            . $this->cl->server
            . " -u"
            . $this->cl->user
            . " -p'"
            . $this->cl->password
            . "' "
            . $this->sl->tempDB
            . " < " . $this->sl->sqlDirectory ."/" . $this->bsc->database . "/" . $table . '.sql';
        echo $command . PHP_EOL;
        system($command);

    }


    /**
     * @param string $database
     * @param string $table
     * @return void
     */
    public function DeleteSQLFile(string $database, string $table) :void
    {
        $file =  $this->sl->sqlDirectory
            . "/"
            . $database
            . "/"
            . $table
            . ".sql";
        echo "unlink " . $file . PHP_EOL;
        unlink($file);
    }
}