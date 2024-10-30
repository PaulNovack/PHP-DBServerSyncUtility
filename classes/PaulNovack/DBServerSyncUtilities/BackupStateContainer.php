<?php

namespace PaulNovack\DBServerSyncUtilities;

class BackupStateContainer
{
    public  $database;
    public  $dumpStrategy;
    public $dumpOnlyTables;
    public  $excludeTables;
    private $sqlInterface;
    public  $anytimeTablesNotProcessed;
    public  $completed;
    private $calculateTablesToProcess;

    /**
     * @param string $database
     * @param string $dumpStrategy
     * @param array $dumpOnlyTables
     * @param array $excludeTables
     * @param string $filterValue
     * @param array $filterIds
     * @param array $filterTables
     * @param SQLInterface $sqlInterface
     * @param bool $calculateTablesToProcess
     * @param bool $backingup
     */
    public function __construct(string       $database,
                                string       $dumpStrategy,
                                array        $dumpOnlyTables,
                                array        $excludeTables,
                                string       $filterValue,
                                array        $filterIds,
                                array        $filterTables,
                                SQLInterface $sqlInterface,
                                bool $calculateTablesToProcess = false,
                                bool $backingup = true)
    {
        $this->database = $database;
        $this->dumpStrategy = $dumpStrategy;
        $this->dumpOnlyTables = $dumpOnlyTables;
        $this->excludeTables = $excludeTables;
        $this->filterValue = $filterValue;
        $this->filterIds = $filterIds;
        $this->filterTables = $filterTables;
        $this->sqlInterface = $sqlInterface;
        $this->calculateTablesToProcess = $calculateTablesToProcess;
        $this->backingup = $backingup;
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
        $this->anytimeTablesNotProcessed = $this->GetTablesToProcess($tablesInDB, $this->excludeTables);
    }
    public function GetTablesToProcess(array $source, array $exclude): array
    {
        $tablesToProcess = [];

        foreach ($source as $table) {
            $shouldExclude = false;
            foreach ($exclude as $excluded) {
                if ($table === $excluded) {
                    $shouldExclude = true;
                    break;
                }
                if (strpos($excluded, '*') !== false) {
                    $prefix = rtrim($excluded, '*');
                    if (strpos($table, $prefix) === 0) {
                        $shouldExclude = true;
                        break;
                    }
                }
            }
            if (!$shouldExclude) {
                $tablesToProcess[] = $table;
            }
        }
        return $tablesToProcess;
    }

    public function GetFilterTables(){
        $idx = 0;
        foreach($this->filterTables as $index){
            $this->filterTables[$idx]->getTablesSql = $this->replacePlaceholder($this->filterTables[$idx]->getTablesSql,'filterIds',$this->filterIds);
            $this->filterTables[$idx]->getTablesSql = $this->replacePlaceholder($this->filterTables[$idx]->getTablesSql,'filterValue',$this->filterValue);
            $this->filterTables[$idx]->filterWhere = $this->replacePlaceholder($this->filterTables[$idx]->filterWhere,'filterIds',$this->filterIds);
            $this->filterTables[$idx]->filterWhere = $this->replacePlaceholder($this->filterTables[$idx]->filterWhere,'filterValue',$this->filterValue);
            if($this->filterTables[$idx]->getTablesSql != "" && $this->filterTables[$idx]->getTablesSql != null){
                $filterTables = $this->sqlInterface->getFilterTables($this->database,$this->filterTables[$idx]->getTablesSql);
                $this->filterTables[$idx]->tables = $filterTables;
            }
            $idx++;
        }
    }
    public function replacePlaceholder($string, $placeholder, $replacement) {
        // Escape any special regex characters in the placeholder to avoid issues
        $escapedPlaceholder = preg_quote($placeholder, '/');

        // If the replacement is an array, convert it to a single-quoted, comma-separated string
        if (is_array($replacement)) {
            $replacement = "'" . implode("', '", $replacement) . "'";
        }

        // Use str_replace to replace the placeholder with the replacement value
        $result = str_replace("{" . $escapedPlaceholder . "}", $replacement, $string);

        return $result;
    }
}