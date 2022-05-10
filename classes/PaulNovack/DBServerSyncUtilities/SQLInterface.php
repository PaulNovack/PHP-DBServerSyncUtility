<?php

namespace PaulNovack\DBServerSyncUtilities;

class SQLInterface
{
    private  $cl;
    private  $sl;
    private  $mysqli;
    private  $isSource = true; // Safety no change operations on prod only read.
    private $connectedDatabase = null;
    public  $databasesOnServer;


    public function __construct(ConfigLoader $cl,
                SettingsLoader $sl)
    {
        $this->cl = $cl;
        $this->sl = $sl;
        $this->connectToDB();
    }
    public function CheckForTempDatabase(){
        $database = $this->sl->tempDB;
        $this->connectToDB($database);
        if($this->mysqli->connect_error != ""){
            echo $this->mysqli->connect_error . PHP_EOL;
            die();
        }
    }
    public function MoveTable($destDatabase,$table){
        $database = $this->sl->tempDB;
        $this->connectToDB($database);
        $query = "drop table " . $destDatabase . ".`" . $table . "`";
        $result = $this->mysqli->query($query);
        echo $query . PHP_EOL;
        $query = "RENAME TABLE " . $this->sl->tempDB . ".`"  . $table . "` TO " . $destDatabase . ".`"  . $table . "`";
        $result = $this->mysqli->query($query);
        echo $query . PHP_EOL;
    }
    private function connectToDB($database = null){
        $c = $this->cl;
        if($database == null){
            $this->mysqli = new \mysqli($c->server, $c->user,$c->password,$c->database);
            $this->connectedDatabase = $c->database;
        } else {
            $this->mysqli = new \mysqli($c->server, $c->user,$c->password,$database);
            $this->connectedDatabase = $database;
        }
    }
    public function getTableNames($database){
        $tables = [];
        //if($this->connectedDatabase != $database){
            $this->connectToDB($database);
       // }
        $query = "show tables";
        $result = $this->mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            array_push($tables,$row['Tables_in_' . $database]);
        }
        echo $query . PHP_EOL;
        return $tables;
    }
    public function getListOfDatabases($database = null) : array {
        $databases = [];
        $this->connectToDB($database);
        $query = "show databases where `database` not in ('mysql','performance_schema','information_schema');";
        $result = $this->mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            array_push($databases,$row['Database']);
        }
        echo $query . PHP_EOL;
        $this->databasesOnServer = $databases;
        return $databases;
    }
    public function createMissingDatabase($database){
        $this->connectToDB();
        $query = "create database " . $database;
        $result = $this->mysqli->query($query);
    }

    public function getIsRunnable($database,$waitForSqlCondition){
        //if($this->connectedDatabase != $database){
        $this->connectToDB($database);
        //}
        $query = $waitForSqlCondition;
        echo "GetIsRunnable: " . $database . "||" . $query;
        $result = $this->mysqli->query($query);
        $row = $result->fetch_assoc();
        return (bool)$row['runnable'];
    }

}