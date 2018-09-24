<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ConfigurationReader
 *
 * @author patrick
 */
class ConfigurationReader
{
    private $filename;
    private $configs;
    private $csvFilePath;
    private $csvSeparator;
    private $csvQuote;
    private $csvReturnCarriage;

    public function __construct($filename)
    {
            $this->filename = $filename;
            $this->configs = parse_ini_file($filename, true);
            if(empty($this->configs['global']['file_name']))
            {
                    throw new InvalidArgumentException;
            }
            else
            {
                    $this->csvFilePath = $this->configs['global']['file_name'];
            }
            $this->csvSeparator = $this->detectDelimiter($this->csvFilePath);
            $this->csvQuote = empty($this->configs['global']['quote']) ? '"' : $this->configs['global']['quote'];
            $this->csvReturnCarriage = empty($this->configs['global']['return_carriage']) ? "\n" : $this->configs['global']['return_carriage'];
    }	

    public function getFilename()
    {
            return $this->filename;
    }

    public function getCsvFilePath()
    {
            return $this->csvFilePath;
    }

    public function getCsvSeparator()
    {
            return $this->csvSeparator;
    }

    public function getCsvQuote()
    {
            return $this->csvQuote;
    }

    public function getCsvReturnCarriage()
    {
            return $this->csvReturnCarriage;
    }
    
    public function getConfigs()
    {
        $toreturn = $this->configs;
        unset($toreturn['global']);
        return $toreturn;
    }
    
    public function detectDelimiter($csvFile)
    {
            $delimiters = array(
                    ';' => 0,
                    ',' => 0,
                    "\t" => 0,
                    "|" => 0,
                    ' ' => 0
            );

            $handle = fopen($csvFile, "r");
            $firstLine = fgets($handle);
            fclose($handle); 
            foreach ($delimiters as $delimiter => &$count) {
                    $count = count(str_getcsv($firstLine, $delimiter));
            }

            return array_search(max($delimiters), $delimiters);
    }

    public function __toString()
    {
            return json_encode(array("filename" => $this->filename,
                                     "configs" => $this->configs,
                                     "csv_file_path" => $this->csvFilePath,
                                     "csv_separator" => $this->csvSeparator,
                                     "csv_quote" => $this->csvQuote,
                                     "csv_return_carriage" => $this->csvReturnCarriage
                                     ));
    }
}
