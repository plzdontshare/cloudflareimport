<?php

namespace PlzDontShare\CloudFlareImport\FileSystem;

class FileWriter
{
    public function saveCSV($path, array $data)
    {
        $fp = fopen($path, 'a+');
        
        foreach ($data as $row) {
            fputcsv($fp, $row, ",");
        }
        
        fclose($fp);
    }
    
    /**
     * @param string $filename
     *
     * @return string
     */
    public function makeFullPath($filename)
    {
        return ROOT_PATH . '/' . rtrim($filename, '/');
    }
}