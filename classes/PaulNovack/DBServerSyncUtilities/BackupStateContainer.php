<?php

namespace PaulNovack\DBServerSyncUtilities;

class BackupStateContainer
{
    public  $database;
    public  $dumpStrategy;
    public  $excludeTables;
    public  $dumpOnlyTables;
    public  $waitForConditionTables;
    public  $waitForSqlCondition;
    private $sqlInterface;
    public  $anytimeTablesNotProcessed;
    public  $waitTableNotProcessed;
    public  $completed;
    /**
     * @var false|mixed
     */
    private $calculateTablesToProcess;
    private $processing;

    /**
     * @param string $database
     * @param string $dumpStrategy
     * @param array $excludeTables
     * @param array $dumpOnlyTables
     * @param array $waitForConditionTables
     * @param string|null $waitForSqlCondition
     * @param SQLInterface $sqlInterface
     * @param bool $calculateTablesToProcess
     */
    public function __construct(string       $database,
                                string       $dumpStrategy,
                                array        $excludeTables,
                                array        $dumpOnlyTables,
                                array        $waitForConditionTables,
                                ?string      $waitForSqlCondition,
                                SQLInterface $sqlInterface,
                                bool $calculateTablesToProcess = false)
    {
        $this->database = $database;
        $this->dumpStrategy = $dumpStrategy;
        $this->excludeTables = $excludeTables;
        $this->dumpOnlyTables = $dumpOnlyTables;
        $this->waitForConditionTables = $waitForConditionTables;
        $this->waitForSqlCondition = $waitForSqlCondition;
        $this->sqlInterface = $sqlInterface;
        $this->completed = false;
        $this->calculateTablesToProcess = $calculateTablesToProcess;
        if($this->calculateTablesToProcess){
            $this->CalculateTablesToProcess();
        }
        $this->processing = [];

    }

    public function CalculateTablesToProcess(){
        $tablesInDB = $this->sqlInterface->getTableNames($this->database);
        $this->anytimeTablesNotProcessed = array_diff($tablesInDB, $this->excludeTables);
        $this->anytimeTablesNotProcessed = array_diff($this->anytimeTablesNotProcessed, $this->waitForConditionTables);
        $this->waitTableNotProcessed = $this->waitForConditionTables;
    }
    public function AddToProcessing($DBName,$TableName){

    }
    public function IsProcessing($DBName,$TableName){

    }
    public function MarkAsProcessed($DBName,$TableName){

    }
}