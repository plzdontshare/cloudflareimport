<?php

namespace PlzDontShare\CloudFlareImport\FileSystem;

class FileReader
{
    public function readLines($filename)
    {
        $handle = fopen($filename, "r");
    
        while(!feof($handle)) {
            yield trim(fgets($handle));
        }
    
        fclose($handle);
    }
    
    /**
     * @param string $filename
     *
     * @return bool
     */
    public function exists($filename)
    {
        return file_exists($filename);
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