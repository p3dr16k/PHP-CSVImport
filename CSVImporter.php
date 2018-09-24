<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CSVImporter
 *
 * @author patrick
 */
class CSVImporter
{ 
    private $db_host;
    private $db_name;
    private $db_user;
    private $db_pass;
    private $filename;
    private $delimiter;
    private $columns;
    private $cfg;

    public function __construct($db_host, $db_name, $db_user, $db_pass, $cfg)
    {
            $this->db_host = $db_host;
            $this->db_name = $db_name;
            $this->db_user = $db_user;
            $this->db_pass = $db_pass;
            $this->filename = $cfg->getCsvFilePath();
            $this->delimiter = $cfg->getCsvSeparator();
            $this->cfg = $cfg;
    }
    
    public function createTemporaryTableStatement($debug = false)
    {
        $sql = 'CREATE TABLE '.explode('.', $this->filename)[0];
        $sql .= ' (id int not null auto_increment,';
        
        $handle = fopen($this->filename, "r");
        $this->columns = fgets($handle);
        fclose($handle); 
        $header_array = explode($this->delimiter, $this->columns);
        foreach ($header_array as $header)
        {
            $sql .= $header. ' varchar(255),';
        }
        
        $sql .= 'primary key(id));';
        
        if($debug)
        {
            error_log($sql);
        }
        return $sql;        
    }
    
    public function removeTemporaryTableStatement($debug = false)
    {
        $sql = 'drop table '.explode('.', $this->filename)[0].';';
        if($debug)
        {
            error_log($sql);
        }
        
        return $sql;
    }
    
    public function createTemporaryTable($debug = false)
    {
        $createStatement = $this->createTemporaryTableStatement($debug);
        $this->launchQuery($createStatement);
    }
    
    public function dropTemporaryTable($debug = false)
    {
        $dropStatement = $this->removeTemporaryTableStatement($debug);
        $this->launchQuery($dropStatement);
    }
    
    public function importTemporaryTable($debug = false)
    {   
        $exit_code = -1;
        $output = array();
        $import_cmd = 'mysqlimport -u '.$this->db_user.
                ' -p'.$this->db_pass.                
                ' --host '.$this->db_host.
                ' --fields-terminated-by="'.$this->delimiter.'"'.
                ' --lines-terminated-by="'.$this->cfg->getCsvReturnCarriage().'"'.
                ' --columns='.str_replace($this->delimiter, ',', trim($this->columns)).
                ' --ignore-lines=1 --local '.$this->db_name.' '.$this->filename.
                ' 2> /dev/null';    
        exec($import_cmd, $output, $exit_code);
        
        if($debug)
        {
            error_log('Execute: '.$import_cmd);
            print_r($output);
        }
        return $exit_code;
    }
    
    public function dispatchData($ignore, $debug = false)
    {
        $cfg = $this->cfg->getConfigs();
        $param = $ignore ? 'IGNORE' : '';
        foreach($cfg as $k => $rules)
        {
            //Manca remap campi
            $query = 'INSERT '.$param.' INTO '.$k.
                    // '('.trim($this->columns).') '.$rules['rules']; 
                    ' '.$rules['rules'];
            if($debug)
            {
                error_log('Launching query: '.$query."\n");
            }
            $this->launchQuery($query);
        }
    }
    
    public function import($ignore, $debug = false)
    {
        $this->createTemporaryTable($debug);
        $this->importTemporaryTable($debug);
        $this->dispatchData($ignore, $debug);
        $this->dropTemporaryTable($debug);
    }
    
    private function launchQuery($query)
    {
        $connection = new mysqli($this->db_host, $this->db_user, $this->db_pass,$this->db_name);
        if ($connection->connect_error)
        {
            die("Connection failed: " . $connection->connect_error."\n");
        } 
        if(!$connection->query($query))
        {
            die("DB Connection failed\n");
        }
        $connection->close();
    }
}