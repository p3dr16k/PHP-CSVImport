<?php
include 'ConfigurationReader.php';
include 'CSVImporter.php';

$cr = new ConfigurationReader('./example_config.ini');
$csvi = new CSVImporter('127.0.0.1', 'test', 'root', 'root',  $cr);
echo $csvi->import(true,true);
