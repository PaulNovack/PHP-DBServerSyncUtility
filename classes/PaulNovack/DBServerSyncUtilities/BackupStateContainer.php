<?php

namespace PaulNovack\DBServerSyncUtilities;

class BackupStateContainer
{
    public  $database;
    public  $dumpStrategy;
    public  $excludeTables;
    private $sqlInterface;
    public  $anytimeTablesNotProcessed;
    public  $completed;
    private $calculateTablesToProcess;

    /**
     * @param string $database
     * @param string $dumpStrategy
     * @param array $excludeTables
     * @param array $filterTables
     * @param SQLInterface $sqlInterface
     * @param bool $calculateTablesToProcess
     * @param bool $backingup
     */
    public function __construct(string       $database,
                                string       $dumpStrategy,
                                array        $excludeTables,
                                array        $filterTables,
                                SQLInterface $sqlInterface,
                                bool $calculateTablesToProcess = false,
                                bool $backingup = true)
    {
        $this->database = $database;
        $this->dumpStrategy = $dumpStrategy;
        $this->excludeTables = $excludeTables;
        $this->filterTables = $filterTables;
        $this->sqlInterface = $sqlInterface;
        $this->completed = false;
        $this->calculateTablesToProcess = $calculateTablesToProcess;
        if($this->calculateTablesToProcess){
            $this->CalculateTablesToProcess();
        }
        if(sizeof($this->filterTables) > 0 && $backingup){
            $this->GetFilterTables();
        }
        $this->processing = [];

    }

    public function CalculateTablesToProcess(){
        $tablesInDB = $this->sqlInterface->getTableNames($this->database);
        $this->anytimeTablesNotProcessed = array_diff($tablesInDB, $this->excludeTables);
    }
    public function GetFilterTables(){
        $filterTables = $this->sqlInterface->getFilterTables($this->database,$this->filterTables[0]->getTablesSql);
        $this->filterTables[0]->tables = $filterTables;
    }
}