<?php

namespace PaulNovack\DBServerSyncUtilities;

class SQLInterface
{
    private  $cl;
    private  $sl;
    private  $mysqli;
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
            die("No Temp Database!!");
        }
    }
    public function MoveTable($destDatabase,$table){
        $database = $this->sl->tempDB;
        $this->connectToDB($database);
        $result = $this->mysqli->query('SET foreign_key_checks = 0');
        $query = "drop table if exists " . $destDatabase . ".`" . $table . "`";
        $result = $this->mysqli->query($query);
        echo $query . PHP_EOL;
        try {
            $query = "RENAME TABLE " . $this->sl->tempDB . ".`" . $table . "` TO " . $destDatabase . ".`" . $table . "`";
            $result = $this->mysqli->query($query);
        } catch(\Exception $e){
            echo "Exception " . $e->getMessage();
        }
        $result = $this->mysqli->query('SET foreign_key_checks = 1');
        echo $query . PHP_EOL;
    }
    private function connectToDB($database = null){
        $c = $this->cl;
        if($database == null){
            $this->mysqli = new \mysqli($c->server, $c->user, $c->password);
            if ($this->mysqli->connect_error) {
                die("Connection failed: " . $this->mysqli->connect_error);
            }
            $result = $this->mysqli->query("SHOW DATABASES LIKE '%" . $c->database . "%'");
            if ($result->num_rows == 0) {
                if ($this->mysqli->query("CREATE DATABASE `" . $c->database . "`")) {
                    echo "Database: " . $c->database . " created successfully.";
                } else {
                    die("Error creating database: " . $this->mysqli->error);
                }
            }
            $this->mysqli->select_db($c->database);
            $this->connectedDatabase = $c->database;
        } else {
            $this->mysqli = new \mysqli($c->server, $c->user, $c->password);
            if ($this->mysqli->connect_error) {
                die("Connection failed: " . $this->mysqli->connect_error);
            }
            $result = $this->mysqli->query("SHOW DATABASES LIKE '%" . $database . "%'");
            if ($result->num_rows == 0) {
                if ($this->mysqli->query("CREATE DATABASE `" . $database . "`")) {
                    echo "Database: " . $database . " created successfully.";
                } else {
                    die("Error creating database: " . $this->mysqli->error);
                }
            }
            $this->mysqli->select_db($database);
            $this->connectedDatabase = $database;
        }
    }
    public function getTableNames($database){
        $tables = [];
        $this->connectToDB($database);
        $query = "show tables";
        $result = $this->mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            if(!strstr($row['Tables_in_' . $database],'view')){
                array_push($tables,$row['Tables_in_' . $database]);
            }
        }
        return $tables;
    }
    public function getFilterTables($database = null, string $sql) : array {
        $filterTables = [];
        $this->connectToDB($database);
        $query = $sql;
        echo "Filter Table Query: " . $query;
        $result = $this->mysqli->query($query);
        while($row = $result->fetch_assoc()){
            //echo $row['TABLE_NAME'] . PHP_EOL;
            array_push($filterTables,$row['TABLE_NAME']);
        }
        return $filterTables;
    }
    public function getListOfDatabases($database = null) : array {
        $databases = [];
        $this->connectToDB($database);
        $query = "show databases where `database` not in ('mysql','performance_schema','information_schema');";
        $result = $this->mysqli->query($query);
        while ($row = $result->fetch_assoc()) {
            array_push($databases,$row['Database']);
        }
        $this->databasesOnServer = $databases;
        return $databases;
    }
    public function createMissingDatabase($database){
        $this->connectToDB();
        $query = "create database " . $database;
        $this->mysqli->query($query);
    }
}