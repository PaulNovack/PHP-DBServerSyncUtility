<?php

namespace PaulNovack\DBServerSyncUtilities;

class ConfigLoader
{
    public  $database;
    public  $password;
    public  $server;
    public  $user;
    public  $mysqldumpBinaryPath;
    public  $mysqlBinaryPath;

    public function __construct($configEnum){

        $configFields = json_decode(file_get_contents(dirname(__FILE__,4) . "/" . $configEnum));
        foreach($configFields as $key => $value){
            $this->{$key} = $value;
        }
        $compiledmysqlDump = "mysqlbinaries/mysqldump";
        $compiledmysql = "mysqlbinaries/mysql";
        if(file_exists($compiledmysqlDump)){
            $this->mysqldumpBinaryPath = $compiledmysqlDump;
        } else {
            $this->mysqldumpBinaryPath = "mysqldump";
        }
        if(file_exists($compiledmysql)){
            $this->mysqlBinaryPath = $compiledmysql;
        } else {
            $this->mysqlBinaryPath = "mysql";
        }
    }
}